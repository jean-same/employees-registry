# employee-registry

## Installation

For local development, you can install this api like a PHP symfony project:

```bash
composer install

cp .env .env.local
# EDIT Database configuration on .env.local

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
```
