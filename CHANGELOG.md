# Changelog

All notable changes to `container` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 1.6.0 - 2020-05-18  

### Added
- New feature `extended` method was added to extended already binded entries.  
- Detecting Circular Dependencies
- New method `isAlias` method to check if a entry is aliases.  

### Deprecated
- Nothing

### Fixed
- Remove some complexity from main class, and put it on sister class.    

### Removed
- Nothing

### Security
- Nothing

## 1.5.2 - 2020-05-10

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Remove some complexity from main class, and put it on sister class.    

### Removed
- Nothing

### Security
- Nothing

## 1.5.1 - 2020-05-10

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Add optional arguments for auto wiring.  

### Removed
- Nothing

### Security
- Nothing

## 1.5.0 - 2020-05-09

### Added
- new feature added `factory` and `set` accept array callable, e.g.: `$container->set('my-entry', [Test::class, 'getFoo']);`  
- new feature added `alias`, in order to make aliases to match one entry to another.  

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing

## 1.4.2 - 2020-05-10

### Added
- More testing to covering more edges cases.  
- Benchmarking to test speed.  
- Documentations corrections thank to [drupol](https://github.com/drupol).  

### Deprecated
- Nothing

### Fixed
- Organize some files inside the correct folder, thank to [drupol](https://github.com/drupol).  
- PHPStan give some error on `findType` on `ContainerException`.  


### Removed
- Nothing

### Security
- Nothing

## 1.4.1 - 2020-05-06

### Added
- Added: GrumpPHP, thank to [drupol](https://github.com/drupol).  

### Deprecated
- Nothing

### Fixed
- Some internal change of method visibility.

### Removed
- Nothing

### Security
- Nothing

## 1.4.0 - 2020-05-06

### Added
- ```make(string $id, array $arguments = [])``` it will resolve a dependency from container with argument supplier
if argument not supplier it will resolve with information of container.  
- ```ContainerException``` it will raise an exception if called for entry registed as ```share```.  

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing

## 1.3.1 - 2020-05-05

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- When passing a factory function, the argument aren't resolve by container, only resolving 
typehint of class. Now we can support resolve dependencies on factory.
- If you typehint ```ContainerInterface``` it will resolve itself into factory.  

### Removed
- Nothing

### Security
- Nothing

## 1.3.0 - 2020-05-03

### Added
- Can resolve built in type of class.  
- If argument on construct is nullable, then if can't be resolve it will inject null class.  

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing

## 1.2.1 - 2020-05-03

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Namespaces is now inside the correct namespaces. Change from `Gravatalonga\Container` to `Gravatalonga\Container\Container`.  

### Removed
- Nothing

### Security
- Nothing


## 1.2.0 - 2020-05-01

### Added
- `set` can accept mixed value as second argument rather than Closure   
- `offsetGet` a way to get entry from container. Ease way to get entry like `$container['entry']`  
- `offsetSet` get entry from container. Easy way to include entry to container like `$container['entry'] = "ola";`  
- `offsetExists` it's same as `has`.  
- `offsetUnset` remove entry from `$container`    

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing


## 1.1.0 - 2020-04-19

### Added
- `getInstance` get container instance  
- `setInstance` set container itself inside the container  

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing


## 1.0 - 2020-04-18

### Added
- `share` method which resolve to same instance  
- `get` get entry  
- `has` check if entry exists on container  
- `set` it is alias for `make`  
- `make` add entry in factory maner.  

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
