<?php
use CRM_Usermover_ExtensionUtil as E;

/**
 * Usermover.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_usermover_get($params) {
  $userOptions = [];
  $config = CRM_Core_Config::singleton();
  if ($config->userSystem->is_wordpress) {
    $allUsers = get_users();
    foreach ($allUsers as $key => $userInfo) {
      $userOptions[] = [
        'id' => $userInfo->data->ID,
        'label' => $userInfo->data->user_login,
      ];
    }
  }

  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($userOptions, $params, 'Usermover', 'get');
}
