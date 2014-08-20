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

    public function getFirstInQueue($groupSha)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.status <> :status')
                ->andWhere('i.groupSha = :sha')
                ->setParameters(array(
                    'sha' => $groupSha,
                    'status' => FlgScriptStatus::STATE_RUNNING
                ))
                ->setMaxResults(1);
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }

    public function getRunningInstanceWithinGroup($groupSha)
    {
        $qb = $this->getQbRunningInstance('i')
                ->andWhere('i.groupSha = :sha')
                ->setParameter('sha', $groupSha);

        return $qb->getQuery()->getResult();
    }

    public function getRunningInstanceWithinUnique($uniqueSha)
    {
        $qb = $this->getQbRunningInstance('i')
                ->andWhere('i.uniqueSha = :sha')
                ->setParameter('sha', $uniqueSha);

        return $qb->getQuery()->getResult();
    }

    public function getQbRunningInstance()
    {
        $qb = $this->createQueryBuilder('i')
                ->where("i.status = :status")
                ->setParameter('status', FlgScriptStatus::STATE_RUNNING);

        return $qb;
    }

    public function getPendingInstance($groupSha)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.groupSha = :sha')
                ->andWhere("i.status = :status")
                ->setParameters(
                array(
                    'sha' => $groupSha,
                    'status' => FlgScriptStatus::STATE_PENDING,
                )
        );

        return $qb->getQuery()->getResult();
    }

    public function getPreviousInstance(FlgScriptRunningInstance $flgScriptRunningInstance)
    {
        $qb = $this->createQueryBuilder('i')
                ->where('i.id < :id')
                ->setParameters(
                array(
                    'id' => $flgScriptRunningInstance->getId()
                )
        );

        return $qb->getQuery()->getResult();
    }

    public function getPendingInstanceWithSameUniqueId($groupSha, $uniqueSha)
    {
        $qb = $this->createQueryBuilder('i')
                ->where("i.status = :status")
                ->andWhere("i.uniqueSha = :uniqueSha")
                ->setMaxResults(1)
                ->setParameters(
                array(
                    'uniqueSha' => $uniqueSha,
                    'status' => FlgScriptStatus::STATE_PENDING,
                )
        );

        if ($groupSha) {
            $qb
                    ->andWhere('i.groupSha = :sha')
                    ->setParameter('sha', $groupSha);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

}
