<?php

declare(strict_types=1);

namespace P3\Test\Plates;

use PHPUnit\Framework\TestCase;

use League\Plates\Engine;
use P3\Test\Plates\Asset\DummyExtension;

final class ExtensionTest extends TestCase
{
    private Engine $engine;
    private DummyExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new Engine();
        $this->extension = new DummyExtension();
    }

    public function testThatPublicMethodsAreAutomaticallyRegistered(): void
    {
        $this->extension->register($this->engine);

        self::assertTrue($this->engine->doesFunctionExist('somethingPublic'));
        self::assertTrue($this->engine->doesFunctionExist('somethingElse'));
    }

    public function testThatNotPublicMethodsAreNotAutomaticallyRegistered(): void
    {
        $this->extension->register($this->engine);

        self::assertFalse($this->engine->doesFunctionExist('somethingProtected'));
        self::assertFalse($this->engine->doesFunctionExist('somethingPrivate'));
    }

    public function testThatMagicMethodsAreNotAutomaticallyRegistered(): void
    {
        $this->extension->register($this->engine);

        self::assertFalse($this->engine->doesFunctionExist('__construct'));
        self::assertFalse($this->engine->doesFunctionExist('__destruct'));
        self::assertFalse($this->engine->doesFunctionExist('__call'));
        self::assertFalse($this->engine->doesFunctionExist('__callStatic'));
        self::assertFalse($this->engine->doesFunctionExist('__get'));
        self::assertFalse($this->engine->doesFunctionExist('__set'));
        self::assertFalse($this->engine->doesFunctionExist('__isset'));
        self::assertFalse($this->engine->doesFunctionExist('__unset'));
        self::assertFalse($this->engine->doesFunctionExist('__sleep'));
        self::assertFalse($this->engine->doesFunctionExist('__wakeup'));
        self::assertFalse($this->engine->doesFunctionExist('__serialize'));
        self::assertFalse($this->engine->doesFunctionExist('__unserialize'));
        self::assertFalse($this->engine->doesFunctionExist('__toString'));
        self::assertFalse($this->engine->doesFunctionExist('__invoke'));
        self::assertFalse($this->engine->doesFunctionExist('__set_state'));
        self::assertFalse($this->engine->doesFunctionExist('__clone'));
        self::assertFalse($this->engine->doesFunctionExist('__debugInfo'));
    }

    public function testThatAliasesAreRegistered(): void
    {
        $this->extension->register($this->engine);

        self::assertTrue($this->engine->doesFunctionExist('public'));
        self::assertTrue($this->engine->doesFunctionExist('else'));
    }
}
