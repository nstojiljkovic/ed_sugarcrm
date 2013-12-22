<?php
namespace EssentialDots\EdSugarcrm\Persistence\Generic\Storage;

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
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;

/**
 * A persistence backend. This backend maps objects to the relational model of the storage backend.
 * It persists all added, removed and changed objects.
 */
class SugarCRMBackend implements \TYPO3\CMS\Extbase\Persistence\Generic\Storage\BackendInterface {

	const OPERATOR_EQUAL_TO_NULL = 'operatorEqualToNull';
	const OPERATOR_NOT_EQUAL_TO_NULL = 'operatorNotEqualToNull';

	/**
	 * The TYPO3 database object
	 *
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseHandle;

	/**
	 * The TYPO3 database object
	 *
	 * @var \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\SugarCRMRESTHandle
	 */
	protected $restAPIHandle;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
	 * @inject
	 */
	protected $dataMapper;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * Constructor. takes the database handle from $GLOBALS['TYPO3_DB']
	 *
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 */
	public function __construct(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->databaseHandle = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return SugarCRMRESTHandle
	 */
	protected function getRestAPIHandle() {
		if (!$this->restAPIHandle) {
			$this->restAPIHandle = GeneralUtility::makeInstance('EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Storage\\SugarCRMRESTHandle');
			$frameworkSettings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
			$this->restAPIHandle->setRESTUrl($frameworkSettings['SugarCRMBackend']['url']);
			$this->restAPIHandle->setUsername($frameworkSettings['SugarCRMBackend']['username']);
			$this->restAPIHandle->setPassword($frameworkSettings['SugarCRMBackend']['password']);

			$this->restAPIHandle->connect();
		}
		return $this->restAPIHandle;
	}

	/**
	 * Adds a row to the storage
	 *
	 * @param string $tableName The database table name
	 * @param array $row The row to insert
	 * @param boolean $isRelation TRUE if we are currently inserting into a relation table, FALSE by default
	 * @return integer the UID of the inserted row
	 * @throws Exception\UnsupportedQueryException
	 */
	public function addRow($tableName, array $row, $isRelation = FALSE) {
		if ($isRelation) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRMBackend currently does not support relation rows.', 1242814327);
		} else {
			$result = $this->getRestAPIHandle()->execQuery($this->buildSetEntryParameters($tableName, $row), 'set_entry');
			return $result['id'];
		}
	}

	/**
	 * Updates a row in the storage
	 *
	 * @param string $tableName The database table name
	 * @param array $row The row to update
	 * @param boolean $isRelation TRUE if we are currently inserting into a relation table, FALSE by default
	 * @return mixed|void
	 * @throws Exception\UnsupportedQueryException
	 */
	public function updateRow($tableName, array $row, $isRelation = FALSE) {
		if ($isRelation) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRMBackend currently does not support relation rows.', 1242814327);
		} else {
			$this->getRestAPIHandle()->execQuery($this->buildSetEntryParameters($tableName, $row), 'set_entry');
		}
	}

	/**
	 * Updates a relation row in the storage
	 *
	 * @param string $tableName The database relation table name
	 * @param array $row The row to be updated
	 * @return boolean
	 * @throws Exception\UnsupportedQueryException
	 */
	public function updateRelationTableRow($tableName, array $row) {
		throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRMBackend currently does not support relation rows.', 1242814327);
	}

	/**
	 * Deletes a row in the storage
	 *
	 * @param string $tableName The database table name
	 * @param array $identifier An array of identifier array('fieldname' => value). This array will be transformed to a WHERE clause
	 * @param boolean $isRelation TRUE if we are currently inserting into a relation table, FALSE by default
	 * @return mixed|void
	 * @throws Exception\UnsupportedQueryException
	 */
	public function removeRow($tableName, array $identifier, $isRelation = FALSE) {
		if ($isRelation) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRMBackend currently does not support relation rows.', 1242814327);
		} else {
			if (count($identifier) != 1 || !$identifier['uid']) {
				throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRM API supports deleting rows by identifiers only. Complex queries are not supported.', 1242814332);
			}
			$row = $identifier;
			$row['deleted'] = 1;
			$this->getRestAPIHandle()->execQuery($this->buildSetEntryParameters($tableName, $row), 'set_entry');
		}
	}

	/**
	 * Fetches maximal value for given table column
	 *
	 * @param string $tableName The database table name
	 * @param array $identifier An array of identifier array('fieldname' => value). This array will be transformed to a WHERE clause
	 * @param string $columnName column name to get the max value from
	 * @return mixed the max value
	 * @throws Exception\UnsupportedQueryException
	 */
	public function getMaxValueFromTable($tableName, $identifier, $columnName) {
		throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRMBackend does not support getMaxValueFromTable method.', 1242814374);
	}

	/**
	 * Returns the number of items matching the query.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @return integer
	 * @api
	 */
	public function getObjectCountByQuery(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query) {
		$apiParameters = $this->getAPIParametersForQuery($query);
		$result = $this->getRestAPIHandle()->execQuery($apiParameters, 'get_entries_count');
		return intval($result['result_count']);
	}

	/**
	 * Returns the object data matching the $query.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @return array
	 * @api
	 */
	public function getObjectDataByQuery(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query) {
		$apiParameters = $this->getAPIParametersForQuery($query);

		if ($apiParameters['module_name'] === 'Emails') {
			// fix for the SugarCRM API bug:
			// EmailText module is available only through get_entries and get_entry methods
			$selectFieldsOriginal = $apiParameters['select_fields'];
			$apiParameters['select_fields'] = array('id');
			$result = $this->getRestAPIHandle()->execQuery($apiParameters, 'get_entry_list');
			$rows = $this->getRowsFromResult($result, $query);

			$apiParameters['ids'] = array();
			foreach ($rows as $row) {
				$apiParameters['ids'][] = $row['uid'];
			}
			$apiParameters['track_view'] = true;
			$apiParameters['select_fields'] = $selectFieldsOriginal;
			$result = $this->getRestAPIHandle()->execQuery($apiParameters, 'get_entries');
		} else {
			$result = $this->getRestAPIHandle()->execQuery($apiParameters, 'get_entry_list');
		}

		$rows = $this->getRowsFromResult($result, $query);
		return $rows;
	}

	/**
	 * @param string $tableName
	 * @param array $row
	 * @return array
	 */
	protected function buildSetEntryParameters($tableName, $row) {
		$nameValueList = array();
		unset($row['pid']);
		if ($row['uid']) {
			$nameValueList[] = array(
				'name' => 'id',
				'value' => $row['uid']
			);
			unset($row['uid']);
			unset($row['id']);
		}
		foreach ($row as $k => $v) {
			$nameValueList[] = array(
				'name' => $k,
				'value' => $v
			);
		}

		return array(
			'module_name' => GeneralUtility::underscoredToUpperCamelCase($tableName),
			'name_value_list' => $nameValueList
		);
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @return string
	 * @throws Exception\UnsupportedQueryException
	 */
	protected function getAPIParametersForQuery(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query) {
		$statement = $query->getStatement();
		if ($statement instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('Unsupported statement type encountered.', 1242814374);
		} else {
			$parameters = array();
			$statementParts = $this->parseQuery($query, $parameters);
			$apiParameters = $this->buildEntryListParameters($statementParts, $parameters);
		}
		$tableName = 'foo';
		if (is_array($statementParts) && !empty($statementParts['tables'])) {
			$tableName = implode('', $statementParts['tables']);
		}
		$this->replacePlaceholders($apiParameters['query'], $parameters, $tableName);

		return $apiParameters;
	}

	/**
	 * Parses the query and returns the SQL statement parts.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query The query
	 * @param array &$parameters
	 * @return array The SQL statement parts
	 */
	protected function parseQuery(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query, array &$parameters) {
		$sql = array();
		$sql['keywords'] = array();
		$sql['tables'] = array();
		$sql['unions'] = array();
		$sql['fields'] = array();
		$sql['where'] = array();
		$sql['additionalWhereClause'] = array();
		$sql['orderings'] = array();
		$sql['limit'] = array();
		$sql['offset'] = array();
		$source = $query->getSource();
		$this->parseSource($source, $sql);
		$this->parseConstraint($query->getConstraint(), $source, $sql, $parameters);
		$this->parseOrderings($query->getOrderings(), $source, $sql);
		$this->parseLimitAndOffset($query->getLimit(), $query->getOffset(), $sql);
		$tableNames = array_unique(array_keys($sql['tables'] + $sql['unions']));
		foreach ($tableNames as $tableName) {
			if (is_string($tableName) && strlen($tableName) > 0) {
				$this->addAdditionalWhereClause($query->getQuerySettings(), $tableName, $sql);
			}
		}
		return $sql;
	}

	/**
	 * Returns the statement, ready to be executed.
	 *
	 * @param array $sqlStatementParts The SQL statement parts
	 * @return string The SQL statement
	 * @throws Exception\UnsupportedQueryException
	 */
	protected function buildEntryListParameters(array $sqlStatementParts) {
		$get_entry_list_params = array(
			'module_name' => '',
			'query' => null,
			'order_by' => null,
			'offset' => 0,
			'select_fields' => array(),
			'link_name_to_fields_array' => array(),
			'max_results' => 0,
			'deleted' => 0,
			'favorites' => false
		);

		if (count($sqlStatementParts['tables'])!=1) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRMBackend does not support joins.', 1242814326);
		}

		if (count($sqlStatementParts['fields'])!=1) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException("No selectable properties found.", 1242814325);
		}

		if (count($sqlStatementParts['unions'])>0) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('Unsupported UNION statement encountered.', 1242814373);
		}

		$get_entry_list_params['module_name'] = GeneralUtility::underscoredToUpperCamelCase(implode('', $sqlStatementParts['tables']));
		$get_entry_list_params['select_fields'] = GeneralUtility::trimExplode(',', implode('', $sqlStatementParts['fields']));

		if (!empty($sqlStatementParts['where'])) {
			$get_entry_list_params['query'] .= implode('', $sqlStatementParts['where']);
			if (!empty($sqlStatementParts['additionalWhereClause'])) {
				$get_entry_list_params['query'] .= ' AND ' . implode(' AND ', $sqlStatementParts['additionalWhereClause']);
			}
		} elseif (!empty($sqlStatementParts['additionalWhereClause'])) {
			$get_entry_list_params['query'] .= implode(' AND ', $sqlStatementParts['additionalWhereClause']);
		}
		if (!empty($sqlStatementParts['orderings'])) {
			$get_entry_list_params['order_by'] = implode(', ', $sqlStatementParts['orderings']);
		}
		if (!empty($sqlStatementParts['limit'])) {
			$get_entry_list_params['max_results'] = $sqlStatementParts['limit'];
		}
		if (!empty($sqlStatementParts['offset'])) {
			$get_entry_list_params['offset'] = $sqlStatementParts['offset'];
		}
		return $get_entry_list_params;
	}

	/**
	 * Transforms a Query Source into SQL and parameter arrays
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source
	 * @param array $sql
	 * @throws Exception\UnsupportedQueryException
	 */
	protected function parseSource(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source, array &$sql) {
		if ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface) {
			$className = $source->getNodeTypeName();
			$dataMap = $this->dataMapper->getDataMap($className);
			$tableName = $dataMap->getTableName();
			$selectableProperties = array();
			foreach ($dataMap->getPropertyNames() as $propertyName) {
				$columnMap = $dataMap->getColumnMap($propertyName);
				if ($columnMap->getTypeOfRelation() == \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_NONE || $columnMap->getTypeOfRelation() == \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_HAS_ONE) {
					$selectableProperties[] = $columnMap->getColumnName();
				}
			}
			//$this->addRecordTypeConstraint($className, $sql);
			if (count($selectableProperties) == 0) {
				throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException("No selectable properties found for module $tableName", 1242814325);
			}
			$sql['fields'][$tableName] = implode(',', $selectableProperties); //$tableName . '.*';
			$sql['tables'][$tableName] = $tableName;
		} elseif ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\JoinInterface) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('SugarCRMBackend does not support joins.', 1242814326);
		}
	}


	/**
	 * Transforms a constraint into SQL and parameter arrays
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint The constraint
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source The source
	 * @param array &$sql The query parts
	 * @param array &$parameters The parameters that will replace the markers
	 * @return void
	 */
	protected function parseConstraint(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint = NULL, \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source, array &$sql, array &$parameters) {
		if ($constraint instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\AndInterface) {
			$sql['where'][] = '(';
			$this->parseConstraint($constraint->getConstraint1(), $source, $sql, $parameters);
			$sql['where'][] = ' AND ';
			$this->parseConstraint($constraint->getConstraint2(), $source, $sql, $parameters);
			$sql['where'][] = ')';
		} elseif ($constraint instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface) {
			$sql['where'][] = '(';
			$this->parseConstraint($constraint->getConstraint1(), $source, $sql, $parameters);
			$sql['where'][] = ' OR ';
			$this->parseConstraint($constraint->getConstraint2(), $source, $sql, $parameters);
			$sql['where'][] = ')';
		} elseif ($constraint instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\NotInterface) {
			$sql['where'][] = 'NOT (';
			$this->parseConstraint($constraint->getConstraint(), $source, $sql, $parameters);
			$sql['where'][] = ')';
		} elseif ($constraint instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface) {
			$this->parseComparison($constraint, $source, $sql, $parameters);
		}
	}

	/**
	 * Parse a Comparison into SQL and parameter arrays.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface $comparison The comparison to parse
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source The source
	 * @param array &$sql SQL query parts to add to
	 * @param array &$parameters Parameters to bind to the SQL
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\RepositoryException
	 * @return void
	 */
	protected function parseComparison(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface $comparison, \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source, array &$sql, array &$parameters) {
		$operand1 = $comparison->getOperand1();
		$operator = $comparison->getOperator();
		$operand2 = $comparison->getOperand2();
		if ($operator === \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_IN) {
			$items = array();
			$hasValue = FALSE;
			foreach ($operand2 as $value) {
				$value = $this->getPlainValue($value);
				if ($value !== NULL) {
					$items[] = $value;
					$hasValue = TRUE;
				}
			}
			if ($hasValue === FALSE) {
				$sql['where'][] = '1<>1';
			} else {
				$this->parseDynamicOperand($operand1, $operator, $source, $sql, $parameters, NULL, $operand2);
				$parameters[] = $items;
			}
		} elseif ($operator === \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_CONTAINS) {
			if ($operand2 === NULL) {
				$sql['where'][] = '1<>1';
			} else {
				$className = $source->getNodeTypeName();
				$tableName = $this->dataMapper->convertClassNameToTableName($className);
				$propertyName = $operand1->getPropertyName();
				while (strpos($propertyName, '.') !== FALSE) {
					$this->addUnionStatement($className, $tableName, $propertyName, $sql);
				}
				$columnName = $this->dataMapper->convertPropertyNameToColumnName($propertyName, $className);
				$dataMap = $this->dataMapper->getDataMap($className);
				$columnMap = $dataMap->getColumnMap($propertyName);
				$typeOfRelation = $columnMap instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap ? $columnMap->getTypeOfRelation() : NULL;
				if ($typeOfRelation === \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_HAS_AND_BELONGS_TO_MANY) {
					$relationTableName = $columnMap->getRelationTableName();
					$sql['where'][] = $tableName . '.uid IN (SELECT ' . $columnMap->getParentKeyFieldName() . ' FROM ' . $relationTableName . ' WHERE ' . $columnMap->getChildKeyFieldName() . '=?)';
					$parameters[] = intval($this->getPlainValue($operand2));
				} elseif ($typeOfRelation === \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_HAS_MANY) {
					$parentKeyFieldName = $columnMap->getParentKeyFieldName();
					if (isset($parentKeyFieldName)) {
						$childTableName = $columnMap->getChildTableName();
						$sql['where'][] = $tableName . '.uid=(SELECT ' . $childTableName . '.' . $parentKeyFieldName . ' FROM ' . $childTableName . ' WHERE ' . $childTableName . '.uid=?)';
						$parameters[] = intval($this->getPlainValue($operand2));
					} else {
						$sql['where'][] = 'FIND_IN_SET(?,' . $tableName . '.' . $columnName . ')';
						$parameters[] = intval($this->getPlainValue($operand2));
					}
				} else {
					throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\RepositoryException('Unsupported or non-existing property name "' . $propertyName . '" used in relation matching.', 1327065745);
				}
			}
		} else {
			if ($operand2 === NULL) {
				if ($operator === \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_EQUAL_TO) {
					$operator = self::OPERATOR_EQUAL_TO_NULL;
				} elseif ($operator === \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_NOT_EQUAL_TO) {
					$operator = self::OPERATOR_NOT_EQUAL_TO_NULL;
				}
			}
			$this->parseDynamicOperand($operand1, $operator, $source, $sql, $parameters);
			$parameters[] = $this->getPlainValue($operand2);
		}
	}

	/**
	 * Returns a plain value, i.e. objects are flattened out if possible.
	 *
	 * @param mixed $input
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException
	 * @return mixed
	 */
	protected function getPlainValue($input) {
		if (is_array($input)) {
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException('An array could not be converted to a plain value.', 1274799932);
		}
		if ($input instanceof \DateTime) {
			return $input->format('U');
		} elseif (TypeHandlingUtility::isCoreType($input)) {
			return (string) $input;
		} elseif (is_object($input)) {
			if ($input instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy) {
				$realInput = $input->_loadRealInstance();
			} else {
				$realInput = $input;
			}
			if ($realInput instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
				return $realInput->getUid();
			} else {
				throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException('An object of class "' . get_class($realInput) . '" could not be converted to a plain value.', 1274799934);
			}
		} elseif (is_bool($input)) {
			return $input === TRUE ? 1 : 0;
		} else {
			return $input;
		}
	}

	/**
	 * Parse a DynamicOperand into SQL and parameter arrays.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\DynamicOperandInterface $operand
	 * @param string $operator One of the JCR_OPERATOR_* constants
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source The source
	 * @param array &$sql The query parts
	 * @param array &$parameters The parameters that will replace the markers
	 * @param string $valueFunction an optional SQL function to apply to the operand value
	 * @param null $operand2
	 * @return void
	 */
	protected function parseDynamicOperand(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\DynamicOperandInterface $operand, $operator, \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source, array &$sql, array &$parameters, $valueFunction = NULL, $operand2 = NULL) {
		if ($operand instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\LowerCaseInterface) {
			$this->parseDynamicOperand($operand->getOperand(), $operator, $source, $sql, $parameters, 'LOWER');
		} elseif ($operand instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\UpperCaseInterface) {
			$this->parseDynamicOperand($operand->getOperand(), $operator, $source, $sql, $parameters, 'UPPER');
		} elseif ($operand instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValueInterface) {
			$propertyName = $operand->getPropertyName();
			if ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface) {
				// FIXME Only necessary to differ from  Join
				$className = $source->getNodeTypeName();
				$tableName = $this->dataMapper->convertClassNameToTableName($className);
				while (strpos($propertyName, '.') !== FALSE) {
					$this->addUnionStatement($className, $tableName, $propertyName, $sql);
				}
			} elseif ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\JoinInterface) {
				$tableName = $source->getJoinCondition()->getSelector1Name();
			}
			$columnName = $this->dataMapper->convertPropertyNameToColumnName($propertyName, $className);
			$operator = $this->resolveOperator($operator);
			$constraintSQL = '';
			if ($valueFunction === NULL) {
				$constraintSQL .= (!empty($tableName) ? $tableName . '.' : '') . $columnName . ' ' . $operator . ' ?';
			} else {
				$constraintSQL .= $valueFunction . '(' . (!empty($tableName) ? $tableName . '.' : '') . $columnName . ') ' . $operator . ' ?';
			}
			$sql['where'][] = $constraintSQL;
		}
	}

	/**
	 * @param string &$className
	 * @param string &$tableName
	 * @param array &$propertyPath
	 * @param array &$sql
	 * @throws \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException
	 */
	protected function addUnionStatement(&$className, &$tableName, &$propertyPath, array &$sql) {
		throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\UnsupportedQueryException('Unsupported UNION statement encountered.', 1242814373);
	}

	/**
	 * Returns the SQL operator for the given JCR operator type.
	 *
	 * @param string $operator One of the JCR_OPERATOR_* constants
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
	 * @return string an SQL operator
	 */
	protected function resolveOperator($operator) {
		switch ($operator) {
			case self::OPERATOR_EQUAL_TO_NULL:
				$operator = 'IS';
				break;
			case self::OPERATOR_NOT_EQUAL_TO_NULL:
				$operator = 'IS NOT';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_IN:
				$operator = 'IN';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_EQUAL_TO:
				$operator = '=';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_NOT_EQUAL_TO:
				$operator = '!=';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_LESS_THAN:
				$operator = '<';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO:
				$operator = '<=';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_GREATER_THAN:
				$operator = '>';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
				$operator = '>=';
				break;
			case \TYPO3\CMS\Extbase\Persistence\QueryInterface::OPERATOR_LIKE:
				$operator = 'LIKE';
				break;
			default:
				throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception('Unsupported operator encountered.', 1242816073);
		}
		return $operator;
	}

	/**
	 * Replace query placeholders in a query part by the given
	 * parameters.
	 *
	 * @param string &$sqlString The query part with placeholders
	 * @param array $parameters The parameters
	 * @param string $tableName
	 *
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
	 */
	protected function replacePlaceholders(&$sqlString, array $parameters, $tableName = 'foo') {
		if (substr_count($sqlString, '?') !== count($parameters)) {
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception('The number of question marks to replace must be equal to the number of parameters.', 1242816074);
		}
		$offset = 0;
		foreach ($parameters as $parameter) {
			$markPosition = strpos($sqlString, '?', $offset);
			if ($markPosition !== FALSE) {
				if ($parameter === NULL) {
					$parameter = 'NULL';
				} elseif (is_array($parameter) || $parameter instanceof \ArrayAccess || $parameter instanceof \Traversable) {
					$items = array();
					foreach ($parameter as $item) {
						$items[] = $this->databaseHandle->fullQuoteStr($item, $tableName);
					}
					$parameter = '(' . implode(',', $items) . ')';
				} else {
					$parameter = $this->databaseHandle->fullQuoteStr($parameter, $tableName);
				}
				$sqlString = substr($sqlString, 0, $markPosition) . $parameter . substr($sqlString, ($markPosition + 1));
			}
			$offset = $markPosition + strlen($parameter);
		}
	}

	/**
	 * Adds additional WHERE statements according to the query settings.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @param string $tableName The table name to add the additional where clause for
	 * @param string &$sql
	 * @return void
	 */
	protected function addAdditionalWhereClause(\TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings, $tableName, &$sql) {
		//$this->addVisibilityConstraintStatement($querySettings, $tableName, $sql);
		//if ($querySettings->getRespectSysLanguage()) {
		//	$this->addSysLanguageStatement($tableName, $sql, $querySettings);
		//}
		//if ($querySettings->getRespectStoragePage()) {
		//	$this->addPageIdStatement($tableName, $sql, $querySettings->getStoragePageIds());
		//}
	}

	/**
	 * Transforms orderings into SQL.
	 *
	 * @param array $orderings An array of orderings (Tx_Extbase_Persistence_QOM_Ordering)
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source The source
	 * @param array &$sql The query parts
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedOrderException
	 * @return void
	 */
	protected function parseOrderings(array $orderings, \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source, array &$sql) {
		foreach ($orderings as $propertyName => $order) {
			switch ($order) {
				case \TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelConstantsInterface::JCR_ORDER_ASCENDING:

				case \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING:
					$order = 'ASC';
					break;
				case \TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelConstantsInterface::JCR_ORDER_DESCENDING:

				case \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING:
					$order = 'DESC';
					break;
				default:
					throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedOrderException('Unsupported order encountered.', 1242816074);
			}
			$className = '';
			$tableName = '';
			if ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface) {
				$className = $source->getNodeTypeName();
				$tableName = $this->dataMapper->convertClassNameToTableName($className);
				while (strpos($propertyName, '.') !== FALSE) {
					$this->addUnionStatement($className, $tableName, $propertyName, $sql);
				}
			} elseif ($source instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\JoinInterface) {
				$tableName = $source->getLeft()->getSelectorName();
			}
			$columnName = $this->dataMapper->convertPropertyNameToColumnName($propertyName, $className);
			if (strlen($tableName) > 0) {
				$sql['orderings'][] = $tableName . '.' . $columnName . ' ' . $order;
			} else {
				$sql['orderings'][] = $columnName . ' ' . $order;
			}
		}
	}

	/**
	 * Transforms limit and offset into SQL
	 *
	 * @param integer $limit
	 * @param integer $offset
	 * @param array &$sql
	 * @return void
	 */
	protected function parseLimitAndOffset($limit, $offset, array &$sql) {
		$sql['limit'] = intval($limit);
		$sql['offset'] = intval($offset);
	}

	/**
	 * @param array $result
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @return array The result as an array of rows (tuples)
	 */
	protected function getRowsFromResult(array $result, \TYPO3\CMS\Extbase\Persistence\QueryInterface $query) {
		$draftRow = array();
		if ($query->getSource() instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface) {
			$className = $query->getSource()->getNodeTypeName();
			$dataMap = $this->dataMapper->getDataMap($className);
			foreach ($dataMap->getPropertyNames() as $propertyName) {
				$columnMap = $dataMap->getColumnMap($propertyName);
				if ($columnMap->getTypeOfRelation() != \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_NONE) {
					$draftRow[$columnMap->getColumnName()] = '0';
				}
			}

		}

		$rows = array();
		if ($result['entry_list'] && is_array($result['entry_list'])) {
			foreach($result['entry_list'] as $entry) {
				$row = $draftRow;
				foreach($entry['name_value_list'] as $name_value_pair) {
					$row[$name_value_pair['name']] = $name_value_pair['value'];
				}
				$row['uid'] = $row['id']; // needed for identity map
				$rows[] = $row;
			}
		}
		return $rows;
	}
}