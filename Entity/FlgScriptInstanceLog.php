<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog
 *
 * @ORM\Table(name="flg_script_instance_log")
 * @ORM\Entity
 */
class FlgScriptInstanceLog
{

    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $log
     *
     * @ORM\Column(name="log", type="array")
     */
    protected $log;

    /**
     * @var int $duration
     *
     * @ORM\Column(name="duration", type="integer")
     */
    protected $duration;

    /**
     * @var int $pendingDuration
     *
     * @ORM\Column(name="pending_duration", type="integer")
     */
    protected $pendingDuration;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var integer flgStatus
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
