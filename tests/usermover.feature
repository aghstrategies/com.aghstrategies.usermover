FEATURE: User Mover

SCENARIO: Moving a CMS User from CiviCRM Contact A to CiviCRM Contact B

WHEN a logged in administrator goes to CiviCRM Contact A's Summary Screen
AND CiviCRM Contact A is connected to CMS User ID 2
AND they Click the Actions button at the top of the Screen
AND they select "Move User"
AND they are taken to the "User Mover" form
AND they change the "Select Contact to Connect User to" form "Contact A" to "Contact B"
AND they click Submit
THEN "Contact B" should be connected to user ID 2
AND "Contact A" Should no longer be connected to a user ID
