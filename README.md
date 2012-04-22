Behat-Sauce
===========

[Behat](https://github.com/Behat/Behat) integrated with [Sauce On-Demand](https://saucelabs.com/ondemand), including [Sauce Connect](https://saucelabs.com/docs/ondemand/connect) support.

Note on Patches/Pull Requests
-----------------------------
 
- Fork the projecthere, master for releases & hotfixes only).
- Make your feature addition or bug fix.
- Commit
- Send me a pull request.

Installing Dependencies
-----------------------

    wget -nc http://getcomposer.org/composer.phar
    php composer.phar update

Create New Test Suite
---------------------

	bin/behat-sauce --init

Modify `config/behat.yml` to have the correct Sauce credentials (`username` and `access_key`) and site under test url (`base_url`).

Create features, step definitions, as you would with plain-old `behat`.

Run Tests
---------

Behat-Sauce lets you vary the browser/os combination on the command line.

Usage:

    bin/behat-sauce [--browser="..."] [--browser-version="..."] [--os="..."] [features]

Arguments:

     --browser              SauceLabs browser name.  Default is:  firefox
     --browser-version      SauceLabs browser version.  Default is:  7
     --os                   SauceLabs operating system.  Default is:  Windows 2003

Start SauceConnect
------------------

If you need a SauceConnect tunnel to run your tests, you can reuse the credentials you already have in your `config/behat.yml`.

    bin/behat-sauce --tunnel

Make sure you have satisfied all the requirements needed to run [Sacue Connect](https://saucelabs.com/docs/ondemand/connect) before using.

Copyright
---------

Copyright (c) 2012 LB Denker.

Contributors
------------

- LB Denker [elblinkin](http://github.com/elblinkin)
