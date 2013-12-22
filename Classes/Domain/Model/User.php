<?php
namespace EssentialDots\EdSugarcrm\Domain\Model;

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

class User extends \EssentialDots\EdSugarcrm\Domain\Model\AbstractEntity {

	/**
	 * @var string
	 */
	protected $userName;

	/**
	 * @var bool
	 */
	protected $isGroup;

	/**
	 * @var bool
	 */
	protected $portalOnly;

	/**
	 * @var bool
	 */
	protected $showOnEmployees;

	/**
	 * @lazy
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	protected $reportsTo;

	/**
	 * @var string
	 */
	protected $firstName;

	/**
	 * @var string
	 */
	protected $lastName;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $department;

	/**
	 * @var string
	 */
	protected $status;

	/**
	 * @var string
	 */
	protected $addressStreet;

	/**
	 * @var string
	 */
	protected $addressCity;

	/**
	 * @var string
	 */
	protected $addressState;

	/**
	 * @var string
	 */
	protected $addressCountry;

	/**
	 * @var string
	 */
	protected $addressPostalcode;

	/**
	 * @var string
	 */
	protected $employeeStatus;

	/**
	 * @var string
	 */
	protected $messengerId;

	/**
	 * @var string
	 */
	protected $messengerType;

	/**
	 * @param string $userName
	 */
	public function setUserName($userName) {
		$this->userName = $userName;
	}

	/**
	 * @return string
	 */
	public function getUserName() {
		return $this->userName;
	}

	/**
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\User $reportsTo
	 */
	public function setReportsTo($reportsTo) {
		$this->reportsTo = $reportsTo;
	}

	/**
	 * @return \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	public function getReportsTo() {
		return $this->reportsTo;
	}

	/**
	 * @param string $addressCity
	 */
	public function setAddressCity($addressCity) {
		$this->addressCity = $addressCity;
	}

	/**
	 * @return string
	 */
	public function getAddressCity() {
		return $this->addressCity;
	}

	/**
	 * @param string $addressCountry
	 */
	public function setAddressCountry($addressCountry) {
		$this->addressCountry = $addressCountry;
	}

	/**
	 * @return string
	 */
	public function getAddressCountry() {
		return $this->addressCountry;
	}

	/**
	 * @param string $addressPostalcode
	 */
	public function setAddressPostalcode($addressPostalcode) {
		$this->addressPostalcode = $addressPostalcode;
	}

	/**
	 * @return string
	 */
	public function getAddressPostalcode() {
		return $this->addressPostalcode;
	}

	/**
	 * @param string $addressState
	 */
	public function setAddressState($addressState) {
		$this->addressState = $addressState;
	}

	/**
	 * @return string
	 */
	public function getAddressState() {
		return $this->addressState;
	}

	/**
	 * @param string $addressStreet
	 */
	public function setAddressStreet($addressStreet) {
		$this->addressStreet = $addressStreet;
	}

	/**
	 * @return string
	 */
	public function getAddressStreet() {
		return $this->addressStreet;
	}

	/**
	 * @param string $department
	 */
	public function setDepartment($department) {
		$this->department = $department;
	}

	/**
	 * @return string
	 */
	public function getDepartment() {
		return $this->department;
	}

	/**
	 * @param string $employeeStatus
	 */
	public function setEmployeeStatus($employeeStatus) {
		$this->employeeStatus = $employeeStatus;
	}

	/**
	 * @return string
	 */
	public function getEmployeeStatus() {
		return $this->employeeStatus;
	}

	/**
	 * @param string $firstName
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @param boolean $isGroup
	 */
	public function setIsGroup($isGroup) {
		$this->isGroup = $isGroup;
	}

	/**
	 * @return boolean
	 */
	public function getIsGroup() {
		return $this->isGroup;
	}

	/**
	 * @param string $lastName
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * @param string $messengerId
	 */
	public function setMessengerId($messengerId) {
		$this->messengerId = $messengerId;
	}

	/**
	 * @return string
	 */
	public function getMessengerId() {
		return $this->messengerId;
	}

	/**
	 * @param string $messengerType
	 */
	public function setMessengerType($messengerType) {
		$this->messengerType = $messengerType;
	}

	/**
	 * @return string
	 */
	public function getMessengerType() {
		return $this->messengerType;
	}

	/**
	 * @param boolean $portalOnly
	 */
	public function setPortalOnly($portalOnly) {
		$this->portalOnly = $portalOnly;
	}

	/**
	 * @return boolean
	 */
	public function getPortalOnly() {
		return $this->portalOnly;
	}

	/**
	 * @param boolean $showOnEmployees
	 */
	public function setShowOnEmployees($showOnEmployees) {
		$this->showOnEmployees = $showOnEmployees;
	}

	/**
	 * @return boolean
	 */
	public function getShowOnEmployees() {
		return $this->showOnEmployees;
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
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
}