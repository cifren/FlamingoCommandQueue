<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Entity;

use Earls\FlamingoCommandQueueBundle\Entity\FlgScript;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;

class FlgScriptRunningInstanceTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateEntity()
    {
        $flgScript = new FlgScript();
        
        $entity = new FlgScriptRunningInstance();
        
        $entity->setPid();
        $entity->setStatus('test2');
        $entity->setGroupSha('test3');
        $entity->setGroupName('test4');
        $entity->setFlgScript($flgScript);
        $entity->setCreatedAt();
        $entity->setUniqueSha('test5');
        $entity->setUniqueId('test6');
        $entity->setLog('test7');
        
        $this->assertNull($entity->getId());
        $this->assertEquals(posix_getpid(), $entity->getPid());
        $this->assertEquals('test2', $entity->getStatus());
        $this->assertEquals(sha1('test3'), $entity->getGroupSha());
        $this->assertEquals('test4', $entity->getGroupName());
        $this->assertEquals($flgScript, $entity->getFlgScript());
        $this->assertInstanceOf('DateTime', $entity->getCreatedAt());
        $this->assertEquals(sha1('test5'), $entity->getUniqueSha());
        $this->assertEquals('test6', $entity->getUniqueId());
        $this->assertEquals('test7', $entity->getLog());
        $this->assertEquals(true, $entity->hasUniqueId());
    }
}
