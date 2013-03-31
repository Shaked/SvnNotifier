SvnNoifier
==========

Svn post commit notifier based Silex notifies about SVN commits via email. 

- Supports multiple emails
- Works with a twig template which lets you add your own (and share with others as well) 
- Easy to use 

Installation using Composer: 

```
	"require":"shaked/svnnoifier": "dev-master"
```

Usage: 

``` 
  cli:/path/web/> php index.php -r repoName -e myemail@shakedos.com,another@example.com
```

Based on [CVSSPAN](http://www.badgers-in-foil.co.uk/projects/cvsspam/)
