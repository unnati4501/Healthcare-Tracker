# Installation Requirements

You must have below dependencies installed in your system.

1. Nginx v1.18.0
2. PHP v8.2
3. Mysql v8.0
3. Composer v2.0
4. NPM v8.19.3 or above
5. Node v18.13.0 or above
5. Redis

## Below PHP Extension must be enabled in your system.

1. Ctype PHP Extension
2. cURL PHP Extension
3. DOM PHP Extension
4. Fileinfo PHP Extension
5. Filter PHP Extension
6. Hash PHP Extension
7. Mbstring PHP Extension
8. OpenSSL PHP Extension
9. PCRE PHP Extension
10. PDO PHP Extension
11. Session PHP Extension

# Installation Guidelines

1. To install this project, clone the Git repository:

2. Go to Web directory
```bash
cd web/
```

2. Run below command 
```bash
git fetch origin 
git checkout development

# install composer.
composer install

# or

composer install --ignore-platform-reqs

# install node modules

npm install

# Generate key

php artisan key:generate

# Assign permission to storage folder
chmod -R 777 storage/logs
```

2. Define env file [ Env file take it from developer ]

3. Setup mysql database [ Database take it from developer ]

4. Run below command
```bash
php artisan migrate
```

5. Run below command if do you have ubuntu
```bash
chmod -R 777 /tmp/
```

5. Run project using below command
```bash
php artisan serve
```

# Install Socket Server [ If you need to install ]

Run below command 

```bash
npm install

npm start
```

# Reference

1. [Laravel Guide Line](https://laravel.com/docs/10.x)
2. [Redis Installation Guide Line](https://redis.io/docs/install/install-redis/)
3. [Composer Installation Guide Line](https://getcomposer.org/download/)
4. [PHP & Nginx Installation Guide](https://www.php.net/manual/en/install.php)
5. [Node & NPM installation Guide](https://nodejs.org/en/download)

