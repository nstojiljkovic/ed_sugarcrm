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

class Account extends \EssentialDots\EdSugarcrm\Domain\Model\AbstractEntity {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $accountType;

	/**
	 * @var string
	 */
	protected $industry;

	/**
	 * @var string
	 */
	protected $annualRevenue;

	/**
	 * @var string
	 */
	protected $phoneFax;

	/**
	 * @var string
	 */
	protected $billingAddressStreet;

	/**
	 * @var string
	 */
	protected $billingAddressCity;

	/**
	 * @var string
	 */
	protected $billingAddressState;

	/**
	 * @var string
	 */
	protected $billingAddressPostalcode;

	/**
	 * @var string
	 */
	protected $billingAddressCountry;

	/**
	 * @var string
	 */
	protected $rating;

	/**
	 * @var string
	 */
	protected $phoneOffice;

	/**
	 * @var string
	 */
	protected $phoneAlternate;

	/**
	 * @var string
	 */
	protected $website;

	/**
	 * @var string
	 */
	protected $ownership;

	/**
	 * @var string
	 */
	protected $employees;

	/**
	 * @var string
	 */
	protected $tickerSymbol;

	/**
	 * @var string
	 */
	protected $shippingAddressStreet;

	/**
	 * @var string
	 */
	protected $shippingAddressCity;

	/**
	 * @var string
	 */
	protected $shippingAddressState;

	/**
	 * @var string
	 */
	protected $shippingAddressPostalcode;

	/**
	 * @var string
	 */
	protected $shippingAddressCountry;

	/**
	 * @var string
	 */
	protected $sicCode;

	/**
	 * emails
	 *
	 * @lazy
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\Email>
	 */
	protected $emails;

	/**
	 * cases
	 *
	 * @lazy
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\SupportCase>
	 */
	protected $cases;

	/**
	 * @lazy
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	protected $assignedUser;

	/**
	 * @lazy
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\EmailAddress>
	 */
	protected $emailAddresses;

	/**
	 * @lazy
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\EmailAddress>
	 */
	protected $emailAddressesPrimary;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $_objectManager = NULL;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected $_casesQueryResult;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected $_emailsQueryResult;

	/**
	 * __construct
	 *
	 * @param $decoratedObject
	 */
	public function __construct($decoratedObject) {
		parent::__construct($decoratedObject);
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
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
		$this->cases = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->emailAddresses = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->emailAddressesPrimary = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
	 * @param string $accountType
	 */
	public function setAccountType($accountType) {
		$this->accountType = $accountType;
	}

	/**
	 * @return string
	 */
	public function getAccountType() {
		return $this->accountType;
	}

	/**
	 * @param string $annualRevenue
	 */
	public function setAnnualRevenue($annualRevenue) {
		$this->annualRevenue = $annualRevenue;
	}

	/**
	 * @return string
	 */
	public function getAnnualRevenue() {
		return $this->annualRevenue;
	}

	/**
	 * @param string $billingAddressCity
	 */
	public function setBillingAddressCity($billingAddressCity) {
		$this->billingAddressCity = $billingAddressCity;
	}

	/**
	 * @return string
	 */
	public function getBillingAddressCity() {
		return $this->billingAddressCity;
	}

	/**
	 * @param string $billingAddressCountry
	 */
	public function setBillingAddressCountry($billingAddressCountry) {
		$this->billingAddressCountry = $billingAddressCountry;
	}

	/**
	 * @return string
	 */
	public function getBillingAddressCountry() {
		return $this->billingAddressCountry;
	}

	/**
	 * @param string $billingAddressPostalcode
	 */
	public function setBillingAddressPostalcode($billingAddressPostalcode) {
		$this->billingAddressPostalcode = $billingAddressPostalcode;
	}

	/**
	 * @return string
	 */
	public function getBillingAddressPostalcode() {
		return $this->billingAddressPostalcode;
	}

	/**
	 * @param string $billingAddressState
	 */
	public function setBillingAddressState($billingAddressState) {
		$this->billingAddressState = $billingAddressState;
	}

	/**
	 * @return string
	 */
	public function getBillingAddressState() {
		return $this->billingAddressState;
	}

	/**
	 * @param string $billingAddressStreet
	 */
	public function setBillingAddressStreet($billingAddressStreet) {
		$this->billingAddressStreet = $billingAddressStreet;
	}

	/**
	 * @return string
	 */
	public function getBillingAddressStreet() {
		return $this->billingAddressStreet;
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
	 * @param string $employees
	 */
	public function setEmployees($employees) {
		$this->employees = $employees;
	}

	/**
	 * @return string
	 */
	public function getEmployees() {
		return $this->employees;
	}

	/**
	 * @param string $industry
	 */
	public function setIndustry($industry) {
		$this->industry = $industry;
	}

	/**
	 * @return string
	 */
	public function getIndustry() {
		return $this->industry;
	}

	/**
	 * @param string $ownership
	 */
	public function setOwnership($ownership) {
		$this->ownership = $ownership;
	}

	/**
	 * @return string
	 */
	public function getOwnership() {
		return $this->ownership;
	}

	/**
	 * @param string $phoneAlternate
	 */
	public function setPhoneAlternate($phoneAlternate) {
		$this->phoneAlternate = $phoneAlternate;
	}

	/**
	 * @return string
	 */
	public function getPhoneAlternate() {
		return $this->phoneAlternate;
	}

	/**
	 * @param string $phoneFax
	 */
	public function setPhoneFax($phoneFax) {
		$this->phoneFax = $phoneFax;
	}

	/**
	 * @return string
	 */
	public function getPhoneFax() {
		return $this->phoneFax;
	}

	/**
	 * @param string $phoneOffice
	 */
	public function setPhoneOffice($phoneOffice) {
		$this->phoneOffice = $phoneOffice;
	}

	/**
	 * @return string
	 */
	public function getPhoneOffice() {
		return $this->phoneOffice;
	}

	/**
	 * @param string $rating
	 */
	public function setRating($rating) {
		$this->rating = $rating;
	}

	/**
	 * @return string
	 */
	public function getRating() {
		return $this->rating;
	}

	/**
	 * @param string $shippingAddressCity
	 */
	public function setShippingAddressCity($shippingAddressCity) {
		$this->shippingAddressCity = $shippingAddressCity;
	}

	/**
	 * @return string
	 */
	public function getShippingAddressCity() {
		return $this->shippingAddressCity;
	}

	/**
	 * @param string $shippingAddressCountry
	 */
	public function setShippingAddressCountry($shippingAddressCountry) {
		$this->shippingAddressCountry = $shippingAddressCountry;
	}

	/**
	 * @return string
	 */
	public function getShippingAddressCountry() {
		return $this->shippingAddressCountry;
	}

	/**
	 * @param string $shippingAddressPostalcode
	 */
	public function setShippingAddressPostalcode($shippingAddressPostalcode) {
		$this->shippingAddressPostalcode = $shippingAddressPostalcode;
	}

	/**
	 * @return string
	 */
	public function getShippingAddressPostalcode() {
		return $this->shippingAddressPostalcode;
	}

	/**
	 * @param string $shippingAddressState
	 */
	public function setShippingAddressState($shippingAddressState) {
		$this->shippingAddressState = $shippingAddressState;
	}

	/**
	 * @return string
	 */
	public function getShippingAddressState() {
		return $this->shippingAddressState;
	}

	/**
	 * @param string $shippingAddressStreet
	 */
	public function setShippingAddressStreet($shippingAddressStreet) {
		$this->shippingAddressStreet = $shippingAddressStreet;
	}

	/**
	 * @return string
	 */
	public function getShippingAddressStreet() {
		return $this->shippingAddressStreet;
	}

	/**
	 * @param string $sicCode
	 */
	public function setSicCode($sicCode) {
		$this->sicCode = $sicCode;
	}

	/**
	 * @return string
	 */
	public function getSicCode() {
		return $this->sicCode;
	}

	/**
	 * @param string $tickerSymbol
	 */
	public function setTickerSymbol($tickerSymbol) {
		$this->tickerSymbol = $tickerSymbol;
	}

	/**
	 * @return string
	 */
	public function getTickerSymbol() {
		return $this->tickerSymbol;
	}

	/**
	 * @param string $website
	 */
	public function setWebsite($website) {
		$this->website = $website;
	}

	/**
	 * @return string
	 */
	public function getWebsite() {
		return $this->website;
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
					$query->equals('parent_type', 'Accounts')
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
	 * Adds an email address
	 *
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\EmailAddress $emailAddress
	 * @return void
	 */
	public function addEmailAddress(\EssentialDots\EdSugarcrm\Domain\Model\EmailAddress $emailAddress) {
		$this->emailAddresses->attach($emailAddress);
	}

	/**
	 * Removes an email address
	 *
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\EmailAddress $emailAddressToRemove The email to be removed
	 * @return void
	 */
	public function removeEmailAddress(\EssentialDots\EdSugarcrm\Domain\Model\EmailAddress $emailAddressToRemove) {
		$this->emailAddresses->detach($emailAddressToRemove);
	}

	/**
	 * Returns the email addresses
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\EmailAddress> $emailAddresses
	 */
	public function getEmailsAddresses() {
		return $this->emailAddresses;
	}

	/**
	 * Sets the email addresses
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\EmailAddress> $emailAddresses
	 */
	public function setEmailAddresses(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $emailAddresses) {
		$this->emailAddresses = $emailAddresses;
	}

	/**
	 * Returns the primary email address
	 *
	 * @return \EssentialDots\EdSugarcrm\Domain\Model\EmailAddress $emailAddress
	 */
	public function getPrimaryEmailAddress() {
		$tArr = $this->emailAddressesPrimary->toArray();
		return count($tArr) ? $tArr[0] : NULL;
	}

	/**
	 * Sets the email addresses
	 *
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\EmailAddress $emailAddress
	 */
	public function setPrimaryEmailAddress(\EssentialDots\EdSugarcrm\Domain\Model\EmailAddress $emailAddress) {
		foreach ($this->emailAddressesPrimary as $oldPrimaryEmailAddress) {
			if ($emailAddress->getUid() != $oldPrimaryEmailAddress->getUid()) {
				$this->emailAddressesPrimary->detach($oldPrimaryEmailAddress);
			}
		}
		if (!$this->emailAddresses->contains($emailAddress)) {
			$this->emailAddresses->attach($emailAddress);
		}
		if (!$this->emailAddressesPrimary->contains($emailAddress)) {
			$this->emailAddressesPrimary->attach($emailAddress);
		}
	}

	/**
	 * Adds a case
	 *
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $case
	 * @return void
	 */
	public function addCase(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase $case) {
		$this->cases->attach($case);
	}

	/**
	 * Removes a case
	 *
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\SupportCase $caseToRemove The case to be removed
	 * @return void
	 */
	public function removeCase(\EssentialDots\EdSugarcrm\Domain\Model\SupportCase $caseToRemove) {
		$this->cases->detach($caseToRemove);
	}

	/**
	 * Returns the cases
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\SupportCase> $cases
	 */
	public function getCases() {
		return $this->cases;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function getCasesQueryResult() {
		if (!$this->_casesQueryResult) {
			$caseRepository = $this->getObjectManager()->get('EssentialDots\\EdSugarcrm\\Domain\\Repository\\SupportCaseRepository'); /* @var $caseRepository \EssentialDots\EdSugarcrm\Domain\Repository\SupportCaseRepository */
			$query = $caseRepository->createQuery();
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->setOrderings(array(
				'case_number' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
			));
			$this->_casesQueryResult = $query->matching(
				$query->equals('account_id', $this->getUid())
			)->execute();

		}
		return $this->_casesQueryResult;
	}

	/**
	 * Sets the cases
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\EssentialDots\EdSugarcrm\Domain\Model\SupportCase> $cases
	 */
	public function setCases(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $cases) {
		$this->cases = $cases;
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
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		if ($this->_objectManager === NULL) {
			$this->_objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		}
		return $this->_objectManager;
	}
}