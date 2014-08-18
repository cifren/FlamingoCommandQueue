<?php

namespace Earls\FlamingoCommandQueueBundle\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Model\FlgScriptStatus;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstance;

/**
 * Earls\FlamingoCommandQueueBundle\Manager\ExecutionControlInterface
 */
interface ExecutionControlInterface
{

    public function openScriptInstance($id, $name, $group = null);

    public function authorizeRunning(FlgScriptInstance $flgScriptInstance);

    public function finish(FlgScriptInstance $flgScriptInstance);

    public function fail(FlgScriptInstance $flgScriptInstance, $reason = null, $status = FlgScriptStatus::STATE_FAILED);

    public function changeStatus(FlgScriptInstance $flgScriptInstance, $status);

    /**
     *
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     *
     * @param  \Doctrine\ORM\EntityManager              $entityManager
     * @return \Earls\FlamingoCommandQueueBundle\Manager\Pool
     */
    public function setEntityManager(EntityManager $entityManager);

    public function setConfig(array $config);
}
