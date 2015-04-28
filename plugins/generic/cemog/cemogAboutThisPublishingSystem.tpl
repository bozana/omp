{**
 * plugins/generic/cemog/cemogAboutThisPublishingSystem.tpl
 *
 * Copyright (c) 2015 CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / About This Publishing System.
 *
 * TODO: Display the image describing the system.
 *}
{strip}
{assign var="pageTitle" value="about.aboutThisPublishingSystem"}
{include file="common/header.tpl"}
{/strip}

<p>
{if $currentPress}
	{translate key="plugins.generic.cemog.about.aboutOMPSite" ompVersion=$appVersion}
{else}
	{translate key="plugins.generic.cemog.about.aboutOMPSite" ompVersion=$appVersion}
{/if}
</p>

{include file="common/footer.tpl"}
