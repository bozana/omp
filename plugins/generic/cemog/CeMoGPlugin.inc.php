<?php

/**
 * @file plugins/generic/cemog/CeMoGPlugin.inc.php
 *
 * Copyright (c) 2015 CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CeMoGPlugin
 * @ingroup plugins_generic_cemog
 *
 * @brief CeMoG plugin for all CeMoG changes
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class CeMoGPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.cemog.name');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.cemog.description');
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				// Register BookreaderPlugin
				HookRegistry::register('PluginRegistry::loadCategory', array($this, 'callbackLoadCategory'));

				// Overwrite the bookSpecs and bookInfo templates, as well as registration page
				// Add description text to the login page
				HookRegistry::register ('TemplateManager::display', array(&$this, 'handleTemplateDisplay'));
				// Overwrite the horizontal navigation
				HookRegistry::register('TemplateManager::fetch', array($this, 'handleTemplateDisplay'));

				// Delete the Bookreader directory when deleting the ZIP-file
				HookRegistry::register('FileManager::deleteFile', array($this, 'handleDeleteFile'));

				// Additional submission metadata form fields
				HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldsEdit'));
				HookRegistry::register('catalogentrysubmissionreviewform::initdata', array($this, 'metadataInitData'));
				HookRegistry::register('catalogentrysubmissionreviewform::readuservars', array($this, 'metadataFieldsReadUserVars'));
 				HookRegistry::register('catalogentrysubmissionreviewform::execute', array($this, 'metadataFieldsSave'));
 				HookRegistry::register('submissionsubmitstep3form::readuservars', array($this, 'metadataFieldsReadUserVars'));
 				HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'metadataFieldsSave'));

 				// Consider the new submission metadata form fields in MonographDAO for storage
 				HookRegistry::register('monographdao::getLocaleFieldNames', array($this, 'submissionGetFieldNames'));

				// Additional registration form fields
				HookRegistry::register('registrationform::readuservars', array($this, 'registrationFormReadUserVars'));
 				HookRegistry::register('registrationform::execute', array($this, 'registrationFormSave'));
				// Consider the new registration form fields in UserDAO for storage
				HookRegistry::register('userdao::getAdditionalFieldNames', array($this, 'registrationGetFieldNames'));

			}
			return true;
		}
		return false;
	}

	/**
	 * Register as a viewableFiles plugin, even though this is a generic plugin.
	 * This will allow the plugin to behave as a viewableFiles plugin
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	*/
	function callbackLoadCategory($hookName, $args) {
		$category =& $args[0];
		$plugins =& $args[1];
		switch ($category) {
			case 'viewableFiles':
				$this->import('BookreaderPlugin');
				$viewableFilesPlugin = new BookreaderPlugin($this->getName());
				$plugins[$viewableFilesPlugin->getSeq()][$viewableFilesPlugin->getPluginPath()] =& $viewableFilesPlugin;
				break;
		}
		return false;
	}

	/**
	 * Overwrite the bookSpecs and bookInfo templates
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function handleReaderTemplateInclude($hookName, $args) {
		$templateMgr =& $args[0];
		$params =& $args[1];
		$request = $this->getRequest();
		if (!isset($params['smarty_include_tpl_file'])) return false;
		switch ($params['smarty_include_tpl_file']) {
			case 'catalog/book/bookSpecs.tpl':
				$templateMgr->assign('pluginJSPath', $this->_getJSPath($request));
				$templateMgr->assign('templatePath', $this->getTemplatePath());
				$templateMgr->display($this->getTemplatePath() . 'cemogBookSpecs.tpl', 'text/html', 'TemplateManager::include');
				return true;
			case 'catalog/book/bookInfo.tpl':
				$templateMgr->display($this->getTemplatePath() . 'cemogBookInfo.tpl', 'text/html', 'TemplateManager::include');
				return true;
			case 'header/localnav.tpl':
				$templateMgr->display($this->getTemplatePath() . 'cemogLocalnav.tpl', 'text/html', 'TemplateManager::include');
				return true;
		}
		return false;
	}

	/**
	 * Hook callback: Handle requests.
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function handleTemplateDisplay($hookName, $args) {
		$templateMgr =& $args[0];
		$template =& $args[1];

		switch ($template) {
			case 'catalog/book/book.tpl':
				HookRegistry::register ('TemplateManager::include', array(&$this, 'handleReaderTemplateInclude'));
				break;
			case 'user/register.tpl':
				$templateMgr->display($this->getTemplatePath() . 'cemogRegister.tpl', 'text/html', 'TemplateManager::include');
				return true;
			case 'controllers/page/header.tpl':
				HookRegistry::register ('TemplateManager::include', array(&$this, 'handleReaderTemplateInclude'));
				break;
			case 'user/login.tpl':
				$templateMgr->assign('loginMessage', 'plugins.generic.cemog.login.loginMessage');
				break;
			case 'user/profile.tpl':
				$templateMgr->display($this->getTemplatePath() . 'cemogProfile.tpl', 'text/html', 'TemplateManager::display');
				return true;
				break;
			case 'about/aboutThisPublishingSystem.tpl':
				$templateMgr->display($this->getTemplatePath() . 'cemogAboutThisPublishingSystem.tpl', 'text/html', 'TemplateManager::display');
				return true;
				break;
		}
		return false;
	}

	/**
	 * Hook callback: Handle requests.
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function handleDeleteFile($hookName, $args) {
		$filePath =& $args[0];
		$path_parts = pathinfo($filePath);
		$fileMimeType = String::mime_content_type($filePath);
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		if ($fileManager->getDocumentType($fileMimeType) == DOCUMENT_TYPE_ZIP) {
			$zipDirName = $path_parts['dirname'].'/'.$path_parts['filename'];
			// remove directory
			$fileManager->rmtree($zipDirName);
		}
		return false;
	}

	/**
	 * Init new submission metadata
	 */
	function metadataInitData($hookName, $params) {
		$form =& $params[0];
		$submission =& $form->getSubmission();
		$cemogPrintOnDemandUrl = $submission->getData('cemogPrintOnDemandUrl');
		$cemogBuyBookUrl = $submission->getData('cemogOrderEbookUrl');
		$cemogBookReviews = $submission->getData('cemogBookReviews');
		$cemogBookPressMaterial = $submission->getData('cemogBookPressMaterial');
		$form->setData('cemogPrintOnDemandUrl', $cemogPrintOnDemandUrl);
		$form->setData('cemogOrderEbookUrl', $cemogBuyBookUrl);
		$form->setData('cemogBookReviews', $cemogBookReviews);
		$form->setData('cemogBookPressMaterial', $cemogBookPressMaterial);
		return false;
	}

	/**
	 * Insert new submission metadata form fields (print-on-demand and order ebook link)
	 */
	function metadataFieldsEdit($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
		$output .= $smarty->fetch($this->getTemplatePath() . 'additionalSubmissionMetadataFormFields.tpl');
		return false;
	}

	/**
	 * Read new submission metadata fields from the form
	 */
	function metadataFieldsReadUserVars($hookName, $params) {
		$userVars =& $params[1];
		$userVars[] = 'cemogPrintOnDemandUrl';
		$userVars[] = 'cemogOrderEbookUrl';
		$userVars[] = 'cemogBookReviews';
		$userVars[] = 'cemogBookPressMaterial';
		return false;
	}

	/**
	 * Save new submission metadata form fields (print-on-demand and buy link)
	 */
	function metadataFieldsSave($hookName, $params) {
		$submissionForm =& $params[0];
		$submission =& $params[1];
		$cemogPrintOnDemandUrl = $submissionForm->getData('cemogPrintOnDemandUrl');
		$cemogBuyBookUrl = $submissionForm->getData('cemogOrderEbookUrl');
		$cemogBookReviews = $submissionForm->getData('cemogBookReviews');
		$cemogBookPressMaterial = $submissionForm->getData('cemogBookPressMaterial');
		$submission->setData('cemogPrintOnDemandUrl', $cemogPrintOnDemandUrl);
		$submission->setData('cemogOrderEbookUrl', $cemogBuyBookUrl);
		$submission->setData('cemogBookReviews', $cemogBookReviews);
		$submission->setData('cemogBookPressMaterial', $cemogBookPressMaterial);
		return false;
	}

	/**
	 * Add new metadata to the submission
	 */
	function submissionGetFieldNames($hookName, $params) {
		$fields =& $params[1];
		$fields[] = 'cemogPrintOnDemandUrl';
		$fields[] = 'cemogOrderEbookUrl';
		$fields[] = 'cemogBookReviews';
		$fields[] = 'cemogBookPressMaterial';
		return false;
	}

	/**
	 * Read new registration fields (newsletter, terms of use) from the form
	 */
	function registrationFormReadUserVars($hookName, $params) {
		$userVars =& $params[1];
		$userVars[] = 'cemogNewsletter';
		$userVars[] = 'cemogTermsOfUse';
		return false;
	}

	/**
	 * Save new registration form fields (newsletter, terms of use)
	 */
	function registrationFormSave($hookName, $params) {
		$registrationForm =& $params[0];
		$user =& $params[1];
		$cemogNewsletter = $registrationForm->getData('cemogNewsletter');
		$cemogTermsOfUse = $registrationForm->getData('cemogTermsOfUse');
		$user->setData('cemogNewsletter', $cemogNewsletter);
		$user->setData('cemogTermsOfUse', $cemogTermsOfUse);
		return false;
	}

	/**
	 * Add new registration form fields to the user
	 */
	function registrationGetFieldNames($hookName, $params) {
		$fields =& $params[1];
		$fields[] = 'cemogNewsletter';
		$fields[] = 'cemogTermsOfUse';
		return false;
	}

	/**
	 * returns the base path for JS included in this plugin.
	 * @param $request PKPRequest Request object
	 * @return string Path to the JS file
	 */
	function _getJSPath($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js';
	}



}

?>
