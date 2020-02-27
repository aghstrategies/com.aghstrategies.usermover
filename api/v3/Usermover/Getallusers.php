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
  $userOptions = [];
  $config = CRM_Core_Config::singleton();
  if ($config->userSystem->is_wordpress) {
    $allUsers = get_users();
    foreach ($allUsers as $key => $userInfo) {
      if (!empty($params['user_login'])) {
        if (strpos($params['user_login'], $userInfo->data->user_login) !== FALSE || $userInfo->data->ID == $params['user_login']) {
          $userOptions[$userInfo->data->ID] = "{$userInfo->data->ID} / {$userInfo->data->user_login}";
        }
      }
      elseif (empty($params['user_login'])) {
        $userOptions[$userInfo->data->ID] = $userInfo->data->user_login;
      }
      if ($params['pretty_print'] == 1) {
        $userOptions[$userInfo->data->ID] = "{$userInfo->data->ID} / {$userInfo->data->user_login}";
      }
    }
  }
  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($userOptions, $params, 'Usermover', 'Getallusers');
}


/**
 * Get All users in the CMS
 * @param  array $userOptions array keyed id => id/user name
 * @return array              $userOptions array keyed id => id/user name
 */
function getAvailableUsers() {
  $userOptions = [];
  $config = CRM_Core_Config::singleton();
  if ($config->userSystem->is_wordpress) {
    $allUsers = get_users();
    foreach ($allUsers as $key => $userInfo) {
      $userOptions[$userInfo->data->ID] = $userInfo->data->user_login;
    }
  }
  // TODO get all users for other cms's
  return $userOptions;
}
