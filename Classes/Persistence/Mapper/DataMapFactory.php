<?php
namespace EssentialDots\EdSugarcrm\Persistence\Mapper;

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

class DataMapFactory extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory implements \Tx_ExtbaseDomainDecorator_Persistence_Mapper_DataMapFactoryInterface {

	/**
	 * @var array<\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap>
	 */
	protected $_dataMapsPerTable = array();

	/**
	 * @param $tableName
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap
	 */
	public function getDataMapForTable($tableName) {
		return $this->_dataMapsPerTable[$tableName];
	}

	/**
	 * Builds a data map by adding column maps for all the configured columns in the $TCA.
	 * It also resolves the type of values the column is holding and the typo of relation the column
	 * represents.
	 *
	 * @param string $className The class name you want to fetch the Data Map for
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidClassException
	 */
	public function buildDataMap($className) {
		if (!class_exists($className)) {
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidClassException('Could not find class definition for name "' . $className . '". This could be caused by a mis-spelling of the class name in the class definition.');
		}
		$recordType = NULL;
		$subclasses = array();
		$tableName = '';
		$columnMapping = array();
		$frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$classSettings = $frameworkConfiguration['persistence']['classes'][$className];
		if ($classSettings !== NULL) {
			if (isset($classSettings['subclasses']) && is_array($classSettings['subclasses'])) {
				$subclasses = $this->resolveSubclassesRecursive($frameworkConfiguration['persistence']['classes'], $classSettings['subclasses']);
			}
			if (isset($classSettings['mapping']['recordType']) && strlen($classSettings['mapping']['recordType']) > 0) {
				$recordType = $classSettings['mapping']['recordType'];
			}
			if (isset($classSettings['mapping']['tableName']) && strlen($classSettings['mapping']['tableName']) > 0) {
				$tableName = $classSettings['mapping']['tableName'];
			}
			$classHierarchy = array_merge(array($className), class_parents($className));
			foreach ($classHierarchy as $currentClassName) {
				if (in_array($currentClassName, array('TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity', 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject'))) {
					break;
				}
				$currentClassSettings = $frameworkConfiguration['persistence']['classes'][$currentClassName];
				if ($currentClassSettings !== NULL) {
					if (isset($currentClassSettings['mapping']['columns']) && is_array($currentClassSettings['mapping']['columns'])) {
						$columnMapping = \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule($columnMapping, $currentClassSettings['mapping']['columns'], 0, FALSE);
					}
				}
			}
		}
		if (!$tableName) {
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidClassException('Could not find table definition for class "' . $className . '".');
		}
		/** @var $dataMap \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap */
		$dataMap = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMap', $className, $tableName, $recordType, $subclasses);

		foreach ($columnMapping as $columnName => $columnDefinition) {
			if (isset($columnDefinition['mapOnProperty'])) {
				$propertyName = $columnDefinition['mapOnProperty'];
			} else {
				$propertyName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($columnName);
			}
			$columnMap = $this->createColumnMap($columnName, $propertyName);
			$propertyMetaData = $this->reflectionService->getClassSchema($className)->getProperty($propertyName);
			$columnMap = $this->setType($columnMap, $columnDefinition['config']);
			$columnMap = $this->setRelations($columnMap, $columnDefinition['config'], $propertyMetaData);
			$columnMap = $this->setFieldEvaluations($columnMap, $columnDefinition['config']);
			$dataMap->addColumnMap($columnMap);
		}

		$this->_dataMapsPerTable[$tableName] = $dataMap;

		return $dataMap;
	}
}