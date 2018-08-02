<?php

namespace ROH\Util\Test;

use ROH\Util\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testConstruct()
    {
        $collection = new Collection();
        $this->assertEquals($collection->toArray(), []);

        $collection = new Collection(['foo' => 'bar']);
        $this->assertEquals($collection->toArray(), ['foo' => 'bar']);

        $collection = new Collection($collection);
        $this->assertEquals($collection->toArray(), ['foo' => 'bar']);
    }

    public function testAsArrayAccess()
    {
        $collection = new Collection([
            'foo' => 'bar',
        ]);

        $this->assertEquals($collection['foo'], 'bar');

        $collection['foo'] = 'baz';
        $collection['bar'] = 'baz';

        $this->assertEquals($collection['foo'], 'baz');
        $this->assertEquals($collection['bar'], 'baz');

        unset($collection['bar']);
        $this->assertEquals($collection['bar'], null);
    }

    public function testAsIteratorAndCountable()
    {
        $collection = new Collection([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'foo',
        ]);

        $this->assertEquals(iterator_to_array($collection)['foo'], 'bar');
        $this->assertEquals(iterator_to_array($collection)['bar'], 'baz');
        $this->assertEquals(iterator_to_array($collection)['baz'], 'foo');

        $this->assertEquals(count($collection), 3);
    }

    public function testCompare()
    {
        $collection = new Collection([]);
        $collection2 = new Collection([]);
        $this->assertEquals($collection->compare($collection2), 0);

        $collection2 = new Collection(['foo' => 'bar']);
        $this->assertEquals($collection->compare($collection2), 1);
    }

    public function testDebugInfoJsonSerializeAndToArray()
    {
        $collection = new Collection([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'foo',
        ]);

        $this->assertEquals($collection->__debugInfo(), $collection->toArray());
        $this->assertEquals($collection->jsonSerialize(), $collection->toArray());
    }
}
