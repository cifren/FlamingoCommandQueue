<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScript
 * 
 * @ORM\Table(name="flg_script", indexes={@ORM\Index(name="sha_id_idx", columns={"sha_id"})})
 * @ORM\Entity
 */
class FlgScript
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
     * @var string $shaId
     *
     * @ORM\Column(name="sha_id", type="string", length=40)
     */
    protected $shaId;

    /**
     * @var string $name
     * 
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

    /**
     * @var FlgScriptGroup flgScriptGroup
     *
     * @ORM\ManyToOne(targetEntity="FlgScriptGroup")
     * @ORM\JoinColumn(name="flg_script_group_id", referencedColumnName="id")
     */
    protected $flgScriptGroup;

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
     * @return string
     */
    public function getShaId()
    {
        return $this->shaId;
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @return FlgScriptGroup
     */
    public function getFlgScriptGroup()
    {
        return $this->flgScriptGroup;
    }

    /**
     * 
     * @param string $shaId
     * @return \Earls\FlamingoCommandQueueBundle\Entity\FlgScript
     */
    public function setShaId($shaId)
    {
        $this->shaId = $shaId;
        return $this;
    }

    /**
     * 
     * @param string $name
     * @return \Earls\FlamingoCommandQueueBundle\Entity\FlgScript
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 
     * @param \Earls\FlamingoCommandQueueBundle\Entity\FlgScriptGroup $flgScriptGroup
     * @return \Earls\FlamingoCommandQueueBundle\Entity\FlgScript
     */
    public function setFlgScriptGroup(FlgScriptGroup $flgScriptGroup)
    {
        $this->flgScriptGroup = $flgScriptGroup;
        return $this;
    }

}
