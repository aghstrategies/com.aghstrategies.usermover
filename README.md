# com.aghstrategies.usermover

This Extension:
 + Creates a user interface "Edit CMS User Connection" for assigning CMS Users to Contacts in CiviCRM.
 + Creates a custom search "Search For CMS Users (com.aghstrategies.usermover)" for searching CiviCRM Contacts by CMS User.  

See more details on these feature below.

## Features:

### Search For CMS Users

The "Search For CMS Users" allows administrators to search for Contacts WITH CMS Users by:
+ "Contact Name"
+ "Contact Email"
+ "CMS ID"
+ "CMS User Name"
+ "CiviCRM User Unique Identifier" (uf_name)

It can be found by going to the CiviCRM Admin Menu -> Search -> Custom Searches -> Search For CMS Users (com.aghstrategies.usermover)

![Search For CMS Users Screenshot](/images/searchScreen.png)

### Edit CMS User Connection

Adds a form "Edit CMS User Connection" with the following fields:

+ CiviCRM Contact: This is the CiviCRM contact being updated
+ CMS User: This is the CMS user to connect the CiviCRM Contact to (Select -No User- to disconnect the CiviCRM Contact from the CMS User).
+ The email address associated with this user (EMAIL) does not exist on the selected contact. Check this box to copy this email address to the CiviCRM Contact: CiviCRM assumes that the email on the user is also on the contact. This checkbox gives you the opportunity to copy the email address on the user to the contact. It will only show up if the email address on the user is not already on the contact.

On Submit of the form you will be taken to a confirm screen where the consequences of the edits to the CMS User Connection will be displayed (EX: orphaned users and/or contacts... added email addresses etc.).

![Edit CMS User Connection](/images/editCmsUser.png)


#### How to access the "Edit CMS User Connection" form?

This extension adds a "Reassign CMS User" (you must have the permission "Administer CiviCRM" to see the "Reassign CMS User" links) link to:
+ each row in the "Search For CMS Users" results AND
+ to the Actions Dropdown on Contact's Summaries for contacts that are connected to a CMS User.

![Move User Action Screenshot](/images/reassign.png)

Additionally, A link to the form is displayed at the top of the "Search For CMS Users" form.

Clicking the "Edit CMS User Connection" action takes you to the "User Mover" form where you can select a CiviCRM Contact and a User ID to connect the contact to.

## Wishlist features:

+ Move confirm code to js and get rid of the confirm page
