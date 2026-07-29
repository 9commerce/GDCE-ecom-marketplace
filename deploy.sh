php bin/magento maintenance:enable;
rm -rf var/cache/* var/di/* var/generation/* var/page_cache/*
rm -rf pub/static/*;
rm -rf generated/code/*;
php bin/magento setup:upgrade;
php bin/magento setup:di:compile;
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush;
php bin/magento cache:clean;
php bin/magento maintenance:disable;