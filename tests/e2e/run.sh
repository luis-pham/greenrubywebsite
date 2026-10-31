#!/bin/zsh
# Editorial browser E2E (1E hardening). LOCAL TEST DATA ONLY: a throwaway clone of a local test database, test-only
# users with a test password, local servers. Never point it at production.
#
#   WEB_BUILD=/path/to/built/greenruby-web E2E_SOURCE_DB=green_ruby_i18n_p1i zsh tests/e2e/run.sh
#
# 1. clones $E2E_SOURCE_DB (a local clone with ja rows) into green_ruby_i18n_e2e and applies the 1E migrations;
# 2. sets ja 'editing', seeds the locale, creates test users and assigns the editorial roles;
# 3. starts the CMS (:8093), the website build (:3134) and the writing app (:5181);
# 4. runs Playwright; stops the servers.
set -e
E2E=${0:A:h}; CMS=${E2E:h:h}; ROOT=${CMS:h}
WEB=${WEB_BUILD:?set WEB_BUILD to a built greenruby-web}; WRITE=$ROOT/greenruby-write
SRC=${E2E_SOURCE_DB:-green_ruby_i18n_p1i}; DB=green_ruby_i18n_e2e; SAFE=$ROOT/greenruby-web/scripts/i18n/cms/safe-env.sh
[[ $SRC == green_ruby_i18n_* ]] || { echo "E2E_SOURCE_DB must be a local green_ruby_i18n_* clone"; exit 2; }
LOGS=$E2E/.state; mkdir -p $LOGS

mysql -uroot -e "drop database if exists $DB; create database $DB character set utf8mb4 collate utf8mb4_unicode_ci"
mysqldump -uroot --single-transaction --set-gtid-purged=OFF $SRC 2>/dev/null | mysql -uroot $DB
cd $CMS
for m in 2026_11_04_000001_create_content_revision_and_preview_tables 2026_11_04_000002_widen_article_plan_lang 2026_11_05_000001_add_editorial_permissions_and_audit; do
  I18N_DB=$DB $SAFE php artisan migrate --force --path=database/migrations/$m.php >/dev/null
done
HASH=$(php -r 'echo password_hash("e2e-local-only", PASSWORD_BCRYPT);')
mysql -uroot $DB <<SQL
update ad_language set site_status = 'editing' where code = 'ja';
delete from ad_user_role; delete from ad_user;
insert into ad_user (id, username, password, fullname, theme, status, created_at, created_by) values
  (1, 'e2e-admin', '$HASH', 'E2E admin', 0, 1, now(), 1), (2, 'e2e-translator', '$HASH', 'E2E translator', 0, 1, now(), 1),
  (3, 'e2e-reviewer', '$HASH', 'E2E reviewer', 0, 1, now(), 1), (4, 'e2e-publisher', '$HASH', 'E2E publisher', 0, 1, now(), 1);
insert into ad_user_role (user_id, role_id) select 2, id from ad_role where name in ('Quản trị hệ thống', 'Biên dịch');
insert into ad_user_role (user_id, role_id) select 3, id from ad_role where name in ('Quản trị hệ thống', 'Duyệt bản dịch', 'Duyệt SEO');
insert into ad_user_role (user_id, role_id) select 4, id from ad_role where name in ('Quản trị hệ thống', 'Xuất bản bản dịch');
SQL
I18N_DB=$DB $SAFE php artisan cache:clear >/dev/null
I18N_DB=$DB $SAFE php artisan i18n:seed-locale ja >/dev/null || true

PIDS=()
cleanup() { for p in $PIDS; do kill $p 2>/dev/null; done; lsof -ti tcp:8093,3134,5181 | xargs kill 2>/dev/null; }
trap cleanup EXIT
K=$(grep '^CONTENT_API_KEY=' $CMS/.env | cut -d= -f2- | tr -d '\r"')
(I18N_DB=$DB APP_URL=http://127.0.0.1:8093 PHP_CLI_SERVER_WORKERS=8 $SAFE env CONTENT_PREVIEW_KEY=pk-e2e-local-only PREVIEW_ENTRY_URL=http://127.0.0.1:3134/_draft/enter php -S 127.0.0.1:8093 -t public server.php > $LOGS/cms.log 2>&1 &)
(cd $WEB && CONTENT_API_URL=http://127.0.0.1:8093 CONTENT_API_KEY="$K" CONTENT_PREVIEW_KEY=pk-e2e-local-only LARAVEL_ORIGIN=http://127.0.0.1:8093 SITE_URL=https://greenrubycruises.com REVALIDATE_SECRET=x npx next start -p 3134 > $LOGS/web.log 2>&1 &)
(node $E2E/write-server.mjs $WRITE/dist 5181 http://127.0.0.1:8093 > $LOGS/write.log 2>&1 &)
for u in http://127.0.0.1:8093/admincp/login http://127.0.0.1:3134/ http://127.0.0.1:5181/; do
  for i in $(seq 1 60); do curl -s -o /dev/null -w '%{http_code}' $u | grep -q 200 && break; sleep 1; done
done
cd $E2E && E2E_DB=$DB npx playwright test "$@"
