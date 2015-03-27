<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance
 *
 * @ORM\Table(name="flg_script_running_instance", indexes={
 *      @ORM\Index(name="group_sha_idx", columns={"group_sha"}),
 *      @ORM\Index(name="unique_sha_idx", columns={"unique_sha"})
 * })
 * @ORM\Entity(repositoryClass="Earls\FlamingoCommandQueueBundle\Repository\FlgScriptRunningInstanceRepository")
 */
class FlgScriptRunningInstance
{

    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="bigint", options={"unsigned"=true})
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
     * Should use only :
     *  - STATE_PENDING
     *  - STATE_RUNNING
     * 
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @var string $log
     *
     * @ORM\Column(name="log", type="array")
     */
    protected $log;

    /**
     * @var string $groupSha
     *
     * @ORM\Column(name="group_sha", type="string", length=40, nullable=true)
     */
    protected $groupSha;

    /**
     * @var string $groupName
     *
     * @ORM\Column(name="group_name", type="string", length=255, nullable=true)
     */
    protected $groupName;

    /**
     * @var string $uniqueSha
     *
     * @ORM\Column(name="unique_sha", type="string", length=40, nullable=true)
     */
    protected $uniqueSha;

    /**
     * @var string $uniqueId
     *
     * @ORM\Column(name="unique_id", type="string", length=255, nullable=true)
     */
    protected $uniqueId;

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

    public function setPid()
    {
        $this->pid = posix_getpid();

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function setGroupSha($groupName)
    {
        $this->groupSha = null;
        if ($groupName) {
            $this->groupSha = sha1($groupName);
        }

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

    public function getUniqueSha()
    {
        return $this->uniqueSha;
    }

    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function setUniqueSha($uniqueId)
    {
        $this->uniqueSha = null;
        if ($uniqueId) {
            $this->uniqueSha = sha1($uniqueId);
        }

        return $this;
    }

    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    public function hasUniqueId()
    {
        return $this->uniqueId ? true : false;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

}
