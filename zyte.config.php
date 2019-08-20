<?php
# DATABASE VALUES
define('ZYTE_DB_HOST', 'localhost');
define('ZYTE_DB_NAME', '');
define('ZYTE_DB_CHARSET', 'utf8mb4');
define('ZYTE_DB_USERNAME', '');
define('ZYTE_DB_PASSWORD', '');

# SECRETS VALUES
define('ZYTE_SECRET_JWT', '');
define('ZYTE_SECRET_ENCRYPT', '');

# HTTPS AS DEFAULT API PROTOCOL
define('ZYTE_API_HTTPS', true);

# API DOMAIN
define('ZYTE_API_DOMAIN', '');

# ZYTE EMAIL
define('ZYTE_EMAIL_SMTP', false);
define('ZYTE_EMAIL_AUTH', true);
define('ZYTE_EMAIL_SECURE', 'ssl');
define('ZYTE_EMAIL_PORT', 246);
define('ZYTE_EMAIL_HOST', '');
define('ZYTE_EMAIL_USERNAME', '');
define('ZYTE_EMAIL_PASSWORD', '');

# CROSS ORIGIN RESOURCE SHARING (CORS)
$crossOrigin = [
  'https://example.com' => [
    'HEADERS' => 'Origin, Content-Type, Accept, X-XSRF-TOKEN, AUTH-TOKEN',
    'CREDENTIALS' => 'true'
  ]
];
?>
