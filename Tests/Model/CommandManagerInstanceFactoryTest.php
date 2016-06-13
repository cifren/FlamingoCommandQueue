<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Manager;

use Earls\FlamingoCommandQueueBundle\Model\CommandManagerInstance;
use Earls\FlamingoCommandQueueBundle\Model\CommandManagerInstanceFactory;

class CommandManagerInstanceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new CommandManagerInstanceFactory();

        $instance = $item->get($this->getStopwatch(), $this->getExecutionControl(), $this->getEntityManager());

        $this->assertInstanceOf(CommandManagerInstance::class, $instance);
    }

    protected function getStopwatch()
    {
        $item = $this->getMockBuilder('Earls\FlamingoCommandQueueBundle\Model\Stopwatch')
            ->disableOriginalConstructor()
            ->getMock();

        return $item;
    }

    protected function getExecutionControl()
    {
        return $this->getMockBuilder('Earls\FlamingoCommandQueueBundle\Model\ExecutionControl')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getEntityManager()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
