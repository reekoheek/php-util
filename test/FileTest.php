<?php

namespace ROH\Util\Test;

use ROH\Util\File;
use PHPUnit_Framework_TestCase;

class FileTest extends PHPUnit_Framework_TestCase
{
    public function testRm()
    {
        mkdir('./tmp', 0755);
        touch('./tmp/foo');

        File::rm('./tmp');

        $this->assertEquals(glob('./tmp'), []);
    }
}