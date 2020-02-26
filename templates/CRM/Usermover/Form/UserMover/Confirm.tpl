{* HEADER *}

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

{* {foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach} *}

{* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT)

  <div>
    <span>{$form.favorite_color.label}</span>
    <span>{$form.favorite_color.html}</span>
  </div>

{* FOOTER *}
<table>
  <thead>
        <tr>
            <th>The CiviCRM Contact will be connected to</th>
            <th>CMS User</th>
            <th>and have the Unique Identifier</th>
        </tr>
    </thead>
    <tbody>
      {foreach from=$contacts item=contact}
       <tr>
           <td><a href="{$contact.contact_url}">{$contact.display_name}</a></td>
           <td>with two columns</td>
           <td>with two columns</td>
       </tr>
      {/foreach}
   </tbody>
</table>
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
