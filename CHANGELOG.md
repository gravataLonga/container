# Changelog

All notable changes to `container` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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
