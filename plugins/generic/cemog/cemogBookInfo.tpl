{**
 * plugins/generic/cemog/cemogBookInfo.tpl
 *
 * Copyright (c) CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the information pane of a public-facing book view in the catalog.
 *}

<script type="text/javascript">
	// Attach the tab handler.
	$(function() {ldelim}
		$('#bookInfoTabs').pkpHandler(
			'$.pkp.controllers.TabHandler',
			{ldelim}
				notScrollable: true
			{rdelim}
		);
	{rdelim});
</script>

<div class="bookInfo">
	<div class="bookInfoHeader">
		<h3>{$publishedMonograph->getLocalizedFullTitle()|strip_unsafe_html}</h3>
		<div class="authorName">{$publishedMonograph->getAuthorString()}</div>
	</div>

	<div id="bookInfoTabs">
		<ul>
			<li><a href="#abstractTab">{translate key="submission.synopsis"}</a></li>
			{* content tab removed *}
			{* download tab removed *}
			{call_hook|assign:"sharingCode" name="Templates::Catalog::Book::BookInfo::Sharing"}
			{if !is_null($sharingCode) || !empty($blocks)}
				<li><a href="#sharingTab">{translate key="submission.sharing"}</a></li>
			{/if}
		</ul>

		<div id="abstractTab">
			{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}

			{assign var=authors value=$publishedMonograph->getAuthors()}
			{foreach from=$authors item=author}
				{if $author->getIncludeInBrowse()}
					<p>{translate key="catalog.aboutTheAuthor" roleName=$author->getLocalizedUserGroupName()}: <strong>{$author->getFullName()}</strong></p>
					{assign var=biography value=$author->getLocalizedBiography()|strip_unsafe_html}
					{if $biography != ''}{$biography}{else}{translate key="catalog.noBioInfo"}{/if}
				{/if}
			{/foreach}
		</div>

		{* content tab removed *}
		{* download tab removed *}

		{if !is_null($sharingCode) || !empty($blocks)}
			<div id="sharingTab">
				{$sharingCode}
				{foreach from=$blocks item=block key=blockKey}
					<div id="socialMediaBlock{$blockKey|escape}" class="pkp_helpers_clear">
						{$block}
					</div>
				{/foreach}
			</div>
		{/if}
	</div>
</div>
