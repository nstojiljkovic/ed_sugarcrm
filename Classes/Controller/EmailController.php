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

class EmailController extends \EssentialDots\EdSugarcrm\Controller\AbstractController {

    /**
     * @var \EssentialDots\ExtbaseDomainDecorator\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @var \EssentialDots\EdSugarcrm\Domain\Repository\EmailRepository
     * @inject
     */
    protected $emailRepository;

    /**
     * @var \EssentialDots\EdSugarcrm\Domain\Repository\AccountRepository
     * @inject
     */
    protected $accountRepository;

    /**
     * create action
     *
     * @var \EssentialDots\EdSugarcrm\Domain\Model\Email $email
     * @var \boolean $called
     */
    public function createAction(\EssentialDots\EdSugarcrm\Domain\Model\Email $email) {
        $user = $this->getUser();
        $id = $user->getCrmAccount()->getUid();
        $account = $this->accountRepository->findByUid($id);
        /** @var \EssentialDots\EdSugarcrm\Domain\Model\Account $account*/
        $email->setAccount($account);
        $helper = $account->getPrimaryEmailAddress();
        if (!empty($helper)){
            $primaryEmail = $helper->getEmailAddress();
        }else{
            $primaryEmail = $user->getEmail();
        }
        $email->setCreatedByUser($user);
        $email->setFromAddr($primaryEmail);
        $email->setDateSent(new \DateTime('NOW'));
        $email->setStatus(\EssentialDots\EdSugarcrm\Domain\Model\Email::STATUS_UNREAD);
        $email->setType(\EssentialDots\EdSugarcrm\Domain\Model\Email::TYPE_INBOUND);
        $this->emailRepository->add($email);
        $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        /* @var $persistenceManager \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
        $persistenceManager->persistAll();
        $called = FALSE;
        $args = func_get_args();
        if ($args[1]){
            $called = $args[1];
        }
        if (!$called){
            $this->forward('info');
        }
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