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

    CRM_Core_Resources::singleton()->addScriptFile('com.aghstrategies.usermover', 'js/userMover.js');

    // If there is a UFMatch id in the url use that
    if (!empty($_GET['id'])) {
      $existingRecord = self::apiShortCut('UFMatch', 'getsingle', ['id' => $_GET['id']]);
      if (!empty($defaults['contact_id'])) {
        $defaults['contact_id'] = $existingRecord['contact_id'];
        $defaults['uf_id'] = $existingRecord['uf_id'];
        $defaults['uf_name'] = $existingRecord['uf_name'];
      }
    } else {
      if (isset($_GET['cid'])) {
        $defaults['contact_id'] = $_GET['cid'];
      }
      if (isset($_GET['ufid'])) {
        $defaults['uf_id'] = $_GET['ufid'];
      }
    }

    // add form elements
    $this->addEntityRef('contact_id', ts('Connect CiviCRM Contact'), [], TRUE);
    $this->addEntityRef('uf_id', ts('to CMS User ID'), array(
      'entity' => 'Usermover',
      'placeholder' => ts('- No User -'),
    ));

    $this->add('text', 'uf_name', ts('Unique Identifier in the CMS'));

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    $this->setDefaults($defaults);

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

  // TODO make sure the username added is valid
  public function validateForm() {
    print_r($this); die();
    if (isset($this->_submitValues['uf_id']) && empty($this->_submitValues['uf_name'])) {
      $this->setElementError('uf_name', 'You must enter a Unique User Name');
      // $this->_errors['uf_name'] = 'You must enter a Unique User Name';
    }
    return;
    // if (isset($this->_submitValues['uf_name'])) {
    //   $params = [
    //     'name' => $this->_submitValues['uf_name'],
    //     'mail' => $this->_submitValues['uf_name'],
    //   ];
    //   $validUserName = CRM_Contact_Form_Task_Useradd::usernameRule($params);
    // }
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
        CRM_Core_Session::setStatus(E::ts('User id %1 is now connected to contact id %2', array(
          1 => $values['uf_id'],
          2 => $values['contact_id'],
        )),E::ts('User Reassigned'), 'success');
      }
    } else {
      CRM_Core_Session::setStatus(E::ts('CiviCRM contact ID "%1" no longer connected to a User', array(
        1 => $values['contact_id'],
      )), E::ts('User Connection Removed'), 'success');
    }

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
