<?php

/**
 * @file plugins/generic/cemog/BookreaderPlugin.inc.php
 *
 * Copyright (c) 2015 CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookreaderPlugin
 * @ingroup plugins_generic_cemog
 *
 * @brief Class for BookreaderPlugin plugin
 */

import('classes.plugins.ViewableFilePlugin');

class BookreaderPlugin extends ViewableFilePlugin {
	var $parentPluginName;

	/*
	 * Constructor
	 */
	function BookreaderPlugin($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.cemog.bookreader.name');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.cemog.bookreader.description');
	}

	/**
	 * @copydoc Plugin::getHideManagement()
	 */
	function getHideManagement() {
		return true;
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				// Bookreader images download/view
				HookRegistry::register('CatalogBookHandler::download', array($this, 'downloadCallback'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the CeMoG plugin
	 * @return object
	 */
	function &getCeMoGPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * @copydoc Plugin::getPluginPath()
	 */
	function getPluginPath() {
		$plugin =& $this->getCeMoGPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * @copydoc Plugin::getTemplatePath()
	 */
	function getTemplatePath() {
		$plugin =& $this->getCeMoGPlugin();
		return $plugin->getTemplatePath();
	}

	/**
	 * @copydoc LazyLoadPlugin::getTemplatePath()
	 */
	function getEnabled() {
		return true;
	}

	/**
	 * Callback for download function
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 * @return boolean
	 */
	function downloadCallback($hookName, $args) {
		$publishedMonograph =& $args[1];
		$submissionFile =& $args[2];
		$fileId = $submissionFile->getFileId();
		$revision = $submissionFile->getRevision();
		$request = $this->getRequest();
		$isImg = $request->getUserVar('img');
		if ($this->canHandle($publishedMonograph, $submissionFile) && $isImg) {
			import('plugins.generic.cemog.ImgFileManager');
			$monographFileManager = new ImgFileManager($publishedMonograph->getContextId(), $publishedMonograph->getId());
			$monographFileManager->downloadImgFile($fileId, $revision);
			exit();
		}
	}

	/**
	 * @copydoc ViewableFilePlugin::canHandle
	 */
	function canHandle($publishedMonograph, $submissionFile) {
		return ($submissionFile->getFileType() == 'application/zip');
	}

	/**
	 * copydoc ViewableFilePlugin::displaySubmissionFile
	 */
	function displaySubmissionFile($publishedMonograph, $submissionFile) {
		// unzip the file, if not yet
		$filePath = $submissionFile->getFilePath();
		$path_parts = pathinfo($filePath);
		$zipDirName = $path_parts['dirname'].'/'.$path_parts['filename'];
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		// is it already extracted -- does the directory exist
		if (!$fileManager->fileExists($zipDirName, 'dir')) {
			// make directory
			$fileManager->mkdir($zipDirName);
			// extract the zip
			$zip = new ZipArchive();
  			$res = $zip->open($filePath);
  			if ($res === TRUE) {
  				$zip->extractTo($zipDirName);
  				$zip->close();
  			}
		}
		// count the file in the directory
		$fileCount = count(scandir($zipDirName));

		$request = $this->getRequest();
		// Get Social media blocks enabled for the catalog
		$socialMediaDao = DAORegistry::getDAO('SocialMediaDAO');
		$socialMedia = $socialMediaDao->getEnabledForContextByContextId($publishedMonograph->getContextId());
		$blocks = array();
		while ($media = $socialMedia->next()) {
			$media->replaceCodeVars($publishedMonograph);
			$blocks[] = $media->getCode();
		}

		$templateMgr = TemplateManager::getManager($this->getRequest());
		$templateMgr->assign('pluginJSPath', $this->_getJSPath($request));
		$templateMgr->assign('pluginPath', $this->getPluginPath());
		$templateMgr->assign('fileCount', $fileCount-2);
		$templateMgr->assign('fileName', $submissionFile->getOriginalFileName());
		$templateMgr->assign_by_ref('blocks', $blocks);

		return parent::displaySubmissionFile($publishedMonograph, $submissionFile);
	}

	/**
	 * returns the base path for JS included in this plugin.
	 * @param $request PKPRequest
	 * @return string
	 */
	function _getJSPath($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js';
	}

}

?>
