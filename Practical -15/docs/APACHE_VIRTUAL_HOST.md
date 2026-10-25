# Optional Apache virtual host (Intermediate extension)

This document configures **local-only** `http://lumiora.local`, not an internet site. Make a backup of Apache configuration before editing.

## 1. Apache config

In XAMPP, edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and add:

```apache
<VirtualHost *:80>
    ServerName lumiora.local
    DocumentRoot "C:/xampp/htdocs/StudentHub_MySQL_Practical_15"
    <Directory "C:/xampp/htdocs/StudentHub_MySQL_Practical_15">
        AllowOverride All
        Require local
        Options FollowSymLinks
    </Directory>
    ErrorLog "logs/lumiora-local-error.log"
    CustomLog "logs/lumiora-local-access.log" common
</VirtualHost>
```

For this VirtualHost to be loaded, verify that `httpd.conf` includes:

```apache
Include conf/extra/httpd-vhosts.conf
```

`Require local` prevents remote-network clients accessing this particular vhost. If using other vhosts, keep their entries intact. Avoid embedding database passwords inside web-served project files.

## 2. Windows hosts file

Start Notepad **as Administrator**, open `C:\Windows\System32\drivers\etc\hosts`, add:

```text
127.0.0.1 lumiora.local
```

## 3. Verify

Check Apache syntax (XAMPP Shell):

```powershell
C:\xampp\apache\bin\httpd.exe -t
```

Restart Apache in XAMPP. Open `http://lumiora.local/` and expect the Lumiora integrated portal. Then login, open the admin Integration Center and screenshot the URL and dashboard. The actual virtual host is **not installed/configured automatically** by this ZIP.

If Apache config rejects `Require local`, make sure Apache 2.4 is in use. If Windows hosts edit fails, run the editor as Administrator. In PHP, session cookies are hostname-specific: login again after switching from localhost to lumiora.local.
