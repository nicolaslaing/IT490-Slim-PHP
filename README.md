# Apache 2 Setup

Edit the following files via the commands below. Make sure the Directory paths match the path to the root PHP folder. 

    sudo vim /etc/apache2/apache2.conf
    sudo vim /etc/apache2/sites-enabled/000-default.conf

If you see '/var/www/' change to '/var/www/html/path/to/php/files', or where ever your root apache files are located. Mine are in '/var/www/html'. After saving the changes, restart apache2 service.

	sudo service apache2 restart

# Slim Framework 3 Skeleton Application

Use this skeleton application to quickly setup and start working on a new Slim Framework 3 application. This application uses the latest Slim 3 with the PHP-View template renderer. It also uses the Monolog logger.

This skeleton application was built for Composer. This makes setting up a new Slim Framework application quick and easy.

## Install the Application

Run this command from the directory in which you want to install your new Slim Framework application.

    php composer.phar create-project slim/slim-skeleton [my-app-name]

Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writeable.

To run the application in development, you can run these commands 

	cd [my-app-name]
	php composer.phar start

Run this command in the application directory to run the test suite

	php composer.phar test

# Adding to the Composer

Your PHP controller files will be using namespaces. These namespaces need to point to the correct file path. Here is a default configuration that must be added to composer.json:

	"autoload": {
	    "psr-4": {
	        "app\\" : "",
	        "App\\" : "src/"  
	    }
	}

When you define the namespace,

	namespace app/controller;

the code will fine "app\\" in the autoloader, and mark that as the path to the class object you are defining. If you had a folder structure such as:

	slim-php-app
		-controller
			--LoginController.php
		-src
			--routes.php
		-etc...

Then you would say the namespace is 'app/controller', not 'slim-php-app/controller'. If you changed the autoload to 'slim-php-app\\' THEN you would use the latter definition.


That's it! Now go build something cool.
