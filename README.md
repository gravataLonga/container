# Container

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![PHP Unit][ico-action]][link-action]

Container implementation which follow PSR-11.

## Requirements  

Require at least PHP >= 7.1.3.    

## Installation

```bash
composer require gravatalonga/container
```

## Usage

### Basic Usage  

```php
use Gravatalonga\Container\Container;

$container = new Container();
$container->set('random', function() {
    return rand(0, 1000);
});

$container->share('random1', function() {
    return rand(0, 1000);
});

echo $container->get('random'); // get random number each time you call this.

if ($container->has('random1'))  {
    echo $container->get('random1'); // get same random number.  
}
```

When creating a new instance of Container, you can pass on first argument configurations or entries to be already binded into container.  

```php
use Gravatalonga\Container\Container;

new Container([
    'settings' => ['my-settings'], 
    FooBar::class => function (\Psr\Container\ContainerInterface $c) { 
        return new FooBar();
    }
]);
```

### Using Service Provider  

```php
use Gravatalonga\Container\Container;


$container = new Container();
$container->set(RedisClass::class, function () {
    return new RedisClass();
});

// then you can use...
$cache = $container->get('Cache');
```

When using `set`, `factory` or `share` with Closure and if you want to get `Container` it's self, you can pass type hint of `ContainerInterface` as argument.  

```php
use Gravatalonga\Container\Container;
use Psr\Container\ContainerInterface;

$container = new Container([
    'server' => 'localhost',
    'username' => 'root'
]);

$container->set('Cache', function (ContainerInterface $container) {
    // some where you have binding this RedisClass into container... 
    return $container->make(RedisClass::class, [
        'server' => $container->get('server'), 
        'username' => $container->get('username')
    ]);
});

// factory is alias for set.  
$container->factory('CacheManager', function() {
    return new CacheManager();
});

// then you can use...
$cache = $container->get('Cache');
```

### Using Array like access  

```php
use Gravatalonga\Container\Container;

$container = new Container();  
$container[FooBar::class] = function(ContainerInterface $container) {
    return new FooBar($container->get('settings'));
};

if (isset($container[FooBar::class])) {
    echo $container[FooBar::class]->helloWorld();
}
```  

### Advance usage  

You can resolve class which not set into container. Our container it will attempt resolve from builtin/type hint arguments of constructions.  

> **Information**: Builtin is type which is built in on PHP, which is `string`, `int`, `boolean`, etc. Type Hint is type which is created by user land, for example, when creating a class you are creating a new type.  

### Using Type Hint Class  

```php
use Gravatalonga\Container\Container;

class FooBar {}

class Test
{
    public function __construct(FooBar $foobar)
    {
        $this->foobar = $foobar;
    }
}

$container = new Container(); 
$container->set(FooBar::class, function () {
    return new FooBar();
});

$container->get(Test::class); // FooBar it will inject into Test class.  
```

**Note:** We only support resolving auto wiring argument on construction if they is binded into container. Otherwise it will throw an exception.  

### Using Built in type  

```php
use Gravatalonga\Container\Container;
class Test
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

$container = new Container(); 
$container->set('name', 'my-var');

$container->get(Test::class); // my-var it will inject into Test class.  
```  

If argument accept nullable it will attempt resolve; otherwise it will inject null as argument.  

```php
use Gravatalonga\Container\Container;

class Test
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name = null)
    {
        $this->name = $name;
    }
}

$container = new Container(); 

$container->get(Test::class); // null it will inject into Test class.  
```  

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
composer grumphp
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CODE_OF_CONDUCT](.github/CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email jonathan.alexey16[at]gmail.com instead of using the issue tracker.

## Credits

- [Jonathan Fontes][link-author]
- [Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/gravatalonga/container.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/gravatalonga/container.svg?style=flat-square
[ico-action]: https://github.com/gravataLonga/container/workflows/Continuous%20Integration/badge.svg?branch=master
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/gravatalonga/container.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/gravatalonga/container.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/gravatalonga/container
[link-downloads]: https://packagist.org/packages/gravatalonga/container
[link-author]: https://github.com/gravatalonga
[link-scrutinizer]: https://scrutinizer-ci.com/g/gravatalonga/container/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/gravatalonga/container
[link-contributors]: https://github.com/gravataLonga/container/graphs/contributors
[link-action]: https://github.com/gravataLonga/container/actions?query=workflow%3A%22PHP+Composer%22

