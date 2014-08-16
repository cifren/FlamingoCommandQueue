<?php

namespace Earls\FlamingoCommandQueue\Model;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Earls\FlamingoCommandQueue\Model\Stopwatch
 */
class Stopwatch extends Stopwatch implements StopwatchInterface
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

}
