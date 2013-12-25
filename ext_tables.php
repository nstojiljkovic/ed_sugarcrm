<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = array();
$tempColumns['tx_edsugarcrm_crmaccount'] = Array (
	'exclude' => 1,
	'label' => 'LLL:EXT:ed_sugarcrm/Resources/Private/Language/locallang_db.xml:fe_users.fields.tx_edsugarcrm_crmaccount',
	'config' => Array (
		'type' => 'input',
		'readOnly' => TRUE,
	)
);

//\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:ed_sugarcrm/Resources/Private/Language/locallang_db.xml:fe_users.tabs.sugarcrm, tx_edsugarcrm_crmaccount;;;;1-1-1');
unset($tempColumns);

/**
 * Registers a Plugin to be listed in the Backend.
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'EssentialDots.' . $_EXTKEY,// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
	'Pi1', // A unique name of the plugin in UpperCamelCase
	'SugarCRM user panel' // A title shown in the backend dropdown field
);

$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignaturePi1 = strtolower($extensionName) . '_pi1';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignaturePi1] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignaturePi1, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_pi1.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'SugarCRM');

?>