import { execFileSync } from 'node:child_process';
import { expect, test, type Browser, type BrowserContext, type Page } from '@playwright/test';

// Editorial browser E2E (1E hardening). Runs through run.sh only: a throwaway clone (green_ruby_i18n_e2e) with
// test-only users, ja 'editing' (never public). Subjects: EN cabin #8 (2 rooms, paired with VI #44; EN is the source
// language) and EN article #222 (paired; its category has no ja version yet). One shared database, so the tests run in order.

const DB = process.env.E2E_DB ?? 'green_ruby_i18n_e2e';
const WEB = 'http://127.0.0.1:3134';
const WRITE = 'http://127.0.0.1:5181';
const PASSWORD = 'e2e-local-only';
const SOURCE = 8;
const PAIR_VI = 44;

test.describe.configure({ mode: 'serial' });

function sql(query: string): string {
  if (!DB.startsWith('green_ruby_i18n_')) throw new Error('E2E runs on a local green_ruby_i18n_* clone only');
  return execFileSync('mysql', ['-uroot', '-N', '-B', DB, '-e', query]).toString().trim();
}
const jaCabin = () => Number(sql("select id from app_cabin where language_id = (select id from ad_language where code = 'ja') and name like '%E2E%' order by id desc limit 1") || 0);
const groupOf = (type: string, id: number) => sql(`select group_key from content_translations where entity_type = '${type}' and entity_id = ${id}`);

async function login(browser: Browser, username: string): Promise<BrowserContext> {
  const context = await browser.newContext();
  const page = await context.newPage();
  await page.goto('/admincp/login');
  await page.fill('input[name=username]', username);
  await page.fill('input[name=password]', PASSWORD);
  await page.getByRole('button', { name: 'Đăng nhập' }).click();
  await expect(page).not.toHaveURL(/\/login/);
  await page.close();
  return context;
}

let admin: BrowserContext, translator: BrowserContext, reviewer: BrowserContext, publisher: BrowserContext;
let editUrl = '';

test.beforeAll(async ({ browser }) => {
  // Four logins: under the 5-per-minute login throttle.
  admin = await login(browser, 'e2e-admin');
  translator = await login(browser, 'e2e-translator');
  reviewer = await login(browser, 'e2e-reviewer');
  publisher = await login(browser, 'e2e-publisher');
});

test.afterAll(async () => {
  for (const c of [admin, translator, reviewer, publisher]) await c?.close();
});

const matrixRow = (page: Page, id: number) => page.locator('tbody tr').filter({ has: page.locator('td').first().filter({ hasText: new RegExp(`^\\s*#${id} `) }) });
const panel = (page: Page) => page.locator('#translation-panel');
async function open(context: BrowserContext, url: string) {
  const page = await context.newPage();
  await page.goto(url);
  return page;
}
async function save(page: Page) {
  await page.getByRole('button', { name: 'Lưu thông tin' }).click();
  await page.waitForLoadState('domcontentloaded');
}
async function workflow(context: BrowserContext, label: string) {
  const page = await open(context, editUrl);
  await panel(page).getByRole('button', { name: label }).click();
  await expect(page.locator('.alert-danger')).toHaveCount(0);
  await page.close();
}

test('language selection: registry screen and matrix language filter', async () => {
  const page = await open(admin, '/admincp/language');
  await expect(page.locator('tr', { hasText: '日本語' })).toContainText('Đang biên tập');
  await page.goto('/admincp/translation?type=cabin');
  await page.selectOption('select[name=language]', 'ja');
  await page.getByRole('button', { name: 'Lọc' }).click();
  await page.waitForURL(/language=ja/);
  const headers = page.locator('table.table-data thead th');
  await expect(headers).toHaveCount(2);
  await expect(headers.nth(1)).toHaveAttribute('lang', 'ja');
  // The translator cannot administer languages.
  const t = await translator.newPage();
  expect((await t.goto('/admincp/language'))?.status()).toBe(403);
  expect(sql("select site_status from ad_language where code = 'ja'")).toBe('editing');
  await page.close();
  await t.close();
});

test('parent prerequisite: an article whose category has no ja version is refused', async () => {
  const count = () => sql("select count(*) from app_article where language_id = (select id from ad_language where code = 'ja')");
  const before = count();
  const page = await open(translator, '/admincp/translation?type=article&language=ja');
  await matrixRow(page, 222).getByRole('button', { name: /Tạo bản/ }).click();
  await expect(page.locator('.alert-danger')).toContainText('trước, rồi mới tạo');
  expect(count()).toBe(before);
  await page.close();
});

test('create a Japanese translation from the matrix, with source comparison and read-only business data', async () => {
  const before = sql(`select concat_ws('|', name, capacity, cruise_id) from app_cabin where id in (${SOURCE}, ${PAIR_VI}) order by id`);
  const page = await open(translator, '/admincp/translation?type=cabin&language=ja');
  await matrixRow(page, SOURCE).getByRole('button', { name: /Tạo bản/ }).click();
  await expect(panel(page)).toBeVisible();
  editUrl = page.url();
  await expect(panel(page)).toContainText('Chưa xuất bản');
  await expect(panel(page)).toContainText('Nháp');
  // Source comparison: the VI source, read-only.
  await panel(page).locator('summary', { hasText: 'Bản gốc' }).click();
  await expect(panel(page).locator('details table')).toContainText('Serenity Deluxe');
  // Business data is inherited and read-only in the form.
  await expect(page.locator('input[name=capacity]')).toHaveJSProperty('readOnly', true);
  expect(sql(`select concat_ws('|', name, capacity, cruise_id) from app_cabin where id in (${SOURCE}, ${PAIR_VI}) order by id`)).toBe(before);
  await page.close();
});

test('edit localized text; a business-data write is refused server-side', async () => {
  const page = await open(translator, editUrl);
  await page.fill('input[name=name]', 'セレニティ デラックス E2E');
  await save(page);
  await expect(page.locator('.alert-danger')).toHaveCount(0);
  const id = jaCabin();
  expect(id).toBeGreaterThan(0);
  expect(sql(`select name from app_cabin where id = ${id}`)).toBe('セレニティ デラックス E2E');

  const capacity = sql(`select capacity from app_cabin where id = ${id}`);
  const page2 = await open(translator, editUrl);
  await page2.evaluate(() => {
    const input = document.querySelector<HTMLInputElement>('input[name=capacity]')!;
    input.readOnly = false;
    input.value = '9';
  });
  await save(page2);
  await expect(page2.locator('.alert-danger')).toBeVisible();
  expect(sql(`select capacity from app_cabin where id = ${id}`)).toBe(capacity);
  expect(sql(`select name from app_cabin where id = ${SOURCE}`)).toBe('Serenity Deluxe');
  await page.close();
  await page2.close();
});

test('detach and re-attach the ja version without touching the EN/VI pair', async () => {
  const id = jaCabin();
  const pair = () => sql(`select group_concat(entity_id order by entity_id) from content_translations where entity_type = 'cabin' and group_key = '${groupOf('cabin', SOURCE)}' and language_id in (1, 2)`);
  const pairBefore = pair();
  const page = await open(translator, '/admincp/translation?type=cabin&language=ja');
  const cell = matrixRow(page, SOURCE).locator('td').nth(1);
  await cell.locator('summary', { hasText: 'Gỡ khỏi nhóm' }).click();
  await cell.locator('input[name=reason]').fill('E2E: kiểm tra gỡ bản ja');
  await cell.getByRole('button', { name: 'Gỡ (chỉ bản này)' }).click();
  await expect(page.locator('.alert-danger')).toHaveCount(0);
  expect(groupOf('cabin', id)).toBe('');
  expect(pair()).toBe(pairBefore);

  await page.goto('/admincp/translation?type=cabin&language=ja');
  const cell2 = matrixRow(page, SOURCE).locator('td').nth(1);
  await expect(cell2.getByRole('button', { name: 'Tạo bản 日本語' })).toBeVisible();
  await cell2.locator('summary', { hasText: 'Gắn bản có sẵn' }).click();
  await cell2.locator('select[name=target_id]').selectOption(String(id));
  await cell2.locator('input[name=reason]').fill('E2E: gắn lại bản ja');
  await cell2.getByRole('button', { name: 'Gắn' }).click();
  await expect(page.locator('.alert-success')).toBeVisible();
  expect(groupOf('cabin', id)).toBe(groupOf('cabin', SOURCE));
  expect(pair()).toBe(pairBefore);
  await page.close();
});

test('slug change needs slug-update: refused for the translator, done by the admin', async () => {
  const id = jaCabin();
  const t = await open(translator, editUrl);
  await panel(t).locator('summary', { hasText: 'Đổi slug' }).click();
  await panel(t).locator('input[name=slug]').fill('serenity-deluxe-e2e');
  await panel(t).locator('input[name=reason]').fill('E2E: đổi slug thử');
  const [response] = await Promise.all([t.waitForResponse((r) => r.url().endsWith('/admincp/slug')), panel(t).getByRole('button', { name: 'Đổi slug' }).click()]);
  expect(response.status()).toBe(403);
  const a = await open(admin, editUrl);
  await panel(a).locator('summary', { hasText: 'Đổi slug' }).click();
  await panel(a).locator('input[name=slug]').fill('serenity-deluxe-e2e');
  await panel(a).locator('input[name=reason]').fill('E2E: đổi slug thử');
  await panel(a).getByRole('button', { name: 'Đổi slug' }).click();
  await expect(a.locator('.alert-danger')).toHaveCount(0);
  expect(sql(`select slug from app_cabin where id = ${id}`)).toBe('serenity-deluxe-e2e');
  expect(sql(`select slug from app_cabin where id = ${SOURCE}`)).toBe('serenity-deluxe');
  await t.close();
  await a.close();
});

test('workflow transitions by separate people; no self-approval; publish needs its own privilege', async () => {
  const id = jaCabin();
  await workflow(translator, 'Gửi duyệt bản dịch');
  // The translator holds no approval privilege: the button is shown disabled with the reason.
  const t = await open(translator, editUrl);
  await expect(panel(t).getByRole('button', { name: 'Duyệt bản dịch' })).toBeDisabled();
  await t.close();
  await workflow(reviewer, 'Duyệt bản dịch');
  await workflow(reviewer, 'Duyệt SEO');
  expect(sql(`select workflow from translation_meta where entity_type = 'cabin' and entity_id = ${id}`)).toBe('approved');
  const r = await open(reviewer, editUrl);
  await expect(panel(r).getByRole('button', { name: 'Xuất bản' })).toBeDisabled();
  await r.close();
  await workflow(publisher, 'Xuất bản');
  expect(sql(`select publish_state from app_cabin where id = ${id}`)).toBe('published');
  expect(sql(`select group_concat(concat(action, ':', ifnull(actor, '-')) order by id) from editorial_audit_log where entity_type = 'cabin' and entity_id = ${id}`))
    .toBe('slug_change:1,submit:2,approve_translation:3,approve_seo:3,publish:4');
  // Still never public: ja is 'editing'.
  expect((await (await fetch(`${WEB}/ja/cabin/serenity-deluxe-e2e-${id}.html`)).status)).toBe(404);
});

test('pending child revision: room text waits for review; a second editor cannot overwrite it; approval applies atomically', async () => {
  const id = jaCabin();
  const liveRooms = () => sql(`select group_concat(description order by ord, id separator '|') from app_cabin_room where cabin_id = ${id}`);
  const before = liveRooms();
  const page = await open(translator, editUrl);
  await page.fill('input[name=name]', 'セレニティ デラックス E2E 改訂');
  await page.evaluate(() => {
    const description = document.querySelector<HTMLInputElement>('input[name="room_description[]"]')!;
    description.value = 'E2E-ROOM-REVISION';
  });
  await save(page);
  await expect(page.locator('.alert-danger')).toHaveCount(0);
  expect(liveRooms()).toBe(before);
  expect(sql(`select name from app_cabin where id = ${id}`)).toBe('セレニティ デラックス E2E');
  const p = await open(translator, editUrl);
  await expect(panel(p)).toContainText('Có bản sửa chờ duyệt');

  // Another editor (the admin) edits the same published row: refused, the translator's revision is intact.
  const a = await open(admin, editUrl);
  await a.fill('input[name=name]', 'E2E ADMIN OVERWRITE');
  await save(a);
  await expect(a.locator('.alert-danger')).toContainText('đang do người dùng #2');
  expect(sql(`select json_unquote(json_extract(fields, '$.name')) from content_revision where entity_type = 'cabin' and entity_id = ${id} and applied_at is null and discarded_at is null`)).toBe('セレニティ デラックス E2E 改訂');

  // Preview shows the revision; exiting leaves nothing visible.
  const [popup] = await Promise.all([p.waitForEvent('popup'), panel(p).getByRole('button', { name: 'Xem trước' }).click()]);
  await popup.waitForURL(new RegExp(`${WEB}/ja/cabin/`));
  await expect(popup.locator('body')).toContainText('Exit preview');
  await expect(popup.locator('body')).toContainText('セレニティ デラックス E2E 改訂');
  await expect(popup.locator('meta[name=robots]')).toHaveAttribute('content', /noindex/);
  const previewUrl = popup.url();
  await popup.getByRole('link', { name: 'Exit preview' }).click();
  await popup.waitForLoadState();
  const after = await popup.goto(previewUrl);
  expect(after?.status()).toBe(404);
  await popup.close();

  await workflow(translator, 'Gửi duyệt bản dịch');
  await workflow(reviewer, 'Duyệt bản dịch');
  await workflow(reviewer, 'Duyệt SEO');
  expect(sql(`select name from app_cabin where id = ${id}`)).toBe('セレニティ デラックス E2E 改訂');
  expect(liveRooms().split('|')[0]).toBe('E2E-ROOM-REVISION');
  expect(sql(`select publish_state from app_cabin where id = ${id}`)).toBe('published');
  expect(sql(`select description from app_cabin_room where cabin_id = ${SOURCE} order by ord, id limit 1`)).not.toBe('E2E-ROOM-REVISION');
  await page.close();
  await p.close();
  await a.close();
});

test('rejecting a revision keeps the public version', async () => {
  const id = jaCabin();
  const page = await open(translator, editUrl);
  await page.fill('input[name=name]', 'E2E REJECTED TEXT');
  await save(page);
  await workflow(translator, 'Gửi duyệt bản dịch');
  await workflow(reviewer, 'Trả về nháp');
  await workflow(translator, 'Huỷ bản sửa (giữ bản đang hiển thị)');
  expect(sql(`select name from app_cabin where id = ${id}`)).toBe('セレニティ デラックス E2E 改訂');
  expect(sql(`select count(*) from content_revision where entity_type = 'cabin' and entity_id = ${id} and applied_at is null and discarded_at is null`)).toBe('0');
  await page.close();
});

test('EN/VI editing is unchanged: a VI cabin saves directly, without workflow', async () => {
  const page = await open(admin, `/admincp/vi/cabin-manager/edit/${PAIR_VI}`);
  await expect(page.locator('#translation-panel .badge-info')).toHaveCount(0);
  const name = sql(`select name from app_cabin where id = ${PAIR_VI}`);
  await page.fill('input[name=name]', `${name} E2E`);
  await save(page);
  expect(sql(`select name from app_cabin where id = ${PAIR_VI}`)).toBe(`${name} E2E`);
  expect(sql(`select count(*) from content_revision where entity_type = 'cabin' and entity_id in (${SOURCE}, ${PAIR_VI})`)).toBe('0');
  await page.close();
});

test('writing app: Japanese draft and its source tab', async ({ browser }) => {
  const context = await browser.newContext({ baseURL: WRITE });
  const page = await context.newPage();
  await page.goto(`${WRITE}/articles`);
  // Read-only guest view (write.public_view) shows a "Đăng nhập" link first; otherwise the login form.
  await page.locator('input[autocomplete=username], button.link-btn').first().waitFor();
  if (await page.locator('button.link-btn', { hasText: 'Đăng nhập' }).isVisible()) await page.locator('button.link-btn', { hasText: 'Đăng nhập' }).click();
  await page.locator('input[autocomplete=username]').fill('e2e-translator');
  await page.locator('input[type=password]').fill(PASSWORD);
  await page.locator('button.btn-primary.btn-block').click();
  const add = page.getByRole('button', { name: '+ 日本語' });
  await expect(add).toBeVisible();
  await add.click();
  await page.waitForURL(/\/articles\/\d+/);
  const id = Number(page.url().split('/').pop());
  expect(sql(`select l.code from app_article a join ad_language l on l.id = a.language_id where a.id = ${id}`)).toBe('ja');
  expect(sql(`select is_published from app_article where id = ${id}`)).toBe('0');
  await expect(page.getByRole('button', { name: 'Bản gốc' })).toBeVisible();
  await context.close();
});
