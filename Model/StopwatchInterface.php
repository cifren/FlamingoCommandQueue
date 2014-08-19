<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

/**
 * Earls\FlamingoCommandQueueBundle\Model\StopwatchInterface
 */
interface StopwatchInterface
{

    /**
     *
     * @param string $name
     *
     * @return \DateInterval
     */
    public function getFinishTime($name);
}
