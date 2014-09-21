<?php

/**
 * @file plugins/generic/cemog/CeMoGPlugin.inc.php
 *
 * Copyright (c) CeDiS, Freie Universität Berlin
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
	 * Register the plugin, if enabled; note that this plugin
	 * runs under both Journal and Site contexts.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				HookRegistry::register ('TemplateManager::display', array(&$this, 'handleTemplateDisplay'));
			}
			return true;
		}
		return false;
	}




	/**
	 * Intercept the article comments template to add referral content
	 */
	function handleReaderTemplateInclude($hookName, $args) {
		$templateMgr =& $args[0];
		$params =& $args[1];
		if (!isset($params['smarty_include_tpl_file'])) return false;
		switch ($params['smarty_include_tpl_file']) {
			case 'catalog/book/bookSpecs.tpl':
				$templateMgr->display($this->getTemplatePath() . 'cemogBookSpecs.tpl', 'text/html');
				return true;
			case 'catalog/book/bookInfo.tpl':
				$templateMgr->display($this->getTemplatePath() . 'cemogBookInfo.tpl', 'text/html');
				return true;
		}
		return false;
	}

	/**
	 * Hook callback: Handle requests.
	 */
	function handleTemplateDisplay($hookName, $args) {
		$templateMgr =& $args[0];
		$template =& $args[1];

		switch ($template) {
			case 'catalog/book/book.tpl':
				HookRegistry::register ('TemplateManager::include', array(&$this, 'handleReaderTemplateInclude'));
				break;
			/*
			case 'catalog/book/bookInfo.tpl':
				$templateMgr->fetch($this->getTemplatePath() . 'cemogBookInfo.tpl', 'text/html');
				break;
			*/
		}
		return false;
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.referral.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.referral.description');
	}

}

?>
