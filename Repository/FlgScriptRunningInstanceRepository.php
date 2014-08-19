<?php

namespace Earls\FlamingoCommandQueueBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Earls\FlamingoCommandQueueBundle\Entity\FlgScriptRunningInstance;
use Earls\FlamingoCommandQueueBundle\Model\FlgScriptStatus;

/**
 * Earls\FlamingoCommandQueueBundle\Repository\FlgScriptRunningInstanceRepository
 */
class FlgScriptRunningInstanceRepository extends EntityRepository
{

    public function getFirstInQueue($shaGroup)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.status <> :status')
                ->andWhere('i.groupSha = :sha')
                ->setParameters(array(
                    'sha' => $shaGroup,
                    'status' => FlgScriptStatus::STATE_RUNNING
                ))
                ->setMaxResults(1);
        $result = $qb->getQuery()->getOneOrNullResult();
                
        return $result;
    }

    public function getRunningInstance($shaGroup)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.groupSha = :sha')
                ->andWhere("i.status = :status")
                ->setParameters(array(
                    'sha' => $shaGroup,
                    'status' => FlgScriptStatus::STATE_RUNNING,
                ));

        return $qb->getQuery()->getResult();
    }

    public function getPendingInstance($shaGroup)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.groupSha = :sha')
                ->andWhere("i.status = :status")
                ->setParameters(array(
                    'sha' => $shaGroup,
                    'status' => FlgScriptStatus::STATE_PENDING,
                ));

        return $qb->getQuery()->getResult();
    }

    public function getPreviousInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.id < :id')
                ->setParameters(array(
                    'id' => $flgScriptRunningInstance->getId()
                ));

        return $qb->getQuery()->getResult();
    }

}
