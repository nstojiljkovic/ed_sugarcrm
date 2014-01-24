<?php
namespace EssentialDots\EdSugarcrm\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Nikola Stojiljkovic, Essential Dots d.o.o. Belgrade
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

class SupportCaseRepository extends \EssentialDots\EdSugarcrm\Domain\Repository\AbstractRepository {

    /**
     * @var \EssentialDots\ExtbaseDomainDecorator\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @var \EssentialDots\EdSugarcrm\Domain\Repository\AccountRepository
     * @inject
     */
    protected $accountRepository;

    /**
     * @var \EssentialDots\EdSugarcrm\Domain\Repository\EmailRepository
     * @inject
     */
    protected $emailRepository;

    /**
     * @param \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase
     */
    public function generateNewSupportCase(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase &$supportCase){
        $user = $this->getUser();
        $id = $user->getCrmAccount()->getUid();
        $account = $this->accountRepository->findByUid($id);
        $supportCase->setAccount($account);
        $supportCase->setStatus(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase::STATUS_NEW);
    }

    /**
     * @param \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase
     * @param \EssentialDots\EdSugarcrm\Domain\Model\Email $email
     * @param mixed $settings
     */
    public function addFirstEmail(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase &$supportCase, \EssentialDots\EdSugarcrm\Domain\Model\Email &$email = NULL,  $settings){
        $email = $this->objectManager->get('EssentialDots\\EdSugarcrm\\Domain\\Model\\Email');
        /* @var $email \EssentialDots\EdSugarcrm\Domain\Model\Email */
        $email->setDescription($supportCase->getDescription());
        $email->setParentType(\EssentialDots\EdSugarcrm\Domain\Model\Email::PARENT_CASE);
        $email->setParentId($supportCase->getUid());
        $email->setName($supportCase->getName());
        $email->setDescriptionHtml(html_entity_decode($email->getDescription()));
        $email->setToAddrs($settings['SugarCRMBackend']['email']);
        $this->emailRepository->generateNewEmail($email);
        $this->emailRepository->add($email);
    }

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
