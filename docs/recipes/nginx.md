# nginx

For those who prefer using nginx, we have good news: sharecloud runs on nginx, too!

This guide was successfully tested on Ubuntu 14.04 LTS.

## Install PHP5 FPM

	$ sudo apt-get install php5-fpm
	
## Configure nginx to use PHP FPM

We need to tell nginx to use PHP FPM.

	$ sudo nano /etc/nginx/sites-available/default`

In this file, modify the `location ~ \.php$` section as follow:
	
	location ~ [^/]\.php(/|$) {
			fastcgi_split_path_info ^(.+?\.php)(/.*)$;
			if (!-f $document_root$fastcgi_script_name) {
					return 404;
			}
			
			# With php5-cgi alone:
			# fastcgi_pass 127.0.0.1:9000;
			# With php5-fpm:
			fastcgi_pass php;
			fastcgi_index index.php;
			include fastcgi_params;
	}
	
Source: [http://wiki.nginx.org/PHPFcgiExample](http://wiki.nginx.org/PHPFcgiExample)

Note: in our configuration `cgi.fix_pathinfo` was not set (which means its value is set to 1) in our `php.ini`.

For security reasons, you should uncomment (or add) the following section:

	location ~ /\.ht {
		deny all;
	}
	
## Restart nginx and PHP FPM

	$ sudo service php5-fpm restart
	$ sudo service nginx restart
	
## FAQ

### Which php.ini is used?

By default, PHP FPM uses a php.ini located in `/etc/php5/fpm/php.ini` (Ubuntu/Debian).