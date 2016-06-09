<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;

/**
 * Earls\FlamingoCommandQueueBundle\Model\ExecutionControlInterface.
 */
interface ExecutionControlInterface
{
    public function createScriptRunningInstance($name, $group, $uniqueId, $flgScript);

    public function authorizeRunning(FlgScriptRunningInstance $flgScriptInstance);
    public function closeInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs, $scriptTime, $pendingTime, $status = FlgScriptStatus::STATE_FINISHED, $archiveEnable = true);

    /**
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     *
     * @return \Earls\FlamingoCommandQueueBundle\Manager\Pool
     */
    public function setEntityManager(EntityManager $entityManager);

    public function setOptions(array $options);
}
