<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\FunctionalTests\Tests\DefaultTest;

use Earls\FlamingoCommandQueueBundle\Tests\FunctionalTests\Model\FixtureAwareTestCase;

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

  // Used only to build kernel
  public function testBuild()
  {
      $doctrine = $this->getContainer()->get('doctrine');
      $entityManager = $doctrine->getManager();

      $this->assertEquals('test', 'test');
  }
}
