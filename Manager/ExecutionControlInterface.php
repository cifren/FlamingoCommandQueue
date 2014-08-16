<?php

namespace Earls\FlamingoCommandQueue\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueue\Model\FlgScriptStatus;
use Earls\FlamingoCommandQueue\Entity\FlgScriptInstance;

/**
 * Earls\FlamingoCommandQueue\Manager\ExecutionControlInterface
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
     * @return \Earls\FlamingoCommandQueue\Manager\Pool
     */
    public function setEntityManager(EntityManager $entityManager);

    public function setConfig(array $config);
}
