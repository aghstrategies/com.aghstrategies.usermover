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

  // If searching based on label like
  if (!empty($params['label']['LIKE'])) {
    foreach ($userOptions as $id => $userInfo) {
      if (preg_match($params['label']['LIKE'], $userInfo['label'])) {
        $usersToReturn[] = $userInfo;
      }
    }
  }
  elseif (!empty($params['uf_id']) && (array_key_exists($params['user_id'], $userOptions) && !empty($userOptions[$params['uf_id']]))) {
    $usersToReturn[] = $userOptions[$params['uf_id']];
  }
  // Otherwise return everyone
  else {
    $usersToReturn = $userOptions;
  }

  // If pretty_print reformat results id => "id / name"
  if (!empty($usersToReturn) && isset($params['pretty_print']) && $params['pretty_print'] == 1) {
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
  if ($config->userFramework == 'WordPress') {
    $allUsers = get_users(['fields' => ['ID', 'user_login', 'user_email']]);
    foreach ($allUsers as $key => $userInfo) {
      $userOptions[$userInfo->ID] = [
        'id' => $userInfo->ID,
        'uf_id' => $userInfo->ID,
        'user_login' => $userInfo->user_login,
        'uf_name' => $userInfo->user_email,
        'label' => "{$userInfo->ID} ({$userInfo->user_login})",
        'user_url' => $config->userFrameworkBaseURL . "wp-admin/user-edit.php?user_id=" . $userInfo->ID,
      ];
    }
  }  
  // Joomla
  elseif (get_class($config->userSystem) == 'CRM_Utils_System_Joomla') {
    $id = 'id';
    $mail = 'email';
    $name = 'name';

    $JUserTable = JTable::getInstance('User', 'JTable');

    $db = $JUserTable->getDbo();
    $query = $db->getQuery(TRUE);
    $query->select($id . ', ' . $mail . ', ' . $name);
    $query->from($JUserTable->getTableName());
    $query->where($mail != '');

    $db->setQuery($query);
    $allUsers = $db->loadObjectList();
    foreach ($allUsers as $userInfo) {
      $userOptions[$userInfo->id] = [
        'id' => $userInfo->id,
        'uf_id' => $userInfo->id,
        'user_login' => $userInfo->name,
        'uf_name' => $userInfo->email,
        'label' => "{$userInfo->id} ({$userInfo->name})",
        'user_url' => $config->userFrameworkBaseURL . "administrator/index.php?option=com_users&view=user&layout=edit&id=" . $userInfo->id,
      ];
    }

  }
  // Drupal or Backdrop
  elseif ($config->userFramework == 'Drupal' || $config->userFramework == 'Drupal8') {
    $allUsers = db_query("SELECT uid, mail, name FROM {users} where mail != ''");
    foreach ($allUsers as $userInfo) {
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

  return $userOptions;
}

function _civicrm_api3_user_mover_getlist_defaults($request) {
  return [
    'label_field' => 'label',
    'search_field' => 'label',
    'id_field' => 'id',
  ];
}
