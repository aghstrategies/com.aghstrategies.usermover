<?php
use CRM_Usermover_ExtensionUtil as E;

/**
 * UserMover.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_user_mover_Get_spec(&$spec) {

  $spec['user_login'] = [
    'api.required' => 0,
    'name' => 'user_login',
    'title' => 'User login details',
    'description' => 'User id and name',
  ];

  $spec['uf_id'] = [
    'api.required' => 0,
    'name' => 'uf_id',
    'title' => 'User ID',
    'description' => 'User ID',
    'api.aliases' => ['id'],
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['pretty_print'] = [
    'api.required' => 0,
    'name' => 'pretty_print',
    'title' => 'Print as label',
    'description' => 'Formats as label',
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
}

/**
 * UserMover.Get API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_user_mover_Get($params) {
  $config = CRM_Core_Config::singleton();
  $userOptions = getAvailableUsers();
  $usersToReturn = [];

  // If searching for user by id
  if (!empty($params['uf_id'])) {
    if (!empty($userOptions[$params['uf_id']])) {
      $usersToReturn[$params['uf_id']] = $userOptions[$params['uf_id']];
    }
  }

  // If searching by user_login (could be id or username)
  elseif (!empty($params['user_login'])) {
    foreach ($userOptions as $id => $userInfo) {
      if (stripos($userInfo['user_login'], $params['user_login']) !== FALSE || $id == $params['user_login']) {
        $usersToReturn[$userInfo['id']] = $userInfo;
      }
    }
  }

  // no search filters
  else {
    $usersToReturn = $userOptions;
  }

  // If pretty_print reformat results id => "id / name"
  if (!empty($usersToReturn) && $params['pretty_print'] == 1) {
    foreach ($usersToReturn as $id => $details) {
      $usersToReturn[$id] = $details['label'];
    }
  }

  return civicrm_api3_create_success($usersToReturn, $params, 'UserMover', 'Get');
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
        'uf_id' => $userInfo->data->ID,
        'user_login' => $userInfo->data->user_login,
        'uf_name' => $userInfo->data->user_email,
        'label' => "{$userInfo->data->ID} ({$userInfo->data->user_login})",
        'user_url' => $config->userFrameworkBaseURL . "wp-admin/user-edit.php?user_id=" . $userInfo->data->ID,
      ];
    }
  }

  // Drupal
  elseif ($config->userSystem->is_drupal) {
    $allUsers = entity_load('user');
    foreach ($allUsers as $key => $userInfo) {
      if ($userInfo->uid > 0) {
        $userOptions[$userInfo->uid] = [
          'id' => $userInfo->uid,
          'uf_id' => $userInfo->uid,
          'user_login' => $userInfo->name,
          'uf_name' => $userInfo->mail,
          'label' => "{$userInfo->uid} ({$userInfo->name})",
          'user_url' => $config->userFrameworkBaseURL . "user/" . $userInfo->uid,
        ];
      }
    }
  }

  // TODO Joomla
  // TODO Backdrop

  return $userOptions;
}

function _civicrm_api3_user_mover_getlist_defaults($request) {
  return [
    'label_field' => 'label',
    'search_field' => 'label',
    'id_field' => 'id',
  ];
}

// function _civicrm_api3_user_mover_getlist_output($result, $request) {
//   $data = [];
//     if (!empty($result['values'])) {
//       foreach ($result['values'] as $row) {
//         $data[$row[$request['id_field']]] = array(
//           'id' => $row[$request['id_field']],
//           'label' => $row[$request['label_field']],
//         );
//       }
//     }
//     return $data;
// }

/**
 * Get usermover list parameters.
 *
 * @see _civicrm_api3_generic_getlist_params
 *
 * @param array $request
 */
function _civicrm_api3_user_mover_getlist_params(&$request) {
  $fieldsToReturn = ['id', 'label'];
  $request['params']['return'] = array_unique(array_merge($fieldsToReturn, $request['extra']));
}
