<?php

namespace Earls\FlamingoCommandQueueBundle\Model;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Model\Stopwatch;
use Earls\FlamingoCommandQueueBundle\Model\FlgCommand;

/**
 * Earls\FlamingoCommandQueueBundle\Model\CommandManagerInstance
 *
 * Manage only one instance of command, create a new object in order to manage more than one
 */
class CommandManagerInstance
{

    /**
     *
     * @var \Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstance
     */
    protected $currentInstance;

    /**
     *
     * @var boolean
     */
    protected $started = false;

    /**
     *
     * @var boolean
     */
    protected $stopped = false;

    /**
     *
     * @var Stopwatch
     */
    protected $stopWatch;

    /**
     *
     * @var ExecutionControl
     */
    protected $executionControl;

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     *
     * @var array 
     */
    protected $options = array();

    /**
     *
     * @var FlgCommand 
     */
    protected $flgCOmmand;

    public function __construct(Stopwatch $stopWatch, ExecutionControl $executionControl, EntityManager $em)
    {
        $this->stopWatch = $stopWatch;
        $this->executionControl = $executionControl;
        $this->entityManager = $em;
    }

    public function start($name, FlgCommand $flgCommand)
    {
        if ($this->started) {
            throw new \Exception("The command has already been started");
        }

        $this->executionControl->setOptions($this->getOptions());
        $this->started = true;
        $this->setStartTime();

        $this->currentInstance = $this->currentInstance->openInstance($name, $flgCommand);

        //authorization to run
        $this->setStartTime('pending');
        $this->executionControl->authorizeRunning($this->currentInstance);
        $this->setEndTime('pending');
    }

    public function stop(array $logs = null, $status = FlgScriptStatus::STATE_FINISHED)
    {
        if ($this->stopped) {
            throw new \Exception("The command has already been stopped");
        }
        $this->stopped = true;

        $this->setEndTime();
        $this->executionControl->closeInstance($this->currentInstance, $logs, $this->getFinishTime(), $this->getFinishTime('pending'), $status, $this->flgCOmmand->getArchiveEnable());
    }

    public function saveProgress(array $logs)
    {
        $this->executionControl->saveProgress($this->currentInstance, $logs);
    }

    /**
     *
     * @return Stopwatch
     */
    protected function getStopWatch()
    {
        return $this->stopWatch;
    }

    /**
     *
     * @param string $id
     */
    protected function setStartTime($id = 'main')
    {
        $this->getStopWatch()->start($id);
    }

    /**
     *
     * @param string $id
     */
    protected function setEndTime($id = 'main')
    {
        $this->getStopWatch()->stop($id);
    }

    /**
     *
     * @param  string       $id
     * @return interger     The time (in milliseconds)
     */
    protected function getFinishTime($id = 'main')
    {
        return $this->stopWatch->getEvent($id)->getDuration();
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
     * @param  \Doctrine\ORM\EntityManager                              $entityManager
     * @return \Earls\FlamingoCommandQueueBundle\Manager\CommandManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    public function getDefaultOptions()
    {
        return array(
            'maxPendingInstance' => $this->flgCOmmand->getMaxPendingInstance(),
            'pendingLapsTime' => $this->flgCOmmand->getPendingLapsTime(),
        );
    }

    public function getOptions()
    {
        if (empty($this->options)) {
            $this->options = $this->getDefaultOptions();
        }

        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);

        return $this;
    }

}
