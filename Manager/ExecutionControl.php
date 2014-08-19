<?php

namespace Earls\FlamingoCommandQueueBundle\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScript;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;
use Earls\FlamingoCommandQueueBundle\Model\FlgScriptStatus;

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

    protected function openScript($name)
    {
        $flgScript = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScript')->findOneBy(array('name' => $name));

        if (!$flgScript) {
            $flgScript = new FlgScript();
            $flgScript->setName($name);
            $this->getEntityManager()->persist($flgScript);
        }

        return $flgScript;
    }

    public function createScriptRunningInstance($name, $group = null)
    {
        $flgScript = $this->openScript($name);

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

//        if (rand(1, 10) == 1) {
//            die();
//        }

        var_dump('authorizeRunning');
        while (!$canRun) {
            $this->checkAndArchivePreviousBrokenInstance($flgScriptRunningInstance);
            if (!$this->isFirstInQueue($flgScriptRunningInstance)) {
                var_dump('not isFirstInQueue');
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
        $this->archiveFinishedRunningInstance($flgScriptRunningInstance, $logs, $scriptTime, $pendingTime);
    }

    protected function isFirstInQueue(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $isFirstInQueue = false;
        $firstInQueue = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getFirstInQueue($flgScriptRunningInstance->getGroupSha());

        if ($firstInQueue->getId() == $flgScriptRunningInstance->getId()) {
            $isFirstInQueue = true;
        }

        return $isFirstInQueue;
    }

    protected function hasRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $runningInstances = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getRunningInstance($flgScriptRunningInstance->getGroupSha());

        $mainHasRunningInstance = false;
        $countRunningInstance = 0;
        foreach ($runningInstances as $runningInstance) {
            $hasRunningInstance = true;
            if (!$runningInstance) {
                $hasRunningInstance = false;
            } elseif (!$this->isStillAlive($runningInstance)) {
                $this->archiveBrokenInstance($runningInstance);

                $hasRunningInstance = false;
            }

            //if one loop is true, all true (should not happen more than once, unless there is bug)
            if ($hasRunningInstance) {
                $mainHasRunningInstance = true;
                $countRunningInstance++;
            }
        }
        if ($countRunningInstance > 1) {
            $this->throwSimultaneousInstanceError($flgScriptRunningInstance);
        }

        return $mainHasRunningInstance;
    }

    public function fail(FlgScriptInstanceLog $flgScriptInstanceLog, $reason = null, $status = FlgScriptStatus::STATE_FAILED)
    {
        $message = "This script has failed with the following output : " . ((!$reason) ? "No output given... not helpful !" : $reason);

        $flgScriptInstanceLog->setLog($message);
        $flgScriptInstanceLog->setStatus($status);
    }

    protected function isMaxPendingInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        var_dump("isMaxPendingInstance");
        $countPending = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getPendingInstance($flgScriptRunningInstance->getGroupSha());

        var_dump('$countPending ' . count($countPending));
        var_dump('maxPendingInstance ' . $this->maxPendingInstance);
        var_dump(count($countPending) == $this->maxPendingInstance);
        return count($countPending) > $this->maxPendingInstance;
    }

    protected function checkAndArchivePreviousBrokenInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $instances = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getPreviousInstance($flgScriptRunningInstance);
        foreach ($instances as $instance) {
            if (!$this->isStillAlive($instance)) {
                $this->archiveBrokenInstance($instance);
            }
        }
    }

    protected function isStillAlive(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $isStillAlive = false;
        $pid = $flgScriptRunningInstance->getPid();

        if (file_exists("/proc/$pid")) {
            $isStillAlive = true;
        }

        return $isStillAlive;
    }

    protected function archiveFinishedRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs, $scriptTime, $pendingTime)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
        $instanceLog->setLog($logs);
        $instanceLog->setDuration($scriptTime);
        $instanceLog->setPendingDuration($pendingTime);
        $instanceLog->setStatus(FlgScriptStatus::STATE_FINISHED);

        $this->getEntityManager()->remove($flgScriptRunningInstance);
        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush();
    }

    protected function archiveBrokenInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
        $this->getEntityManager()->remove($flgScriptRunningInstance);
        $this->fail($instanceLog, "The script has stopped after an Fatal Error happening during the process", FlgScriptStatus::STATE_FAILED);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush();
    }

    protected function archiveFailedInstance(FlgScriptRunningInstance $flgScriptRunningInstance, $message, $status = FlgScriptStatus::STATE_FAILED)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
        $this->getEntityManager()->remove($flgScriptRunningInstance);
        $this->fail($instanceLog, $message, $status);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush();
    }

    protected function throwMaxPendingInstanceError(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $message = "You reach the limit of pending instance possible, you can increase this value in your config";
        $this->throwError($flgScriptRunningInstance, $message);
    }

    protected function throwSimultaneousInstanceError(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $message = "Simultaneous running process, this should not happen";
        $this->throwError($flgScriptRunningInstance, $message);
    }

    protected function throwError(FlgScriptRunningInstance $flgScriptRunningInstance, $message)
    {
        $this->archiveFailedInstance($flgScriptRunningInstance, $message, FlgScriptStatus::STATE_TERMINATED);

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

    public function setOptions(array $options)
    {
        $this->maxPendingInstance = $options['maxPendingInstance'];
        $this->pendingLapsTime = $options['pendingLapsTime'];
    }

}
