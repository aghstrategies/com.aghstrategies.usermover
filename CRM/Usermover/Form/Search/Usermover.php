<?php
use CRM_Usermover_ExtensionUtil as E;

/**
 * A custom contact search
 */
class CRM_Usermover_Form_Search_Usermover extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(E::ts('Search For CMS Users'));

    // Add help text for users trying to create a new connection
    CRM_Core_Session::setStatus(E::ts('to Connect a CMS User to a CiviCRM Contact visit the <a href="%1">Reassign CMS User</a> form.', array(
      1 => CRM_Utils_System::url('civicrm/usermover'),
    )), E::ts('New User Connection?'), 'no-popup');

    $form->add('text',
      'contact_name',
      E::ts('Contact Name'),
      TRUE
    );

    $form->add('text',
      'email',
      E::ts('Contact Email'),
      TRUE
    );

    $form->add('text',
      'user_id',
      E::ts('CMS User ID'),
      TRUE
    );

    $form->add('text',
      'uf_name',
      E::ts('CiviCRM User Unique Identifier'),
      TRUE
    );

    $form->add('text',
      'user_name',
      E::ts('User Name'),
      TRUE
    );

    // Optionally define default search values
    $form->setDefaults(array(
      'contact_name' => '',
      'user_id' => '',
    ));
    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('contact_name', 'email', 'user_id', 'uf_name', 'user_name'));
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      E::ts('Contact Id') => 'contact_id',
      E::ts('Name') => 'sort_name',
      E::ts('Email') => 'email',
      E::ts('User ID') => 'user_id',
      E::ts('User Name') => 'user_name',
      E::ts('User Unique Identifer in CiviCRM') => 'uf_name',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, 'GROUP BY contact_a.id');
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      contact_a.id                      as contact_id,
      GROUP_CONCAT(civicrm_email.email) as email,
      contact_a.sort_name               as sort_name,
      civicrm_uf_match.uf_id            as user_id,
      civicrm_uf_match.uf_name          as uf_name
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    return "
      FROM civicrm_contact contact_a
      LEFT JOIN civicrm_email
        ON civicrm_email.contact_id = contact_a.id
      LEFT JOIN civicrm_uf_match
        ON civicrm_uf_match.contact_id = contact_a.id
    ";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $params = array();
    $where = "contact_a.id IS NOT NULL AND civicrm_uf_match.uf_id IS NOT NULL";

    $clause = array();
    $searchCriteria = [
      'name' => [
          'sql' => 'contact_name',
          'param' => 1,
          'clause' => "contact_a.display_name LIKE %1"
      ],
      'email' => [
        'sql' => 'email',
        'param' => 2,
        'clause' => "civicrm_email.email LIKE %2"
      ],
      'user_id' => [
        'sql' => 'user_id',
        'param' => 3,
        'clause' => "civicrm_uf_match.uf_id = %3"
      ],
      'uf_name' => [
        'sql' => 'uf_name',
        'param' => 4,
        'clause' => "civicrm_uf_match.uf_name LIKE %4"
      ],
      'user_name' => [
        'sql' => 'user_name',
        'param' => 5,
        'clause' => "civicrm_uf_match.uf_id = %5"
      ]
    ];

    foreach ($searchCriteria as $field => $fieldDetails) {
      $field = CRM_Utils_Array::value($fieldDetails['sql'],
        $this->_formValues
      );
      if ($field != NULL) {
        switch ($fieldDetails['sql']) {
          case 'user_id':
            $params[$fieldDetails['param']] = array($field, 'String');
            $clause[] = $fieldDetails['clause'];
            break;

          case 'user_name':
            $userInfo = CRM_Usermover_Form_UserMover::apiShortCut('Usermover', 'Getallusers', ['user_login' => $field]);
            $params[$fieldDetails['param']] = array($userInfo['values']['uf_id'], 'String');
            $clause[] = $fieldDetails['clause'];
            break;

          default:
            if (strpos($field, '%') === FALSE) {
              $field = "%{$field}%";
            }
            $params[$fieldDetails['param']] = array($field, 'String');
            $clause[] = $fieldDetails['clause'];
            break;
        }
      }
    }
    if (!empty($clause)) {
      $where .= ' AND ' . implode(' AND ', $clause);
    }
    return $this->whereClause($where, $params);
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    $label = $row['user_id'];
    $users = CRM_Usermover_Form_UserMover::apiShortCut('Usermover', 'Getallusers', []);
    if (!empty($users['values'][$row['user_id']])) {
      $row['user_name'] = $users['values'][$row['user_id']];
    }
    $href = CRM_Core_Config::singleton()->userSystem->getUserRecordUrl($row['contact_id']);
    $row['user_id'] = "<a href=$href>{$row['user_id']}</a>";
  }
}
