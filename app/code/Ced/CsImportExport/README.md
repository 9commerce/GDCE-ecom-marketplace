# m2.2.2-vendor-mass-import-export

# For images not showing in import image section grid

# goto Magento root folder pub/media/import

# edit .htaccess and change content like below "Require all denied" to "Require all granted"

<IfVersion < 2.4>
    order allow,deny
    deny from all
</IfVersion>
<IfVersion >= 2.4>
    Require all granted
</IfVersion
