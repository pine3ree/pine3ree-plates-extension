<?php

declare(strict_types=1);

namespace pine3ree\test\Plates\Asset;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use pine3ree\Plates\Extension;
use stdClass;
use Throwable;

class DummyExtension extends Extension implements ExtensionInterface
{
    public const SOMETHING_PUBLIC    = 'somethingPublic';
    public const SOMETHING_ELSE      = 'somethingElse';
    public const SOMETHING_PROTECTED = 'somethingProtected';
    public const SOMETHING_PRIVATE   = 'somethingPrivate';

    protected function registerFunctions(Engine $engine): void
    {
        $this->registerOwnFunction($engine, 'foo', 'bar');
    }

    protected function registerAliases(Engine $engine): void
    {
        try {
            $this->registerAlias($engine, 'public', 'somethingPublic');
            $this->registerAlias($engine, 'else',   'somethingElse');
        } catch (Throwable $ex) {
            // no-op
        }
    }

    public function __construct(bool $autoregisterPublicMethods = true)
    {
        $this->autoregisterPublicMethods = $autoregisterPublicMethods;
    }

    public function somethingPublic(): string
    {
        return self::SOMETHING_PUBLIC;
    }

    public function somethingElse(): string
    {
        return self::SOMETHING_ELSE;
    }

    protected function somethingProtected(): string
    {
        return self::SOMETHING_PROTECTED;
    }

    private function somethingPrivate(): string
    {
        return self::SOMETHING_PRIVATE;
    }

    public function foo(): string
    {
        return 'foo';
    }

    public function __destruct()
    {
        ;
    }

    public function __call(string $name, array $arguments)
    {
        return null;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return null;
    }

    public function __get(string $name)
    {
        return $name;
    }

    public function __set(string $name, $value)
    {
        ;
    }

    public function __isset(string $name)
    {
        return false;
    }

    public function __unset(string $name)
    {
        ;
    }

    public function __sleep()
    {
        return [];
    }

    public function __wakeup()
    {
        ;
    }

    public function __serialize()
    {
        return [];
    }

    public function __unserialize(array $data)
    {
        ;
    }

    public function __toString(): string
    {
        return self::class;
    }

    public function __invoke()
    {
        ;
    }

    public static function __set_state(array $properties)
    {
        return new stdClass();
    }

    public function __clone()
    {
        ;
    }

    public function __debugInfo(): array
    {
        return [];
    }
}
