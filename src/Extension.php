<?php

/**
 * @package P3\Plates\Extension
 * @author  pine3ree https://github.com/pine3ree
 */

namespace P3\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;
use ReflectionMethod;

use function array_combine;
use function array_diff_key;
use function get_class_methods;

abstract class Extension implements ExtensionInterface
{
    /** Assigned on registered function calls */
    public ?Template $template = null;

    /**
     * Template function aliases
     *
     * These can be added outside the extension by calling:
     * <code>
     * self::addAlias($alias, $name)
     * </code>
     * before the extensions is registered with the plates engine
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Calls:
     *
     * - <code>$this->registerFunctions(Engine $engine)</code>
     * - <code>$this->registerAliases(Engine $engine)</code>
     * - <code>$this->registerExtraAliases(Engine $engine)</code>
     *
     * in order to register function, own methods as template functions and aliases
     *
     * If you override this method, make sure you call the parent method or
     * customize the way the internal registration functions are called
     *
     * {@inheritDoc}
     */
    public function register(Engine $engine)
    {
        $this->registerFunctions($engine);
        $this->registerAliases($engine);
        if (!empty($this->aliases)) {
            $this->registerExtraAliases($engine);
        }
    }

    /**
     * Register your functions here by calling:
     *
     * - <code>$engine->registerFunction($name, $callback)</code>
     * - <code>$this->registerOwnFunction($engine, 'methodName')</code>
     * - <code>$this->registerOwnFunction($engine, 'methodName', 'funcName')</code>
     *
     * or let the default implementation register all public methods not defined
     * in the base abstract class and excluding magic methods
     */
    protected function registerFunctions(Engine $engine)
    {
        $this->autoregisterPublicMethods($engine);
    }

    protected function autoregisterPublicMethods(Engine $engine)
    {
        $base_methods = get_class_methods(self::class);
        $base_methods = array_combine($base_methods, $base_methods);

        $this_methods = get_class_methods($this);
        $this_methods = array_combine($this_methods, $this_methods);

        $methods = array_diff_key($this_methods, $base_methods);

        // Remove magic methods, just in case
        unset(
            $methods['__construct'],
            $methods['__destruct'],
            $methods['__get'],
            $methods['__set'],
            $methods['__isset'],
            $methods['__clone'],
            $methods['__toString'],
        );

        foreach ($methods as $method) {
            $rm = new ReflectionMethod($this, $method);
            if ($rm->isPublic()) {
                $this->registerOwnFunction($engine, $method);
            }
        }
    }

    /**
     * Register your aliases here by calling the following function for each alias:
     *
     * <code>
     * $this->registerAlias($engine, 'myAlias', 'registeredFunctionName');
     * </code>
     *
     * Note:
     *
     * For each registered alias a <code>@method<code> entry should be added to
     * the extension class main php-doc block in order to achieve full ide
     * autocompletion inside template files
     *
     * @param Engine $engine
     */
    protected function registerAliases(Engine $engine)
    {
        // no-op by default, override to set aliases
    }

    /**
     * Registers a function alias for given function name with the plates engine
     *
     * @param Engine $engine The plates engine
     * @param string $alias The desired function alias. Must not be already used as a function name,
     * @param string $name The name of a registered function (existence check can be skipped if not needed)
     * @param bool $check Check that the aliased function is registered?
     */
    protected function registerAlias(Engine $engine, string $alias, string $name, bool $check = false)
    {
        if ($check === false || $engine->doesFunctionExist($name)) {
            $engine->registerFunction($alias, $engine->getFunction($name)->getCallback());
        }
    }

    /**
     * Allows to add a function alias from external code but before the extension
     * is registered with the plates engine
     *
     * Note:
     * When this method is called the function with name <code>$name</code>
     * is not already registered yet. It will be when the extension is registered
     * whit the template engine. Calling this method after the extension is
     * registered has no effect as all the extra aliases are examined and
     * registered when the moment the extension is registered.
     *
     * @param string $alias The function alias
     * @param string $name The target template function name
     */
    public function addAlias(string $alias, string $name)
    {
        $this->aliases[$alias] = $name;
    }

    protected function registerExtraAliases(Engine $engine)
    {
        foreach ($this->aliases as $alias => $name) {
            $this->registerAlias($engine, $alias, $name, true);
        }
    }

    /**
     * Registers a public method as a plates template function with same or different name
     *
     * @param Engine $engine The plates engine
     * @param string $method The name of the method to register
     * @param string $name The name of the template function if different from the method name
     */
    protected function registerOwnFunction(Engine $engine, string $method, string $name = null)
    {
        $engine->registerFunction($name ?? $method, [$this, $method]);
    }
}
