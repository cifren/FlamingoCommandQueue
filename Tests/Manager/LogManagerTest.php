<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Manager;

use Earls\FlamingoCommandQueueBundle\Manager\LogManager;

class LogManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new LogManager();

        $records = array(
            array(
                'priorityName' => 'DEBUG',
                'priority' => 1,
                'Message' => 'here cozmes',
                'timestamp' => 12456323323,
                'context' => 'contexdt',
            ),
            array(
                'priorityName' => 'NOTICE',
                'priority' => 2,
                'Message' => 'here comers',
                'timestamp' => 12456532323,
                'context' => 'contextz',
            ),
            array(
                'priorityName' => 'NOTICE',
                'priority' => 4,
                'Message' => 'here coames',
                'timestamp' => 1245632323,
                'context' => 'condtext',
            ),
        )
        ;

        $this->assertNotNull($item->getShortLogs($records, 30, 40));

        $this->assertEquals(2, count($item->getNoticeLogs($records)));

        $this->assertEquals(1, count($item->getSpecificLogs($records, 'DEBUG')));
        $this->assertEquals(2, count($item->getSpecificLogs($records, 'NOTICE')));
        $this->assertEquals(0, count($item->getSpecificLogs($records, 'NULL')));
    }
}
