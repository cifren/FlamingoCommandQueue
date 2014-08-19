<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance
 *
 * @ORM\Table(name="flg_script_running_instance")
 * @ORM\Entity
 */
class FlgScriptRunningInstance
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
     * @var integer flgStatus
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @var string $shaId
     *
     * @ORM\Column(name="group_sha", type="string", length=40)
     */
    protected $groupSha;

    /**
     * @var string $shaId
     *
     * @ORM\Column(name="group_name", type="string", length=255)
     */
    protected $groupName;

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

    public function getId()
    {
        return $this->id;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getGroupSha()
    {
        return $this->groupSha;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }

    public function getFlgScript()
    {
        return $this->flgScript;
    }

    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function setGroupSha($groupName)
    {
        $this->groupSha = sha1($groupName);

        return $this;
    }

    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

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
        $this->createdAt = new \Datetime();

        return $this;
    }

}
