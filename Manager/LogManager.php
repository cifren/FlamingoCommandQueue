<?php

namespace Earls\FlamingoCommandQueueBundle\Manager;

/**
 * Earls\FlamingoCommandQueueBundle\Manager\LogManager.
 */
class LogManager
{
    public function getShortLogs(array $logs, $startlength = 20, $endLength = 20)
    {
        $logs = $this->getNoticeLogs($logs);

        $start = array_slice($logs, 0, $startlength);
        if (count($logs) <= ($startlength + $endLength)) {
            return $logs;
        }
        $end = array_slice($logs, count($logs) - $endLength);
        $between = array('timestamp' => 0, 'message' => '...');

        if (!empty($start) || !empty($end)) {
            return array_merge($start, array($between), $end);
        }

        return array();
    }

    public function getNoticeLogs(array $logs)
    {
        return $this->getSpecificLogs($logs, 'NOTICE');
    }

    public function getSpecificLogs(array $logs, $type = 'NOTICE')
    {
        $logs = array_filter($logs, function ($var) use ($type) {
            if (isset($var['priorityName'])) {
                return $var['priorityName'] == $type;
            }

            return true;
        });

        return $logs;
    }
}
