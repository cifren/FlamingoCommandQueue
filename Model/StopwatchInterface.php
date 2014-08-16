<?php

namespace Earls\FlamingoCommandQueue\Model;

/**
 * Earls\FlamingoCommandQueue\Model\StopwatchInterface
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
