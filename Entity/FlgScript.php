<?php

namespace Earls\FlamingoCommandQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Earls\FlamingoCommandQueueBundle\Entity\FlgScript
 *
 * @ORM\Table(name="flg_script", indexes={
 *      @ORM\Index(name="group_sha_idx", columns={"group_sha"}),
 *      @ORM\Index(name="group_name_idx", columns={"group_name"})
 * })
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param  string                                             $name
     * @return \Earls\FlamingoCommandQueueBundle\Entity\FlgScript
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

}
