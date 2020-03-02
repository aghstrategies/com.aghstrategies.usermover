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
2. Test narrative on confirm screen
3. Add link to user land
4. Debug using entityref for custom usermover entity (getlist) cant get it to default.
6. Improve help text regarding the uf_name on the user mover page to be dependent on the combo selected (dont suggest copying the email to the contact UNLESS its not there...)
