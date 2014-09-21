{**
 * plugins/generic/cemog/cemogBookInfo.tpl
 *
 * Copyright (c) CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the book specs portion of the public-facing book view.
 *}

<div class="bookSpecs">
	{assign var=coverImage value=$publishedMonograph->getCoverImage()}
	<a title="{$publishedMonograph->getLocalizedFullTitle()|strip_tags|escape}" href="{$bookImageLinkUrl}"><img class="pkp_helpers_container_center" alt="{$publishedMonograph->getLocalizedFullTitle()|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="catalog" submissionId=$publishedMonograph->getId()}" /></a>
	<div id="bookAccordion">
		<h3>{translate key="catalog.publicationInfo"}</h3>
		<div class="publicationInfo">
			<div class="dateAdded">{translate key="catalog.dateAdded" dateAdded=$publishedMonograph->getDatePublished()|date_format:$dateFormatShort}</div>
			{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats(true)}
			{if count($publicationFormats) === 1}
				{foreach from=$publicationFormats item="publicationFormat"}
					{if $publicationFormat->getIsApproved()}
						{include file="catalog/book/bookPublicationFormatInfo.tpl" publicationFormat=$publicationFormat availableFiles=$availableFiles}
					{/if}
				{/foreach}
			{/if}
			{if $series}
				<div class="seriesLink">{translate key="series.series"}: <a href="{url page="catalog" op="series" path=$series->getPath()}">{$series->getLocalizedFullTitle()}</a></div>
			{/if}

		</div>

		{if $availableFiles|@count != 0}
			{foreach from=$publicationFormats item=publicationFormat}
				{assign var=publicationFormatId value=$publicationFormat->getId()}
				{if $publicationFormat->getIsAvailable() && $availableFiles[$publicationFormatId]}
					{foreach from=$availableFiles[$publicationFormatId] item=availableFile}{* There will be at most one of these *}
						<div class="publicationFormatLink">
							{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
								{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
							{elsif $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_ZIP}
								{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
							{else}
								{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
							{/if}
							<a href="{$downloadUrl}">{$availableFile->getLocalizedName()|escape}</a>
						</div>
					{/foreach}
				{/if}
			{/foreach}
		{/if}

		{assign var=categories value=$publishedMonograph->getCategories()}
		{if !$categories->wasEmpty()}
			<h3>{translate key="catalog.relatedCategories}</h3>
			<ul class="relatedCategories">
				{iterate from=categories item=category}
					<li><a href="{url op="category" path=$category->getPath()}">{$category->getLocalizedTitle()|strip_unsafe_html}</a></li>
				{/iterate}{* categories *}
			</ul>
		{/if}{* !$categories->wasEmpty() *}
	</div>
</div>
