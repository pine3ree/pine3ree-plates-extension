# pine3ree Plates Extension

This package introduces a common base abstract Extension for Plates native php templating
engine providing:

- autoregistration of public methods (feature that can be disabled)
- the ability to manually register public methods with shorter syntax
- the ability to add function aliases on extension registration
- the ability to add function aliases before extension registration happens

## Autoregistration of public methods

By default, with the exclusion of magic methods, all public methods are registered
as template helpers with the Plates engine, using method names as template helper
name, basically doing the following for you:

```php
<?php

namespace my\App\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface

class MyCoolExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('foo', [$this, 'foo']);
        $engine->registerFunction('baz', [$this, 'baz']);
    }

    public function foo(string $bar): string
    {
        return 'foo' . $bar;
    }

    public function baz(string $bar): string
    {
        return $bar . 'baz';
    }

    //...
}

```
You can rewrite previous code as:

```php
<?php

namespace my\App\Plates;

use League\Plates\Extension\ExtensionInterface
use pine3ree\Plates\Extension

class MyCoolExtension extends Extension implements ExtensionInterface
{
    public function foo(string $bar): string
    {
        return 'foo' . $bar;
    }

    public function baz(string $bar): string
    {
        return $bar . 'baz';
    }

    //...
}

```

```php
<?php
// file: path/to/templates/my-template.phtml

use League\Plates\Template\Template;
use My\App\Plates\MyCoolExtension;

/** @var Template|MyCoolExtension $this */

```

### Disable autoregistration and manually register your public methods

If you do not want to include all your extension's public methods as template helper
you can disable autoregistration via constructor or property assignment, or overriding
the extension `register` method, but this last option will remove other functionalities
provided by this package.

```php
<?php

namespace my\App\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface

class MyCoolExtension implements ExtensionInterface
{
    // 1. Disable via class property override
    protected bool $autoregisterPublicMethods = false;
    
    // 2. Disable via constructor
    public function __construct()
    {
        $this->autoregisterPublicMethods = $autoregisterPublicMethods;
    }

    // 3. Disable via 'register' method override (not advisable, please read following sections)
    public function register(Engine $engine)
    {
        // Manually register your public method keeping the methods'name as template helper name
        $this->registerOwnFunction($engine, 'foo');

        // Manually register your public method 'baz' using different helper name 'doBaz'
        $this->registerOwnFunction($engine, 'baz', 'doBaz');
    }

    public function foo(string $bar): string
    {
        return 'foo' . $bar;
    }

    public function baz(string $bar): string
    {
        return $bar . 'baz';
    }

    //...
}

```

## Registering other functions and aliases

This package provides a couple of overridable methods wher you register functions
other then your extension's public methods or aliases to registered function:

```php

namespace my\App\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface

/**
 * Include your external methods to achieve full ide-autocompletion in template files
 *
 * @method string toUppercase(string $string) Convert string to uppercase
 * @method string uc(string $string) Alias of @see self:.toUppercase()
*/
class MyCoolExtension implements ExtensionInterface
{
    protected function registerFunctions(Engine $engine): void
    {
        $engine->registerFunction($engine, 'toUppercase', function (string $string): string {
            return mb_strtoupper($string);
        });
    }

    protected function registerAliases(Engine $engine): void
    {
        $this->registerAlias($engine, 'uc', 'toUppercase');
    }
}

```

## Registering aliases without subclassing

You have the chance to register extra aliases before a third-party extension
extending this package abstract class is registered with the plates engine, for
instance inside the extension factory.

```php

use League\Plates\Engine;
use Third\Party\Plates\OtherExtension;
use Third\Party\Plates\OtherExtensionFactory;

// $plates is the app League\Plates\Engine instance
// $container is the app Psr\Container\ContainerInterface
// OtherExtension extends pine3ree\Plates\Extension providing `doSomething` 
// and `doSomethingElse` template helpers

$otherExtension = OtherExtensionFactory::create($container);

// Add aliases here before extension registration (or inside your application 
// OtherExtension factory)

$otherExtension->addAlias('ds', 'doSomething');
$otherExtension->addAlias('dse', 'doSomethingElse');

$plates->loadExtension($otherExtension);

```

// The aliases added using `addAlias($alias, $funcName)` are actually registered
when the extensions is loaded by the plates engine, after automatic registration
of public method, after the registration of extra methods and after the extension of
internal registration of aliases. See `Extension::register(Engine $engine)` source code.

## Installation

This library requires `php >= 7.4`

Run the following to install it:

```bash
$ composer require pine3ree/pine3ree-plates-extension
```



## TODO

- Add github ci workflow