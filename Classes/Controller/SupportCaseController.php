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

class SupportCaseController extends \EssentialDots\EdSugarcrm\Controller\AbstractController
{

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
     * list action
     */
    public function listAction()
    {
        $user = $this->getUser();
        $this->view->assign('user', $user);
        $this->view->assign('supportCases', $user->getCrmAccount() ? $user->getCrmAccount()->getCasesQueryResult() : null);
    }

    /**
     * show action
     *
     * @param string $uid
     */
    public function showAction($uid)
    {
        $user = $this->getUser();
        $this->view->assign('user', $user);
        $supportCases = $this->supportCaseRepository->findByUid($uid);
        $this->view->assign('supportCase', $supportCases);
        /** @var \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCases */
        $emails = $supportCases->getEmails();
        $toEmail = $this->settings['SugarCRMBackend']['email'];
        $assignedUser = NULL;
        foreach ($emails as $email) {
            /** @var \EssentialDots\EdSugarcrm\Domain\Model\Email $email */
            if (strpos($email->getFromAddr(), $user->getEmail()) === FALSE) {
                $toEmail = $email->getFromAddr();
            }
            $helper = $email->getAssignedUser();
            if (!empty($helper)) {
                $assignedUser = $helper;
            }
            if ($email->getDescriptionHtml() == '') {
                $email->setDescriptionHtml(html_entity_decode($email->getDescription()));
            }
            $doc = new \DOMDocument();
            $doc->loadHTML($email->getDescriptionHtml());
            $this->parseHTML($doc);
            $email->setDescriptionHtml($doc->saveHTML());
        }
        $newStatus = \EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_ASSIGNED;
        $helper = $supportCases->getAssignedUser();
        if (empty($helper)) {
            $newStatus = \EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_NEW;
        }
        $status = $supportCases->getStatus();
        if ($status == \EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_NEW ||
            $status == \EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_ASSIGNED ||
            $status == \EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_PENDING_INPUT
        ) {
            $newStatus = \EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_CLOSED;
        }
        $this->view->assign('toEmail', $toEmail);
        $this->view->assign('newStatus', $newStatus);
        $this->view->assign('assignedUser', $assignedUser);
        $this->view->assign('parentCase', \EssentialDots\EdSugarcrm\Domain\Model\Email::PARENT_CASE);
    }

    /**
     * Function for removing signatures from email list.
     *
     * @param \DOMNode $node
     * @return bool
     */
    protected function parseHTML(&$node){
        /** @var \DOMNode $node */
        if ($node->nodeName != 'hr'){
            $result = TRUE;
            $delete = array();
            foreach($node->childNodes as $child){
                /** @var \DOMNode $child */
                if ($result){
                    if (!$result = $this->parseHTML($child)){
                        if ($child->nodeName == 'hr'){
                            array_push($delete, $child);
                        }
                    }
                }else{
                    array_push($delete, $child);
                }
            }
            foreach ($delete as $deleteChild){
                $node->removeChild($deleteChild);
            }
            return $result;
        }else{
            return FALSE;
        }
    }

    /**
     * new action
     *
     */
    public function newAction()
    {
        $this->getUser();
    }

    /**
     * create action
     *
     * @param \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase
     */
    public function createAction(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase){
        $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        /* @var $persistenceManager \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
        $this->supportCaseRepository->generateNewSupportCase($supportCase);
        $this->supportCaseRepository->add($supportCase);
        $persistenceManager->persistAll();
        /** @var \EssentialDots\EdSugarcrm\Domain\Model\Email $email */
        $this->supportCaseRepository->addFirstEmail($supportCase, $email, $this->settings);
        $persistenceManager->persistAll();
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
        $emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $templatePathAndFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:ed_sugarcrm/Resources/Private/Templates/') . 'Email/SupportEmail.html';
        $emailView->setTemplatePathAndFilename($templatePathAndFilename);
        $emailView->assignMultiple(array(
            'link' => $this->settings['SugarCRMBackend']['case_url'] . $supportCase->getUid(),
            'case' => $supportCase->getName(),
            'firstName' => $email->getCreatedByUser()->getFirstName(),
            'lastName' => $email->getCreatedByUser()->getLastName()
        ));
        $emailBody = $emailView->render();
        $emailBody;

        /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
        $message->setTo($this->settings['SugarCRMBackend']['email'])
            ->setFrom($email->getFromAddr())
            ->setSubject($supportCase->getName());
        $message->setBody($emailBody, 'text/html');

        $message->send();

        $this->forward('info');
    }

    /**
     * update action
     *
     * @param string $uid
     * @param string $status
     */
    public function updateAction($uid, $status){
        $supportCase = $this->supportCaseRepository->findByUid($uid);
        $supportCase->setStatus($status);
        $this->supportCaseRepository->update($supportCase);
        $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        /* @var $persistenceManager \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
        $persistenceManager->persistAll();
        if ($supportCase->getStatus() == \EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_CLOSED){
            $this->forward('infoClosed');
        }else{
            $this->forward('infoReopened');
        }
    }

    /**
     * info action
     */
    public function infoAction(){
    }

    /**
     * info action
     */
    public function infoClosedAction(){
    }

    /**
     * info action
     */
    public function infoReopenedAction(){
    }

    /**
     * @return \EssentialDots\EdSugarcrm\Domain\Model\FrontendUserWithCRMAccount|\EssentialDots\EdTravel\Domain\Model\FrontendUserWithPermissionSets|\EssentialDots\ExtbaseDomainDecorator\Domain\Model\AbstractFrontendUser|\EssentialDots\ExtbaseDomainDecorator\Domain\Model\FrontendUser|null
     */
    protected function getUser()
    {
        $user = $this->frontendUserRepository->getCurrentFrontendUser();
        /* @var $user \EssentialDots\EdSugarcrm\Domain\Model\FrontendUserWithCRMAccount */
        if (!$user) {
            $GLOBALS['TSFE']->pageNotFoundAndExit('User not logged in');
        }
        return $user;
    }
}

?>