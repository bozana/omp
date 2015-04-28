{**
 * plugins/generic/cemog/cemogRegister.tpl
 *
 * Copyright (c) 2015 CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User registration form.
 *}
{strip}
{assign var="pageTitle" value="user.register"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#register').pkpHandler('$.pkp.controllers.form.FormHandler',
			{ldelim}
				fetchUsernameSuggestionUrl: '{url|escape:"javascript" router=$smarty.const.ROUTE_COMPONENT component="api.user.UserApiHandler" op="suggestUsername" firstName="FIRST_NAME_DUMMY" lastName="LAST_NAME_DUMMY" escape=false}',
				usernameSuggestionTextAlert: '{translate key="grid.user.mustProvideName"}'
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="register" method="post" action="{url op="registerUser"}">

{url|assign:"loginUrl" page="login"}
{translate key="plugins.generic.cemog.register.registerMessage" loginUrl=$loginUrl}

{if $source}
	<input type="hidden" name="source" value="{$source|escape}" />
{/if}

{fbvFormArea id="registration"}

	{if !$implicitAuth}

<div id="userFormCompactLeftContainer" class="pkp_helpers_clear">
	{fbvFormArea id="userFormCompactLeft"}
		{fbvFormSection title="user.name"}
			{fbvElement type="text" label="user.firstName" required="true" id="firstName" value=$firstName maxlength="40" inline=true size=$fbvStyles.size.SMALL}
			{fbvElement type="text" label="user.lastName" required="true" id="lastName" value=$lastName maxlength="40" inline=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}

		{if !$userId}{capture assign="usernameInstruction"}{translate key="user.register.usernameRestriction"}{/capture}{/if}
		{fbvFormSection for="username" description=$usernameInstruction translate=false}
			{if !$userId}
				{fbvElement type="text" label="user.username" id="username" required="true" value=$username maxlength="32" inline=true size=$fbvStyles.size.MEDIUM}
			{else}
				{fbvFormSection title="user.username" suppressId="true"}
					{$username|escape}
				{/fbvFormSection}
			{/if}
		{/fbvFormSection}

		{fbvFormArea id="emailArea" class="border" title="user.email"}
			{fbvFormSection}
				{fbvElement type="text" label="user.email" id="email" value=$email size=$fbvStyles.size.MEDIUM required=true inline=true}
				{fbvElement type="text" label="user.confirmEmail" id="confirmEmail" value=$confirmEmail required=true size=$fbvStyles.size.MEDIUM inline=true}
			{/fbvFormSection}
			{if $privacyStatement}<a class="action" href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>{/if}
		{/fbvFormArea}

		{if $userId}{capture assign="passwordInstruction"}{translate key="user.profile.leavePasswordBlank"} {translate key="user.register.passwordLengthRestriction" length=$minPasswordLength}{/capture}{/if}
		{fbvFormArea id="passwordSection" class="border" title="user.password"}
			{fbvFormSection for="password" class="border" description=$passwordInstruction translate=false}
				{fbvElement type="text" label="user.password" required="true" name="password" id="password" password="true" value=$password maxlength="32" inline=true size=$fbvStyles.size.MEDIUM}
				{if !$disablePasswordRepeatSection}
					{fbvElement type="text" label="user.repeatPassword" required="true" name="password2" id="password2" password="true" value=$password2 maxlength="32" inline=true size=$fbvStyles.size.MEDIUM}
				{/if}
			{/fbvFormSection}
		{/fbvFormArea}
	{/fbvFormArea}
</div>

	{/if} {* !$implicitAuth *}

	{if !$implicitAuth && !$existingUser}
		{fbvFormSection label="user.sendPassword" list=true}
			{if $sendPassword}
				{fbvElement type="checkbox" id="sendPassword" value="1" label="plugins.generic.cemog.register.sendPassword" checked="checked"}
			{else}
				{fbvElement type="checkbox" id="sendPassword" value="1" label="plugins.generic.cemog.register.sendPassword"}
			{/if}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{* Newsletter *}
			{if $cemogNewsletter}
				{fbvElement type="checkbox" id="cemogNewsletter" value="1" label="plugins.generic.cemog.register.newsletter" checked="checked"}
			{else}
				{fbvElement type="checkbox" id="cemogNewsletter" value="1" label="plugins.generic.cemog.register.newsletter"}
			{/if}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{* TermsOfUse *}
			{url|assign:"termsUrl" page="nutzungsbedingungen"}
			{if $cemogTermsOfUse}
				{fbvElement type="checkbox" id="cemogTermsOfUse" value="1" label="plugins.generic.cemog.register.termsOfUse" checked="checked" required="true"}
			{else}
				{fbvElement type="checkbox" id="cemogTermsOfUse" value="1" label="plugins.generic.cemog.register.termsOfUse" required="true"}
			{/if}
		{/fbvFormSection}
	{/if}

	{if !$implicitAuth && !$existingUser && $captchaEnabled}
		<li>
		{fieldLabel name="captcha" required=true key="common.captchaField" class="desc"}
		<span>
			{$reCaptchaHtml}
		</span>
		</li>
	{/if}

{/fbvFormArea}
{url|assign:"url" page="index" escape=false}
{fbvFormButtons submitText="user.register" cancelUrl=$url}

{if ! $implicitAuth}
	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
{/if}{* !$implicitAuth *}

<div id="privacyStatement">
{if $privacyStatement}
	<h3>{translate key="user.register.privacyStatement"}</h3>
	<p>{$privacyStatement|nl2br}</p>
{/if}
</div>

</form>
{include file="common/footer.tpl"}
