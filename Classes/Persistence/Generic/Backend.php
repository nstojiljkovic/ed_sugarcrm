<?php
namespace EssentialDots\EdSugarcrm\Persistence\Generic;

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

class Backend extends \TYPO3\CMS\Extbase\Persistence\Generic\Backend {

	/**
	 * @var \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\SugarCRMBackend
	 * @inject
	 */
	protected $storageBackend;

	/**
	 * Inserts an object in the storage backend
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object The object to be insterted in the storage
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject The parentobject.
	 * @param string $parentPropertyName
	 * @return void
	 */
	protected function insertObject(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object, \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject = NULL, $parentPropertyName = '') {
		if ($object instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractValueObject) {
			$result = $this->getUidOfAlreadyPersistedValueObject($object);
			if ($result !== FALSE) {
				// old: $object->_setProperty('uid', (integer) $result);
				$object->_setProperty('uid', $result);
				return;
			}
		}
		$dataMap = $this->dataMapper->getDataMap(get_class($object));
		$row = array();
		$this->addCommonFieldsToRow($object, $row);
		if ($dataMap->getLanguageIdColumnName() !== NULL) {
			$row[$dataMap->getLanguageIdColumnName()] = -1;
		}
		if ($parentObject !== NULL && $parentPropertyName) {
			$parentColumnDataMap = $this->dataMapper->getDataMap(get_class($parentObject))->getColumnMap($parentPropertyName);
			$relationTableMatchFields = $parentColumnDataMap->getRelationTableMatchFields();
			if (is_array($relationTableMatchFields) && count($relationTableMatchFields) > 0) {
				$row = array_merge($relationTableMatchFields, $row);
			}
			if ($parentColumnDataMap->getParentKeyFieldName() !== NULL) {
				$row[$parentColumnDataMap->getParentKeyFieldName()] = (int)$parentObject->getUid();
			}
		}
		$uid = $this->storageBackend->addRow($dataMap->getTableName(), $row);
		// old: $object->_setProperty('uid', (integer) $uid);
		$object->_setProperty('uid', $uid);
		// old: if ((integer) $uid >= 1) {
		if ($uid) {
			$this->signalSlotDispatcher->dispatch(__CLASS__, 'afterInsertObject', array('object' => $object));
		}
		$frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		if ($frameworkConfiguration['persistence']['updateReferenceIndex'] === '1') {
			$this->referenceIndex->updateRefIndexTable($dataMap->getTableName(), $uid);
		}
		$this->session->registerObject($object, $uid);
	}

}
