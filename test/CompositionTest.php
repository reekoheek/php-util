<?php

namespace ROH\Util\Test;

use ROH\Util\Composition;
use ROH\Util\Collection;
use PHPUnit\Framework\TestCase;

class CompositionTest extends TestCase
{
    public function testAll()
    {
        $composition = new Composition();
        $composition->compose(function ($context, $next) use (&$hit) {
            $hit++;
            $next($context);
        });

        try {
            $composition->apply();
            $this->fail('Must not here');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Core is undefined') {
                throw $e;
            }
        }

        $composition->setCore(function () use (&$coreHit) {
            $coreHit++;
        });

        $composition->apply();
        $this->assertEquals($hit, 1);
        $this->assertEquals($coreHit, 1);

        $this->assertEquals(count($composition->__debugInfo()['chain']), 1);
    }

    public function testContext()
    {
        $composition = new Composition();
        $composition->compose(function ($ctx, $next) {
            $this->assertEquals($ctx['foo'], 0);
            $ctx['foo'] = $ctx['foo'] + 1;
            $next();
            $this->assertEquals($ctx['bar'], 2);
            $ctx['bar'] = $ctx['bar'] + 1;
        });
        $composition->compose(function ($ctx, $next) {
            $this->assertEquals($ctx['foo'], 1);
            $ctx['foo'] = $ctx['foo'] + 1;
            $next();
            $this->assertEquals($ctx['bar'], 1);
            $ctx['bar'] = $ctx['bar'] + 1;
        });
        $composition->compose(function ($ctx, $next) {
            $this->assertEquals($ctx['foo'], 2);
            $ctx['foo'] = $ctx['foo'] + 1;
            $next();
            $this->assertEquals($ctx['bar'], 0);
            $ctx['bar'] = $ctx['bar'] + 1;
        });
        $composition->setCore(function ($ctx) {
            $ctx['core'] = 'core';
            $this->assertEquals($ctx['foo'], 3);
            $this->assertEquals($ctx['bar'], 0);
        });

        $ctx = new Collection([ 'foo' => 0, 'bar' => 0 ]);
        $result = $composition->apply($ctx);

        $this->assertEquals($ctx['foo'], 3);
        $this->assertEquals($ctx['bar'], 3);
        $this->assertEquals($result, $ctx);
    }
}
