<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScriptCurrentStatus
 * 
 * @ORM\Table(name="flg_script_current_status")
 * @ORM\Entity
 */
class FlgScriptCurrentStatus
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
     * @var integer $pid
     * 
     * @ORM\Column(name="pid", type="integer")
     */
    protected $pid;

    /**
     * @var FlgScriptInstance flgScriptInstance
     *
     * @ORM\OneToOne(targetEntity="FlgScriptInstance", inversedBy="flgScriptCurrentStatus")
     * @ORM\JoinColumn(name="flg_script_instance_id", referencedColumnName="id")
     */
    protected $flgScriptInstance;

    /**
     * @var FlgStatus flgStatus
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return FlgScriptInstance
     */
    public function getFlgScriptInstance()
    {
        return $this->flgScriptInstance;
    }

    /**
     * 
     * @return FlgStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 
     * @param \Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstance $flgScriptInstance
     * @return \Earls\FlamingoCommandQueueBundle\Entity\FlgScriptCurrentStatus
     */
    public function setFlgScriptInstance(FlgScriptInstance $flgScriptInstance)
    {
        $this->flgScriptInstance = $flgScriptInstance;
        return $this;
    }

    /**
     * 
     * @param integer $status
     * @return \Earls\FlamingoCommandQueueBundle\Entity\FlgScriptCurrentStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function setPid()
    {
        $this->pid = posix_getpid();
        return $this;
    }

}
