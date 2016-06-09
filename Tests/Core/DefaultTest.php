<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\FunctionalTests\Tests\DefaultTest;

use Earls\FlamingoCommandQueueBundle\Tests\Core\FixtureAwareTestCase;

/**
 * Default test
 */
class DefaultTest extends FixtureAwareTestCase
{
  public function setup()
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
  }
  
  /** Used only to build kernel
   * @group core
   */ 
  public function testBuild()
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    
    $this->assertEquals('test', 'test');
  }
}