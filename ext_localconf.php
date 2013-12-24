<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/* @var $decoratorManager \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager */
$decoratorManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager");
$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\Account', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\Email', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\EmailAddress', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\SupportCase', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\User', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
unset($decoratorManager);

