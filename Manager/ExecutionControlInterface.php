<?php

namespace Earls\FlamingoCommandQueueBundle\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Model\FlgScriptStatus;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;

/**
 * Earls\FlamingoCommandQueueBundle\Manager\ExecutionControlInterface
 */
interface ExecutionControlInterface
{

    public function createScriptRunningInstance($name, $group = null);

    public function authorizeRunning(FlgScriptRunningInstance $flgScriptInstance);
    public function closeInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs, $scriptTime, $pendingTime);

    /**
     *
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     *
     * @param  \Doctrine\ORM\EntityManager                    $entityManager
     * @return \Earls\FlamingoCommandQueueBundle\Manager\Pool
     */
    public function setEntityManager(EntityManager $entityManager);

    public function setOptions(array $options);
}
