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

    // Optionally define default search values
    $form->setDefaults(array(
      'contact_name' => '',
      'user_id' => '',
    ));

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('contact_name', 'email', 'user_id'));
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
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      contact_a.id           as contact_id,
      civicrm_email.email    as email,
      contact_a.sort_name    as sort_name,
      civicrm_uf_match.uf_id as user_id
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
        ON ( civicrm_email.contact_id = contact_a.id AND civicrm_email.is_primary = 1 )
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
    $where = "contact_a.id IS NOT NULL AND civicrm_uf_match.uf_id";

    $count  = 1;
    $clause = array();
    $name   = CRM_Utils_Array::value('contact_name',
      $this->_formValues
    );
    if ($name != NULL) {
      if (strpos($name, '%') === FALSE) {
        $name = "%{$name}%";
      }
      $params[$count] = array($name, 'String');
      $clause[] = "contact_a.display_name LIKE %{$count}";
      $count++;
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
  // function alterRow(&$row) {
  //   $row['sort_name'] .= ' ( altered )';
  // }
}
