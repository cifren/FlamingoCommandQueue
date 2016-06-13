<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Model;

use Earls\FlamingoCommandQueueBundle\Model\FlgCommandOption;

class FlgCommandOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new FlgCommandOption();

        $item->setGroupName('setGroupName');
        $this->assertEquals('setGroupName', $item->getGroupName());
        $item->setUniqueId('unique');
        $this->assertEquals('unique', $item->getUniqueId());
        $item->setMaxPendingInstance(12441);
        $this->assertEquals(12441, $item->getMaxPendingInstance());
        $item->setPendingLapsTime(325223);
        $this->assertEquals(325223, $item->getPendingLapsTime());
        $item->setArchiveEnable(true);
        $this->assertEquals(true, $item->getArchiveEnable());
        $item->setWatchScriptReferences('setWatchScriptReferences');
        $this->assertEquals('setWatchScriptReferences', $item->getWatchScriptReferences());
    }
}
