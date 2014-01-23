<?php
namespace EssentialDots\EdSugarcrm\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Marko Milojevic, Essential Dots d.o.o. Belgrade
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class SupportCaseController extends \EssentialDots\EdSugarcrm\Controller\AbstractController {

    /**
     * @var \EssentialDots\ExtbaseDomainDecorator\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @var \EssentialDots\EdSugarcrm\Domain\Repository\SupportCaseRepository
     * @inject
     */
    protected $supportCaseRepository;

    /**
     * @var \EssentialDots\EdSugarcrm\Domain\Repository\AccountRepository
     * @inject
     */
    protected $accountRepository;

    /**
     * list action
     */
    public function listAction() {
        $user = $this->getUser();
	    $this->view->assign('user', $user);
	    $this->view->assign('supportCases', $user->getCrmAccount() ? $user->getCrmAccount()->getCasesQueryResult() : null);
    }

    /**
     * show action
     *
     * @param string $uid
     */
    public function showAction($uid) {
        $user = $this->getUser();
        $this->view->assign('user', $user);
        $supportCases = $this->supportCaseRepository->findByUid($uid);
        $this->view->assign('supportCase', $supportCases);
        /** @var \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCases */
        $emails = $supportCases->getEmails();
        $toEmail = $this->settings['SugarCRMBackend']['email'];
        $assignedUser = NULL;
        foreach($emails as $email){
            /** @var \EssentialDots\EdSugarcrm\Domain\Model\Email $email */
            if (strpos($email->getFromAddr(), $user->getEmail()) === FALSE){
                $toEmail = $email->getFromAddr();
            }
            $helper = $email->getAssignedUser();
            if (!empty($helper)){
                $assignedUser = $helper;
            }
            if ($email->getDescriptionHtml() == ''){
                $email->setDescriptionHtml(html_entity_decode($email->getDescription()));
            }
            $helper = explode("<div><hr /></div>", $email->getDescriptionHtml());
            $email->setDescriptionHtml(str_replace('&nbsp;', '', str_replace("<br /><br />","<br />", $helper[0])));
        }
        $this->view->assign('toEmail', $toEmail);
        $this->view->assign('assignedUser', $assignedUser);
        $this->view->assign('parentCase', \EssentialDots\EdSugarcrm\Domain\Model\Email::PARENT_CASE);
    }

    /**
     * new action
     *
     */
    public function newAction() {
        $this->getUser();
    }

    /**
     * create action
     *
     * @var \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase
     */
    public function createAction(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase) {
        $user = $this->getUser();
        $id = $user->getCrmAccount()->getUid();
        $account = $this->accountRepository->findByUid($id);
        $supportCase->setAccount($account);
        $supportCase->setStatus(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_NEW);
        $this->supportCaseRepository->add($supportCase);
        $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        /* @var $persistenceManager \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
        $persistenceManager->persistAll();
        $email = $this->objectManager->get('EssentialDots\\EdSugarcrm\\Domain\\Model\\Email');
        /* @var $email \EssentialDots\EdSugarcrm\Domain\Model\Email */
        $email->setDescription($supportCase->getDescription());
        $email->setParentType(\EssentialDots\EdSugarcrm\Domain\Model\Email::PARENT_CASE);
        $email->setParentId($supportCase->getUid());
        $email->setName($supportCase->getName());
        $email->setDescriptionHtml(html_entity_decode($email->getDescription()));
        $email->setToAddrs($this->settings['SugarCRMBackend']['email']);
        $emailController = $this->objectManager->get('EssentialDots\\EdSugarcrm\\Controller\\EmailController');
        /* @var $emailController \EssentialDots\EdSugarcrm\Controller\EmailController */
        $emailController->createAction($email, TRUE);
        $this->forward('info');
    }

    /**
     * info action
     */
    public function infoAction() {}

    /**
     * @return \EssentialDots\EdSugarcrm\Domain\Model\FrontendUserWithCRMAccount|\EssentialDots\EdTravel\Domain\Model\FrontendUserWithPermissionSets|\EssentialDots\ExtbaseDomainDecorator\Domain\Model\AbstractFrontendUser|\EssentialDots\ExtbaseDomainDecorator\Domain\Model\FrontendUser|null
     */
    protected function getUser(){
        $user = $this->frontendUserRepository->getCurrentFrontendUser(); /* @var $user \EssentialDots\EdSugarcrm\Domain\Model\FrontendUserWithCRMAccount */
        if (!$user) {
            $GLOBALS['TSFE']->pageNotFoundAndExit('User not logged in');
        }
        return $user;
    }
}
?>