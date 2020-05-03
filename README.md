# Container

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Container implementation which follow PSR-11.

## Install

Via Composer

``` bash
$ composer require gravatalonga/container
```

## Usage

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

Can be use like an array access also,  

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

In advance usage, you can resolve class which not set into container. Our container it will attemp resolve class from builtin/type hint arguments of constructions.  

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

If argument accept nullable it will attemp resolve if not, it will inject null as argument.  

```php
use Gravatalonga\Container\Container;
class Test
{
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

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email jonathan.alexey16[at]gmail.com instead of using the issue tracker.

## Credits

- [Jonathan Fontes][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/gravatalonga/container.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/gravatalonga/container.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/gravatalonga/container
[link-downloads]: https://packagist.org/packages/gravatalonga/container
[link-author]: https://github.com/gravatalonga
[link-contributors]: ../../contributors
