{**
 * plugins/generic/cemog/bookreaderLink.tpl
 *
 * Copyright (c) 2015 CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Link to embedded viewing of a Bookreader galley.
 *}


{url|assign:"imgBaseUrl" op="download" path=$publishedMonograph->getId()|to_array:$submissionFile->getAssocId():$submissionFile->getFileIdAndRevision() escape=false}{* Assoc ID is publication format *}
{url|assign:"bookUrl" page="catalog" op="book" path=$publishedMonograph->getId() escape=false}
{assign var=bookTitle value=$publishedMonograph->getLocalizedFullTitle()|strip_unsafe_html}
{assign var=bookAuthor value=$publishedMonograph->getAuthorString()}
{call_hook|assign:"sharingCode" name="Templates::Catalog::Book::BookInfo::Sharing"}

<script type="text/javascript"><!--{literal}
$(window).load(function(){
//$(window).on("load", function(){

	var html = $("#BookReaderWrapper").html();
	document.write("<html><head>");
	document.writeln('<link rel="stylesheet" type="text/css" href="{/literal}{$pluginJSPath}{literal}/bookreader/BookReader.css"/>');
	document.writeln('<link rel="stylesheet" type="text/css" href="{/literal}{$baseUrl}/{$pluginPath}{literal}/BookReaderCeMoG.css"/>');

	document.writeln(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/jquery.min.js' type='text/javascript'%3E%3C/script%3E"));
	document.writeln(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js' type='text/javascript'%3E%3C/script%3E"));

	document.writeln(unescape("%3Cscript src='{/literal}{$pluginJSPath}{literal}/bookreader/dragscrollable.js' type='text/javascript'%3E%3C/script%3E"));
	document.writeln(unescape("%3Cscript src='{/literal}{$pluginJSPath}{literal}/bookreader/jquery.colorbox-min.js' type='text/javascript'%3E%3C/script%3E"));
	document.writeln(unescape("%3Cscript src='{/literal}{$pluginJSPath}{literal}/bookreader/jquery.ui.ipad.js' type='text/javascript'%3E%3C/script%3E"));
	document.writeln(unescape("%3Cscript src='{/literal}{$pluginJSPath}{literal}/bookreader/jquery.bt.min.js' type='text/javascript'%3E%3C/script%3E"));

	document.writeln(unescape("%3Cscript src='{/literal}{$pluginJSPath}{literal}/bookreader/BookReader.js' type='text/javascript'%3E%3C/script%3E"));

	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var imgBaseUrl = '{/literal}{$imgBaseUrl|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookUrl = '{/literal}{$bookUrl|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookTitle = '{/literal}{$bookTitle|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookAuthor = '{/literal}{$bookAuthor|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var baseUrl = '{/literal}{$baseUrl|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var numLeafs = '{/literal}{$fileCount|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));

	document.writeln(unescape("%3Cscript src='{/literal}{$pluginJSPath}{literal}/inlineBookReader.js' type='text/javascript'%3E%3C/script%3E"));
	document.write ("</head><body>");
	document.writeln(html);
	document.writeln("</body></html>");
	document.close();

});
// -->{/literal}
</script>
{translate|assign:"noPluginText" key="plugins.generic.cemog.bookreader.pluginMissing"}
<div id="BookReaderWrapper">
	<div id="BookReader">
		<noscript>
		<p>
		    <div id="pluginMissing">{$noPluginText|escape:"javascript"}</div>
			The BookReader requires JavaScript to be enabled. Please check that your browser supports JavaScript and
			that it is enabled in the browser settings.
		</p>
		</noscript>
	</div>
	<div id="BookReaderInfoAbstract" style="display:none">{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}</div>
	<div id="BookReaderShare" style="display:none">

		{if !is_null($sharingCode) || !empty($blocks)}
			{$sharingCode}
			{foreach from=$blocks item=block key=blockKey}
				<div id="socialMediaBlock{$blockKey|escape}" class="pkp_helpers_clear">
					{$block}
				</div>
			{/foreach}
		{/if}
	</div>
</div>


