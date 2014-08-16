<?php

namespace Earls\FlamingoCommandQueue\Model;

class Stopwatch extends \Symfony\Component\Stopwatch\Stopwatch
{

    /**
     * 
     * @param string $name
     * 
     * @return \DateInterval
     */
    protected function getFinishTime($name)
    {
        $seconds = round($this->getEvent($name)->getDuration() / 1000, 0);

        $d1 = new \DateTime();
        $d2 = new \DateTime();
        $d2->add(new \DateInterval("PT{$seconds}S"));

        $duration = $d2->diff($d1);

        return $duration;
    }

}
