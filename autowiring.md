# Autowiring  

Auto wiring it can be a complex topic on other PSR-11 implementation, but we like it simple and ease, so we don't over
exaggerated in it's complexity.  

Our approach is using, type binding on factory, closure or construct on class and it's default values.  

## Usage  

```php
<?php

class DriverManager 
{
    public function __construct(string $driver = 'default')
    {
    
    }
    
    public function resolve(): array
    {
        return [/* ... */];
    }
}

$container->factory(Redis::class, function (DriverManager $manager) {
    return new Redis($manager->resolve('redis-configuration'));
});

$redis = $container->get(Redis::class); // Driver Manager is auto resolved by looking if exists on class_exists, and check 

```  

DriverManager has also a dependecy on constructor, but it has default value of 'default'. Notice, DriverManager isn't bind into container.  

Remember, you can also use `ContainerInterface` or `Container` as dependency, because both of them are [alias](./alias).  

```php
<?php

$container->factory(Redis::class, function (\Psr\Container\ContainerInterface $container) {
    return new Redis($container->get('config'));
});

// OR 

$container->factory(Redis::class, function (\Gravatalonga\Container $container) {
    return new Redis($container->get('config'));
});
```  