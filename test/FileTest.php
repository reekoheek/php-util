<?php

namespace ROH\Util\Test;

use ROH\Util\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testRm()
    {
        mkdir('./tmp', 0755);
        touch('./tmp/foo');

        File::rm('./tmp');

        $this->assertEquals(glob('./tmp'), []);
    }
}
