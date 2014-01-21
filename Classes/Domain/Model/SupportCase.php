<?php
namespace EssentialDots\EdSugarcrm\Domain\Model;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

class SupportCase extends \EssentialDots\EdSugarcrm\Domain\Model\AbstractEntity {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $status;

	/**
	 * emails
	 *
	 * @lazy
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\Email>
	 */
	protected $emails;

	/**
	 * @lazy
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	protected $assignedUser;

	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $priority;

	/**
	 * @var string
	 */
	protected $resolution;

	/**
	 * @var string
	 */
	protected $workLog;

	/**
	 * @var int
	 */
	protected $caseNumber;

	/**
	 * @lazy
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\Account
	 */
	protected $account;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected $_emailsQueryResult;

    /**
     * @var \DateTime
     */
    protected $dateEntered;

	/**
	 * __construct
	 *
	 * @param $decoratedObject
	 */
	public function __construct($decoratedObject) {
		parent::__construct($decoratedObject);
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
        $this->_dateSent = null;
	}

	/**
	 * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		/**
		 * Do not modify this method!
		 * It will be rewritten on each save in the extension builder
		 * You may modify the constructor of this class instead
		 */
		$this->emails = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Adds an email
	 *
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\Email $email
	 * @return void
	 */
	public function addEmail(\EssentialDots\EdSugarcrm\Domain\Model\Email $email) {
		$this->emails->attach($email);
	}

	/**
	 * Removes an email
	 *
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\Email $emailToRemove The email to be removed
	 * @return void
	 */
	public function removeEmail(\EssentialDots\EdSugarcrm\Domain\Model\Email $emailToRemove) {
		$this->emails->detach($emailToRemove);
	}

	/**
	 * Returns the emails
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\Email> $emails
	 */
	public function getEmails() {
		return $this->emails;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function getEmailsQueryResult() {
		if (!$this->_emailsQueryResult) {
			$emailRepository = $this->getObjectManager()->get('EssentialDots\\EdSugarcrm\\Domain\\Repository\\EmailRepository'); /* @var $emailRepository \EssentialDots\EdSugarcrm\Domain\Repository\EmailRepository */
			$query = $emailRepository->createQuery();
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->setOrderings(array(
				'date_entered' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
			));
			$this->_emailsQueryResult = $query->matching(
				$query->logicalAnd(
						$query->equals('parent_id', $this->getUid()),
						$query->equals('parent_type', 'Cases')
				)
			)->execute();

		}
		return $this->_emailsQueryResult;
	}

	/**
	 * Sets the emails
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\Email> $emails
	 */
	public function setEmails(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $emails) {
		$this->emails = $emails;
	}

	/**
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\User $assignedUser
	 */
	public function setAssignedUser($assignedUser) {
		$this->assignedUser = $assignedUser;
	}

	/**
	 * @return \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	public function getAssignedUser() {
		return $this->assignedUser;
	}

	/**
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\Account $account
	 */
	public function setAccount($account) {
		$this->account = $account;
	}

	/**
	 * @return \EssentialDots\EdSugarcrm\Domain\Model\Account
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * @param int $caseNumber
	 */
	public function setCaseNumber($caseNumber) {
		$this->caseNumber = $caseNumber;
	}

	/**
	 * @return int
	 */
	public function getCaseNumber() {
		return $this->caseNumber;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $priority
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}

	/**
	 * @return string
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @param string $resolution
	 */
	public function setResolution($resolution) {
		$this->resolution = $resolution;
	}

	/**
	 * @return string
	 */
	public function getResolution() {
		return $this->resolution;
	}

	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $workLog
	 */
	public function setWorkLog($workLog) {
		$this->workLog = $workLog;
	}

	/**
	 * @return string
	 */
	public function getWorkLog() {
		return $this->workLog;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		if ($this->_objectManager === NULL) {
			$this->_objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		}
		return $this->_objectManager;
	}

    /**
     * @param DateTime $dateEntered
     */
    public function setDateEntered($dateEntered){
        $this->dateEntered = $dateEntered;
    }

    /**
     * @return DateTime
     */
    public function getDateEntered(){
        return $this->dateEntered;
    }

    /**
     * @return DateTime
     */
    public function getDateEnteredFormatted(){
        if (!is_object($this->dateEntered)) return '';
        return $this->dateEntered->format('Y-m-d H:i:s');
    }
}