<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Entity;

use Earls\FlamingoCommandQueueBundle\Entity\FlgScript;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog;

class FlgScriptInstanceLogTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateEntity()
    {
        $flgScript = new FlgScript();

        $entity = new FlgScriptInstanceLog();

        $entity->setLog('test1');
        $entity->setDuration('test2');
        $entity->setPendingDuration('test3');
        $entity->setCreatedAt();
        $entity->setFlgScript($flgScript);
        $entity->setStatus('test6');

        $this->assertNull($entity->getId());
        $this->assertEquals('test1', $entity->getLog());
        $this->assertEquals('test2', $entity->getDuration());
        $this->assertEquals('test3', $entity->getPendingDuration());
        $this->assertInstanceOf('DateTime', $entity->getCreatedAt());
        $this->assertEquals($flgScript, $entity->getFlgScript());
        $this->assertEquals('test6', $entity->getStatus());
    }
}
