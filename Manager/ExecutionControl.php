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

    public function createScriptRunningInstance($name, $group = null, $uniqueId = null)
    {
        $flgScript = $this->openScript($name);

        $flgScriptRunningInstance = new FlgScriptRunningInstance();
        $flgScriptRunningInstance->setCreatedAt();
        $flgScriptRunningInstance->setFlgScript($flgScript);
        $flgScriptRunningInstance->setPid();
        $flgScriptRunningInstance->setGroupName($group);
        $flgScriptRunningInstance->setGroupSha($group);
        $flgScriptRunningInstance->setUniqueId($uniqueId);
        $flgScriptRunningInstance->setUniqueSha($uniqueId);
        $flgScriptRunningInstance->setStatus(FlgScriptStatus::STATE_PENDING);

        $this->getEntityManager()->persist($flgScriptRunningInstance);
        $this->getEntityManager()->flush();

        return $flgScriptRunningInstance;
    }

    public function authorizeRunning(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;

        var_dump('authorizeRunning');

        //can only have one instance with the same uniqueId pending, stop here if already one pending
        if ($flgScriptRunningInstance->hasUniqueId()) {
            $this->checkUniqueIdInstance($flgScriptRunningInstance);
        }
        while (!$canRun) {
            $this->checkAndArchivePreviousBrokenInstance($flgScriptRunningInstance);

            if ($canRun = $this->canRun($flgScriptRunningInstance)) {
                continue;
            }

            $this->pendingProcess();
        }
    }

    public function closeInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs, $scriptTime, $pendingTime)
    {
        $this->archiveFinishedRunningInstance($flgScriptRunningInstance, $logs, $scriptTime, $pendingTime);
    }

    protected function canRun(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;

        //in group
        if ($flgScriptRunningInstance->getGroupName()) {
            $canRun = $this->canRunWithinGroup($flgScriptRunningInstance);
        } elseif ($flgScriptRunningInstance->getUniqueId()) { //if no group, no queue but still control uniqueId
            $canRun = $this->canRunWithinUniqueId($flgScriptRunningInstance);
        } else {//just log scripts
            $this->run($flgScriptRunningInstance);
            $canRun = true;
        }

        return $canRun;
    }

    protected function canRunWithinGroup(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;

        var_dump('canRunWithinGroup');
        if ($this->isFirstInQueue($flgScriptRunningInstance)) {
            if (!$this->hasGroupRunningInstance($flgScriptRunningInstance)) {
                $this->run($flgScriptRunningInstance);
                $canRun = true;
            }
        } else {
            if ($this->isMaxPendingInstance($flgScriptRunningInstance)) {
                $this->throwMaxPendingInstanceError($flgScriptRunningInstance);
            }
        }

        return $canRun;
    }

    protected function canRunWithinUniqueId(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;

        if (!$this->hasUniqueRunningInstance($flgScriptRunningInstance)) {
            $this->run($flgScriptRunningInstance);
            $canRun = true;
        }

        return $canRun;
    }

    protected function isFirstInQueue(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        var_dump('isFirstInQueue');
        $isFirstInQueue = false;
        $firstInQueue = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getFirstInQueue($flgScriptRunningInstance->getGroupSha());

        if ($firstInQueue->getId() == $flgScriptRunningInstance->getId()) {
            $isFirstInQueue = true;
        }

        var_dump($isFirstInQueue);
        return $isFirstInQueue;
    }

    protected function hasGroupRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $runningInstances = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getRunningInstanceWithinGroup($flgScriptRunningInstance->getGroupSha());
        return $this->hasRunningInstance($flgScriptRunningInstance, $runningInstances);
    }

    protected function hasUniqueRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        var_dump('hasGroupRunningInstance');
        $runningInstances = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getRunningInstanceWithinUnique($flgScriptRunningInstance->getUniqueSha());
        var_dump(count($runningInstances));

        return $this->hasRunningInstance($flgScriptRunningInstance, $runningInstances);
    }

    protected function hasRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $runningInstances = array())
    {
        var_dump('hasRunningInstance');
        $mainHasRunningInstance = false;
        $countRunningInstance = 0;
        foreach ($runningInstances as $runningInstance) {
            $hasRunningInstance = $this->isRunningInstance($runningInstance);

            //if one loop is true, all true (should not happen more than once, unless there is bug)
            if ($hasRunningInstance) {
                $mainHasRunningInstance = true;
                $countRunningInstance++;
            }
        }
        if ($countRunningInstance > 1) {
            $this->throwSimultaneousInstanceError($flgScriptRunningInstance);
        }

        var_dump($mainHasRunningInstance);
        return $mainHasRunningInstance;
    }

    protected function isRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $isRunningInstance = true;
        if (!$this->isStillAlive($flgScriptRunningInstance)) {
            $this->archiveBrokenInstance($flgScriptRunningInstance);

            $isRunningInstance = false;
        }

        return $isRunningInstance;
    }

    public function run(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $flgScriptRunningInstance->setStatus(FlgScriptStatus::STATE_RUNNING);
        $this->getEntityManager()->flush();
    }

    public function fail(FlgScriptInstanceLog $flgScriptInstanceLog, $reason = null, $status = FlgScriptStatus::STATE_FAILED)
    {
        $message = "This script has failed with the following output : " . ((!$reason) ? "No output given... not helpful !" : $reason);

        $flgScriptInstanceLog->setLog($message);
        $flgScriptInstanceLog->setStatus($status);
    }

    protected function isMaxPendingInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $countPending = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getPendingInstance($flgScriptRunningInstance->getGroupSha());

        return count($countPending) > $this->getOption('maxPendingInstance');
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

    protected function checkUniqueIdInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $this->checkAndArchivePreviousBrokenInstance($flgScriptRunningInstance);
        
        $instance = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getPendingInstanceWithSameUniqueId($flgScriptRunningInstance->getGroupSha(), $flgScriptRunningInstance->getUniqueSha());

        if ($instance->getId() != $flgScriptRunningInstance->getId()) {
            $this->throwPendingInstanceWithSameUniqueIdError($flgScriptRunningInstance);
        }
    }

    protected function isStillAlive(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $isStillAlive = false;
        $pid = $flgScriptRunningInstance->getPid();

        //works only for linux (of course)
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

    protected function throwPendingInstanceWithSameUniqueIdError(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $message = "Unique id is already used, no doubles accepted due to config options";
        $this->throwError($flgScriptRunningInstance, $message);
    }

    protected function throwError(FlgScriptRunningInstance $flgScriptRunningInstance, $message)
    {
        $this->archiveFailedInstance($flgScriptRunningInstance, $message, FlgScriptStatus::STATE_TERMINATED);

        throw new \Exception($message);
    }

    public function pendingProcess()
    {
        sleep($this->getOption('pendingLapsTime'));
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

    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        throw new \Exception("The option '$option' is missing");
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this->options;
    }

}
