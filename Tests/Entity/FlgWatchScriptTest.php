<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Entity;

use Earls\FlamingoCommandQueueBundle\Entity\FlgWatchScript;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;

class FlgWatchScriptTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateEntity()
    {
        $entity = new FlgWatchScript();
        $flgScriptRunningInstance = new FlgScriptRunningInstance();
        
        $entity->setReferenceName('name');
        $entity->setReferenceId('id');
        $entity->setFlgScriptRunningInstance($flgScriptRunningInstance);
        
        $this->assertNull($entity->getId());
        $this->assertEquals('name', $entity->getReferenceName());
        $this->assertEquals('id', $entity->getReferenceId());
        $this->assertEquals($flgScriptRunningInstance, $entity->getFlgScriptRunningInstance());
    }
}
