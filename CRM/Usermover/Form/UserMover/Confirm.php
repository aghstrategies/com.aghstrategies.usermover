<?php

use CRM_Usermover_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Usermover_Form_UserMover_Confirm extends CRM_Core_Form {
  public function buildQuickForm() {
    $defaults = [];

    $urlParams = [
      'contact_id',
      'uf_name',
      'uf_id',
    ];

    // Set defaults based on values in url
    foreach ($urlParams as $key => $field) {
      if (isset($_GET[$field])) {
        $defaults[$field] = $_GET[$field];
      }
    }

    // Add Form fields
    $this->addEntityRef('contact_id', ts('Connect CiviCRM Contact'), [], TRUE);
    $userOptions = CRM_Usermover_Form_UserMover::apiShortCut('Usermover', 'getallusers', ['pretty_print' => 1]);

    $this->add('select', 'uf_id', ts('CMS ID'), $userOptions['values'], FALSE, [
      'class' => "crm-select2",
      'placeholder' => "- No User -",
    ]);
    $this->add('text', 'uf_name', ts('Unique Identifier in the CMS'));

    $this->setDefaults($defaults);

    CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'get', ['uf_id' => $defaults['uf_id']]);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));


    // export form elements to post process
    $this->assign('elementNames', $this->getRenderableElementNames());

    // Get contacts that will be changed to display
    $contactsThatWillBeChanged = self::getContactsThatWillBeChanged($defaults);
    $this->assign('contacts', $contactsThatWillBeChanged);

    parent::buildQuickForm();
  }

  public function getContactsThatWillBeChanged($changes) {
    $contactsThatWillBeChanged = [];
    // TODO get all contacts that will be effected and display them to the user
    if (!empty($changes['contact_id'])) {
      $contactDetails = CRM_Usermover_Form_UserMover::apiShortCut('Contact', 'getsingle', ['id' => $changes['contact_id']]);
      $contactsThatWillBeChanged[] = [
        'display_name' => $contactDetails['display_name'],
        'contact_url' => CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$changes['contact_id']}"),
      ];
    }

    return $contactsThatWillBeChanged;
  }

  public function postProcess() {
    $values = $this->exportValues();
    if (isset($values['contact_id'])) {
      // Delete any existing UFMatch records for the civicrm contact
      $existingRecordForCiviID = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'get', [
        'contact_id' => $values['contact_id'],
        'api.UFMatch.delete' => ['id' => "\$value.id"],
      ]);
    }

    if (isset($values['uf_id']) && $values['uf_id'] > 0) {
      // Delete any existing UFMatch records for the uf_id
      $existingRecordForUFID = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'get', [
        'uf_id' => $values['uf_id'],
        'api.UFMatch.delete' => ['id' => "\$value.id"],
      ]);

      // Create new record
      $result = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'create', [
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
    $url = CRM_Usermover_Form_UserMover::getUrlForSearch();
    CRM_Utils_System::redirect($url);
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
