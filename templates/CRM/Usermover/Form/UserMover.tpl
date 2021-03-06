{* HEADER *}

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
<p>Search for Connected Users using the <a href="{$searchUrl}">Search For CMS Users</a> form.</p>

{$userLand}

<div class='userMoverHelp help crm-message'></div>
{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    {if $elementName == 'copy_email'}
      {help id="copy-email-text" tplFile=$tplFile file="CRM/Usermover/Form/UserMover.hlp"}
    {/if}
    <div class="clear"></div>
  </div>
{/foreach}

{* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT)

  <div>
    <span>{$form.favorite_color.label}</span>
    <span>{$form.favorite_color.html}</span>
  </div>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
