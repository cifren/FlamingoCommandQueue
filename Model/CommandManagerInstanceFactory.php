<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Model\Stopwatch;

/**
 * Earls\FlamingoCommandQueueBundle\Model\CommandManagerInstanceFactory
 *
 * Manage only one instance of command, create a new object in order to manage more than one
 */
class CommandManagerInstanceFactory
{

    static public function get(Stopwatch $stopWatch, ExecutionControl $executionControl, EntityManager $em)
    {
        return new CommandManagerInstance($stopWatch, $executionControl, $em);
    }

}
