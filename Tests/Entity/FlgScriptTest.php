<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Entity;

use Earls\FlamingoCommandQueueBundle\Entity\FlgScript;

class FlgScriptTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateEntity()
    {
        $entity = new FlgScript();
        $entity->setName('lol');
        $this->assertEquals('lol', $entity->getName());
    }
}
