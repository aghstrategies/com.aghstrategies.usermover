# com.aghstrategies.usermover

This extension creates a User Interface for reassigning CMS Users to different Contacts in CiviCRM.

## Features:

### Search For CMS Users

The "Search For CMS Users" allows administrators to search for Contacts by CMS User Id, Primary Email or Contact Name.

It can be found by going to the CiviCRM Admin Menu -> Search -> Custom Searches -> Usermover (com.aghstrategies.usermover)

![Search For CMS Users Screenshot](/images/search.png)

### Move User Action

This extension adds a "Reassign CMS User" link to each row in the "Search For CMS Users" results AND to the Actions Dropdown on Contact's Summaries for contacts that are connected to a CMS User for users logged in with the permission "Administer CiviCRM".

![Move User Action Screenshot](/images/MoveUser.png)

Clicking the "Move User" action takes you to the "User Mover" form where you can select a CiviCRM Contact and a User ID to connect the contact to.

![User Mover Form](/images/userMover.png)

On Submit of the form you will receive a success message if everything went as expected and an error message if it did not.

## TODOS:

1. Write get all users for drupal and joomla
2. Improve narrative on confirm screen
3. Add link to user land
4. Debug using entityref for custom usermover entity (getlist) cant get it to default.
5. Display orphaned users on confirm screen
6. Ensure that the email address attached to the user is on the CiviCRM contact
7. Uf_name is the unique email address. In wordpress this is the email on the user...which should be on the contact. I think we should make this field. Andrew thinks we should remove this field from the ui... but I think we should keep it and make it more helpful.. autopopulate to the email on the user... and tell users that editing this email address will result in the email address on the user being updated AND the email being added to the civicrm contact if it is not there already.
