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
        // $this->addElement('hidden', $field, $defaults[$field], ['id' => $field]);
      }
    }

    $this->addElement('hidden','contact_id', $defaults['contact_id'], array('id'=> 'contact_id'));
    $this->addElement('hidden','uf_id', $defaults['uf_id'], array('id'=> 'uf_id'));
    $this->addElement('hidden','uf_name', $defaults['uf_name'], array('id'=> 'uf_name'));

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));


    // export form elements to post process
    $this->assign('elementNames', $this->getRenderableElementNames());

    // Get available user options
    $userOptions = CRM_Usermover_Form_UserMover::apiShortCut('Usermover', 'getallusers', ['pretty_print' => 1]);

    // Get contacts that will be changed to display
    $contactsThatWillBeChanged = self::getContactsThatWillBeChanged($defaults, $userOptions['values']);
    $this->assign('contacts', $contactsThatWillBeChanged);

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    if (isset($values['contact_id'])) {

      // Delete any existing UFMatch records for the civicrm contact/user
      $ufMatchesToDelete = self::findContactsThatWillBeDeleted($values);
      foreach ($ufMatchesToDelete as $ufMatchId => $ufMatchDetails) {
        $existingRecordToDelete = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'delete', [
          'id' => $ufMatchId,
        ]);
      }
    }

    if (isset($values['uf_id']) && $values['uf_id'] > 0) {
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


  public function getContactsThatWillBeChanged($changes, $users) {
    $contactsThatWillBeChanged = $existingRecords = [];
    // TODO get all contacts that will be effected and display them to the user
    if (!empty($changes['contact_id'])) {

      // First format the submitted changes for display -> This is the record that will be created
      $newRecord = self::formatContactForDisplay($changes, $users);

      $existingRecords = self::findContactsThatWillBeDeleted($changes);

      foreach ($existingRecords as $key => $ufmatch) {
        if ($ufmatch['contact_id'] != $changes['contact_id'] && $ufmatch['uf_id'] == $changes['uf_id']) {
          $contactsThatWillBeChanged[] = self::formatContactForDisplay($ufmatch, $users, $changes);
        }
      }

      if (count($existingRecords) == 1) {
        foreach ($existingRecords as $key => $ufmatch) {

          // If just updating the Unique Identifier
          if ($ufmatch['uf_name'] != $changes['uf_name'] && $ufmatch['contact_id'] == $changes['contact_id']) {
            $newRecord['uf_name'] = "Unique Identifier will be updated from <span style='text-decoration: line-through;'>{$ufmatch['uf_name']}</span> to <strong>{$newRecord['uf_name']}</strong>";
          }
          // Removin user connection
          if (empty($changes['uf_id'])) {
            $newRecord['display_name'] = "{$newRecord['display_name']} - Will no longer be connected to a user";
            $newRecord['user'] = "<span style='text-decoration: line-through;'>{$users[$ufmatch['uf_id']]}</span>";
            $newRecord['uf_name'] = "<span style='text-decoration: line-through;'>{$ufmatch['uf_name']}</span>";
          }
          // No Changes
          if ($ufmatch['uf_name'] == $changes['uf_name'] && $ufmatch['uf_id'] == $changes['uf_id'] && $ufmatch['contact_id'] == $changes['contact_id']) {
            CRM_Core_Session::setStatus(E::ts('No changes will be made by submitting this form.'), E::ts('No Changes Found'), 'no-popup');
          }
        }
      }
      $contactsThatWillBeChanged[] = $newRecord;
    }

    return $contactsThatWillBeChanged;
  }

  public function formatContactForDisplay($contactDetailsToDisplay, $users, $changes = []) {
    $contactDetails = CRM_Usermover_Form_UserMover::apiShortCut('Contact', 'getsingle', ['id' => $contactDetailsToDisplay['contact_id']]);
    $contactURL = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$contactDetailsToDisplay['contact_id']}");
    $detailsToDisplay = [
      'display_name' => "<a href='$contactURL'>{$contactDetails['display_name']}</a>",
      'user' => 'None ',
      'uf_name' => 'Not Applicable ',
    ];
    if (!empty($users[$contactDetailsToDisplay['uf_id']]) && !empty($contactDetailsToDisplay['uf_id'])) {
      if (!empty($changes)) {
        $detailsToDisplay['display_name'] = "{$detailsToDisplay['display_name']} - Will no longer be connected to a user";
        $detailsToDisplay['user'] = "<span style='text-decoration: line-through;'>{$users[$contactDetailsToDisplay['uf_id']]}</span>";
      }
      else {
        $detailsToDisplay['user'] = $users[$contactDetailsToDisplay['uf_id']];
      }
      if (!empty($contactDetailsToDisplay['uf_name'])) {
        if (!empty($changes)) {
          $detailsToDisplay['uf_name'] = "<span style='text-decoration: line-through;'>{$contactDetailsToDisplay['uf_name']}</span>";

        } else {
          $detailsToDisplay['uf_name'] = $contactDetailsToDisplay['uf_name'];
        }
      }
    } else {
      $detailsToDisplay['user'] = '<strong>WARNING</strong> - Invalid User Selected, it is not recommended to proceed with this action.';
    }
    return $detailsToDisplay;
  }

  public function findContactsThatWillBeDeleted($changes) {
    $existingRecords = [];

    // Check for contacts that have the same User Id
    $existingRecordCheck = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'get', [
      'uf_id' => $changes['uf_id'],
    ]);

    if (!empty($existingRecordCheck['values'])) {
      foreach ($existingRecordCheck['values'] as $key => $details) {
        $existingRecords[$key] = $details;
      }
    }

    // Check for contacts with the same Contact ID
    $existingRecordCheck = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'get', [
      'contact_id' => $changes['contact_id'],
    ]);

    if (!empty($existingRecordCheck['values'])) {
      foreach ($existingRecordCheck['values'] as $key => $details) {
        $existingRecords[$key] = $details;
      }
    }

    return $existingRecords;
  }

}
