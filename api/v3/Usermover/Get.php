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
      $userOptions[$userInfo->data->ID] = [
        'uf_id' => $userInfo->data->ID,
        'id'  => $userInfo->data->ID,
        'label' => "{$userInfo->data->ID} ({$userInfo->data->user_login})",
      ];
    }
  }

  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($userOptions, $params, 'Usermover', 'get');
}

function _civicrm_api3_usermover_getlist_defaults($request) {
  return [
    'label_field' => 'label',
    'search_field' => 'label',
    'id_field' => 'id',
  ];
}

function _civicrm_api3_usermover_getlist_output($result, $request) {
  $data = [];
    if (!empty($result['values'])) {
      foreach ($result['values'] as $row) {
        $data[$row[$request['id_field']]] = array(
          'id' => $row[$request['id_field']],
          'label' => $row[$request['label_field']],
        );
      }
    }
    return $data;
}

/**
 * Get event list parameters.
 *
 * @see _civicrm_api3_generic_getlist_params
 *
 * @param array $request
 */
function _civicrm_api3_usermover_getlist_params(&$request) {
  $fieldsToReturn = ['id', 'label'];
  $request['params']['return'] = array_unique(array_merge($fieldsToReturn, $request['extra']));
}
