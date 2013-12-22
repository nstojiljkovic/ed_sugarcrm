<?php

namespace EssentialDots\EdSugarcrm\Tests\Unit\Domain\Model;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AccountTest extends \EssentialDots\EdSugarcrm\Tests\Unit\BaseTestCase {

	/**
	 * @var \EssentialDots\EdSugarcrm\Domain\Model\Account
	 */
	protected $fixture;

	/**
	 * @var \EssentialDots\EdSugarcrm\Domain\Repository\AccountRepository
	 */
	protected $fixtureRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var boolean
	 */
	protected $useDBMemoryEngineIfAvailable = false;

	/**
	 * SetUp test case
	 * @see \EssentialDots\ExtbaseFal\Tests\BaseTestCase::setUp()
	 */
	protected function setUp() {
		parent::setUp();

		if (version_compare(TYPO3_version,'6.1.0','>=')) {
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadBaseTca(false);
			//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadExtTables(false);
			//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadNewTcaColumnsConfigFiles();
		}

		// Including only necessary extensions
		$extensions = array('cms', 'extbase', 'extbase_domain_decorator', 'frontend');
		$this->importExtensions($extensions, true);
		$this->importDataSet ( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ( "ed_sugarcrm" ) . 'Tests/Fixtures/database/default_db.xml' );

		if (version_compare(TYPO3_version,'6.1.0','>=')) {
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadNewTcaColumnsConfigFiles();
		}

		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->fixtureRepository = $this->objectManager->get('EssentialDots\\EdSugarcrm\\Domain\\Repository\\AccountRepository'); /* @var $accountRepository \EssentialDots\EdSugarcrm\Domain\Repository\AccountRepository */
		$this->fixture = $this->fixtureRepository->findByName("test account")->getFirst();
	}

	/**
	 * tearDown test case
	 * @see \EssentialDots\ExtbaseFal\Tests\BaseTestCase::tearDown()
	 */
	protected function tearDown() {
		parent::tearDown();
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getNameTest() {
		$this->assertEquals($this->fixture->getName(), "test account");
	}
}