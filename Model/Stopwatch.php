<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

use Earls\FlamingoCommandQueueBundle\Model\StopwatchInterface;

class Stopwatch extends \Symfony\Component\Stopwatch\Stopwatch implements StopwatchInterface
{

    /**
     * 
     * @param string $name
     * 
     * @return \DateInterval
     */
    public function getFinishTime($name)
    {
        $seconds = round($this->getEvent($name)->getDuration() / 1000, 0);

        $d1 = new \DateTime();
        $d2 = new \DateTime();
        $d2->add(new \DateInterval("PT{$seconds}S"));

        $duration = $d2->diff($d1);

        return $duration;
    }

    public function getEvent($name)
    {
        $events = end($this->activeSections);

        if(!(isset($events[$name]))){
            throw new \LogicException(sprintf("Error, %s event does not exists", $name));
        }

        return $events[$name];
    }
}
