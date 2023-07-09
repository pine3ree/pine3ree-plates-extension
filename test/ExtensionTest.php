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

    /**
     * Return an test array of arrays whose elements, in order, are:
     *
     * - an extension public method name
     * - its return value
     * - a registered alias
     *
     * @return array<int, array>
     */
    public function providePublicMethods(): array
    {
        return [
            ['somethingPublic', DummyExtension::SOMETHING_PUBLIC, 'public'],
            ['somethingElse',   DummyExtension::SOMETHING_ELSE,   'else'],
        ];
    }

    /**
     * @dataProvider providePublicMethods
     */
    public function testThatPublicMethodsAreAutomaticallyRegistered(string $publicMethod): void
    {
        self::assertTrue($this->engine->doesFunctionExist($publicMethod));
    }

    public function testThatNotPublicMethodsAreNotAutomaticallyRegistered(): void
    {
        self::assertFalse($this->engine->doesFunctionExist('somethingProtected'));
        self::assertFalse($this->engine->doesFunctionExist('somethingPrivate'));
    }

    public function provideMagicMethods(): array
    {
        return [
            ['__construct'],
            ['__destruct' ],
            ['__call'     ],
            ['__callStatic'],
            ['__get'      ],
            ['__set'      ],
            ['__isset'    ],
            ['__unset'    ],
            ['__sleep'    ],
            ['__wakeup'   ],
            ['__serialize'],
            ['__unserialize'],
            ['__toString' ],
            ['__invoke'   ],
            ['__set_state'],
            ['__clone'    ],
            ['__debugInfo'],
        ];
    }

    /**
     * @dataProvider provideMagicMethods
     */
    public function testThatMagicMethodsAreNotAutomaticallyRegistered(string $magicMethod): void
    {
        self::assertFalse($this->engine->doesFunctionExist($magicMethod));
    }

    /**
     * @dataProvider providePublicMethods
     */
    public function testThatAutomaticallyRegisterdMethodsAreCallableAndReturnExpectedValues(
        string $publicMethod,
        string $returnValue
    ): void {
        $func = $this->engine->getFunction($publicMethod);

        self::assertInstanceOf(Func::class, $func);

        $callback = $func->getCallback();

        self::assertIsCallable($callback);
        self::assertSame($returnValue, $callback());
    }

    /**
     * @dataProvider providePublicMethods
     */
    public function testThatAutomaticallyRegisterdMethodsAreCallableInTemplatesAndReturnExpectedValues(
        string $publicMethod,
        string $returnValue
    ): void {
        self::assertSame($returnValue, $this->template->{$publicMethod}());
    }

    /**
     * @dataProvider providePublicMethods
     */
    public function testThatAliasesAreRegisteredAndReturnExpectedValues(
        string $publicMethod,
        string $returnValue,
        string $alias
    ): void {
        self::assertTrue($this->engine->doesFunctionExist($alias));
        self::assertSame($this->template->{$publicMethod}(), $this->template->{$alias}());
        self::assertSame($returnValue, $this->template->{$alias}());
    }

    public function testThatRegisterOwnFunctionAddsTheAliasIfSet(): void
    {
        self::assertTrue($this->engine->doesFunctionExist('bar'));
        self::assertSame($this->template->foo(), $this->template->bar());
    }

    /**
     * @dataProvider providePublicMethods
     */
    public function testThatPublicMethodsAreNotRegisteredIfFlagIsSetToFalse(string $publicMethod): void
    {
        $engine    = new Engine(vfsStream::url('templates'));
        $extension = new DummyExtension(false);
        $extension->register($engine);
        $template  = new Template($engine, 'template');

        self::assertFalse($engine->doesFunctionExist($publicMethod));

        $this->expectException(LogicException::class);
        $template->$publicMethod();
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
