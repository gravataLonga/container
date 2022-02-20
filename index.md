## Container PSR-11 Implementation

This library is lightweight in comparison to PHP-DI, there aren't nothing wrong using other rather this implementation. 

### Requirement  

 - PHP 7.4 or greater  
 - Composer installed  

### Install  

`composer require gravatalonga/container`  

### Usage  

Basic usage, is ease to start right away.   


```php 
<?php

require_once "./vendor/autoload.php"

use Gravatalonga\Container;

$container = new Container();

$container->set('config', [
  'database' => 'mysql:\\user:password@localhost/dbname'
]);

if ($container->has('config')) {
  var_dump($container->get('config'));
}
```  

Library has a small footsprint but has powerful feature built in, at some level of cost.  
Some feature is:  
 
 - Factory  
 - Lazy Loading  
 - Share Instance  
 - Autowiring  
 - Alias  

### Update  

We try always to keep a stable release.  
