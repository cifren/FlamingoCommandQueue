<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog.
 *
 * @ORM\Table(name="flg_script_instance_log")
 * @ORM\Entity
 */
class FlgScriptInstanceLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="log", type="array")
     */
    protected $log;

    /**
     * @var int The time (in milliseconds), time of the script lasted
     *
     * @ORM\Column(name="duration", type="bigint", options={"unsigned"=true}, nullable=true)
     */
    protected $duration;

    /**
     * @var int The time (in milliseconds), time of the script waited in the queue
     *
     * @ORM\Column(name="pending_duration", type="bigint", options={"unsigned"=true}, nullable=true)
     */
    protected $pendingDuration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var int flgStatus
     *
     * Should use only :
     *  - STATE_CANCELED
     *  - STATE_FINISHED
     *  - STATE_FAILED
     *  - STATE_TERMINATED
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @var FlgScript flgScript
     *
     * @ORM\ManyToOne(targetEntity="FlgScript")
     * @ORM\JoinColumn(name="flg_script_id", referencedColumnName="id")
     */
    protected $flgScript;

    public function getId()
    {
        return $this->id;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getPendingDuration()
    {
        return $this->pendingDuration;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getFlgScript()
    {
        return $this->flgScript;
    }

    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function setPendingDuration($pendingDuration)
    {
        $this->pendingDuration = $pendingDuration;

        return $this;
    }

    public function setCreatedAt()
    {
        $this->createdAt = new \Datetime();

        return $this;
    }

    public function setFlgScript(FlgScript $flgScript)
    {
        $this->flgScript = $flgScript;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
