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
     * list action
     */
    public function listAction() {
        $user = $this->frontendUserRepository->getCurrentFrontendUser(); /* @var $user \EssentialDots\EdSugarcrm\Domain\Model\FrontendUserWithCRMAccount */

	    if (!$user) {
		    $GLOBALS['TSFE']->pageNotFoundAndExit('User not logged in');
	    }

	    $this->view->assign('user', $user);
	    $this->view->assign('supportCases', $user->getCrmAccount() ? $user->getCrmAccount()->getCasesQueryResult() : null);
    }

    /**
     * show action
     *
     * @var \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase
     */
    public function showAction(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase $supportCase) {
        $this->view->assign('supportCase', $supportCase);
    }
}
?>