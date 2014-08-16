<?php

namespace Earls\FlamingoCommandQueue\Manager;

use Doctrine\ORM\EntityManager;
use Earls\FlamingoCommandQueue\Model\Stopwatch;

/**
 * Earls\FlamingoCommandQueue\Manager\CommandManager
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
     * @var \Earls\FlamingoCommandQueue\Entity\FlgScriptInstance
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

    public function start($id, $name, $group = null)
    {
        if ($this->started) {
            throw new \Exception("The command has already been started");
        }
        $this->executionControl->setConfig($this->getConfig());
        $this->started = true;
        $this->setStartTime();

        //create new instance
        $this->currentInstance = $this->executionControl->openScriptInstance($id, $name, $group);

        //authorization to run
        $this->setStartTime('pending');
        $this->executionControl->authorizeRunning($this->currentInstance);
        $this->setStopTime('pending');
    }

    public function stop($logs = null)
    {
        if ($this->stoped) {
            throw new \Exception("The command has already been stopped");
        }
        $this->stoped = true;

        $this->currentInstance->setLog($logs);
        $this->currentInstance->setDuration($this->getFinishTime());
        $this->currentInstance->setPendingDuration($this->getFinishTime('pending'));

        $this->executionControl->finish($this->currentInstance);
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
     * @param  string   $id
     * @return interger
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
     * @param  \Doctrine\ORM\EntityManager                        $entityManager
     * @return \Earls\FlamingoCommandQueue\Manager\CommandManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    public function getConfig()
    {
        return array(
            'maxPendingInstance' => $this->maxPendingInstance,
            'pendingLapsTime' => $this->pendingLapsTime
        );
    }

}
