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
    if (isset($_GET['cid'])) {
      $defaults['contact_id'] = $_GET['cid'];
    }
    if (isset($_GET['ufid'])) {
      $defaults['uf_id'] = $_GET['ufid'];
    }
    // add form elements
    $this->addEntityRef('contact_id', ts('Select Contact to Connect User to'), [], TRUE);

    $this->add('text', 'uf_id', ts('Enter User ID to connect Contact to'), [], TRUE);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
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
    }
    return $results;
  }

  public function postProcess() {
    $values = $this->exportValues();
    if (!empty($values['contact_id']) && !empty($values['uf_id'])) {
      $existingRecord = self::apiShortCut('UFMatch', 'getsingle', ['uf_id' => $values['uf_id']]);
      if (!empty($existingRecord['id'])) {
        $existingRecord = self::apiShortCut('UFMatch', 'create', [
          'id' => $existingRecord['id'],
          'contact_id' => $values['contact_id'],
        ]);
        CRM_Core_Session::setStatus(E::ts('User "%1" is now connected to contact id "%2"', array(
          1 => $values['uf_id'],
          2 => $values['contact_id'],
        )));
      } else {
        CRM_Core_Session::setStatus(E::ts('There is no existing CiviCRM contact connected to User "%1" Contact a System Administrator for support with this action.', array(
          1 => $values['uf_id'],
        )));
      }
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
