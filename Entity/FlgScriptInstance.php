<?php

namespace Earls\FlamingoCommandQueue\Entity;

/**
 * Earls\FlamingoCommandQueue\Entity\FlgScriptInstance
 */
class FlgScriptInstance
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
     * @ORM\Column(name="log", type="text")
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
     * @var FlgScript flgScript
     *
     * @ORM\ManyToOne(targetEntity="FlgScript")
     * @ORM\JoinColumn(name="flg_script_id", referencedColumnName="id")
     */
    protected $flgScript;

    /**
     * @var FlgScriptCurrentStatus $flgScriptCurrentStatus
     *
     * @OneToOne(targetEntity="FlgScriptCurrentStatus", mappedBy="flgScriptInstance")
     */
    protected $flgScriptCurrentStatus;

    /**
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * 
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * 
     * @return int
     */
    public function getPendingDuration()
    {
        return $this->pendingDuration;
    }

    /**
     * 
     * @return FlgScript
     */
    public function getFlgScript()
    {
        return $this->flgScript;
    }

    /**
     * 
     * @param string $log
     * @return \Earls\FlamingoCommandQueue\Entity\FlgScriptInstance
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * 
     * @param int $duration
     * @return \Earls\FlamingoCommandQueue\Entity\FlgScriptInstance
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * 
     * @param int $pendingDuration
     * @return \Earls\FlamingoCommandQueue\Entity\FlgScriptInstance
     */
    public function setPendingDuration($pendingDuration)
    {
        $this->pendingDuration = $pendingDuration;
        return $this;
    }

    /**
     * 
     * @param \Earls\FlamingoCommandQueue\Entity\FlgScript $flgScript
     * @return \Earls\FlamingoCommandQueue\Entity\FlgScriptInstance
     */
    public function setFlgScript(FlgScript $flgScript)
    {
        $this->flgScript = $flgScript;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime("now");

        return $this;
    }

    public function getFlgScriptCurrentStatus()
    {
        return $this->flgScriptCurrentStatus;
    }

    public function setFlgScriptCurrentStatus(FlgScriptCurrentStatus $flgScriptCurrentStatus)
    {
        $this->flgScriptCurrentStatus = $flgScriptCurrentStatus;
        return $this;
    }

}
