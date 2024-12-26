# Open Source Outbreak Server [dev ghostline]

Hello world

To briefly state the goal behind this project, it's simply to preserve [dev ghostline](https://gitlab.com/users/gh0stl1ne/projects] Bioserver1 & 2 projects, as neither of these are available on github and both require a bit of work to get up and running.

But follow the little tutorial inside Wiki : https://github.com/Necrosiak/bioserver-NM/wiki/STEPS-TO-RUN-IN-A-PUBLIC-DEBIAN-FRESH-LINUX-INSTANCE

-----------------------

# STEPS TO RUN IN A PUBLIC DEBIAN FRESH LINUX INSTANCE

## SETUP

1.
```
sudo EDITOR=nano visudo
```
Add user as [USER] :
``` 
[USER] ALL=(ALL) ALL
```
``` 
sudo systemctl reboot
```
2.
```
sudo apt-get update
```
```
sudo apt-get upgrade -y
```
```
sudo apt install sudo make gcc dnsmasq dnsutils unzip php-fpm mariadb-server php7.4-mysql openjdk-17-jre-headless openjdk-17-jre default-jdk libpcre3 libpcre3-dev libexpat1 libexpat1-dev libxml2 libxml2-dev libxslt1-dev libxslt1.1 git
```

3.
```
wget https://www.openssl.org/source/openssl-1.0.2q.tar.gz
tar xzvf openssl-1.0.2q.tar.gz
cd openssl-1.0.2q
```

4.
```
./config --prefix=/opt/openssl-1.0.2 \
--openssldir=/etc/ssl \
shared enable-weak-ssl-ciphers \
enable-ssl3 enable-ssl3-method \
enable-ssl2 \
-Wl,-rpath=/opt/openssl-1.0.2/lib
```

5.
```
make depend
```

6.
```
make
```

7.
```
sudo make install
```

8.
```
/opt/openssl-1.0.2/bin/openssl ciphers -V 'ALL' | grep 0x13
```

9.
```
ls /etc/ld.so.conf.d/
```

10.
```
sudo nano /etc/ld.so.conf.d/x86_64-linux-gnu.conf
```
Add this line on this file :
```
# custom OpenSSL
/opt/openssl-1.0.2/lib
```

11.
```
sudo ldconfig
```

12.
[USER] is your debian account
```
cd /home/[USER]
```

13.
```
wget https://archive.apache.org/dist/httpd/httpd-2.4.57.tar.gz
```
```
wget https://archive.apache.org/dist/apr/apr-1.7.3.tar.gz
```
```
wget https://archive.apache.org/dist/apr/apr-util-1.6.3.tar.gz
```

14.
```
tar xzvf httpd-2.4.57.tar.gz
cd httpd-2.4.57/srclib/
tar xzvf ~/apr-1.7.3.tar.gz
tar xzvf ~/apr-util-1.6.3.tar.gz
ln -s apr-1.7.3 apr
ln -s apr-util-1.6.3 apr-util
cd ~/httpd-2.4.57/
```

15.
```
./configure --prefix=/opt/apache \
--with-included-apr \
--with-ssl=/opt/openssl-1.0.2 \
--enable-ssl
```

16.
```
make
```

17.
```
sudo make install
```

18.
```
nano /opt/apache/bin/envvars
```
Add line in this file :
```
LD_LIBRARY_PATH="/opt/openssl-1.0.2/lib:$LD_LIBRARY_PATH"
```

19.
```
sudo nano /etc/systemd/system/apache.service
```
Add line in this file :
```
[Unit]
Description=Apache Server for Outbreak

[Service]
Type = forking
EnvironmentFile=/opt/apache/bin/envvars
PIDFile=/opt/apache/logs/httpd.pid
ExecStart=/opt/apache/bin/apachectl -k start
ExecReload=/opt/apache/bin/apachectl graceful
ExecStop=/opt/apache/bin/apachectl -k stop
KillSignal=SIGCONT
PrivateTmp=true

[Install]
WantedBy=multi-user.target
```

20.
```
sudo systemctl enable apache.service --now
```

21.
```
sudo systemctl status apache
```

22.
```
sudo nano /etc/dnsmasq.d/obcomsrv
```
Add line in this file (Change XXX.XXX.XXX.XXX by the Server IP) : 
```
address=/gate1.jp.dnas.playstation.org/XXX.XXX.XXX.XXX
address=/www01.kddi-mmbb.jp/XXX.XXX.XXX.XXX
```

23.
```
sudo nano /etc/dnsmasq.conf
```
Add ip on : "listen-address=" (Change XXX.XXX.XXX.XXX by the Server IP) : 
```
XXX.XXX.XXX.XXX,127.0.0.1
```

24.
```
sudo systemctl restart dnsmasq
```
Test connection and redirection with this command (Change XXX.XXX.XXX.XXX by the Server IP) : 
```
nslookup gate1.jp.dnas.playstation.org XXX.XXX.XXX.XXX
```

25.
```
cd
git clone https://github.com/corbin-ch/DNASrep.git
sudo mv DNASrep/etc/dnas /etc/dnas
sudo chown -R 0:0 /etc/dnas
sudo mkdir /var/www
sudo mv DNASrep/www/dnas /var/www/dnas
sudo chown -R www-data:www-data /var/www/dnas
```

26.
```
sudo nano /opt/apache/conf/httpd.conf
```
Remove the # from rewrite_module, mod_proxy, mod_proxy_fcgi, and mod_ssl
change the following in that same file:
```
User daemon
Group daemon 
```
to
```
User www-data
Group www-data
```
Add this line : 
```
<IfModule ssl_module>
        Listen *:443
        SSLEngine on
        # nail it to the securest cipher PS2 understands DHE-RSA-DES-CBC3-SHA
        # check this with openssl
        SSLCipherSuite DHE:!DSS:!AES:!SEED:!CAMELLIA!TLSv1.2
        SSLCertificateFile /etc/dnas/cert-jp.pem
        SSLCertificateKeyFile /etc/dnas/cert-jp-key.pem
        SSLCertificateChainFile /etc/dnas/ca-cert.pem
        ServerName gate1.jp.dnas.playstation.org
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/dnas
        <Directory />
                Options FollowSymLinks
                AllowOverride None
        </Directory>
        <Directory "/var/www/dnas">
                Options -Indexes
                Require all granted
        </Directory>
        # rewrite some URLs
        RewriteEngine on
        RewriteRule ^(/.*)/v2\.5_i-connect$ $1/connect.php [PT]
        RewriteRule ^(/.*)/i-connect$ $1/connect.php [PT]
        RewriteRule ^(/.*)/v2\.5_d-connect$ $1/connect.php [PT]
        RewriteRule ^(/.*)/v2\.5_others$ $1/others.php [PT]
        RewriteRule ^(/.*)/others$ $1/others.php [PT]
        # send this to php-fpm socket (needs write access!)
        <FilesMatch "\.php$">
                SetHandler "proxy:unix:/var/run/php/php7.4-fpm.sock|fcgi://127.0.0.1"
        </FilesMatch>
        ErrorLog /opt/apache/logs/dnas_error.log
        # Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
        LogLevel debug
        CustomLog /opt/apache/logs/dnas_access.log combined
        <FilesMatch "\.(cgi|shtml|phtml|php)$">
                SSLOptions +StdEnvVars
        </FilesMatch>
        <Directory /usr/lib/cgi-bin>
                SSLOptions +StdEnvVars
        </Directory>
        # we need to downgrade protocol for the DNAS browser
        BrowserMatch "open sesame asdfjkl" \
        nokeepalive ssl-unclean-shutdown \
        downgrade-1.0 force-response-1.0
</IfModule>
```

27.
```
sudo systemctl restart apache
```

28.
```
cd
Install Mariadb, php7.4-mysql, and openjdk
sudo apt install mariadb-server
sudo apt install php7.4-mysql
sudo apt install openjdk-17-jre-headless
```

29.
Pulling the SQL data for Outbreak File #1 
```
cd
git clone https://github.com/Necrosiak/BH-Server-Save-NM.git
cd BH-Server-Save-NM/bioserv1
sudo mysql -u root < database/BH-Server-Save-NM.sql
sudo mkdir /var/www/bhof1
sudo cp www/* /var/www/bhof1
sudo chown -R www-data:www-data /var/www/bhof1
sudo ln -s /var/www/bhof1 /var/www/dnas/00000002
```

30.
```
export PATH=$PATH:/usr/local/sbin:/usr/sbin:/sbin
```

31.
```
cd
wget https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-j_8.0.32-1debian11_all.deb
```

32.
```
sudo dpkg --install mysql-connector-j_8.0.32-1debian11_all.deb
```

33.
```
cd ~/BH-Server-Save-NM/bioserv1/BH-Server-Save-NM
javac -cp /usr/share/java/mysql-connector-j-8.0.32.jar:. *.java
```

34.
```
cd ~/BH-Server-Save-NM/bioserv1
```

35.
```
mkdir bin
mkdir bin/BH-Server-Save-NM
mv BH-Server-Save-NM/*.class bin/BH-Server-Save-NM
mv BH-Server-Save-NM/config.properties .
mkdir lib
cp /usr/share/java/mysql-connector-j-8.0.32.jar lib/mysql-connector.jar
chmod +x run_file1.sh
```

36.
Replace the {{External IP}} to the servers actual IP
```
nano config.properties
```

37.
```
cd
wget https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-j_8.0.32-1debian11_all.deb
```

38.
```
cd ~/BH-Server-Save-NM/bioserv2
sudo mysql -u root < database/BH-Server-Save-NM.sql
sudo mysql -u root < database/BH-Server-Save-NM.sql
sudo mkdir /var/www/bhof2
sudo cp www/* /var/www/bhof2
sudo chown -R www-data:www-data /var/www/bhof2
sudo ln -s /var/www/bhof2 /var/www/dnas/00000010
cd BH-Server-Save-NM
javac -cp /usr/share/java/mysql-connector-j-8.0.32.jar:. *.java
cd ..
mkdir bin
mkdir bin/BH-Server-Save-NM
mv BH-Server-Save-NM/*.class bin/BH-Server-Save-NM
mv BH-Server-Save-NM/config.properties .
mkdir lib
cp /usr/share/java/mysql-connector-j-8.0.32.jar lib/mysql-connector.jar
chmod +x run_file2.sh
```
nano config.properties
```
Replace the {{External IP}} to the servers actual IP

## START UP

1.
then run 
```
./run_file1.sh
```
Looks good move on!
```
2.
```
nano config.properties
```

```
cd ~/BH-Server-Save-NM/bioserv1

./run_file1.sh

cd ~/BH-Server-Save-NM/bioserv2

./run_file2.sh
```

or Fire both up at the same time

```
cd ~/BH-Server-Save-NM/bioserv1/
./run_file1.sh &
cd ~/BH-Server-Save-NM/bioserv2/
./run_file2.sh &
```
