<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScript;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;
use Earls\FlamingoCommandQueueBundle\Manager\LogManager;
use Earls\FlamingoCommandQueueBundle\Entity\FlgWatchScript;

/**
 * Earls\FlamingoCommandQueueBundle\Model\ExecutionControl.
 *
 * it is the control tower of the script instance
 */
class ExecutionControl implements ExecutionControlInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $logLimitStatus;

    /**
     * @var int
     */
    protected $logLimitLine;

    /**
     * @var LogManager
     */
    protected $logManager;

    public function __construct(EntityManager $entityManager, $logLimitLine, $logLimitStatus, LogManager $logManager)
    {
        $this->setEntityManager($entityManager);
        $this->logLimitStatus = $logLimitStatus;
        $this->logLimitLine = $logLimitLine;
        $this->logManager = $logManager;
    }

    public function openInstance($name, FlgCommand $flgCommand)
    {
        $flgScript = $this->getScript($name);

        //create new instance
        $currentInstance = $this->createScriptRunningInstance($name, $flgCommand->getGroupName(), $flgCommand->getUniqueId(), $flgScript);

        //create a scriptWatcher if references exist
        $this->createScriptWatcher($name, $currentInstance, $flgCommand->getWatchScriptReferences());

        return $currentInstance;
    }

    protected function createScriptWatcher($name, FlgScriptRunningInstance $currentInstance, array $references)
    {
        $uniqueReference = $this->getSha1FromReferences($name, $references);

        $scriptWatcher = new FlgWatchScript();
        $scriptWatcher->setReferenceId($uniqueReference);
        $scriptWatcher->setFlgScriptRunningInstance($currentInstance);

        $this->getEntityManager()->persist($scriptWatcher);
        $this->getEntityManager()->flush($scriptWatcher);
    }

    protected function getSha1FromReferences($name, array $references)
    {
        $concatRef = $name;
        foreach ($references as $key => $ref) {
            if (!is_array($ref) && !(is_object($ref))) {
                $concatRef .= $key.$ref;
            }
        }

        $uniqueReference = sha1($concatRef);

        return $uniqueReference;
    }

    protected function getScript($name)
    {
        $flgScript = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScript')->findOneBy(array('name' => $name));

        if (!$flgScript) {
            $flgScript = new FlgScript();
            $flgScript->setName($name);
            $this->getEntityManager()->persist($flgScript);
            $this->getEntityManager()->flush($flgScript);
        }

        return $flgScript;
    }

    public function createScriptRunningInstance($name, $group, $uniqueId, $flgScript)
    {
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
        $this->getEntityManager()->flush($flgScriptRunningInstance);

        return $flgScriptRunningInstance;
    }

    public function authorizeRunning(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;

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

    public function closeInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs, $scriptTime, $pendingTime, $status = FlgScriptStatus::STATE_FINISHED, $archiveEnable = true)
    {
        $this->archiveFinishedRunningInstance($flgScriptRunningInstance, $logs, $scriptTime, $pendingTime, $status, $archiveEnable);
    }

    protected function canRun(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;

        //in group
        if ($flgScriptRunningInstance->getGroupName()) {
            $canRun = $this->canRunWithinGroup($flgScriptRunningInstance);
        } elseif ($flgScriptRunningInstance->getUniqueId()) { //if no group, no queue but still control uniqueId
            $canRun = $this->canRunWithinUniqueId($flgScriptRunningInstance);
        } else {
            //just log scripts
            $this->run($flgScriptRunningInstance);
            $canRun = true;
        }

        return $canRun;
    }

    protected function canRunWithinGroup(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $canRun = false;

        if ($this->isFirstInQueue($flgScriptRunningInstance)) {
            if (!$this->hasGroupRunningInstance($flgScriptRunningInstance)) {
                $this->run($flgScriptRunningInstance);
                $canRun = true;
            }
        } else {
            if ($this->isMaxPendingInstance($flgScriptRunningInstance)) {
                $this->archiveFailedInstance($flgScriptRunningInstance, 'You reach the limit of pending instance possible, you can increase this value in your config', FlgScriptStatus::STATE_TERMINATED);
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
        $isFirstInQueue = false;
        $firstInQueue = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getFirstInQueue($flgScriptRunningInstance->getGroupSha());

        if ($firstInQueue->getId() == $flgScriptRunningInstance->getId()) {
            $isFirstInQueue = true;
        }

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
        $runningInstances = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance')
                ->getRunningInstanceWithinUnique($flgScriptRunningInstance->getUniqueSha());

        return $this->hasRunningInstance($flgScriptRunningInstance, $runningInstances);
    }

    protected function hasRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $runningInstances = array())
    {
        $mainHasRunningInstance = false;
        $countRunningInstance = 0;
        foreach ($runningInstances as $runningInstance) {
            $hasRunningInstance = $this->isRunningInstance($runningInstance);

            //if one loop is true, all true (should not happen more than once, unless there is bug)
            if ($hasRunningInstance) {
                $mainHasRunningInstance = true;
                ++$countRunningInstance;
            }
        }
        if ($countRunningInstance > 1) {
            $this->archiveFailedInstance($flgScriptRunningInstance, 'Simultaneous running process, this should not happen', FlgScriptStatus::STATE_TERMINATED);
        }

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
        $this->getEntityManager()->flush($flgScriptRunningInstance);
    }

    public function fail(FlgScriptInstanceLog $flgScriptInstanceLog, $reason = null, $status = FlgScriptStatus::STATE_FAILED)
    {
        $message = 'This script has stopped with the following output : '.((!$reason) ? 'No output given... not helpful !' : $reason);

        //copy from Symfony\Bridge\Monolog\Handler\DebugHandler::getLogs()
        $records[] = array(
            'timestamp' => time(),
            'message' => $message,
            'priority' => 250,
            'priorityName' => 'NOTICE',
            'context' => null,
        );

        //keep previous logs
        if ($flgScriptInstanceLog->getLog()) {
            $records = array_merge($flgScriptInstanceLog->getLog(), $records);
        }
        $flgScriptInstanceLog->setLog($records);
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
            $this->archiveFailedInstance($flgScriptRunningInstance, 'Unique id is already used, no doubles accepted due to config options', FlgScriptStatus::STATE_TERMINATED);
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

    protected function archiveFinishedRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs, $scriptTime, $pendingTime, $status = FlgScriptStatus::STATE_FINISHED, $archiveEnable = true)
    {
        //doesn't create archive if disable
        if ($archiveEnable) {
            $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
            $filtereLogs = $this->filterLogs($logs);
            $instanceLog->setLog($filtereLogs);
            $instanceLog->setDuration($scriptTime);
            $instanceLog->setPendingDuration($pendingTime);
            $instanceLog->setStatus($status);
            $this->getEntityManager()->persist($instanceLog);
        }

        $this->getEntityManager()->remove($flgScriptRunningInstance);
        $this->getEntityManager()->flush($flgScriptRunningInstance);
    }

    protected function archiveBrokenInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
        $this->getEntityManager()->remove($flgScriptRunningInstance);
        $this->fail($instanceLog, 'The script has stopped after an Fatal Error happening during the process', FlgScriptStatus::STATE_FAILED);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush($instanceLog);
    }

    protected function archiveFailedInstance(FlgScriptRunningInstance $flgScriptRunningInstance, $message, $status = FlgScriptStatus::STATE_FAILED)
    {
        $instanceLog = $this->createArchiveInstance($flgScriptRunningInstance);
        $this->getEntityManager()->remove($flgScriptRunningInstance);
        $this->fail($instanceLog, $message, $status);

        $this->getEntityManager()->persist($instanceLog);
        $this->getEntityManager()->flush($instanceLog);
    }

    public function saveProgress(FlgScriptRunningInstance $flgScriptRunningInstance, array $logs)
    {
        $flgScriptRunningInstance->setLog($logs);
        $this->getEntityManager()->flush($flgScriptRunningInstance);
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
        $flgScriptInstanceLog->setLog($flgScriptRunningInstance->getLog());
        $flgScriptInstanceLog->setFlgScript($flgScriptRunningInstance->getFlgScript());

        return $flgScriptInstanceLog;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     *
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

    protected function filterLogs(array $logs)
    {
        $logManager = $this->getLogManager();
        $filteredStatusLogs = $logManager->getSpecificLogs($logs, $this->logLimitStatus);

        if ($this->logLimitLine !== 0) {
            $filteredLineLogs = array_slice($filteredStatusLogs, ($this->logLimitLine + 1) * -1);
            $filteredLogs = $filteredLineLogs;
        } else {
            $filteredLogs = $filteredStatusLogs;
        }

        return $filteredLogs;
    }

    protected function getLogManager()
    {
        return $this->logManager;
    }
}
