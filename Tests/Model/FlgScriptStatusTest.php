<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Model;

use Earls\FlamingoCommandQueueBundle\Model\FlgScriptStatus;

class FlgScriptStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new FlgScriptStatus();
        
        $this->assertEquals(true, is_array($item->getStatusList()));
    }
}