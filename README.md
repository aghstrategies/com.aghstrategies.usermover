# com.aghstrategies.usermover

This extension creates a User Interface for reassigning CMS Users to different Contacts in CiviCRM.

## Features:

### Search For CMS Users

The "Search For CMS Users" allows administrators to search for Contacts by CMS User Id, Primary Email or Contact Name.

It can be found by going to the CiviCRM Admin Menu -> Search -> Custom Searches -> Usermover (com.aghstrategies.usermover)

![Search For CMS Users Screenshot](/images/search.png)

### Move User Action

This extension adds a "Move User" link to each row in the "Search For CMS Users" results AND to the Actions Dropdown on Contact's Summaries for contacts that are connected to a CMS User.

![Move User Action Screenshot](/images/MoveUser.png)

Clicking the "Move User" action takes you to the "User Mover" form where you can select a CiviCRM Contact and a User ID to connect the contact to.

![User Mover Form](/images/userMover.png)

On Submit of the form you will recieve a success message if everything went as expected and an error message if it did not.


## TODOS:
1. Increase security make sure only admin users can move users
2. Add help text to let user know more about the consequences of their actions
3. Add validation if you are overwritting a record
4. Add validation if you are going to create a double link
5. Add search by CMS name and column for CMS name
6. Add search by any email
7. fix search to actually filter by user id etc.
8. Add ability to delete connection
9. Add link to user land
