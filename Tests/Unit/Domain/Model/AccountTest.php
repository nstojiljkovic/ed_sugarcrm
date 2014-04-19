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
	protected $useDBMemoryEngineIfAvailable = true;

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
		$extensions = array('cms', 'frontend', 'backend','extbase', 'tstemplate', 'extbase_domain_decorator');

		$this->importExtensions($extensions, true);
		$this->importDataSet ( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ( "ed_sugarcrm" ) . 'Tests/Fixtures/database/default_db.xml' );

		if (version_compare(TYPO3_version,'6.1.0','>=')) {
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadNewTcaColumnsConfigFiles();
		}

		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->fixtureRepository = $this->objectManager->get('EssentialDots\\EdSugarcrm\\Domain\\Repository\\AccountRepository'); /* @var $accountRepository \EssentialDots\EdSugarcrm\Domain\Repository\AccountRepository */
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
	public function checkAccountTest() {
        $this->fixture = $this->fixtureRepository->findByName("test account")->getFirst();
        $fixture = $this->fixture; /** @var $fixture \EssentialDots\EdSugarcrm\Domain\Model\Account */
		$this->assertEquals($fixture->getName(), "test account");
        $this->assertEquals($fixture->getUid(), md5('id1'));
        $this->assertEquals($fixture->getAnnualRevenue(), "annualRevenue1");
        $this->assertEquals($fixture->getAssignedUser()->getUid(), md5('id1'));
        $this->assertEquals($fixture->getBillingAddressCity(), "billingAddressCity1");
        $this->assertEquals($fixture->getBillingAddressCountry(), "billingAddressCountry1");
        $this->assertEquals($fixture->getBillingAddressPostalcode(), "billingAddressPostalcode1");
        $this->assertEquals($fixture->getBillingAddressState(), "billingAddressState1");
        $this->assertEquals($fixture->getBillingAddressStreet(), "billingAddressStreet1");
        $this->assertEquals($fixture->getCreatedByUser()->getUid(), md5('id1'));
        $this->assertEquals($fixture->getDateEntered(), NULL);
        $this->assertEquals($fixture->getDateModified(), NULL);
        $this->assertEquals($fixture->getDescription(), "description1");
        $this->assertEquals($fixture->getIndustry(), "industry1");
        $this->assertEquals($fixture->getModifiedByUser()->getUid(), md5('id1'));
        $this->assertEquals($fixture->getOwnership(), "ownership1");
        $this->assertEquals($fixture->getPhoneAlternate(), "phoneAlternate1");
        $this->assertEquals($fixture->getPhoneFax(), "phoneFax1");
        $this->assertEquals($fixture->getPhoneOffice(), "phoneOffice1");
        $this->assertEquals($fixture->getRating(), "rating1");
        $this->assertEquals($fixture->getShippingAddressCity(), "shippingAddressCity1");
        $this->assertEquals($fixture->getShippingAddressCountry(), "shippingAddressCountry1");
        $this->assertEquals($fixture->getShippingAddressPostalcode(), "shippingAddressPostalcode1");
        $this->assertEquals($fixture->getShippingAddressState(), "shippingAddressState1");
        $this->assertEquals($fixture->getShippingAddressStreet(), "shippingAddressStreet1");
        $this->assertEquals($fixture->getSicCode(), "sicCode1");
        $this->assertEquals($fixture->getTickerSymbol(), "tickerSymbol1");
        $this->assertEquals($fixture->getWebsite(), "website1");
        $i = 1;
        foreach($fixture->getEmails() as $email){/** @var $email \EssentialDots\EdSugarcrm\Domain\Model\Email */
            $this->assertEquals($email->getUid(), md5('id'.$i));
            $this->assertEquals($email->getAssignedUser()->getUid(), md5('id1'));
            $this->assertEquals($email->getBccAddrs(), "bccAddrs".$i);
            $this->assertEquals($email->getCcAddrs(), "ccAddrs".$i);
            $this->assertEquals($email->getCreatedByUser()->getUid(), md5('id1'));
            $this->assertEquals($email->getDateEntered(), NULL);
            $this->assertEquals($email->getDateModified(), NULL);
            $this->assertEquals($email->getDateSent(), NULL);
            $this->assertEquals($email->getDescription(), "description".$i);
            $this->assertEquals($email->getDescriptionHtml(), "descriptionHtml".$i);
            $this->assertEquals($email->getFromAddr(), "fromAddr".$i);
            $this->assertEquals($email->getIntent(), "intent".$i);
            $this->assertEquals($email->getMessageId(), "messageId".$i);
            $this->assertEquals($email->getModifiedByUser()->getUid(), md5('id1'));
            $this->assertEquals($email->getName(), "name".$i);
            $this->assertEquals($email->getParentId(), "parentId".$i);
            $this->assertEquals($email->getParentType(), "parentType".$i);
            $this->assertEquals($email->getRawSource(), "rawSource".$i);
            $this->assertEquals($email->getReplyToAddr(), "replyToAddr".$i);
            $this->assertEquals($email->getStatus(), "status".$i);
            $this->assertEquals($email->getToAddrs(), "toAddrs".$i);
            $this->assertEquals($email->getType(), "type".$i);
            $i++;
        }
        $i = 1;
        foreach($fixture->getCases() as $case){/** @var $case \EssentialDots\EdSugarcrm\Domain\Model\SupportCase */
            $this->assertEquals($case->getUid(), md5('id'.$i));
            $this->assertEquals($case->getType(), "type".$i);
            $this->assertEquals($case->getStatus(), "status".$i);
            $this->assertEquals($case->getAccount()->getUid(), md5('id1'));
            $this->assertEquals($case->getAssignedUser()->getUid(), md5('id1'));
            $this->assertEquals($case->getCreatedByUser()->getUid(), md5('id1'));
            $this->assertEquals($case->getDateEntered(), NULL);
            $this->assertEquals($case->getDateModified(), NULL);
            $this->assertEquals($case->getDescription(), "description".$i);
            $this->assertEquals($case->getModifiedByUser()->getUid(), md5('id1'));
            $this->assertEquals($case->getName(), "name".$i);
            $this->assertEquals($case->getPriority(), "priority".$i);
            $this->assertEquals($case->getResolution(), "resolution".$i);
            $this->assertEquals($case->getWorkLog(), "workLog".$i);
            $j = 1;
            foreach($case->getEmails() as $email){/** @var $email \EssentialDots\EdSugarcrm\Domain\Model\Email */
                $this->assertEquals($email->getUid(), md5('id'.$j));
                $this->assertEquals($email->getAssignedUser()->getUid(), md5('id1'));
                $this->assertEquals($email->getBccAddrs(), "bccAddrs".$j);
                $this->assertEquals($email->getCcAddrs(), "ccAddrs".$j);
                $this->assertEquals($email->getCreatedByUser()->getUid(), md5('id1'));
                $this->assertEquals($email->getDateEntered(), NULL);
                $this->assertEquals($email->getDateModified(), NULL);
                $this->assertEquals($email->getDateSent(), NULL);
                $this->assertEquals($email->getDescription(), "description".$j);
                $this->assertEquals($email->getDescriptionHtml(), "descriptionHtml".$j);
                $this->assertEquals($email->getFromAddr(), "fromAddr".$j);
                $this->assertEquals($email->getIntent(), "intent".$j);
                $this->assertEquals($email->getMessageId(), "messageId".$j);
                $this->assertEquals($email->getModifiedByUser()->getUid(), md5('id1'));
                $this->assertEquals($email->getName(), "name".$j);
                $this->assertEquals($email->getParentId(), "parentId".$j);
                $this->assertEquals($email->getParentType(), "parentType".$j);
                $this->assertEquals($email->getRawSource(), "rawSource".$j);
                $this->assertEquals($email->getReplyToAddr(), "replyToAddr".$j);
                $this->assertEquals($email->getStatus(), "status".$j);
                $this->assertEquals($email->getToAddrs(), "toAddrs".$j);
                $this->assertEquals($email->getType(), "type".$j);
                $j++;
            }
            $i++;
        }
        $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        $fixture = new \EssentialDots\EdSugarcrm\Domain\Model\Account(GeneralUtility::makeInstance('EssentialDots\\EdSugarcrm\\Domain\\Model\\AbstractEntity'));
        $fixture->setName("testName");
        $fixture->setAnnualRevenue("testAnnualRevenue");
        $fixture->setBillingAddressCity("testBillingAddressCity");
        $fixture->setBillingAddressCountry("testBillingAddressCountry");
        $fixture->setBillingAddressPostalcode("setBillingAddressPostalcode");
        $fixture->setBillingAddressState("setBillingAddressState");
        $fixture->setBillingAddressStreet("setBillingAddressStreet");
        $fixture->setDateEntered(date("Y-m-d H:i:s"));
        $fixture->setDateModified(date("Y-m-d H:i:s"));
        $fixture->setDescription("setDescription");
        $fixture->setIndustry("setIndustry");
        $fixture->setOwnership("setOwnership");
        $fixture->setPhoneAlternate("setPhoneAlternate");
        $fixture->setPhoneFax("setPhoneFax");
        $fixture->setPhoneOffice("setPhoneOffice");
        $fixture->setRating("setRating");
        $fixture->setShippingAddressCity("setShippingAddressCity");
        $fixture->setShippingAddressCountry("setShippingAddressCountry");
        $fixture->setShippingAddressPostalcode("setShippingAddressPostalcode");
        $fixture->setShippingAddressState("setShippingAddressState");
        $fixture->setShippingAddressStreet("setShippingAddressStreet");
        $fixture->setSicCode("setSicCode");
        $fixture->setTickerSymbol("setTickerSymbol");
        $fixture->setWebsite("setWebsite");
        $this->fixtureRepository->add($fixture);
        /* @var $persistenceManager \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
        $persistenceManager->persistAll();
        $get = $this->fixtureRepository->findByName("testName")->getFirst();/** @var $get \EssentialDots\EdSugarcrm\Domain\Model\Account */
        $this->assertEquals($fixture->getName(), $get->getName());
        $this->assertEquals($fixture->getUid(), $get->getUid());
        $this->assertEquals($fixture->getAnnualRevenue(), $get->getAnnualRevenue());
        $this->assertEquals($fixture->getBillingAddressCity(), $get->getBillingAddressCity());
        $this->assertEquals($fixture->getBillingAddressCountry(), $get->getBillingAddressCountry());
        $this->assertEquals($fixture->getBillingAddressPostalcode(), $get->getBillingAddressPostalcode());
        $this->assertEquals($fixture->getBillingAddressState(), $get->getBillingAddressState());
        $this->assertEquals($fixture->getBillingAddressStreet(), $get->getBillingAddressStreet());
        $this->assertEquals($fixture->getDateEntered(), $get->getDateEntered());
        $this->assertEquals($fixture->getDateModified(), $get->getDateModified());
        $this->assertEquals($fixture->getDescription(), $get->getDescription());
        $this->assertEquals($fixture->getIndustry(), $get->getIndustry());
        $this->assertEquals($fixture->getOwnership(), $get->getOwnership());
        $this->assertEquals($fixture->getPhoneAlternate(), $get->getPhoneAlternate());
        $this->assertEquals($fixture->getPhoneFax(), $get->getPhoneFax());
        $this->assertEquals($fixture->getPhoneOffice(), $get->getPhoneOffice());
        $this->assertEquals($fixture->getRating(), $get->getRating());
        $this->assertEquals($fixture->getShippingAddressCity(), $get->getShippingAddressCity());
        $this->assertEquals($fixture->getShippingAddressCountry(), $get->getShippingAddressCountry());
        $this->assertEquals($fixture->getShippingAddressPostalcode(), $get->getShippingAddressPostalcode());
        $this->assertEquals($fixture->getShippingAddressState(), $get->getShippingAddressState());
        $this->assertEquals($fixture->getShippingAddressStreet(), $get->getShippingAddressStreet());
        $this->assertEquals($fixture->getSicCode(), $get->getSicCode());
        $this->assertEquals($fixture->getTickerSymbol(), $get->getTickerSymbol());
        $this->assertEquals($fixture->getWebsite(), $get->getWebsite());
    }
}