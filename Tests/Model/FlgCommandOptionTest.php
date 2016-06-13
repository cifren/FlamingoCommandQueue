<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Model;

use Earls\FlamingoCommandQueueBundle\Model\FlgCommandOption;

class FlgCommandOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new FlgCommandOption();

        $item->setGroupName('setGroupName');
        $this->assertEquals('setGroupName', $this->get());
        $item->setUniqueId('unique');
        $this->assertEquals('unique', $this->getUniqueId());
        $item->setMaxPendingInstance(12441);
        $this->assertEquals(12441, $this->getMaxPendingInstance());
        $item->setPendingLapsTime(325223);
        $this->assertEquals(325223, $this->getPendingLapsTime());
        $item->setArchiveEnable(true);
        $this->assertEquals(true, $this->getArchiveEnable());
        $item->setWatchScriptReferences('setWatchScriptReferences');
        $this->assertEquals('setWatchScriptReferences', $this->getWatchScriptReferences());
    }
}
