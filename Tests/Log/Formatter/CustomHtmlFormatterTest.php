<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Entity;

use Earls\FlamingoCommandQueueBundle\Log\Formatter\CustomHtmlFormatter;

class CustomHtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new CustomHtmlFormatter();

        $records = array(
            array(
                'priorityName' => 'prnz',
                'priority' => 1,
                'Message' => 'here cozmes',
                'timestamp' => 12456323323,
                'context' => 'contexdt',
            ),
            array(
                'priorityName' => 'prné',
                'priority' => 2,
                'Message' => 'here comers',
                'timestamp' => 12456532323,
                'context' => 'contextz',
            ),
            array(
                'priorityName' => 'prnr',
                'priority' => 4,
                'Message' => 'here coames',
                'timestamp' => 1245632323,
                'context' => 'condtext',
            ),
        )
        ;

        $this->assertNotNull($item->format($records));
    }
}
