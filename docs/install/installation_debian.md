# Installation Guide

You need to setup an Webserver environment first. 

## Installation

### Note
This installation guide was created on a Debian 7 machine and should also work on Ubuntu servers.

As we are logged in as root, we will perform all actions as `www-data` user, so we won't have any problems
with access control when using Apache. If you want to perform all action in the context of the current
user, simply remove `sudo -u www-data -H` from all shell commands.

### Get the source code

	$ cd /var/www
	$ sudo -u www-data -H git clone https://github.com/sharecloud/sharecloud.git sharecloud/
	
You are then at the master-branch, which is supposed to have the latest stable code. If you want to use
the current developer branch:

	$ sudo -u www-data -H git checkout dev

Let's switch to the sharecloud directory:

	$ cd sharecloud/	
	
### Database
Now, we create a new database for your local sharecloud instance.

Make sure, you have secured your MySQL installation:

	$ sudo mysql_secure_installation

#### Database with own user

	$ mysql -u root -p
	
Type the database root password
	
	mysql> CREATE USER sharecloud@localhost IDENTIFIED BY '{$password}';

Where `{$password}` should be substituted with a proper password.

	mysql> CREATE DATABASE IF NOT EXISTS `sharecloud` DEFAULT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci`;
	mysql> GRANT SELECT, LOCK TABLES, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER ON `sharecloud`.* TO sharecloud@localhost;
	mysql> \q

	
Let's test the connection:

	$ mysql -u sharecloud -p -D sharecloud
	
Type the password you have set earlier

You should see a 'mysql>' prompt now.

	mysql> \q

#### Database without own user
Run:

	$ mysql -u root -p
	mysql> CREATE DATABASE IF NOT EXISTS `sharecloud` DEFAULT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci`;

### Setup Smarty

	$ sudo -u www-data -H chmod 700 /var/www/sharecloud/classes/smarty/templates_c
	
### Setup config.php

	$ sudo -u www-data -H cp system/config.php.example system/config.php
	$ sudo -u www-data -H nano system/config.php

Modify `config.php` to fit your needs!

### Run installation
Now open a browser and navigate to `http://YOUR_HOST/sharecloud/install/` and follow the steps there.

### Post-installation clean-up
For security reasons, you should remove the `install` and `upgrade` folders:

	$ sudo -u www-data -H rm -rf install/
	$ sudo -u www-data -H rm -rf upgrade/

### Install optional dependencies

For best experience, you should install `imagick` and PHP's rar-extension:

	$ apt-get install php5-imagick php5-dev
	$ pecl -v install rar