<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScript.
 *
 * @ORM\Table(name="flg_script", indexes={
 *      @ORM\Index(name="name_idx", columns={"name"})
 * })
 * @ORM\Entity
 */
class FlgScript
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return \Earls\FlamingoCommandQueueBundle\Entity\FlgScript
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
