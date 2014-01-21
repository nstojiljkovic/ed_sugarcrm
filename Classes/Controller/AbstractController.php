<?php
namespace EssentialDots\EdSugarcrm\Controller;

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

/**
 *
 *
 * @package ed_news
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
abstract class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
	 * @return void
	 */
	protected function setViewConfiguration(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view) {
		parent::setViewConfiguration($view);
		// Template Path Override
		if (isset($this->settings['template']) && !empty($this->settings['template'])) {
			$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->settings['template'], TRUE);
			if (\TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath($templateRootPath)) {
				$view->setTemplateRootPath($templateRootPath);
			}
		}
	}

	/**
	 * @param string $settingName
	 * @return string
	 */
	protected function getSetting($settingName) {
		if ($this->settings[$settingName]) {
			return $this->settings[$settingName];
		} elseif ($this->settings['default'] && $this->settings['default'][$settingName]) {
			return $this->settings['default'][$settingName];
		} else {
			return NULL;
		}
	}

	/**
	 * Generate page url
	 *
	 * @param integer $pageId
	 * @param array	$arguments
	 * @param array	$additionalParams
	 * @return void
	 */
	protected function getPageUrl($pageId, $arguments = NULL, $additionalParams = NULL) {
		if (($arguments != NULL && count($arguments)) || ($additionalParams != NULL && count($additionalParams))) {
			$aP = '';
			foreach($additionalParams as $key=>$value) {
				$aP .= "&$key=$value";
			}
			$namespace = $this->getNamespace();
			foreach($arguments as $key=>$value) {
				$aP .= "&{$namespace}[{$key}]=$value";
			}
			return $GLOBALS['TSFE']->cObj->typoLink_URL(array('parameter' => $pageId, 'additionalParams' => $aP, 'useCacheHash' => 1));
		} else {
			return $GLOBALS['TSFE']->cObj->typoLink_URL(array('parameter' => $pageId, 'useCacheHash' => 1));
		}
	}

	/**
	 * Get the namespace of the uploaded file
	 *
	 * @return string
	 */
	protected function getNamespace() {
		$frameworkSettings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		return strtolower('tx_' . $frameworkSettings['extensionName'] . '_' . $frameworkSettings['pluginName']);
	}
}

?>