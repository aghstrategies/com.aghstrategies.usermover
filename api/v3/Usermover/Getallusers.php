<?php
use CRM_Usermover_ExtensionUtil as E;

/**
 * Usermover.Getallusers API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_usermover_Getallusers($params) {
  $config = CRM_Core_Config::singleton();
  $userOptions = getAvailableUsers();
  $usersToReturn = [];

  foreach ($userOptions as $key => $userInfo) {
    if (!empty($params['user_login'])) {
      if (stripos($userInfo['user_login'], $params['user_login']) !== FALSE || $key == $params['user_login']) {
        $usersToReturn[$userInfo['id']] = "{$userInfo['id']} / {$userInfo['user_login']}";
      }
    }
    else {
      $usersToReturn[$userInfo['id']] = "{$userInfo['id']} / {$userInfo['user_login']}";
    }
  }

  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($usersToReturn, $params, 'Usermover', 'Getallusers');
}


/**
 * Get All users in the CMS
 * @param  array $userOptions array keyed id => id/user name
 * @return array              $userOptions array keyed id => id/user name
 */
function getAvailableUsers() {
  $userOptions = [];
  $config = CRM_Core_Config::singleton();

  // Wordpress
  if ($config->userSystem->is_wordpress) {
    $allUsers = get_users();
    foreach ($allUsers as $key => $userInfo) {
      $userOptions[$userInfo->data->ID] = [
        'id' => $userInfo->data->ID,
        'user_login' => $userInfo->data->user_login,
      ];
    }
  }

  // TODO Drupal
  // TODO Joomla
  // TODO Backdrop

  return $userOptions;
}
