import { execFileSync } from 'node:child_process';
import { join } from 'node:path';
import { expect, test, type Page } from '@playwright/test';

// Japanese guest booking flow (i18n phase 2B). LOCAL ONLY: web :3138, CMS :8098 on the clone green_ruby_i18n_jae2e
// through tests/e2e/cms-router.php (every payment method = tests/Support/FakePaymentGateway.php: no network, no money).
// Test data: EN cruise 3, itinerary 3 departs 2027-02-15 / 2027-02-22; Imperial Suite = EN cabin 24 (USD 700 for 1-3
// guests on the 3-day voyage). Guests are e2e-ja-*@example.test. Every visited page must log no console error.

const DB = 'green_ruby_i18n_jae2e';
const CMS_SCRIPT = join(__dirname, 'ja-cms.sh');
const PREFILL = '?cruise_id=3&itinerary_id=3&start_at=2027-02-15&cabin_id=24';

test.describe.configure({ mode: 'serial' });

function sql(query: string): string {
  if (DB !== 'green_ruby_i18n_jae2e' || !DB.startsWith('green_ruby_i18n_')) throw new Error('E2E: local green_ruby_i18n_jae2e clone only');
  return execFileSync('mysql', ['-uroot', '-N', '-B', DB, '-e', query]).toString().trim();
}
const row = (email: string) => {
  const [code, fullName, phone, currency, locale, total, status] = sql(
    `select code, full_name, phone, currency, ifnull(locale,'NULL'), total_amount, status from bookings where email = '${email}' order by id desc limit 1`,
  ).split('\t');
  return { code, fullName, phone, currency, locale, total: Number(total), status };
};
const paymentOf = (code: string) => sql(`select p.status from payments p join bookings b on b.payment_id = p.id where b.code = '${code}'`);

let mode = '';
/**
 * (Re)starts the CMS with these payment locales and fake gateway outcome; skipped when already in that mode.
 * termsVersion: BOOKING_TERMS_VERSION for scenario 6 only (a booking-purpose payment link needs a configured version).
 */
function cms(locales: string, outcome = 'succeeded', termsVersion = '') {
  const next = `${locales}|${outcome}|${termsVersion}`;
  if (mode === next) return;
  const env = { ...process.env, E2E_FAKE_PAYMENT_OUTCOME: outcome, ...(termsVersion ? { BOOKING_TERMS_VERSION: termsVersion } : {}) };
  execFileSync('zsh', [CMS_SCRIPT, DB, locales], { stdio: 'ignore', env });
  mode = next;
}

/** A real payment link (PaymentLink::token, booking purpose) for the latest Japanese booking on the clone. */
function jaPaymentLink(): { code: string; token: string } {
  const safe = join(__dirname, '..', '..', '..', 'greenruby-web', 'scripts', 'i18n', 'cms', 'safe-env.sh');
  const out = execFileSync(safe, ['php', 'artisan', 'tinker', '--execute',
    'if (config("database.connections.mysql.database") !== "green_ruby_i18n_jae2e") exit(1); $b = App\\Models\\Booking::where("locale", "ja")->whereNull("payment_status")->orWhere("payment_status", "unpaid")->where("locale", "ja")->latest("id")->first(); echo $b->code, " ", App\\Payments\\PaymentLink::token($b, App\\Payments\\PaymentLink::BOOKING, now()->addDays(3)->getTimestamp());'],
  { cwd: join(__dirname, '..', '..'), env: { ...process.env, I18N_DB: DB } }).toString().trim().split('\n').pop()!;
  const [code, token] = out.split(' ');
  return { code, token };
}

// Console errors fail the test. Exceptions, each tracked separately and reported:
//  - media (/upload, /thumbnail): served by Nginx in production, not by the local `next start` (environment only);
//    the browser's matching "Failed to load resource" console lines are then ignored too;
//  - 4xx answers of /api/* that the page handles by design (e.g. the 422 guard, an invalid payment link).
// Any other failed response, any 5xx and any other console error or page exception is an error.
const errors: string[] = [];
const ignored: string[] = [];
test.beforeEach(async ({ page }) => {
  errors.length = 0;
  let handled404 = 0;
  page.on('response', (res) => {
    if (res.status() < 400) return;
    const path = new URL(res.url()).pathname;
    const line = `${res.status()} ${res.request().method()} ${res.url()}`;
    if (/^\/(upload|thumbnail)\//.test(path) || (path.startsWith('/api/') && res.status() < 500)) {
      handled404++;
      ignored.push(line);
    } else errors.push(line);
  });
  page.on('console', (msg) => {
    if (msg.type() !== 'error') return;
    if (/^Failed to load resource: the server responded with a status of 4\d\d/.test(msg.text()) && handled404-- > 0) return;
    errors.push(`${page.url()} console: ${msg.text()}`);
  });
  page.on('pageerror', (err) => errors.push(`${page.url()} pageerror: ${err.message}`));
});
test.afterEach(async () => {
  expect(errors, 'browser console errors').toEqual([]);
});

const email = (tag: string) => `e2e-ja-${tag}-${Date.now()}@example.test`;

/** Step 1 with the prefill: Imperial Suite added with 2 adults. */
async function stepOne(page: Page, path: string, heading: RegExp) {
  await page.goto(path + PREFILL);
  await expect(page.locator('#booking-date')).toHaveValue('2027-02-15');
  await expect(page.locator('#booking-voyage')).toHaveValue('3-3');
  await expect(page.getByRole('heading', { name: heading })).toBeVisible();
  // Cabins load after the voyage: wait for the prefilled Imperial Suite before continuing.
  await expect(page.getByText(path.startsWith('/ja') ? '選択中の客室（1）' : 'In-voyage cabins (1)')).toBeVisible();
}

async function guestDetails(page: Page, g: { name: string; phone: string; email: string }, next: RegExp) {
  await page.locator('#guest-fullName').fill(g.name);
  await page.locator('#guest-email').fill(g.email);
  await page.locator('#guest-phone').fill(g.phone);
  await page.getByRole('button', { name: next }).click();
}

/** The language switcher (header; on phones inside the menu). */
async function switchTo(page: Page, lang: 'en' | 'vi' | 'ja') {
  let link = page.locator(`a[hreflang="${lang}"]`).filter({ visible: true }).first();
  if (!(await link.count())) {
    const summary = page.locator('details summary[title]').filter({ visible: true }).first();
    if (await summary.count()) await summary.click();
    else await page.getByRole('button', { name: /メニュー|Menu|menu/i }).first().click();
    link = page.locator(`a[hreflang="${lang}"]`).filter({ visible: true }).first();
  }
  if (!(await link.isVisible())) await page.locator('details summary').filter({ visible: true }).first().click();
  await link.click();
}

// ------------------------------------------------------------------------------------------------------------------
test('1. request path (ja request-only): Japanese guest, US$, 大人2名, 2027年2月15日, request copy, DB row', async ({ page }) => {
  cms('en,vi');
  for (const phone of ['+81 90-1234-5678', '０９０－１２３４－５６７８']) {
    const mail = email('req');
    await stepOne(page, '/ja/booking', /ご希望に合わせた/);
    await expect(page.locator('#booking-date option:checked')).toHaveText('2027年2月15日');
    // Item links of the English inventory go to the English pages (fix made during this E2E: needs the rebuilt web).
    await expect.soft(page.getByRole('link', { name: /詳しく見る.*Imperial Suite/ }), 'cabin link (rebuilt web)').toHaveAttribute('href', '/cabin/imperial-suite-24.html', { timeout: 5_000 });
    await expect.soft(page.locator('a[href*="/itinerary/"]').filter({ hasText: '詳しく見る' }).first(), 'voyage link (rebuilt web)')
      .toHaveAttribute('href', '/itinerary/the-halong-immersion-3-days-2-nights-3-3.html', { timeout: 5_000 });
    await expect(page.getByText('選択中の客室（1）')).toBeVisible();
    await expect(page.getByText('US$700.00〜').first()).toBeVisible();
    await expect(page.getByText('1室あたり').first()).toBeVisible();
    await page.getByRole('button', { name: /お客様情報の入力へ/ }).click();

    await expect(page.getByRole('region').getByRole('link', { name: 'プライバシーポリシー（英語版）' })).toBeVisible();
    await guestDetails(page, { name: '山田 太郎（ヤマダ タロウ）', phone, email: mail }, /^次へ/);

    // Step 3: the reservation request only.
    await expect(page.getByText('担当者に相談')).toBeVisible();
    await expect(page.getByText('オンライン決済', { exact: true })).toHaveCount(0);
    await expect(page.locator('input[name="payment-method"]')).toHaveCount(0);
    await expect(page.getByText(/現在、日本語ページからのオンライン決済はご利用いただけません/)).toBeVisible();
    await page.getByRole('button', { name: /内容を確認する/ }).click();

    // Step 4: review.
    const review = page.locator('dl').filter({ hasText: '合計（概算）' });
    await expect(review).toContainText('2027年2月15日');
    await expect(review).toContainText('Imperial Suite（大人2名）– US$700.00');
    await expect(review).toContainText('US$700.00');
    await expect(page.getByText('合計（概算）').first()).toBeVisible();
    await page.getByRole('button', { name: /予約リクエストを送信/ }).click();

    await expect(page.getByRole('heading', { name: '予約リクエストを受け付けました' })).toBeVisible();
    await expect(page.getByText(/まだご予約は確定しておらず/)).toBeVisible();
    await expect(page.getByText('お支払いが完了しました')).toHaveCount(0);
    await expect(page.getByText(/予約番号：/)).toBeVisible();

    const b = row(mail);
    expect(b).toMatchObject({ fullName: '山田 太郎（ヤマダ タロウ）', currency: 'usd', locale: 'ja', total: 70000 });
    expect(b.phone).toBe(phone.startsWith('+') ? '+81 90-1234-5678' : '090-1234-5678');
    expect(paymentOf(b.code)).toBe('pending');
    await expect(page.getByText(b.code).first()).toBeVisible();
  }
});

// ------------------------------------------------------------------------------------------------------------------
test('2. server guard: payment init from ja → 422 payment_locale_not_allowed; en passes the guard', async ({ request }) => {
  cms('en,vi');
  const body = (locale: string, mail: string) => ({
    method: 'stripe',
    amount: 700,
    return_base: 'http://127.0.0.1:3138/ja/booking',
    locale,
    booking: {
      departure_date: '2027-02-15', itinerary_id: '3-3', itinerary_name: 'The Halong Immersion', cruise_name: 'Green Ruby 1',
      itinerary_duration_label: '2泊3日', destination: 'Luon Cave', guests_total: 2, full_name: '山田 太郎', email: mail, phone: '+81 90-1234-5678',
      subtotal_cabins: 700, subtotal_amenities: 0, total_amount: 700,
      cabins: [{ cabin_id: 24, cabin_name: 'Imperial Suite', cabin_description: '', unit_price: 700, quantity: 1, adults: 2, children_6_12: 0, children_2_5: 0, infants: 0, total_price: 700 }],
      amenities: [],
    },
  });
  const before = Number(sql('select count(*) from payments'));
  const ja = await request.post('/api/payment/init', { data: body('ja', email('guard')) });
  expect(ja.status()).toBe(422);
  expect(await ja.json()).toMatchObject({ success: false, code: 'payment_locale_not_allowed', locale: 'ja' });
  expect(Number(sql('select count(*) from payments'))).toBe(before);

  const mail = email('guard-en');
  const en = await request.post('/api/payment/init', { data: body('en', mail) });
  expect(en.status()).toBe(200);
  expect((await en.json()).success).toBe(true); // fake gateway: no provider is called
  expect(row(mail)).toMatchObject({ locale: 'en', currency: 'usd', total: 70000 });
});

// ------------------------------------------------------------------------------------------------------------------
async function payInJapanese(page: Page, tag: string) {
  const mail = email(tag);
  await stepOne(page, '/ja/booking', /ご希望に合わせた/);
  await page.getByRole('button', { name: /お客様情報の入力へ/ }).click();
  await guestDetails(page, { name: '佐藤 花子', phone: '+81 3-1234-5678', email: mail }, /^次へ/);
  await expect(page.getByText('オンライン決済', { exact: true })).toBeVisible();
  await expect(page.locator('input[name="payment-method"][value="stripe"]')).toBeChecked();
  await page.getByRole('button', { name: /内容を確認する/ }).click();
  await expect(page.getByText(/Stripe決済の画面に移動し/)).toBeVisible();
  await page.getByRole('button', { name: /安全なお支払いへ進む/ }).click();
  await page.waitForURL(/\/ja\/booking\?.*result=/);
  return mail;
}

test('3a. payment path (ja allowed): fake gateway succeeds → お支払いが完了しました, payment succeeded', async ({ page }) => {
  cms('en,vi,ja', 'succeeded');
  const mail = await payInJapanese(page, 'paid');
  await expect(page.getByRole('heading', { name: 'お支払いが完了しました' })).toBeVisible({ timeout: 30_000 });
  await expect(page.getByText('予約リクエストを受け付けました')).toHaveCount(0);
  const b = row(mail);
  expect(b).toMatchObject({ locale: 'ja', currency: 'usd', total: 70000 });
  expect(paymentOf(b.code)).toBe('succeeded');
});

test('3b. payment path: gateway still pending → 確認中 copy, never success', async ({ page }) => {
  test.setTimeout(150_000);
  cms('en,vi,ja', 'pending');
  const mail = await payInJapanese(page, 'pending');
  await expect(page.getByRole('heading', { name: 'お支払いを確認中' })).toBeVisible();
  // The wizard polls for about a minute, then settles on the pending state.
  await expect(page.getByText(/お支払いを確認しています。この時点ではお支払いとご予約はまだ確定していません/)).toBeVisible({ timeout: 90_000 });
  await expect(page.getByText('お支払いが完了しました')).toHaveCount(0);
  expect(paymentOf(row(mail).code)).toBe('pending');
});

test('3c. payment path: gateway fails → failed copy, never success', async ({ page }) => {
  cms('en,vi,ja', 'failed');
  const mail = await payInJapanese(page, 'failed');
  await expect(page.getByRole('heading', { name: 'お支払いが完了しませんでした' })).toBeVisible({ timeout: 30_000 });
  await expect(page.getByText('お支払いが完了しました')).toHaveCount(0);
  expect(paymentOf(row(mail).code)).toBe('failed');
});

test('3d. payment path: guest cancels at the gateway → failed copy, payment canceled', async ({ page }) => {
  cms('en,vi,ja', 'succeeded');
  // The fake gateway returns to the success URL; rewrite it to the cancel URL the CMS issued alongside it.
  await page.route('**/api/payment/callback/**', (route) => route.continue({ url: route.request().url().replace('result=success', 'result=cancel') }));
  const mail = await payInJapanese(page, 'cancel');
  await expect(page.getByRole('heading', { name: 'お支払いが完了しませんでした' })).toBeVisible({ timeout: 30_000 });
  await expect(page.getByText('お支払いが完了しました')).toHaveCount(0);
  expect(paymentOf(row(mail).code)).toBe('canceled');
});

// ------------------------------------------------------------------------------------------------------------------
test('4. language switch keeps the wizard: ja → en (selection + guest), → vi (date + guest), → ja', async ({ page }) => {
  cms('en,vi');
  const guest = { name: '山田 太郎', phone: '+81 90-1234-5678', email: email('switch') };
  await stepOne(page, '/ja/booking', /ご希望に合わせた/);
  // 3 adults in the suite, plus a second cabin.
  await page.getByRole('button', { name: '1名増やす：大人（Imperial Suite #1）' }).or(page.getByRole('button', { name: /^1名増やす：大人/ }).first()).first().click();
  await page.getByRole('button', { name: 'Ocean Breeze Premiumを追加' }).click();
  await expect(page.getByText('選択中の客室（2）')).toBeVisible();
  await page.getByRole('button', { name: /お客様情報の入力へ/ }).click();
  await page.locator('#guest-fullName').fill(guest.name);
  await page.locator('#guest-email').fill(guest.email);
  await page.locator('#guest-phone').fill(guest.phone);

  await switchTo(page, 'en');
  await page.waitForURL(/\/booking\?start_at=2027-02-15&cruise_id=3&itinerary_id=3&cabin_id=24$/);
  await expect(page.getByText('In-voyage cabins (2)')).toBeVisible();
  await expect(page.locator('#booking-voyage')).toHaveValue('3-3');
  // Guest counts are in the reservation summary (collapsed on phones).
  const show = page.getByRole('button', { name: 'View reservation details' });
  if (await show.isVisible()) await show.click();
  await expect(page.getByText('3 Adults').first()).toBeVisible();
  await page.getByRole('button', { name: /Continue to guest details/ }).click();
  await expect(page.locator('#guest-fullName')).toHaveValue(guest.name);
  await expect(page.locator('#guest-phone')).toHaveValue(guest.phone);
  await expect(page.locator('#guest-email')).toHaveValue(guest.email);

  await switchTo(page, 'vi');
  await page.waitForURL(/\/vi\/booking\?start_at=2027-02-15$/);
  // VI lists no departures on this clone (free date entry). The date is kept only by the handoff fix in
  // BookingWizard.tsx made during this E2E (needs a web rebuild): soft, so the rest of the scenario still runs.
  await expect.soft(page.locator('#booking-date'), 'VI keeps the carried date (needs the rebuilt web)').toHaveValue('2027-02-15', { timeout: 8_000 });
  // VI inventory is separate (VND rows): no voyage or cabin id is carried, the guest chooses again.
  await expect(page.getByText('Chọn hành trình để xem các cabin.')).toBeVisible();
  await expect(page.getByRole('button', { name: /^Xóa .* #\d+$/ })).toHaveCount(0);
  expect(await page.locator('#booking-voyage').inputValue()).toBe('');

  await switchTo(page, 'ja');
  await page.waitForURL(/\/ja\/booking\?start_at=2027-02-15$/);
  await expect(page.locator('#booking-date')).toHaveValue('2027-02-15');
  await page.locator('#booking-voyage').selectOption('3-3');
  await page.getByRole('button', { name: 'Imperial Suiteを追加' }).click();
  await page.getByRole('button', { name: /お客様情報の入力へ/ }).click();
  await expect(page.locator('#guest-fullName')).toHaveValue(guest.name);
  await expect(page.locator('#guest-email')).toHaveValue(guest.email);
});

// ------------------------------------------------------------------------------------------------------------------
test('5. EN regression: /booking request flow keeps its copy and stores locale en', async ({ page }) => {
  cms('en,vi');
  const mail = email('en');
  await stepOne(page, '/booking', /Create your/);
  await expect(page.getByText('$700.00').first()).toBeVisible();
  await expect(page.getByText('From').first()).toBeVisible();
  await expect(page.locator('#booking-date option:checked')).toHaveText('15 Feb 2027');
  await page.getByRole('button', { name: /Continue to guest details/ }).click();
  await guestDetails(page, { name: 'Jane Doe', phone: '+44 20 7946 0000', email: mail }, /^Continue/);
  await expect(page.getByText('Direct payment')).toBeVisible();
  await page.getByText('Direct consultation').click();
  await page.getByRole('button', { name: /Review your request/ }).click();
  await expect(page.locator('dl').filter({ hasText: 'Estimated total' }).first()).toContainText('Imperial Suite (2 Adults) – $700.00');
  await page.getByRole('button', { name: /Send reservation request/ }).click();
  await expect(page.getByRole('heading', { name: 'Booking successful!' })).toBeVisible(); // unchanged EN copy (known issue)
  await expect(page.getByText(/Booking reference: /)).toBeVisible();
  expect(row(mail)).toMatchObject({ fullName: 'Jane Doe', currency: 'usd', locale: 'en', total: 70000 });
});

// ------------------------------------------------------------------------------------------------------------------
test('6. /ja/booking/pay: no token → invalid; with a token → payment unavailable while ja is not allowed', async ({ page }) => {
  cms('en,vi');
  await page.goto('/ja/booking/pay');
  await expect(page.getByText(/このお支払いリンクは無効か、有効期限が切れています/)).toBeVisible();
  await page.goto('/ja/booking/pay?t=e2e-not-a-real-token');
  await expect(page.getByText(/オンライン決済は一時的にご利用いただけません/)).toBeVisible();
  await expect(page.getByRole('button', { name: /安全なお支払いへ進む/ })).toHaveCount(0);

  // A real link of a booking made on the Japanese page (scenario 1).
  cms('en,vi', 'succeeded', '2027-01-01');
  const link = jaPaymentLink();
  expect(sql(`select locale from bookings where code = '${link.code}'`)).toBe('ja');
  await page.goto(`/ja/booking/pay?t=${encodeURIComponent(link.token)}`);
  await expect(page.getByText(/オンライン決済は一時的にご利用いただけません/)).toBeVisible();
  await expect(page.getByRole('button', { name: /安全なお支払いへ進む/ })).toHaveCount(0);

  // The same link on the English page: the form is offered (en may pay), the server refuses (booking made in ja).
  const payments = sql('select count(*) from payments');
  await page.goto(`/booking/pay?t=${encodeURIComponent(link.token)}`);
  await expect(page.getByRole('heading', { name: 'Confirm your booking' })).toBeVisible();
  await page.locator('#pay-terms-accept').check();
  const refused = page.waitForResponse((r) => r.url().includes('/api/booking/payment-link') && r.request().method() === 'POST');
  await page.getByRole('button', { name: /Continue to secure payment/ }).click();
  const res = await refused;
  expect(res.status()).toBe(422);
  expect((await res.json()).code).toBe('payment_locale_not_allowed');
  await expect(page.getByText('Online payment is temporarily unavailable. Please contact our reservations team.')).toBeVisible();
  expect(sql('select count(*) from payments')).toBe(payments);
});

test.afterAll(() => {
  cms('en,vi'); // leave the CMS in the default (ja request-only) mode
  const media = ignored.filter((l) => !l.includes('/api/'));
  console.log(`ignored responses: ${media.length} media (local env), api 4xx: ${JSON.stringify([...new Set(ignored.filter((l) => l.includes('/api/')).map((l) => l.replace(/\?.*/, '')))])}`);
});
