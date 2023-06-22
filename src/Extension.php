<?php

/**
 * @package P3\Plates\Extension
 * @author  pine3ree https://github.com/pine3ree
 */

namespace P3\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

abstract class Extension implements ExtensionInterface
{
    /**
     * Template function aliases
     *
     * These can be added outside the extension by calling self::addAlias($alias, $name)
     * before the extensions is registered with the plates engine
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Register html-helper functions with the Plates engine.
     */
    public function register(Engine $engine)
    {
        $this->registerFunctions($engine);
        $this->registerAliases($engine);
        if (!empty($this->aliases)) {
            $this->registerAddedAliases($engine);
        }
    }

    abstract protected function registerFunctions(Engine $engine);

    /**
     * Register your aliases internally here!
     *
     * <code>
     * $this->registerAlias($engine, 'myAlias', 'soonToBeRegisteredFunctionName');
     * </code>
     *
     * @param Engine $engine
     */
    protected function registerAliases(Engine $engine)
    {
        // no-op by default, override to set aliases
    }

    /**
     * Register a function alias for given function name with the plates engine
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
     * Add a function alias form external code before the extension is registered
     * with the plates engine
     *
     * @param string $alias The function alias
     * @param string $name The template function name
     */
    public function addAlias(string $alias, string $name)
    {
        $this->aliases[$alias] = $name;
    }

    private function registerAddedAliases(Engine $engine)
    {
        foreach ($this->aliases as $alias => $name) {
            $this->registerAlias($engine, $alias, $name, true);
        }
    }

    /**
     * Register a public method as a plates template function with same or different name
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
