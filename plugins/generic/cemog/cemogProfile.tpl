{**
 * plugins/generic/cemog/cemogProfile.tpl
 *
 * Copyright (c) 2015 CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User profile page.
 *}
{include file="common/header.tpl" pageTitle="user.profile.publicProfile"}

<script type="text/javascript">
        // Attach the JS file tab handler.
        $(function() {ldelim}
                $('#userProfileTabs').pkpHandler('$.pkp.controllers.TabHandler');
        {rdelim});
</script>
<div id="userProfileTabs">
        <ul>
                <li>
			<a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.user.ProfileTabHandler" op="profile"}">{translate key="user.profile"}</a>
		</li>
	</ul>
</div>

{include file="common/footer.tpl"}
