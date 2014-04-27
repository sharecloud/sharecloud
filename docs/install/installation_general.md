# Installation Guide

You need to setup an Webserver environment first. If you have a webspace, this was supposed to be done
by your Internet hosting service provider.

## Installation

### Get the source code
Either use `git` to clone the repository or download the source code [here](https://github.com/frostieDE/filehost/releases).
	
### Database
Create a database for our local Filehost instance or use an existing one.

### Setup Smarty
Create the following directory:

	{$ROOT_PATH}/classes/smarty/templates_c

where `{$ROOT_PATH}` reffers to the path where you have the source code.

Make sure, PHP has read and write access to this directory.
	
### Setup config.php
Create the `config.php` in the `system` directory and modifiy it to fit your needs.
There is a template `config.php.example`.

### Run installation
Now open a browser and navigate to `http://YOUR_HOST/filehost/install/` and follow the steps there.

### Post-installation clean-up
For security reasons, you should remove the `install` and `upgrade` folders.
