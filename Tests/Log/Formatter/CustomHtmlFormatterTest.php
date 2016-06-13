<?php

namespace Earls\FlamingoCommandQueueBundle\Tests\Entity;

use Earls\FlamingoCommandQueueBundle\Log\Formatter\CustomHtmlFormatter;

class CustomHtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $item = new CustomHtmlFormatter();

        $record = array(
            'priorityName' => 'prnz',
            'priority' => 100,
            'message' => 'here cozmes',
            'timestamp' => 12456323323,
            'context' => 'contexdt',
        );

        $this->assertNotNull($item->format($record));
    }
}
