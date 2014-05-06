# Installation Guide

## Update source code

Download the latest release from [here](https://github.com/sharecloud/sharecloud/releases) and simply copy all files to your webspace.

## Update your configuration
Let's take a look at the `system/config.php.example` and make sure you add new configuration stuff to your `system/config.php`.

## Migrate database
Check if, there are any `*.sql` files within the `upgrade/` directory. If so, open a webbrowser and navigate to `http://YOUR_HOST/sharecloud/upgrade/` and run all necessary migrations:

** Attention: ** Please make sure you run all migrations in the correct order!