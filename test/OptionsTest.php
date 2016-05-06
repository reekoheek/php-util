<?php

namespace ROH\Util\Test;

use ROH\Util\Options;
use ROH\Util\File;
use PHPUnit_Framework_TestCase;

class OptionsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Options::resetEnv();
        mkdir('tmp', 0755);
        file_put_contents('foo.php', '<?php return '.var_export([
            'foo' => 'bar'
        ], true).';');
    }

    public function tearDown()
    {
        File::rm('tmp');
        File::rm('foo.php');
    }

    public function testSetAndGetEnv()
    {
        $this->assertEquals(Options::getEnv(), 'development');
        Options::setEnv('production');
        $this->assertEquals(Options::getEnv(), 'production');
    }

    public function testConstruct()
    {
        $options = new Options();
        $this->assertEquals($options->toArray(), []);

        $options = new Options(['foo' => 'bar']);
        $this->assertEquals($options->toArray(), ['foo' => 'bar']);
    }

    public function testMerge()
    {
        $options = new Options();
        $this->assertEquals($options->merge(['foo' => 'bar']), $options);
        $this->assertEquals($options->toArray(), ['foo' => 'bar']);

        $options = new Options([
            'foo' => 'bar',
            'assoc' => [
                'foo' => 'bar',
            ],
            'assocToSet' => [
                'foo' => 'bar',
            ],
            'array' => [1,2,3],
            'single' => 'foo',
        ]);
        $options->merge([
            'foo!unset' => null,
            'assoc' => ['bar' => 'baz'],
            'assocToSet!set' => ['bar' => 'baz'],
            'array' => [4,5],
            'single' => ['foo', 'bar']
        ]);

        $this->assertEquals($options->toArray(), [
            'assoc' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
            'assocToSet' => [
                'bar' => 'baz',
            ],
            'array' => [ 4, 5, 3],
            'single' => ['foo', 'bar']
        ]);

    }

    public function testMergeFile()
    {

        $options = new Options();
        $this->assertEquals($options->mergeFile('foo.php'), $options);
        $this->assertEquals($options->toArray(), ['foo' => 'bar']);
    }
}
