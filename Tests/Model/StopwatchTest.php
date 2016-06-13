<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Model;

use Earls\FlamingoCommandQueueBundle\Model\Stopwatch;

class StopwatchTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new Stopwatch();

        $id = 'main';
        $item->start($id);

        $item->stop($id);

        $this->assertInstanceOf('DateInterval', $item->getFinishTime($id));
    }
}
