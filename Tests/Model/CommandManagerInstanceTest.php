<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Model;

use Earls\FlamingoCommandQueueBundle\Model\CommandManagerInstance;
use Earls\FlamingoCommandQueueBundle\Model\FlgCommandOption;
use Earls\FlamingoCommandQueueBundle\Model\Stopwatch;

class CommandManagerInstanceTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new CommandManagerInstance(
            $this->getStopwatch(),
            $this->getExecutionControl(),
            $this->getEntityManager()
        );

        $this->assertNotNull($item);

        $this->assertInstanceOf(Stopwatch::class, $this->executeMethod($item, 'getStopWatch'));
        $this->executeMethod($item, 'setStartTime');
        $this->executeMethod($item, 'setEndTime');
        $this->executeMethod($item, 'getFinishTime');
        $item->setEntityManager($this->getEntityManager());
        $this->assertEquals($this->getEntityManager(), $item->getEntityManager());

        $this->assertEquals(true, is_array($item->getDefaultOptions()));
        $this->assertEquals(true, is_array($item->getOptions()));
        $item->setOptions(array('lol' => 15));
        $this->assertEquals(true, array_key_exists('maxPendingInstance', $item->getOptions()));
        $this->assertEquals(true, array_key_exists('lol', $item->getOptions()));

        $this->assertInstanceOf(FlgCommandOption::class, $this->executeMethod($item, 'getFlgCommandOption'));
        $this->executeMethod($item, 'setFlgCommandOption', array(null));
        $this->assertInstanceOf(FlgCommandOption::class, $this->executeMethod($item, 'getFlgCommandOption'));
        $this->assertInstanceOf(FlgCommandOption::class, $this->executeMethod($item, 'getDefaultFlgCommandOption'));
    }

    public function testStart()
    {
        $executionControl = $this->getMockBuilder('Earls\FlamingoCommandQueueBundle\Model\ExecutionControl')
            ->disableOriginalConstructor()
            ->setMethods(array('openInstance', 'authorizeRunning', 'setOptions'))
            ->getMock();
        $executionControl
            ->method('openInstance')
            ->will($this->returnValue($this->getFlgScriptRunningInstance()));
        $executionControl
            ->method('authorizeRunning')
            ->will($this->returnValue(true));
        $executionControl
            ->method('setOptions')
            ->will($this->returnValue(true));

        $item = new CommandManagerInstance(
            $this->getStopwatch(),
            $executionControl,
            $this->getEntityManager()
        );

        $this->assertEquals(false, $this->getProperties($item, 'started'));
        $item->start('testFlg');
        $this->assertEquals(true, $this->getProperties($item, 'started'));

        try {
            $item->start('testFlg');
            $this->fail();
        } catch (\Exception $e) {
        }

        $item2 = new CommandManagerInstance(
            $this->getStopwatch(),
            $executionControl,
            $this->getEntityManager()
        );
        $flgCommandOption = new FlgCommandOption();
        $item2->start('testFlg', $flgCommandOption);
        $this->assertEquals(true, $this->getProperties($item2, 'started'));
    }

    public function testStop()
    {
        $executionControl = $this->getMockBuilder('Earls\FlamingoCommandQueueBundle\Model\ExecutionControl')
            ->disableOriginalConstructor()
            ->setMethods(array('openInstance', 'authorizeRunning', 'setOptions', 'closeInstance'))
            ->getMock();
        $executionControl
            ->method('openInstance')
            ->will($this->returnValue($this->getFlgScriptRunningInstance()));
        $executionControl
            ->method('authorizeRunning')
            ->will($this->returnValue(true));
        $executionControl
            ->method('setOptions')
            ->will($this->returnValue(true));
        $executionControl
            ->method('closeInstance')
            ->will($this->returnValue(true));

        $item = new CommandManagerInstance(
            $this->getStopwatch(),
            $executionControl,
            $this->getEntityManager()
        );

        $this->assertEquals(false, $this->getProperties($item, 'started'));
        $item->start('testFlg');
        $this->assertEquals(true, $this->getProperties($item, 'started'));

        $this->assertEquals(false, $this->getProperties($item, 'stopped'));
        $item->stop(array());
        $this->assertEquals(true, $this->getProperties($item, 'stopped'));
    }

    public function testSaveProgress()
    {
        $executionControl = $this->getMockBuilder('Earls\FlamingoCommandQueueBundle\Model\ExecutionControl')
            ->disableOriginalConstructor()
            ->setMethods(array('openInstance', 'authorizeRunning', 'setOptions', 'saveProgress'))
            ->getMock();
        $executionControl
            ->method('saveProgress')
            ->will($this->returnValue(true));
        $executionControl
            ->method('openInstance')
            ->will($this->returnValue($this->getFlgScriptRunningInstance()));
        $executionControl
            ->method('authorizeRunning')
            ->will($this->returnValue(true));
        $executionControl
            ->method('setOptions')
            ->will($this->returnValue(true));

        $item = new CommandManagerInstance(
            $this->getStopwatch(),
            $executionControl,
            $this->getEntityManager()
        );

        $item->start('testFlg');
        $item->saveProgress(array());
        $this->assertEquals(true, $this->getProperties($item, 'started'));
        $this->assertEquals(false, $this->getProperties($item, 'stopped'));
    }

    protected function getFlgScriptRunningInstance()
    {
        return $this->getMockBuilder('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getStopwatch()
    {
        $event = $this->getMockBuilder('Symfony\Component\Stopwatch\StopwatchEvent')
            ->disableOriginalConstructor()
            //->setMethods(array('getDuration', 'start', 'stop'))
            ->getMock();
        $event
            ->method('getDuration')
            ->will($this->returnValue(1453));

        $item = $this->getMockBuilder('Earls\FlamingoCommandQueueBundle\Model\Stopwatch')
            ->disableOriginalConstructor()
            //->setMethods(array('getEvent'))
            ->getMock();
        $item
            ->method('getEvent')
            ->will($this->returnValue($event));

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

    protected function executeMethod($object, $methodname, $args = array())
    {
        $reflector = new \ReflectionClass(get_class($object));
        $reflectionMethod = $reflector->getMethod($methodname);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $args);
    }

    protected function getProperties($object, $propertyname)
    {
        $reflector = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflector->getProperty($propertyname);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
