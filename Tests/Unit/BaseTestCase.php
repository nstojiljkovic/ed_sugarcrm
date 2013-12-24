<?php

namespace EssentialDots\EdSugarcrm\Tests\Unit;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

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
 *  the Free Software Foundation; either version 2 of the License, or
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

abstract class BaseTestCase extends \Tx_Phpunit_Database_TestCase {

	/**
	 * @var    Array
	 */
	private $mockObjects = array();
	
	
	/**
	 * @var array
	 */
	protected $tcaBackup;


	/**
	 * @var array
	 */
	protected $typo3ConfVarsBackup;

	
	/**
	 * @var boolean
	 */
	protected $useDBMemoryEngineIfAvailable = false;
	
	
	/**
	 * @var boolean
	 */
	private $useDBMemoryEngine = false;

	
	/**
	 * @throws Exception
	 */
	protected function setUp() {
		GLOBAL $TCA;

		$this->typo3ConfVarsBackup = $GLOBALS['TYPO3_CONF_VARS'];

		// needed in order to avoid SQL errors
		$GLOBALS ['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = '';

		$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'] = array();
		$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'] = array();
		$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'] = array();
		$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamap_preProcessFieldArray'] = array();
		$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'] = array();

		if ($this->useDBMemoryEngineIfAvailable) {
			$db = $GLOBALS['TYPO3_DB']; /* @var $db t3lib_db */
			$res = $db->sql_query('SHOW ENGINES');
			while ($row = $db->sql_fetch_assoc($res)) {
				if (strtolower($row['Engine'])=='memory' && (strtolower($row['Support'])=='yes' || strtolower($row['Support'])=='default')) {
					$this->useDBMemoryEngine = true;
					break;
				}
			}
			$db->sql_free_result($res);
		}
		
		$this->tcaBackup = &$TCA;
		$TCA = array();

		if (ExtensionManagementUtility::isLoaded('core')) {
			$stdDBExtTablesFilename = ExtensionManagementUtility::extPath ( 'core', 'ext_tables.php' );
		} else {
			$stdDBExtTablesFilename = GeneralUtility::getFileAbsFileName(PATH_t3lib . 'stddb/tables.php');
		}
		if (!file_exists($stdDBExtTablesFilename) || is_dir($stdDBExtTablesFilename)) {
			throw new Exception("Cannot find STD DB PHP file.");
		}

		/** @noinspection PhpIncludeInspection */
		include ($stdDBExtTablesFilename);
		
		$this->createDatabase();
		$this->useTestDatabase();
		$this->importStdDB();

		if (ExtensionManagementUtility::isLoaded('aoe_dbsequenzer')) {
			$this->importExtensions(array('aoe_dbsequenzer'), false);
		}

		// make sure that required implementations are registered in extbase (as ext_localconf.php from extbase is not included in this moment
		// TODO: we should probably just include ext_localconf.php from extbase here
		$extbaseObjectContainer = GeneralUtility::makeInstance('Tx_Extbase_Object_Container_Container'); // Singleton
		$extbaseObjectContainer->registerImplementation('Tx_Extbase_Persistence_Storage_BackendInterface', 'Tx_Extbase_Persistence_Storage_Typo3DbBackend');
		$extbaseObjectContainer->registerImplementation('Tx_Extbase_Persistence_QuerySettingsInterface', 'Tx_Extbase_Persistence_Typo3QuerySettings');

		$extbaseObjectContainer->registerImplementation('TYPO3\CMS\Extbase\Persistence\QueryInterface', 'TYPO3\CMS\Extbase\Persistence\Generic\Query');
		$extbaseObjectContainer->registerImplementation('TYPO3\CMS\Extbase\Persistence\QueryResultInterface', 'TYPO3\CMS\Extbase\Persistence\Generic\QueryResult');
		$extbaseObjectContainer->registerImplementation('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface', 'TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager');
		$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Storage\\BackendInterface', 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Storage\\Typo3DbBackend');
		$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface', 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $extbaseObjectContainer->registerImplementation('EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Storage\\SugarCRMRESTHandle', 'EssentialDots\\EdSugarcrm\\Tests\\Mock\\Persistance\\Generic\\Storage\\SugarCRMRESTHandleMock');
		//Tx_Extbase_Persistence_Storage_Typo3DbBackend::OPERATOR_EQUAL_TO_NULL
		unset($extbaseObjectContainer);
	}
	
	/**
	 * @return void
	 */
	protected function tearDown() {
		GLOBAL /** @noinspection PhpUnusedLocalVariableInspection */
		$TCA, $TYPO3_CONF_VARS;
		
		$this->cleanDatabase();
		$this->dropDatabase();
		$this->switchToTypo3Database();
		GeneralUtility::purgeInstances();

		/** @noinspection PhpUnusedLocalVariableInspection */
		$TCA = &$this->tcaBackup;
		$TYPO3_CONF_VARS = $this->typo3ConfVarsBackup;

		unset($this->tcaBackup);
	}

	/**
	 * Accesses the TYPO3 database instance and uses it to fetch the list of
	 * abailable databases. Then this function creates a test database (if none
	 * has been set up yet).
	 *
	 * @return boolean
	 *         TRUE if the database has been created successfully (or if there
	 *         already is a test database), FALSE otherwise
	 */
	protected function createDatabase() {
		$success = TRUE;

		$this->dropDatabase();
		/** @var $db t3lib_DB */
		$db = $GLOBALS['TYPO3_DB'];

		if (!in_array($this->testDatabase, $this->admin_get_dbs())) {
			if ($db->admin_query('CREATE DATABASE ' . $this->testDatabase) === FALSE) {
				$success = FALSE;
			}
		}

		return $success;
	}

	/**
	 * Drops the test database.
	 *
	 * @return boolean
	 *         TRUE if the database has been dropped successfully, FALSE otherwise
	 */
	protected function dropDatabase() {
		/** @var $db t3lib_DB */
		$db = $GLOBALS['TYPO3_DB'];
		if (!in_array($this->testDatabase, $this->admin_get_dbs())) {
			return TRUE;
		}

		$db->sql_select_db($this->testDatabase);

		return ($db->admin_query('DROP DATABASE ' . $this->testDatabase) !== FALSE);
	}

	/**
	 * @return array
	 */
	protected function admin_get_dbs() {
		/** @var $db t3lib_DB */
		$db = $GLOBALS['TYPO3_DB'];
		$res = $db->sql_query("SHOW DATABASES");

		$databaseNames = array();
		while ($res && $row = $db->sql_fetch_row($res)) {
			$databaseNames[] = $row[0];
		}

		return $databaseNames;
	}

	/**
	 * Imports the ext_tables.sql and ext_tables.php files from the given extensions.
	 *
	 * @param array $extensions
	 *        keys of the extensions to import, may be empty
	 * @param boolean $importDependencies
	 *        whether to import dependency extensions on which the given extensions
	 *        depend as well
	 * @param array &$skipDependencies
	 *        keys of the extensions to skip, may be empty, will be modified
	 *
	 * @return void
	 */
	protected function importExtensions(array $extensions, $importDependencies = FALSE, array &$skipDependencies = array()) {
		GLOBAL /** @noinspection PhpUnusedLocalVariableInspection */
		$TCA;
		
		$this->useTestDatabase();
		
		foreach ($extensions as $extensionName) {
			if (!ExtensionManagementUtility::isLoaded($extensionName)) {
				$this->markTestSkipped(
						'This test is skipped because the extension ' . $extensionName .
						' which was marked for import is not loaded on your system!'
				);
			} elseif (in_array($extensionName, $skipDependencies)) {
				continue;
			}
				
			$skipDependencies = array_merge($skipDependencies, array($extensionName));
		
			if ($importDependencies) {
				$dependencies = $this->findDependencies($extensionName);
				if (is_array($dependencies)) {
					$this->importExtensions($dependencies, TRUE, $skipDependencies);
				}
			}
	
			$extTablesPath = ExtensionManagementUtility::extPath($extensionName, 'ext_tables.php');
			if (file_exists($extTablesPath) && is_file($extTablesPath)) {
				/** @noinspection PhpUnusedLocalVariableInspection */
				$_EXTKEY = $extensionName;
				/** @noinspection PhpIncludeInspection */
				include($extTablesPath);
			}
			
			$extLocalconfPath = ExtensionManagementUtility::extPath($extensionName, 'ext_tables.php');
			if (file_exists($extLocalconfPath) && is_file($extLocalconfPath)) {
				//include($extLocalconfPath);
			}
			
			$this->importExtension($extensionName);
		}

		// TODO: The hook should be replaced by real clean up and rebuild the whole
		// "TYPO3_CONF_VARS" in order to have a clean testing environment.
		// hook to load additional files
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'] as $file) {
				$sqlFilename = GeneralUtility::getFileAbsFileName($file);
				$fileContent = GeneralUtility::getUrl($sqlFilename);
		
				$this->importDatabaseDefinitions($fileContent);
			}
		}
		//parent::importExtensions($extensions, $importDependencies, $skipDependencies);
	}
	
	
	/**
	 * Imports the ext_tables.sql file of the extension with the given name
	 * into the test database.
	 *
	 * @param string $extensionName
	 *        the name of the installed extension to import, must not be empty
	 *
	 * @return void
	 */
	protected function importExtension($extensionName) {
		$sqlFilename = GeneralUtility::getFileAbsFileName(ExtensionManagementUtility::extPath($extensionName) . 'ext_tables.sql');
		$fileContent = GeneralUtility::getUrl($sqlFilename);

		$this->importDatabaseDefinitions($fileContent);
	}
		
	
	/**
	 * Imports the data from the stddb tables.sql file.
	 *
	 * Example/intended usage:
	 *
	 * <pre>
	 * public function setUp() {
	 *   $this->createDatabase();
	 *   $db = $this->useTestDatabase();
	 *   $this->importStdDB();
	 *   $this->importExtensions(array('cms', 'static_info_tables', 'templavoila'));
	 * }
	 * </pre>
	 *
	 * @throws Exception
	 */
	protected function importStdDb() {
		if (ExtensionManagementUtility::isLoaded('core')) {
			$sqlFilename = ExtensionManagementUtility::extPath ( 'core', 'ext_tables.sql' );
		} else {
			$sqlFilename = GeneralUtility::getFileAbsFileName(PATH_t3lib . 'stddb/tables.sql');
		}
		if (!file_exists($sqlFilename) || is_dir($sqlFilename)) {
			throw new Exception("Cannot find STD DB SQL file.");
		}
		$fileContent = GeneralUtility::getUrl($sqlFilename);

		if (class_exists('\TYPO3\CMS\Core\Cache\Cache')) {
			// Add SQL content coming from the caching framework
			$fileContent .= chr(10).\TYPO3\CMS\Core\Cache\Cache::getDatabaseTableDefinitions();
		} else {
			$fileContent .= chr(10).t3lib_cache::getDatabaseTableDefinitions();
		}

		if (class_exists('\TYPO3\CMS\Core\Category\CategoryRegistry')) {
			// Add SQL content coming from the category registry
			$fileContent .= chr(10).\TYPO3\CMS\Core\Category\CategoryRegistry::getInstance()->getDatabaseTableDefinitions();
		}

		$this->importDatabaseDefinitions($fileContent);
	}
		
	/**
	 * @param $functionName
	 * @param $folderName
	 * @param string $fileName
	 * @return array
	 */
	protected function getDefaultDataProvider($functionName, $folderName, $fileName = 'default.yml') {
		/** @noinspection PhpIncludeInspection */
		require_once 'PHPUnit/Extensions/Database/DataSet/YamlDataSet.php';
		
		$result = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet($folderName . DIRECTORY_SEPARATOR . 'DataProvider' . DIRECTORY_SEPARATOR . $fileName);
		
		$tbl = $result->getTable($functionName);
		
		$dataProvider = array();
		
		for ($i = 0; $i < $tbl->getRowCount(); $i++) {
			$dataProvider[] = $tbl->getRow($i);
		}
		
		return $dataProvider;
	}
	
	
    /**
     * Returns a mock object for the specified class.
     *
     * @param  string  $classNameForRegistration
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return string
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.0.0
     */
    public function registerExtbaseSingletonMockImplementation($classNameForRegistration, $originalClassName = '', $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE) {
    	
    	if (!$originalClassName) {
    		$originalClassName = $classNameForRegistration;
    	}
    	
    	$dummyStub = $this->getMock($originalClassName, $methods, $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload);
    	$mockImplementationClassName = get_class($dummyStub);
    	 
    	/* @var $extbaseObjectContainer Tx_Extbase_Object_Container_Container */
    	$extbaseObjectContainer = GeneralUtility::makeInstance('Tx_Extbase_Object_Container_Container'); // Singleton
    	$extbaseObjectContainer->registerImplementation($classNameForRegistration, $mockImplementationClassName);
    	
    	$dummyStub->__phpunit_cleanup();
    	unset($dummyStub);

    	/* @var $objectManager Tx_Extbase_Object_ObjectManager */
    	$objectManager = GeneralUtility::makeInstance('Tx_Extbase_Object_ObjectManager');
    	$this->mockObjects[] = $objectManager->get($mockImplementationClassName);
    	
    	return $mockImplementationClassName;
	}
	
	
	/**
	 * Finds all direct dependencies of the extension with the key $extKey.
	 *
	 * @param string $extKey the key of an installed extension, must not be empty
	 *
	 * @return array<string>|NULL
	 *         the keys of all extensions on which the given extension depends,
	 *         will be NULL if the dependencies could not be determined
	 */
	protected function findDependencies($extKey) {
		$path = GeneralUtility::getFileAbsFileName(ExtensionManagementUtility::extPath($extKey) . 'ext_emconf.php');
		$_EXTKEY = $extKey;
		/** @noinspection PhpIncludeInspection */
		include($path);

		/** @noinspection PhpUndefinedVariableInspection */
		$dependencies = $EM_CONF[$_EXTKEY]['constraints']['depends'];
		if (!is_array($dependencies)) {
			return NULL;
		}
	
		// remove php and typo3 extension (not real extensions)
		if (isset($dependencies['php'])) {
			unset($dependencies['php']);
		}
		if (isset($dependencies['typo3'])) {
			unset($dependencies['typo3']);
		}
	
		return array_keys($dependencies);
	}	
	
	/**
	 * Imports the SQL definitions from a (ext_)tables.sql file.
	 *
	 * @param string $definitionContent
	 *        the SQL to import, must not be empty
	 *
	 * @return void
	 */
	protected function importDatabaseDefinitions($definitionContent) {
	
		if ($this->useDBMemoryEngine) {
			$definitionContent = preg_replace('/\)\s*ENGINE\s*=\s*(.*)\s*;/msU', ') ENGINE=MEMORY;', $definitionContent);
			$definitionContent = preg_replace('/\)\s*;/msU', ') ENGINE=MEMORY;', $definitionContent);
			$definitionContent = preg_replace('/\s+text\s*,/', ' varchar(4096) DEFAULT \'\' NOT NULL,', $definitionContent);
			$definitionContent = preg_replace('/\s+longtext\s*,/', ' varchar(4096) DEFAULT \'\' NOT NULL,', $definitionContent);
			$definitionContent = preg_replace('/\s+mediumtext\s*,/', ' varchar(1024) DEFAULT \'\' NOT NULL,', $definitionContent);
			$definitionContent = preg_replace('/\s+tinytext\s*,/', ' varchar(255) DEFAULT \'\' NOT NULL,', $definitionContent);
			$definitionContent = preg_replace('/\s+blob\s*,/', ' varchar(4096) DEFAULT \'\' NOT NULL,', $definitionContent);
			$definitionContent = preg_replace('/\s+longblob\s*,/', ' varchar(4096) DEFAULT \'\' NOT NULL,', $definitionContent);
			$definitionContent = preg_replace('/\s+mediumblob\s*,/', ' varchar(1024) DEFAULT \'\' NOT NULL,', $definitionContent);
			$definitionContent = preg_replace('/\s+tinyblob\s*,/', ' varchar(255) DEFAULT \'\' NOT NULL,', $definitionContent);
		}
	
		$sqlHandler = GeneralUtility::makeInstance('t3lib_install_Sql'); /* @var $sqlHandler t3lib_install_Sql */
		if (method_exists($sqlHandler, 'getFieldDefinitions_fileContent')) {
			$fieldDefinitionsFile = $sqlHandler->getFieldDefinitions_fileContent($definitionContent);
		} else {
			$fieldDefinitionsFile = $sqlHandler->getFieldDefinitions_sqlContent($definitionContent);
		}

		if (empty($fieldDefinitionsFile)) {
			return;
		}
	
		// find statements to query
		if (method_exists($sqlHandler, 'getFieldDefinitions_fileContent')) {
			$fieldDefinitionsDatabase = $sqlHandler->getFieldDefinitions_fileContent($this->getTestDatabaseSchema());
		} else {
			$fieldDefinitionsDatabase = $sqlHandler->getFieldDefinitions_sqlContent($this->getTestDatabaseSchema());
		}

		$diff = $sqlHandler->getDatabaseExtra($fieldDefinitionsFile, $fieldDefinitionsDatabase);
		$updateStatements = $sqlHandler->getUpdateSuggestions($diff);
	
		$updateTypes = array('add', 'change', 'create_table');
	
		foreach ($updateTypes as $updateType) {
			if (array_key_exists($updateType, $updateStatements)) {
				foreach ((array) $updateStatements[$updateType] as $string) {
					$GLOBALS['TYPO3_DB']->admin_query($string);
				}
			}
		}
	}	
	
	
	/**
	 * Returns an SQL dump of the test database.
	 *
	 * @return string SQL dump of the test databse, might be empty
	 */
	protected function getTestDatabaseSchema() {
		$db = $this->useTestDatabase();
		$tables = $this->getDatabaseTables();
	
		// finds create statement for every table
		$linefeed = chr(10);
	
		$schema = '';
		$db->sql_query('SET SQL_QUOTE_SHOW_CREATE = 0');
		foreach ($tables as $tableName) {
			$res = $db->sql_query('show create table ' . $tableName);
			$row = $db->sql_fetch_row($res);
	
			// modifies statement to be accepted by TYPO3
			$createStatement = preg_replace('/ENGINE.*$/', '', $row[1]);
			$createStatement = preg_replace(
					'/(CREATE TABLE.*\()/', $linefeed . '\\1' . $linefeed, $createStatement
			);
			$createStatement = preg_replace('/\) $/', $linefeed . ')', $createStatement);
	
			$schema .= $createStatement . ';';
		}
	
		return $schema;
	}	
	
	
	/**
	 * Verifies the mock object expectations.
	 *
	 * @since Method available since Release 3.5.0
	 */
	protected function verifyMockObjects() {
		foreach ($this->mockObjects as $mockObject) {
			$this->addToAssertionCount(1);
			$mockObject->__phpunit_verify();
			$mockObject->__phpunit_cleanup();
		}
	
		$this->mockObjects = array();
		
		parent::verifyMockObjects();
	}

	/**
	 * @param string $src
	 * @param string $dst
	 */
	protected function copyRecursive($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		GeneralUtility::fixPermissions($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) {
					$this->copyRecursive($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
				}
				else {
					copy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
					GeneralUtility::fixPermissions($dst . DIRECTORY_SEPARATOR . $file);
				}
			}
		}
		closedir($dir);
	}

	/**
	 * @param string $folder
	 */
	protected function deleteRecursive($folder) {
		GeneralUtility::rmdir($folder, TRUE);
	}
}