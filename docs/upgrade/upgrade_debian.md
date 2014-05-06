# Installation Guide

## Update source code

	$ cd /var/www/sharecloud/
	$ sudo -u www-data -H git pull

## Update your configuration
Check if there are any changes in the `config.php`:

	$ sudo -u www-data -H git diff system/config.php system/config.php.example


## Migrate database

Check if, there are any `*.sql` files within the `upgrade/` directory. If so, open a webbrowser and navigate to `http://YOUR_HOST/sharecloud/upgrade/` and run all necessary migrations:

** Attention: ** Please make sure you run all migrations in the correct order!