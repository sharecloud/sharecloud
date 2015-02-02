# nginx

For those who prefer using nginx, we have good news: sharecloud runs on nginx, too!

This guide was successfully tested on Ubuntu 14.04 LTS.

## Install PHP5 FPM

	$ sudo apt-get install php5-fpm
	
## Configure nginx

We provide configurations for nginx. Note: Instead of `sharecloud-ssl` you can also use `sharecloud` which disables HTTPS support (this is highly discouraged).

	$ sudo cp support/nginx/sharecloud-ssl /etc/nginx/sites-available/sharecloud-ssl
	$ sudo ln -s /etc/nginx/sites-available/sharecloud-ssl /etc/nginx/sites-enabled/sharecloud-ssl

Then modify the configuration file to fit your needs:

	$ sudo nano /etc/nginx/sites-available/sharecloud-ssl
	
## Restart nginx and PHP FPM

	$ sudo service php5-fpm restart
	$ sudo service nginx restart
	
## FAQ

### Which php.ini is used?

By default, PHP FPM uses a php.ini located in `/etc/php5/fpm/php.ini` (Ubuntu/Debian).
