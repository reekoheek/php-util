<?php

namespace ROH\Util\Test;

use ROH\Util\Injector;
use ROH\Util\InjectorException;
use ROH\Util\Collection;
use PHPUnit_Framework_TestCase;

class InjectorTest extends PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $injector = Injector::getInstance();
        $this->assertEquals($injector, Injector::getInstance());
        $this->assertEquals($injector->resolve(Injector::class), $injector);

        $this->assertEquals(array_keys($injector->__debugInfo()), ['singletons', 'delegators', 'aliases']);
    }

    public function testSingleton()
    {
        $injector = new Injector();
        $injector->singleton(Collection::class, new Collection(['foo' => 'bar']));
        $this->assertEquals($injector->resolve(Collection::class)->toArray(), ['foo' => 'bar']);
    }

    public function testDelegate()
    {
        $injector = new Injector();
        $injector->delegate('foo', function() {
            return 'bar';
        });
        $this->assertEquals($injector->resolve('foo'), 'bar');
    }

    public function testAlias()
    {
        $injector = new Injector();
        $injector->alias(Foo::class, Baz::class);
        $this->assertInstanceOf(Baz::class, $injector->resolve(Foo::class));
    }

    public function testResolve()
    {
        $injector = new Injector();
        $this->assertEquals($injector->resolve(Collection::class)->toArray(), []);

        $fn = function()  {};
        $this->assertEquals($injector->resolve($fn), $fn);

        $arr = [Collection::class, ['attributes' => ['foo' => 'bar']]];
        $this->assertEquals($injector->resolve($arr)->toArray(), ['foo' => 'bar']);

        $this->assertEquals($injector->resolve(['foo' => 'bar']), ['foo' => 'bar']);
    }

    public function testResolveSpecifiedParam()
    {
        $injector = new Injector();
        $bar = $injector->resolve(Bar::class, ['foo' => new Baz(), 'arr' => [] ]);
        $this->assertInstanceOf(Baz::class, $bar->foo);
    }

    public function testResolveSpecifiedParamAsAnotherContract()
    {
        $injector = new Injector();
        $bar = $injector->resolve(Bar::class, ['@foo' => Baz::class, 'arr' => [] ]);
        $this->assertInstanceOf(Baz::class, $bar->foo);
    }

    public function testResolveUnspecifyingParamAsSingleton()
    {
        $injector = new Injector();
        $baz = new Baz();
        $injector->singleton(Foo::class, $baz);
        $bar = $injector->resolve(Bar::class, ['arr' => []]);
        $this->assertEquals($bar->foo, $baz);
    }

    public function testResolveUnspecifyingParamAsDelegated()
    {
        $injector = new Injector();
        $baz = new Baz();
        $injector->delegate(Foo::class, function() use ($baz) {
            return $baz;
        });
        $bar = $injector->resolve(Bar::class, ['arr' => []]);
        $this->assertEquals($bar->foo, $baz);
    }

    public function testResolveUnresolvedContract()
    {
        $injector = new Injector();
        try {
            $injector->resolve(Foo::class);
            $this->fail('Must not here');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Injector cannot resolve contract') !== 0) {
                throw $e;
            }
        }
    }

    public function testResolveUnresolvedParameter()
    {
        $injector = new Injector();
        try {
            $injector->resolve(Bar::class, ['foo' => new Baz() ]);
            $this->fail('Must not here');
        } catch(\ROH\Util\InjectorException $e) {
            if (strpos($e->getMessage(), 'Unresolved parameter #') !== 0) {
                throw $e;
            }
        }
    }

    public function testResolveWithOptionalParam()
    {
        $injector = new Injector();
        $this->assertEquals($injector->resolve(Collection::class)->toArray(), []);

        $injector = new Injector();
        $this->assertEquals($injector->resolve(Baz1::class)->baz->foo, null);

        $injector = new Injector();
        $this->assertEquals($injector->resolve(Foo1::class)->foo->foo, null);
    }

    public function testResolveThrowError()
    {
        $injector = new Injector();
        try {
            $injector->resolve(Baz3::class);
            $this->fail('Must not here');
        } catch (InjectorException $e) {}

        try {
            $injector->resolve(Baz4::class);
            $this->fail('Must not here');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Ouch') {
                throw $e;
            }
        }
    }
}

interface Foo {}
class Bar { public $foo; public function __construct(Foo $foo, array $arr) { $this->foo = $foo; } }
class Baz implements Foo { public $foo; public function FunctionName(Foo $foo = null) { $this->foo = $foo; }}
class Baz1 { public $baz; public function __construct(Baz $baz) { $this->baz = $baz; }}
class Baz2 { public $baz; public function __construct(Baz $baz = null) { $this->baz = $baz; }}
class Baz3 { public $foo; public function __construct(Foo $foo) { $this->foo = $foo; }}
class Baz4 { public $baz5; public function __construct(Baz5 $baz5) { $this->baz5 = $baz5; }}
class Baz5 { public function __construct() { throw new \Exception('Ouch'); }}
class Foo1 { public function __construct(Foo2 $foo) { $this->foo = $foo; }}
class Foo2 { public function __construct(Foo2 $foo = null) { $this->foo = $foo; }}