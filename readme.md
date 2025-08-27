# required to install

 - download FastRoute into vendor/
```bash
composer require nikic/fast-route
````
```bash
composer require ramsey/uuid
```
```bash
composer require psr/container
```
```bash
composer require vlucas/phpdotenv
```



## Add vhost to access development through gearfalcon.test
1. Open Apache config file
```bash
C:\xampp\apache\conf\extra\httpd-vhosts.conf (or the main httpd.conf if vhosts not used).
```
2. Add a new Virtual Host
- At the bottom, add:
```bash
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/Projects/gearfalcon-app/public"
    ServerName gearfalcon.test

    <Directory "C:/xampp/htdocs/Projects/gearfalcon-app/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
| ServerName can be anything, e.g., gearfalcon.test.
3. Edit your hosts file
```bash
C:\Windows\System32\drivers\etc\hosts with admin rights.
```
- Add:
```bash
127.0.0.1   gearfalcon.test
```
4. Restart Apache
