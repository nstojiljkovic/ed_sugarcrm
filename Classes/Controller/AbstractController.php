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

abstract class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \Tx_ExtbaseHijax_Event_Dispatcher
	 */
	protected $hijaxEventDispatcher;
	
	/**
	 * Injects the event dispatcher
	 *
	 * @param \Tx_ExtbaseHijax_Event_Dispatcher $eventDispatcher
	 * @return void
	 */
	public function injectEventDispatcher(\Tx_ExtbaseHijax_Event_Dispatcher $eventDispatcher) {
		$this->hijaxEventDispatcher = $eventDispatcher;
	}	
	
	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
	 * @return void
	 */
	protected function setViewConfiguration(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view) {
		parent::setViewConfiguration($view);
			// Template Path Override
		if (isset($this->settings['template']) && !empty($this->settings['template'])) {
			$templateRootPath = GeneralUtility::getFileAbsFileName($this->settings['template'], TRUE);
			if (GeneralUtility::isAllowedAbsPath($templateRootPath)) {
				$view->setTemplateRootPath($templateRootPath);
			}
		}
	}
	
	/**
	 * Calls the specified action method and passes the arguments.
	 *
	 * If the action returns a string, it is appended to the content in the
	 * response object. If the action doesn't return anything and a valid
	 * view exists, the view is rendered automatically.
	 * 
	 * @return void
	 * @api
	 */
	protected function callActionMethod() {
		
		if (!$this->settings['enablePasswordRecovery'] && $this->request->getControllerName()=='Password') {
			$this->forward('login', 'User');
		}

		parent::callActionMethod();
	}	
	
	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 * @api
	 */
	protected function initializeAction() {
		if (!$this->settings['doNotShowErrorsFromOtherInstances']) {
			$this->hijaxEventDispatcher->connect('user-loginFailure', array($this, 'onUserLoginFailure'));
		}
		$this->hijaxEventDispatcher->connect('user-loggedIn', array($this, 'onUserLoggedIn'));
		$this->hijaxEventDispatcher->connect('user-loggedOut', array($this, 'onUserLoggedOut'));
	}	
	
	/**
	 * @var $event \Tx_ExtbaseHijax_Event_Event
	 */
	public function onUserLoginFailure(\Tx_ExtbaseHijax_Event_Event $event) {
		if (!$this->request->hasArgument('dontForwardOnEvents') || !$this->request->getArgument('dontForwardOnEvents')) {
			if ($this->actionMethodName!='checkLoginAction') {
				$this->forward('checkLogin', 'User', NULL, array('dontForwardOnEvents' => true));
			}
		}
	}
		
	/**
	 * @var $event \Tx_ExtbaseHijax_Event_Event
	 */
	public function onUserLoggedIn(\Tx_ExtbaseHijax_Event_Event $event) {
		if (!$this->request->hasArgument('dontForwardOnEvents') || !$this->request->getArgument('dontForwardOnEvents')) {
			if ($this->actionMethodName!='checkLoginAction') {
				$this->forward('checkLogin', 'User', NULL, array('dontForwardOnEvents' => true));
			}
		}
	}
	
	/**
	 * @var $event \Tx_ExtbaseHijax_Event_Event
	 */
	public function onUserLoggedOut(\Tx_ExtbaseHijax_Event_Event $event) {
		if (!$this->request->hasArgument('dontForwardOnEvents') || !$this->request->getArgument('dontForwardOnEvents')) {
			if ($this->actionMethodName=="show" && $this->request->getControllerName()=="User") {
				$this->forward('login', 'User', NULL, array('dontForwardOnEvents' => true));
			}
		}
	}
		
	/**
	 * Returns a valid and XSS cleaned url for redirect, checked against configuration "allowedRedirectHosts"
	 *
	 * @param string $url
	 * @return string cleaned referer or empty string if not valid
	 */
	protected function validateRedirectUrl($url) {
		$url = strval($url);
		if ($url === '') {
			return NULL;
		}
	
		$decodedUrl = rawurldecode($url);
		$sanitizedUrl = GeneralUtility::removeXSS($decodedUrl);
	
		if ($decodedUrl !== $sanitizedUrl || preg_match('#["<>\\\]+#', $url)) {
			return NULL;
		}
	
		// Validate the URL:
		if ($this->isRelativeUrl($url) || $this->isInCurrentDomain($url)) {
			return $url;
		}
	
		// URL is not allowed
		return NULL;
	}
	
	/**
	 * Determines wether the URL is relative to the
	 * current TYPO3 installation.
	 *
	 * @param string $url URL which needs to be checked
	 * @return boolean Whether the URL is considered to be relative
	 */
	protected function isRelativeUrl($url) {
		$parsedUrl = @parse_url($url);
		if ($parsedUrl !== FALSE && !isset($parsedUrl['scheme']) && !isset($parsedUrl['host'])) {
			// If the relative URL starts with a slash, we need to check if it's within the current site path
			return (!GeneralUtility::isFirstPartOfStr($parsedUrl['path'], '/') || GeneralUtility::isFirstPartOfStr($parsedUrl['path'], GeneralUtility::getIndpEnv('TYPO3_SITE_PATH')));
		}
		return FALSE;
	}
	
	/**
	 * Determines whether the URL is on the current host
	 * and belongs to the current TYPO3 installation.
	 *
	 * @param string $url URL to be checked
	 * @return boolean Whether the URL belongs to the current TYPO3 installation
	 */
	protected function isInCurrentDomain($url) {
		return (GeneralUtility::isOnCurrentHost($url) && GeneralUtility::isFirstPartOfStr($url, GeneralUtility::getIndpEnv('TYPO3_SITE_URL')));
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