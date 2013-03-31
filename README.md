SvnNoifier
==========

Svn post commit notifier based Silex notifies about SVN commits via email. 

- Supports multiple emails
- Works with a twig template which lets you add your own (and share with others as well) 
- Easy to use 

Installation: 
```
  composer update 
  OR
  php composer.phar update
```

Usage: 

``` 
  cli:/path/web/> php index.php -r repoName -e myemail@shakedos.com,another@example.com
```

Based on [CVSSPAM](http://www.badgers-in-foil.co.uk/projects/cvsspam/)

Using:
```
      Silex
      Config Service Provider 
      Twig Bridge
	    Swiftmailer 
	    Symfony Validator 
	    Webcreate Util 
	    Webcreate Vcs
```
