<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgWatchScript
 *
 * @ORM\Table(name="flg_watch_script", indexes={
 *      @ORM\Index(name="name_idx", columns={"referenceId"})
 * })
 * @ORM\Entity
 */
class FlgWatchScript
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
     * @var int $referenceId
     *
     * @ORM\Column(name="reference_id", type="interger")
     */
    protected $referenceId;

    /**
     * @var FlgScriptRunningInstance
     * 
     * @ORM\ManyToOne(targetEntity="FlgScriptRunningInstance")
     * @ORM\JoinColumn(name="flg_script_running_instance_id", referencedColumnName="id")
     */
    protected $flgScriptRunningInstance;

    public function getId()
    {
        return $this->id;
    }

    public function getReferenceName()
    {
        return $this->referenceName;
    }

    public function getReferenceId()
    {
        return $this->referenceId;
    }

    public function getFlgScriptRunningInstance()
    {
        return $this->flgScriptRunningInstance;
    }

    public function setReferenceName($referenceName)
    {
        $this->referenceName = $referenceName;
        return $this;
    }

    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
        return $this;
    }

    public function setFlgScriptRunningInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $this->flgScriptRunningInstance = $flgScriptRunningInstance;
        return $this;
    }

}
