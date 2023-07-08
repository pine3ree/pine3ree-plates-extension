<?php

declare(strict_types=1);

namespace pine3ree\test\Plates;

use League\Plates\Engine;
use League\Plates\Template\Func;
use League\Plates\Template\Template;
use LogicException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use pine3ree\test\Plates\Asset\DummyExtension;

final class ExtensionTest extends TestCase
{
    private Engine $engine;
    private DummyExtension $extension;
    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();

        vfsStream::setup('templates');

        $this->engine    = new Engine(vfsStream::url('templates'));
        $this->extension = new DummyExtension();
        $this->extension->register($this->engine);
        $this->template  = new Template($this->engine, 'template');
    }

    public function testThatPublicMethodsAreAutomaticallyRegistered(): void
    {
        self::assertTrue($this->engine->doesFunctionExist('somethingPublic'));
        self::assertTrue($this->engine->doesFunctionExist('somethingElse'));
    }

    public function testThatNotPublicMethodsAreNotAutomaticallyRegistered(): void
    {
        self::assertFalse($this->engine->doesFunctionExist('somethingProtected'));
        self::assertFalse($this->engine->doesFunctionExist('somethingPrivate'));
    }

    public function testThatMagicMethodsAreNotAutomaticallyRegistered(): void
    {
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
        self::assertTrue($this->engine->doesFunctionExist('public'));
        self::assertTrue($this->engine->doesFunctionExist('else'));
    }

    public function testThatAutomaticallyRegisterdMethodsAreCallableAndReturnExpectedValues(): void
    {
        $somethingPublicFunc = $this->engine->getFunction('somethingPublic');
        $somethingElseFunc   = $this->engine->getFunction('somethingElse');

        self::assertInstanceOf(Func::class, $somethingPublicFunc);
        self::assertInstanceOf(Func::class, $somethingElseFunc);

        $somethingPublicCallback = $somethingPublicFunc->getCallback();
        $somethingElseCallback   = $somethingElseFunc->getCallback();

        self::assertIsCallable($somethingPublicCallback);
        self::assertIsCallable($somethingElseCallback);

        self::assertSame(DummyExtension::SOMETHING_PUBLIC, $somethingPublicCallback());
        self::assertSame(DummyExtension::SOMETHING_ELSE,   $somethingElseCallback());
    }

    public function testThatAutomaticallyRegisterdMethodsAreCallableInTemplatesAndReturnExpectedValues(): void
    {
        self::assertSame(DummyExtension::SOMETHING_PUBLIC, $this->template->somethingPublic());
        self::assertSame(DummyExtension::SOMETHING_ELSE,   $this->template->somethingElse());
    }

    public function testThatRegisterOwnFunctionAddsTheAliasIfSet(): void
    {
        self::assertTrue($this->engine->doesFunctionExist('bar'));
        self::assertSame($this->template->foo(), $this->template->bar());
    }

    public function testThatPublicMethodsAreNotRegisteredIfFlagIsSetToFalse(): void
    {
        $engine    = new Engine(vfsStream::url('templates'));
        $extension = new DummyExtension(false);
        $extension->register($engine);
        $template  = new Template($engine, 'template');

        self::assertFalse($engine->doesFunctionExist('somethingProtected'));
        self::assertFalse($engine->doesFunctionExist('somethingPrivate'));

        $this->expectException(LogicException::class);
        $template->somethingPublic();

        $this->expectException(LogicException::class);
        $template->somethingElse();
    }

    public function testThatExtraAliaseCanBeAddedOnlyBeforeExtensionRegistration(): void
    {
        $engine    = new Engine(vfsStream::url('templates'));
        $extension = new DummyExtension(true);
        $extension->addAlias('baz', 'foo');
        $extension->register($engine);
        $extension->addAlias('bat', 'foo');

        $template  = new Template($engine, 'template');

        self::assertTrue($engine->doesFunctionExist('baz'));
        self::assertFalse($engine->doesFunctionExist('bat'));

        self::assertSame($template->foo(), $template->baz());

        $this->expectException(LogicException::class);
        $template->bat();
    }
}
