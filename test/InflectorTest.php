<?php

namespace ROH\Util\Test;

use ROH\Util\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorTest extends TestCase
{
    public function setUp()
    {
        Inflector::reset();
    }

    public function testTableize()
    {
        $this->assertEquals(Inflector::tableize('FooBar'), 'foo_bar');
    }

    public function testClassify()
    {
        $this->assertEquals(Inflector::classify('foo_bar'), 'FooBar');
    }

    public function testHumanize()
    {
        $this->assertEquals(Inflector::humanize('foo_bar'), 'Foo bar');
        $this->assertEquals(Inflector::humanize('FooBar'), 'Foo bar');
        $this->assertEquals(Inflector::humanize(null), '');
    }

    public function testCamelize()
    {
        $this->assertEquals(Inflector::camelize('foo_bar'), 'fooBar');
    }

    public function testPluralize()
    {
        $this->assertEquals(Inflector::pluralize('man'), 'men');
        $this->assertEquals(Inflector::pluralize('alias'), 'aliases');
        $this->assertEquals(Inflector::pluralize('picture'), 'pictures');
        $this->assertEquals(Inflector::pluralize('foo'), 'foos');
        $this->assertEquals(Inflector::pluralize('fish'), 'fish');

        $this->assertEquals(Inflector::pluralize('foo'), 'foos');
    }

    public function testSingularize()
    {
        $this->assertEquals(Inflector::singularize('men'), 'man');
        $this->assertEquals(Inflector::singularize('pictures'), 'picture');
        $this->assertEquals(Inflector::singularize('foos'), 'foo');
        $this->assertEquals(Inflector::singularize('fish'), 'fish');
        $this->assertEquals(Inflector::singularize('sox'), 'sox');

        $this->assertEquals(Inflector::singularize('foos'), 'foo');
    }
}
