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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Email extends \EssentialDots\EdSugarcrm\Domain\Model\AbstractEntity {

	const STATUS_ARCHIVED = 'archived';
	const STATUS_CLOSED = 'closed';
	const STATUS_DRAFT = 'draft';
	const STATUS_READ = 'read';
	const STATUS_REPLIED = 'replied';
	const STATUS_SENT = 'sent';
	const STATUS_SEND_ERROR = 'send_error';
	const STATUS_UNREAD = 'unread';

	const TYPE_SENT = 'out';
	const TYPE_ARCHIVED = 'archived';
	const TYPE_DRAFT = 'draft';
	const TYPE_INBOUND = 'inbound';
	const TYPE_CAMPAIGN = 'campaign';

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var \DateTime
	 */
	protected $dateSent;

	/**
	 * @var string
	 */
	protected $parentType;

	/**
	 * @var string
	 */
	protected $parentId;

	/**
	 * @var string
	 */
	protected $messageId;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $status;

	/**
	 * @var string
	 */
	protected $intent;

	/**
	 * @var int
	 */
	protected $flagged;

	/**
	 * @var int
	 */
	protected $replyToStatus;

	/**
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\AbstractEntity
	 */
	protected $_parentObject = NULL;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $_objectManager = NULL;

	/**
	 * @lazy
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\User
	 */
	protected $assignedUser;

	/**
	 * @var string
	 */
	protected $fromAddr;

	/**
	 * @var string
	 */
	protected $replyToAddr;

	/**
	 * @var string
	 */
	protected $toAddrs;

	/**
	 * @var string
	 */
	protected $ccAddrs;

	/**
	 * @var string
	 */
	protected $bccAddrs;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $descriptionHtml;

	/**
	 * @var string
	 */
	protected $rawSource;

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
	 * @param string $parentType
	 */
	public function setParentType($parentType) {
		$this->parentType = $parentType;
		$this->_parentObject = NULL;
	}

	/**
	 * @return string
	 */
	public function getParentType() {
		return $this->parentType;
	}

	/**
	 * @param string $parentId
	 */
	public function setParentId($parentId) {
		$this->parentId = $parentId;
		$this->_parentObject = NULL;
	}

	/**
	 * @return string
	 */
	public function getParentId() {
		return $this->parentId;
	}

	/**
	 * @param \DateTime $dateSent
	 */
	public function setDateSent($dateSent) {
		$this->dateSent = $dateSent;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateSent() {
		return $this->dateSent;
	}

	/**
	 * @param string $intent
	 */
	public function setIntent($intent) {
		$this->intent = $intent;
	}

	/**
	 * @return string
	 */
	public function getIntent() {
		return $this->intent;
	}

	/**
	 * @param string $messageId
	 */
	public function setMessageId($messageId) {
		$this->messageId = $messageId;
	}

	/**
	 * @return string
	 */
	public function getMessageId() {
		return $this->messageId;
	}

	/**
	 * @param int $flagged
	 */
	public function setFlagged($flagged) {
		$this->flagged = $flagged;
	}

	/**
	 * @return int
	 */
	public function getFlagged() {
		return $this->flagged;
	}

	/**
	 * @param int $replyToStatus
	 */
	public function setReplyToStatus($replyToStatus) {
		$this->replyToStatus = $replyToStatus;
	}

	/**
	 * @return int
	 */
	public function getReplyToStatus() {
		return $this->replyToStatus;
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
	 * @return \EssentialDots\EdSugarcrm\Domain\Model\AbstractEntity
	 */
	public function getParentObject() {

		if ($this->getParentId() && $this->_parentObject === NULL) {
			$parentObjectRepository = NULL;

			switch ($this->getParentType()) {
				case 'Accounts':
					$parentObjectRepository = $this->getObjectManager()->get('EssentialDots\\EdSugarcrm\\Domain\\Repository\\AccountRepository'); /* @var $parentObjectRepository \EssentialDots\EdSugarcrm\Domain\Repository\AbstractRepository */
					break;
				case 'Cases':
					$parentObjectRepository = $this->getObjectManager()->get('EssentialDots\\EdSugarcrm\\Domain\\Repository\\SupportCaseRepository'); /* @var $parentObjectRepository \EssentialDots\EdSugarcrm\Domain\Repository\AbstractRepository */
					break;
				default:
					// other types unsupported atm
					break;
			}

			if ($parentObjectRepository) {
				$this->_parentObject = $parentObjectRepository->findByUid($this->getParentId());
			}
		}

		return $this->_parentObject;
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
	 * @param string $bccAddrs
	 */
	public function setBccAddrs($bccAddrs) {
		$this->bccAddrs = $bccAddrs;
	}

	/**
	 * @return string
	 */
	public function getBccAddrs() {
		return $this->bccAddrs;
	}

	/**
	 * @param string $ccAddrs
	 */
	public function setCcAddrs($ccAddrs) {
		$this->ccAddrs = $ccAddrs;
	}

	/**
	 * @return string
	 */
	public function getCcAddrs() {
		return $this->ccAddrs;
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
	 * @param string $descriptionHtml
	 */
	public function setDescriptionHtml($descriptionHtml) {
		$this->descriptionHtml = $descriptionHtml;
	}

	/**
	 * @return string
	 */
	public function getDescriptionHtml() {
		return $this->descriptionHtml;
	}

	/**
	 * @param string $fromAddr
	 */
	public function setFromAddr($fromAddr) {
		$this->fromAddr = $fromAddr;
	}

	/**
	 * @return string
	 */
	public function getFromAddr() {
		return $this->fromAddr;
	}

	/**
	 * @param string $rawSource
	 */
	public function setRawSource($rawSource) {
		$this->rawSource = $rawSource;
	}

	/**
	 * @return string
	 */
	public function getRawSource() {
		return $this->rawSource;
	}

	/**
	 * @param string $replyToAddr
	 */
	public function setReplyToAddr($replyToAddr) {
		$this->replyToAddr = $replyToAddr;
	}

	/**
	 * @return string
	 */
	public function getReplyToAddr() {
		return $this->replyToAddr;
	}

	/**
	 * @param string $toAddrs
	 */
	public function setToAddrs($toAddrs) {
		$this->toAddrs = $toAddrs;
	}

	/**
	 * @return string
	 */
	public function getToAddrs() {
		return $this->toAddrs;
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