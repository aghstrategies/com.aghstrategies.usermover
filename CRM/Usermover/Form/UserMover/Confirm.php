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

    // Add url values to form so you can get them in postprocess
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

    // Get contacts that will be changed to display
    $consequences = self::getConsequencesOfThisAction($defaults);

    $this->assign('consequences', $consequences);
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


  public function getConsequencesOfThisAction($changes) {
    $consequences = $existingRecords = [];

    $existingRecords = self::findContactsThatWillBeDeleted($changes);

    // First format the submitted changes for display -> This is the record that will be created
    $recordToBeCreated = self::formatContactForDisplay($changes);

    // If creating a new connection print consequences
    if (!empty($changes['uf_id'])) {
      $consequences[] = "CiviCRM Contact {$recordToBeCreated['display_name']} will be connected to user {$recordToBeCreated['user']} ";

      // If there are existing records with this contact id and or user id print information on how they will be altered
      if (count($existingRecords) > 0) {
        foreach ($existingRecords as $key => $ufmatch) {
          $record = self::formatContactForDisplay($ufmatch);
          if ($ufmatch['contact_id'] != $changes['contact_id'] && $ufmatch['uf_id'] == $changes['uf_id']) {
            $consequences[] = "CiviCRM Contact {$record['display_name']} will no longer be connected to a user.";
          }
          elseif ($ufmatch['contact_id'] == $changes['contact_id'] && $ufmatch['uf_id'] != $changes['uf_id']) {
            $consequences[] = "User {$record['user']} will no longer be connected to a CiviCRM contact.";
            $consequences[0] = "CiviCRM Contact {$recordToBeCreated['display_name']} will be connected to user {$recordToBeCreated['user']} instead of {$record['user']}.";
          }
          elseif ($ufmatch['contact_id'] == $changes['contact_id'] && $ufmatch['uf_id'] == $changes['uf_id']) {
            $consequences[0] = "CiviCRM Contact {$recordToBeCreated['display_name']} will continue to be connected to user {$recordToBeCreated['user']}.";
          }
          else {
            $consequences[] = "WARNING: unknown consequences";
          }
        }
      }
    }

    // User will be disconnected
    if (empty($changes['uf_id'])) {
      $consequences[] = "CiviCRM Contact {$recordToBeCreated['display_name']} will no longer be connected to a User.";
      if (count($existingRecords) > 0) {
        foreach ($existingRecords as $key => $ufmatch) {
          $record = self::formatContactForDisplay($ufmatch);
          if ($ufmatch['contact_id'] == $changes['contact_id']) {
            $consequences[0] = "CiviCRM Contact {$recordToBeCreated['display_name']} (previously connected to {$record['user']}) will no longer be connected to a User.";
            $consequences[] = "User {$record['user']} will no longer be connected to a CiviCRM Contact.";
          }
          else {
            $consequences[] = "WARNING: unknown consequences";
          }
        }
      }
    }
    return $consequences;
  }

  public function formatContactForDisplay($contactDetailsToDisplay) {
    $contactDetails = CRM_Usermover_Form_UserMover::apiShortCut('Contact', 'getsingle', ['id' => $contactDetailsToDisplay['contact_id']]);
    $contactURL = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$contactDetailsToDisplay['contact_id']}");
    $detailsToDisplay = [
      'display_name' => "<a href='$contactURL'>{$contactDetails['display_name']}</a>",
      'user' => 'None ',
      'uf_name' => 'Not Applicable ',
    ];
    $user = CRM_Usermover_Form_UserMover::apiShortCut('UserMover', 'getsingle', ['id' => $contactDetailsToDisplay['uf_id']]);

    // if label found for user
    if (!empty($user['label'])) {
      $detailsToDisplay['user'] = "<a href={$user['user_url']}>{$user['label']}</a>";
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
