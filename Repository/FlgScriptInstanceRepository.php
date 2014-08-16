<?php

namespace Earls\FlamingoCommandQueue\Repository;

use Doctrine\ORM\EntityRepository;
use Earls\FlamingoCommandQueue\Entity\FlgScript;
use Earls\FlamingoCommandQueue\Model\FlgScriptStatus;

class FlgScriptInstance extends EntityRepository
{

    public function getFirstInQueue(FlgScript $flgScript)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.flgScript = :script')
                ->setParameters(array(
                    'script' => $flgScript,
                ))
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getRunningInstance(FlgScript $flgScript)
    {
        $qb = $this->createQueryBuilder('i')
                ->innerJoin('i.flgScriptCurrentStatus', 'cs')
                ->where('i.flgScript = :script')
                ->andWhere("cs.status = :status")
                ->setParameters(array(
                    'script' => $flgScript,
                    'status' => FlgScriptStatus::STATE_RUNNING,
                ));

        return $qb->getQuery()->getOneOrNullResult();
    }

}
