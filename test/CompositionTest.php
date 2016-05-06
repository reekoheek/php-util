<?php

namespace ROH\Util\Test;

use ROH\Util\Composition;
use PHPUnit_Framework_TestCase;

class CompositionTest extends PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $composition = new Composition();
        $composition->compose(function($context, $next) use (&$hit) {
            $hit++;
            $next($context);
        });

        try {
            $composition->apply();
            $this->fail('Must not here');
        } catch(\Exception $e) {
            if ($e->getMessage() !== 'Core is undefined') {
                throw $e;
            }
        }

        $composition->setCore(function() use (&$coreHit) {
            $coreHit++;
        });

        $composition->apply();
        $this->assertEquals($hit, 1);
        $this->assertEquals($coreHit, 1);

        $this->assertEquals(count($composition->__debugInfo()['chain']), 1);
    }
}