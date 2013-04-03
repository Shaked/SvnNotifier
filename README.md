SvnNotifier
==========

Svn post commit notifier based Silex notifies about SVN commits via email. 

- Supports multiple emails
- Works with a twig template which lets you add your own (and share with others as well) 
- Easy to use 

Installation: 
==============
```
  composer update 
  OR
  php composer.phar update
```

Usage: 
======
``` 
  cli:/path/web/> php index.php -n repoName -e myemail@shakedos.com,another@example.com 

  Optional parameter -r to specify revision(s) number(s) 

  cli:/path/web/> php index.php -n repoName -e myemail@shakedos.com,another@example.com -r r1

  cli:/path/web/> php index.php -n repoName -e myemail@shakedos.com,another@example.com -r r1:r2 
```

Using:
=====
```
	Silex
	Config Service Provider 
	Twig Bridge
	Swiftmailer 
	Symfony Validator 
	Webcreate Util 
	Webcreate Vcs
```

Based on [CVSSPAM](http://www.badgers-in-foil.co.uk/projects/cvsspam/)

