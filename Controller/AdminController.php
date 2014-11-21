<?php

namespace Earls\FlamingoCommandQueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Earls\FlamingoCommandQueueBundle\Model\FlgScriptStatus;

class AdminController extends Controller
{

    public function scriptListAction()
    {
        $scripts = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScript')->findAll();
        
        return $this->render('EarlsFlamingoCommandQueueBundle:Admin:scriptList.html.twig', array(
                    'base_template' => $this->container->getParameter('flamingo.admin.template'),
                    'scripts' => $scripts
        ));
    }

    public function scriptInstanceListAction($flgScriptId)
    {
        $scriptInstances = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog')
                ->createQueryBuilder('fi')
                ->where('fi.flgScript = :flg_script')
                ->setParameter('flg_script', $flgScriptId)
                ->setMaxResults("20")
                ->orderBy('fi.id', 'DESC')
                ->getQuery()
                ->getResult();

        return $this->render('EarlsFlamingoCommandQueueBundle:Admin:scriptInstanceList.html.twig', array(
                    'base_template' => $this->container->getParameter('flamingo.admin.template'),
                    'scriptInstances' => $scriptInstances,
                    'instanceStatus' => FlgScriptStatus::getStatusList()
        ));
    }

    public function scriptInstanceDetailsAction($flgScriptInstanceId)
    {
        $scriptInstance = $this->getEntityManager()->getRepository('Earls\FlamingoCommandQueueBundle\Entity\FlgScriptInstanceLog')->find($flgScriptInstanceId);
        
        $logManager = $this->container->get('flamingo.manager.log');
        $logs = $logManager->getShortLogs($scriptInstance->getLog());
//die(var_dump($logs));
        return $this->render('EarlsFlamingoCommandQueueBundle:Admin:scriptInstanceDetails.html.twig', array(
                    'base_template' => $this->container->getParameter('flamingo.admin.template'),
                    'scriptInstance' => $scriptInstance,
                    'logs' => $logs
        ));
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

}
