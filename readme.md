# Run this to install required pacakes.
```bash
composer install
````

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


❓ Optional Repositories (only if needed separately)

You may skip these and handle them inside parent repositories:

CartItemRepository → only if you want cart items separate, otherwise keep inside CartRepository.

TechnicianSkillRepository → only if skills management becomes complex, otherwise handle via TechnicianRepository.

JobAssignmentRepository → only if you need heavy reporting/logic for assignments, otherwise keep inside JobRepository.