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

class AbstractEntity extends \EssentialDots\ExtbaseDomainDecorator\DomainObject\AbstractEntity {

	/**
	 * @var string The uid of the record. The uid is only unique in the context of the database table.
	 */
	protected $uid;

	/**
	 * @var \DateTime
	 */
	protected $dateEntered;

	/**
	 * @var \DateTime
	 */
	protected $dateModified;

	/**
	 * @lazy
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	protected $modifiedByUser;

	/**
	 * @lazy
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	protected $createdByUser;

	/**
	 * Getter for uid.
	 *
	 * @return integer the uid or NULL if none set yet.
	 */
	public function getUid() {
		if ($this->uid !== NULL) {
			return $this->uid;
		} else {
			return NULL;
		}
	}

	/**
	 * @param \DateTime $dateEntered
	 */
	public function setDateEntered($dateEntered) {
		$this->dateEntered = $dateEntered;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateEntered() {
		return $this->dateEntered;
	}

	/**
	 * @param \DateTime $dateModified
	 */
	public function setDateModified($dateModified) {
		$this->dateModified = $dateModified;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateModified() {
		return $this->dateModified;
	}

	/**
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\User $createdByUser
	 */
	public function setCreatedByUser($createdByUser) {
		$this->createdByUser = $createdByUser;
	}

	/**
	 * @return \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	public function getCreatedByUser() {
		return $this->createdByUser;
	}

	/**
	 * @param \EssentialDots\EdSugarcrm\Domain\Model\User $modifiedByUser
	 */
	public function setModifiedByUser($modifiedByUser) {
		$this->modifiedByUser = $modifiedByUser;
	}

	/**
	 * @return \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	public function getModifiedByUser() {
		return $this->modifiedByUser;
	}

	/**
	 * @return array
	 */
	public function _getProperties() {
		$properties = parent::_getProperties();
		unset($properties['uid']);
		$properties = array_merge(array('uid' => $this->uid), $properties);
		return $properties;
	}
}