# CryptAccess

PHP 8.3 native classified-information manager. Point Apache/Laragon's document root to `public/`, import `database/cryptaccess_db.sql`, then copy `config/local.php.example` to `config/local.php` and adjust the database values. Visit `/setup.php` to provision the first administrator; the private PEM is shown once and must be stored securely.

The application needs the `openssl` CLI available on `PATH`. It deliberately uses OpenSSL 3 CLI options for RSA-PSS/SHA-256 and RSA-OAEP/SHA-256 because PHP 8.3's `openssl_*` API does not expose controls for OAEP digest or PSS padding. `config/openssl.cnf` is supplied for PHP installations whose system OpenSSL config path is absent. Never expose `storage/` through the web server.
