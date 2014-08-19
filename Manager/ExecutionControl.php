<?php

namespace Earls\FlamingoCommandQueueBundle\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScript;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;

/**
 * Earls\FlamingoCommandQueueBundle\Manager\ExecutionControl
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

    protected function openScript($name, $group = null)
    {
        $flgScript = $this->getEntityManager->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScript')->findByShaId($id);

        if (!$flgScript) {
            $flgScript = new FlgScript();
            $flgScript->setName($name);
            $this->getEntityManager()->persist($flgScript);
        }

        return $flgScript;
    }

    public function createScriptRunningInstance($name, $group = null)
    {
        $flgScript = $this->openScript($name, $group);

        $flgScriptRunningInstance = new FlgScriptRunningInstance();
        $flgScriptRunningInstance->setCreatedAt();
        $flgScriptRunningInstance->setFlgScript($flgScript);
        $flgScriptRunningInstance->setPid();
        $flgScriptRunningInstance->setGroupName($group);
        $flgScriptRunningInstance->setGroupSha($group);
        $flgScriptRunningInstance->setStatus(FlgScriptStatus::STATE_PENDING);

        $this->getEntityManager()->persist($flgScriptRunningInstance);
        $this->getEntityManager()->flush();

        return $flgScriptRunningInstance;
    }

    public function authorizeRunning(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;
        while ($canRun) {

            if (!$this->isFirstInQueue($flgScriptRunningInstance)) {
                if ($this->isMaxPendingInstance($flgScriptRunningInstance)) {

                    $this->throwMaxPendingInstanceError($flgScriptRunningInstance);
                }
            } else {
                if (!$this->hasRunningInstance($flgScriptRunningInstance)) {
                    $flgScriptRunningInstance->setStatus(FlgScriptStatus::STATE_RUNNING);
                    $this->getEntityManager()->flush();
                    $canRun = true;
                    continue;
                }
            }
            $this->pendingProcess();
        }
    }

    public function closeInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs, $scriptTime, $pendingTime)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);

        $instanceLog->setLog($logs);
        $instanceLog->setDuration($this->getFinishTime());
        $instanceLog->setPendingDuration($this->getFinishTime('pending'));
        $this->finish($instanceLog);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush();
    }

    protected function isFirstInQueue(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $isFirstInQueue = false;
        $firstInQueue = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')->getFirstInQueue($flgScriptRunningInstance->getFlgScript());

        if ($firstInQueue->getId() == $flgScriptRunningInstance->getId()) {
            $isFirstInQueue = true;
        }

        return $isFirstInQueue;
    }

    protected function hasRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $hasRunningInstance = true;
        $runningInstance = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')->getRunningInstance($flgScriptRunningInstance->getFlgScript());

        if (!$runningInstance) {
            $hasRunningInstance = false;
        } elseif (!$this->isStillAlive($runningInstance)) {
            $this->archiveBrokenRunningInstance($runningInstance);

            $hasRunningInstance = false;
        }

        return $hasRunningInstance;
    }

    public function finish(FlgScriptInstanceLog $flgScriptInstanceLog)
    {
        $flgScriptInstanceLog->setStatus(FlgScriptStatus::STATE_FINISHED);
    }

    public function fail(FlgScriptInstanceLog $flgScriptInstanceLog, $reason = null, $status = FlgScriptStatus::STATE_FAILED)
    {
        $message = "This script has failed with the following output : " . (!$reason) ? "No output given... not helpful !" : $reason;

        $flgScriptInstanceLog->setLog($message);
        $flgScriptInstanceLog->setStatus($status);
    }

    protected function isMaxPendingInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $countPending = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')->getRunningInstance($flgScriptRunningInstance->getFlgScript());

        return $countPending;
    }

    protected function isStillAlive(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $isStillAlive = false;
        $pid = $flgScriptRunningInstance->getFlgScriptCurrentStatus()->getPid();

        if (file_exists("/proc/$pid")) {
            $isStillAlive = true;
        }

        return $isStillAlive;
    }

    protected function archiveFinishedRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
        $this->finish($instanceLog);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush();
    }

    protected function archiveBrokenRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
        $this->fail($instanceLog, "The script has stopped after an Fatal Error happening during the process", FlgScriptStatus::STATE_FAILED);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush();
    }

    protected function throwMaxPendingInstanceError(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);

        $message = "The limit of pending instance possible, you can increase this value in your config";
        $this->fail($instanceLog, $message, FlgScriptStatus::STATE_TERMINATED);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush();

        throw new \Exception($message);
    }

    public function pendingProcess()
    {
        sleep($this->pendingLapsTime);
    }

    protected function createArchiveInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $flgScriptInstanceLog = new FlgScriptInstanceLog();
        $flgScriptInstanceLog->setCreatedAt();
        $flgScriptInstanceLog->setFlgScript($flgScriptRunningInstance->getFlgScript());

        return $flgScriptInstanceLog;
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
     * @param  \Doctrine\ORM\EntityManager                    $entityManager
     * @return \Earls\FlamingoCommandQueueBundle\Manager\Pool
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
