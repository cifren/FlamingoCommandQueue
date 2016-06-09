<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Core;

use Earls\FlamingoCommandQueueBundle\Entity\FlgScript;

/**
 * Default test.
 */
class DefaultTest extends FixtureAwareTestCase
{
    public function setup()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $this->initTestDatabase();
    }

    /** Used only to build kernel
    * @group core
    */
    public function testBuild()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        
        $item = new FlgScript();
        $item->setName('test');
        
        $entityManager->persist($item);
        $entityManager->flush();
        
        $this->assertEquals('test', $item->getName());
        
        $item = $entityManager->getRepository(FlgScript::class)->find(1);
        
        $this->assertNotNull($item);
        $this->assertEquals('test', $item->getName());
    }
}
