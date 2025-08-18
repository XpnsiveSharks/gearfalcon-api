required to install

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



to do:
typical Presentation layer interfaces

Http/ → REST, FastRoute, API controllers, middleware, etc.

Cli/ → console commands, scripts (e.g. Symfony Console, Laravel Artisan-style).

Websocket/ → real-time communication controllers.

Grpc/ or Rpc/ → if your system exposes gRPC or RPC endpoints.

GraphQL/ → resolvers, queries, mutations.

MessageConsumer/ → if your app consumes messages from queues (Kafka, RabbitMQ, etc).

Ui/ → if you have server-side rendered templates (Twig, Blade, etc).

Add vhost to access development through gearfalcon.test
Open Apache’s config file

Path: C:\xampp\apache\conf\extra\httpd-vhosts.conf

Add a new virtual host
Example:

```txt
<VirtualHost *:80>
    ServerAdmin admin@gearfalcon.test
    DocumentRoot "C:/xampp/htdocs/Projects/gearfalcon-app/public"
    ServerName gearfalcon.test
    <Directory "C:/xampp/htdocs/Projects/gearfalcon-app/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable virtual hosts in Apache (if not already)

Open C:\xampp\apache\conf\httpd.conf

Make sure this line is uncommented:

Include conf/extra/httpd-vhosts.conf


Restart Apache

Stop and start Apache from XAMPP Control Panel.

Test

Go to http://gearfalcon.test in your browser.

It should now serve your app’s public/ folder instead of the default dashboard.


1. Open Apache config file

Go to C:\xampp\apache\conf\extra\httpd-vhosts.conf (or the main httpd.conf if vhosts not used).

2. Add a new Virtual Host

At the bottom, add:

<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/Projects/gearfalcon-app/public"
    ServerName gearfalcon.test

    <Directory "C:/xampp/htdocs/Projects/gearfalcon-app/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>


Make sure the paths use forward slashes (/).

ServerName can be anything, e.g., gearfalcon.test.

3. Edit your hosts file

Open C:\Windows\System32\drivers\etc\hosts with admin rights.

Add:

127.0.0.1   gearfalcon.test

4. Restart Apache

Open XAMPP Control Panel → Stop → Start Apache.

5. Access your app

In browser:

http://gearfalcon.test/users


Now FastRoute will correctly dispatch /users without needing /public in the URL.





DI


