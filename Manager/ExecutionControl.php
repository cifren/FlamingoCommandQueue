<?php

namespace Earls\FlamingoCommandQueue\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueue\Entity\FlgScript;
use Earls\FlamingoCommandQueue\Entity\FlgScriptInstance;
use Earls\FlamingoCommandQueue\Entity\FlgScriptGroup;
use Earls\FlamingoCommandQueue\Entity\FlgScriptCurrentStatus;
use Earls\FlamingoCommandQueue\Model\FlgScriptStatus;

/**
 * Earls\FlamingoCommandQueue\Manager\ExecutionControl
 *
 * it is the control tower of the script instance
 */
class ExecutionControl implements ExecutionControlInterface
{

    /**
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     *
     * @var integer
     */
    protected $maxPendingInstance;

    /**
     * in second
     *
     * @var integer
     */
    protected $pendingLapsTime;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    protected function openScript($id, $name, $group = null)
    {
        $flgScript = $this->getEntityManager->getRepository('Earls\FlamingoCommandQueue\Entity\FlgScript')->findByShaId($id);

        if (!$flgScript) {
            $flgScript = new FlgScript();
            $flgScript->setName($name);
            $flgScript->setShaId($id);
            $this->getEntityManager()->persist($flgScript);
        }

        $flgScriptGroup = $this->openGroup($group);
        $flgScript->setFlgScriptGroup($flgScriptGroup);

        return $flgScript;
    }

    protected function openGroup($groupName = 'main')
    {
        $flgScriptGroup = $this->getEntityManager->getRepository('Earls\FlamingoCommandQueue\Entity\FlgScript')->findByName($groupName);

        if (!$flgScriptGroup) {
            $flgScriptGroup = new FlgScriptGroup();
            $flgScriptGroup->setName($groupName);
            $this->getEntityManager()->persist($flgScriptGroup);
        }

        return $flgScriptGroup;
    }

    public function openScriptInstance($id, $name, $group = null)
    {
        $flgScript = $this->openScript($id, $name, $group);

        $flgScriptInstance = new FlgScriptInstance();
        $flgScriptInstance->setCreatedAt();
        $flgScriptInstance->setFlgScript($flgScript);

        $flgScriptCurrentStatus = new FlgScriptCurrentStatus();
        $flgScriptCurrentStatus->setPid();
        $flgScriptCurrentStatus->setStatus(FlgScriptStatus::STATE_PENDING);
        $flgScriptCurrentStatus->setFlgScriptInstance($flgScriptInstance);

        $this->getEntityManager()->persist($flgScriptInstance);
        $this->getEntityManager()->persist($flgScriptCurrentStatus);
        $this->getEntityManager()->flush();

        return $flgScriptInstance;
    }

    public function authorizeRunning(FlgScriptInstance $flgScriptInstance)
    {
        $canRun = false;
        while ($canRun) {

            if (!$this->isFirstInQueue($flgScriptInstance)) {
                if ($this->isMaxPendingInstance($flgScriptInstance)) {
                    $message = "The limit of pending instance possible, you can increase this value in your config";
                    $this->terminate($flgScriptInstance, $message, FlgScriptStatus::STATE_TERMINATED);
                    throw new \Exception($message);
                }
            } else {
                if (!$this->hasRunningInstance($flgScriptInstance)) {
                    $this->changeStatus($flgScriptInstance, FlgScriptStatus::STATE_RUNNING);
                    $this->getEntityManager()->flush();
                    $canRun = true;
                    continue;
                }
            }
            $this->pendingProcess();
        }
    }

    protected function isFirstInQueue(FlgScriptInstance $flgScriptInstance)
    {
        $isFirstInQueue = false;
        $firstInQueue = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueue\Entity\FlgScriptInstance')->getFirstInQueue($flgScriptInstance->getFlgScript());

        if ($firstInQueue->getId() == $flgScriptInstance->getId()) {
            $isFirstInQueue = true;
        }

        return $isFirstInQueue;
    }

    protected function hasRunningInstance(FlgScriptInstance $flgScriptInstance)
    {
        $hasRunningInstance = true;
        $runningInstance = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueue\Entity\FlgScriptInstance')->getRunningInstance($flgScriptInstance->getFlgScript());

        if (!$runningInstance) {
            $hasRunningInstance = false;
        } elseif (!$this->isStillAlive($runningInstance)) {
            $this->repairBrokenRunningInstance($runningInstance);

            $hasRunningInstance = false;
        }

        return $hasRunningInstance;
    }

    public function finish(FlgScriptInstance $flgScriptInstance)
    {
        $this->changeStatus($flgScriptInstance, FlgScriptStatus::STATE_FINISHED);
        $this->getEntityManager()->flush();
    }

    public function fail(FlgScriptInstance $flgScriptInstance, $reason = null, $status = FlgScriptStatus::STATE_FAILED)
    {
        $message = "This script has failed with the following output : " . (!$reason) ? "No output given... not helpful !" : $reason;

        $flgScriptInstance->setLog($message);
        $this->changeStatus($flgScriptInstance, $status);
        $this->getEntityManager()->flush();
    }

    public function changeStatus(FlgScriptInstance $flgScriptInstance, $status)
    {
        $flgScriptInstance->getFlgScriptCurrentStatus()->setStatus($status);
    }

    protected function isMaxPendingInstance(FlgScriptInstance $flgScriptInstance)
    {
        $countPending = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueue\Entity\FlgScriptInstance')->getRunningInstance($flgScriptInstance->getFlgScript());

        return $countPending;
    }

    protected function isStillAlive(FlgScriptInstance $flgScriptInstance)
    {
        $isStillAlive = false;
        $pid = $flgScriptInstance->getFlgScriptCurrentStatus()->getPid();

        if (file_exists("/proc/$pid")) {
            $isStillAlive = true;
        }

        return $isStillAlive;
    }

    protected function repairBrokenRunningInstance(FlgScriptInstance $flgScriptInstance)
    {
        $flgScriptInstance->fail($flgScriptInstance, "The script has stopped after an Fatal Error happening during the process", FlgScriptStatus::STATE_FAILED);
    }

    public function pendingProcess()
    {
        sleep($this->pendingLapsTime);
    }

    /**
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     *
     * @param  \Doctrine\ORM\EntityManager              $entityManager
     * @return \Earls\FlamingoCommandQueue\Manager\Pool
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    public function setConfig(array $config)
    {
        $this->maxPendingInstance = $config['maxPendingInstance'];
        $this->pendingLapsTime = $config['pendingLapsTime'];
    }

}
