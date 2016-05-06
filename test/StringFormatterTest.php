<?php

namespace ROH\Util\Test;

use ROH\Util\StringFormatter;
use ROH\Util\Collection;
use PHPUnit_Framework_TestCase;

class StringFormatterTest extends PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $this->assertEquals(StringFormatter::format('foo'), 'foo');
        $this->assertEquals(StringFormatter::format('foo {foo}', ['foo' => 'bar']), 'foo bar');
        $this->assertEquals(StringFormatter::format('foo {foo}', new Collection(['foo' => 'bar'])), 'foo bar');

        try {
            StringFormatter::format('foo {foo}', new \stdClass());
            $this->fail('Must not here');
        } catch(\Exception $e) {

        }
    }
}