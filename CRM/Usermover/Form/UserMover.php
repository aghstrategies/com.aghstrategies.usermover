<?php

use CRM_Usermover_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Usermover_Form_UserMover extends CRM_Core_Form {
  public function buildQuickForm() {
    $defaults = [];

    $csid = self::apiShortCut('CustomSearch', 'getvalue', [
      'name' => "CRM_Usermover_Form_Search_Usermover",
      'return' => 'value',
    ]);

    if (!empty($csid)) {
      CRM_Core_Session::setStatus(E::ts('Search for Connected Users using the <a href="%1">Search For CMS Users</a> form.', array(
        1 => CRM_Utils_System::url('civicrm/contact/search/custom', "reset=1&csid={$csid}"),
      )), E::ts('Need to Search?'), 'no-popup');
    }

    CRM_Core_Resources::singleton()->addScriptFile('com.aghstrategies.usermover', 'js/userMover.js');

    // If there is a UFMatch id in the url use that
    if (!empty($_GET['id'])) {
      $existingRecord = self::apiShortCut('UFMatch', 'getsingle', ['id' => $_GET['id']]);
      if (!empty($existingRecord['contact_id'])) {
        $defaults['contact_id'] = $existingRecord['contact_id'];
        $defaults['uf_id'] = $existingRecord['uf_id'];
        $defaults['uf_name'] = $existingRecord['uf_name'];
      }
    }
    if (empty($defaults['contact_id'])) {
      if (isset($_GET['cid'])) {
        $defaults['contact_id'] = $_GET['cid'];
      }
      if (isset($_GET['ufid'])) {
        $defaults['uf_id'] = $_GET['ufid'];
      }
    }

    // add form elements
    $this->addEntityRef('contact_id', ts('Connect CiviCRM Contact'), [], TRUE);

    // There is a weird bug that I cannot for the life of me figure out where
    // this is not defaulting SO I am going to use a select for now
    // TODO get entityRef for custom api to work right
    // $this->addEntityRef('uf_id', ts('to CMS User ID'), [
    //   'entity' => 'Usermover',
    //   'placeholder' => ts('- No User -'),
    //   'select' => array('minimumInputLength' => 0),
    //   'api' => ['label_field' => 'label', 'search_field' => 'label'],
    // ]);

    $userOptions = self::apiShortCut('Usermover', 'getallusers', ['pretty_print' => 1]);
    // print_r($userOptions); die();
    $this->add('select', 'uf_id', ts('CMS ID'), $userOptions['values'], FALSE, [
      'class' => "crm-select2",
      'placeholder' => "- No User -",
    ]);

    $this->add('text', 'uf_name', ts('Unique Identifier in the CMS'));

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Confirm'),
        'isDefault' => TRUE,
      ),
    ));
    $this->setDefaults($defaults);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    parent::buildQuickForm();
  }

  public function apiShortCut($entity, $action, $params) {
    try {
      $results = civicrm_api3($entity, $action, $params);
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.usermover',
        1 => $error,
      )));
      return [
        'is_error' => 1,
        'error_message' => $error,
      ];
    }
    return $results;
  }

  public function postProcess() {
    $values = $this->exportValues();
    if (isset($values['contact_id'])) {
      // Delete any existing UFMatch records for the civicrm contact
      $existingRecordForCiviID = self::apiShortCut('UFMatch', 'get', [
        'contact_id' => $values['contact_id'],
        'api.UFMatch.delete' => ['id' => "\$value.id"],
      ]);
    }

    if (isset($values['uf_id']) && $values['uf_id'] > 0) {
      // Delete any existing UFMatch records for the uf_id
      $existingRecordForUFID = self::apiShortCut('UFMatch', 'get', [
        'uf_id' => $values['uf_id'],
        'api.UFMatch.delete' => ['id' => "\$value.id"],
      ]);

      // Create new record
      $result = self::apiShortCut('UFMatch', 'create', [
        'uf_id' => $values['uf_id'],
        'uf_name' => $values['uf_name'],
        'contact_id' => $values['contact_id'],
      ]);

      if ($result['is_error'] == 0) {
        CRM_Core_Session::setStatus(E::ts('User id <a href="%4">%1</a> is now connected to <a href="%3">contact id %2</a>', array(
          1 => $values['uf_id'],
          2 => $values['contact_id'],
          3 => CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$values['contact_id']}"),
          4 => CRM_Core_Config::singleton()->userSystem->getUserRecordUrl($values['contact_id']),
        )),E::ts('User Reassigned'), 'success');
      }
    } else {
      CRM_Core_Session::setStatus(E::ts('CiviCRM <a href="%2">contact ID "%1"</a> no longer connected to a User', array(
        1 => $values['contact_id'],
        2 => CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$values['contact_id']}"),
      )), E::ts('User Connection Removed'), 'success');
    }
    // CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/merge', $urlParams));
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
