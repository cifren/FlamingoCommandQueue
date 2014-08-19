<?php

namespace Earls\FlamingoCommandQueueBundle\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueueBundle\Model\Stopwatch;

/**
 * Earls\FlamingoCommandQueueBundle\Manager\CommandManager
 *
 * Manage only one instance of command, create a new object in order to manage more than one
 */
class CommandManager
{

    /**
     *
     * @var integer
     */
    protected $maxPendingInstance = 30;

    /**
     * in second
     * @var integer
     */
    protected $pendingLapsTime = 60;

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
    protected $stoped = false;

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

    public function __construct(Stopwatch $stopWatch, ExecutionControl $executionControl, EntityManager $em)
    {
        $this->stopWatch = $stopWatch;
        $this->executionControl = $executionControl;
        $this->entityManager = $em;
    }

    public function start($name, $group = null)
    {
        if ($this->started) {
            throw new \Exception("The command has already been started");
        }
        $this->executionControl->setOptions($this->getOptions());
        $this->started = true;
        $this->setStartTime();

        //create new instance
        $this->currentInstance = $this->executionControl->createScriptRunningInstance($name, $group);

        //authorization to run
        $this->setStartTime('pending');
        $this->executionControl->authorizeRunning($this->currentInstance);
        $this->setEndTime('pending');
    }

    public function stop(array $logs = null)
    {
        if ($this->stoped) {
            throw new \Exception("The command has already been stopped");
        }
        $this->stoped = true;
        
        $this->setEndTime();
        $this->executionControl->closeInstance($this->currentInstance, $logs, $this->getFinishTime(), $this->getFinishTime('pending'));
    }

    public function setMaxPendingInstance($maxInstance)
    {
        $this->maxPendingInstance = $maxInstance;
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

    public function getOptions()
    {
        return array(
            'maxPendingInstance' => $this->maxPendingInstance,
            'pendingLapsTime' => $this->pendingLapsTime
        );
    }

    public function setOptions(array $options)
    {
        if (isset($options['maxPendingInstance'])) {
            $this->maxPendingInstance = $options['maxPendingInstance'];
        }
        if (isset($options['pendingLapsTime'])) {
            $this->pendingLapsTime = $options['pendingLapsTime'];
        }

        return $this;
    }

}
