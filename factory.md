# Factory  

Factories is a way of setting values inside of container, and for that we use this method `factory`.  
> Note: `factory` and `set` are same.


## Usage  

```php
<?php

// ... autoload stuff

$container->factory('redis', function() {
    return new Redis();
});

```   

Factory are lazy loading by default, which mean it's only be resolve only we get a instance of it.  

```php
<?php

// ...
$redis = $container->get('redis');
```  

But this example isn't show everthing, we can pass arguments on factory to be resolve and passed along. 

```php
<?php

// set and factory is same, but in this case, isn't lazy loading, if you want lazy loading, 
// create a function instead.  
$container->set('config', ['host' => '127.0.0.1', 'user' => 'root', 'password' => '1234']);

$container->factory('pdo', function (array $config) {
    return new \PDO($config);  
});

```

There are more, container will attempt resolve parameter by it's default value too, if can't be found on container it 
self.  

```php
<?php

// it's almost same as above.  
$container->factory('pdo', function (array $config = ['host' => '127.0.0.1', 'user' => 'root', 'password' => '1234']) {
    return new \PDO($config);  
});

```  

There are one more aspect to noticed, until now we are using string as key, but you can use class name as id.  

```php
<?php

// it's almost same as above.  
$container->factory(PdoInterface::class, function (array $config) {
    return new \PDO($config);  
});

// then use it

$pdo = $container->get(PdoInterface::class);

```  