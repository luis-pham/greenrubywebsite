#!/bin/zsh
# Browser E2E (phase 2B): (re)starts the CMS on :8098 for the Japanese guest flow, on a LOCAL clone only, with Japanese
# simulated public (fake manifests), the fake payment gateway (cms-router.php) and the given payment locales.
#   zsh tests/e2e/ja-cms.sh green_ruby_i18n_jae2e en,vi        (default: Japanese request-only)
#   zsh tests/e2e/ja-cms.sh green_ruby_i18n_jae2e en,vi,ja     (Japanese payments allowed, fake gateway)
set -e
E2E=${0:A:h}; CMS=${E2E:h:h}; SAFE=${CMS:h}/greenruby-web/scripts/i18n/cms/safe-env.sh
DB=${1:?local clone}; LOCALES=${2:-en,vi}
[[ $DB == green_ruby_i18n_* ]] || { echo "local green_ruby_i18n_* clone only"; exit 2; }
lsof -ti tcp:3201,3202 >/dev/null || (node $E2E/fake-manifest.mjs > /dev/null 2>&1 &)
lsof -ti tcp:8098 | xargs kill 2>/dev/null; sleep 0.5
cd $CMS && I18N_DB=$DB $SAFE php artisan cache:clear >/dev/null
(cd $CMS && I18N_DB=$DB APP_URL=http://127.0.0.1:8098 PHP_CLI_SERVER_WORKERS=8 $SAFE env I18N_ALLOW_NEW_LOCALES=true I18N_ALLOW_PUBLIC_NEW_LOCALES=true \
  FRONTEND_MANIFEST_URLS=http://127.0.0.1:3201/api/i18n/manifest,http://127.0.0.1:3202/api/i18n/manifest FRONTEND_MANIFEST_SECRET=e2e-manifest-secret \
  BOOKING_PAYMENT_LOCALES=$LOCALES php -S 127.0.0.1:8098 -t public tests/e2e/cms-router.php > $E2E/.state-ja-cms.log 2>&1 &)
for i in $(seq 1 30); do curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:8098/admincp/login | grep -q 200 && break; sleep 1; done
echo "CMS :8098 on $DB, payment locales $LOCALES"
