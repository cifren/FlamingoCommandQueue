<?php

namespace Earls\FlamingoCommandQueueBundle\Log\Formatter;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Logger;

/**
 * extends HtmlFormater but only used after Symfony\Bridge\Monolog\Handler\DebugHandler
 */
class CustomHtmlFormatter extends HtmlFormatter
{

    /**
     * Translates Monolog log levels to html color priorities.
     */
    protected $logLevels = array(
        Logger::DEBUG => '#cccccc',
        Logger::INFO => '#468847',
        Logger::NOTICE => '#3a87ad',
        Logger::WARNING => '#c09853',
        Logger::ERROR => '#f0ad4e',
        Logger::CRITICAL => '#FF7708',
        Logger::ALERT => '#C12A19',
        Logger::EMERGENCY => '#000000',
    );

    /**
     * Creates an HTML table row
     *
     * @param  string $th Row header content
     * @param  string $td Row standard cell content
     * @return string
     */
    protected function addRow($th, $td = ' ')
    {
        $th = htmlspecialchars($th, ENT_NOQUOTES, 'UTF-8');
        $td = '<pre>' . htmlspecialchars($td, ENT_NOQUOTES, 'UTF-8') . '</pre>';

        return "<tr style=\"padding: 4px;spacing: 0;text-align: left;\">\n<th style=\"background: #cccccc\" width=\"100px\">$th:</th>\n<td style=\"padding: 4px;spacing: 0;text-align: left;background: #eeeeee\">" . $td . "</td>\n</tr>";
    }

    /**
     * Create a HTML h1 tag
     *
     * @param  string  $title Text to be in the h1
     * @param  integer $level Error level
     * @return string
     */
    protected function addTitle($title, $level)
    {
        $title = htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8');

        return '<h1 style="background: ' . $this->logLevels[$level] . ';color: #ffffff;padding: 5px;">' . $title . '</h1>';
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $output = $this->addTitle($record['priorityName'], $record['priority']);
        $output .= '<table cellspacing="1" width="100%">';

        $output .= $this->addRow('Message', (string) $record['message']);
        $output .= $this->addRow('Time', date('Y-m-d\TH:i:s.uO', $record['timestamp']));

        if ($record['context']) {
            $output .= $this->addRow('Context', $this->convertToString($record['context']));
        }

        return $output . '</table>';
    }

}
