<?php

use CRM_Usermover_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Usermover_Form_UserMover extends CRM_Core_Form {

  public static function getUrlForSearch() {
    $url = NULL;
    $csid = self::apiShortCut('CustomSearch', 'getvalue', [
      'name' => "CRM_Usermover_Form_Search_Usermover",
      'return' => 'value',
    ]);
    if (!empty($csid) && $csid > 0) {
      $url = CRM_Utils_System::url('civicrm/contact/search/custom', "reset=1&csid={$csid}");
    }
    return $url;
  }

  public function buildQuickForm() {
    $defaults = [];

    $csid = self::apiShortCut('CustomSearch', 'getvalue', [
      'name' => "CRM_Usermover_Form_Search_Usermover",
      'return' => 'value',
    ]);
    $searchUrl = self::getUrlForSearch();
    $this->assign('searchUrl', $searchUrl);

    $userUrl = CRM_Usermover_Form_UserMover::linkToUserLand();
    $userLand = '';
    if ($userUrl) {
      $userLand = "<p>To create a new CMS user or edit an existing user go to the <a href='$userUrl'>CMS User Administration Page</a>.</p>";
    } else {
      CRM_Core_Session::setStatus(E::ts(
        'No valid url to user land found. This extension only works for Drupal and Wordpress at this time.
        Perhaps you are using a differnt CMS. Proceed with caution.'), E::ts('CMS Compatibility'), 'error');
    }
    $this->assign('userLand', $userLand);

    CRM_Core_Resources::singleton()->addScriptFile('com.aghstrategies.usermover', 'js/userMover.js');

    // If there is a UFMatch id in the url use that
    if (!empty($_GET['id'])) {
      $existingRecord = self::apiShortCut('UFMatch', 'getsingle', ['id' => $_GET['id']]);
      if (!empty($existingRecord['contact_id'])) {
        $defaults['contact_id'] = $existingRecord['contact_id'];
        $defaults['uf_id'] = $existingRecord['uf_id'];
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
    $this->addEntityRef('uf_id', ts('to CMS User ID'), [
      'entity' => 'UserMover',
      'placeholder' => "- No User -",
    ]);

    $this->add('checkbox', 'copy_email', ts('Copy the user email address to the CiviCRM contact if it is not already there.'));

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

  public static function apiShortCut($entity, $action, $params) {
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
    // print_r($values); die();
    $valuesToConfirm = [
      'uf_id' => $values['uf_id'],
      'uf_name' => $values['uf_name'],
      'contact_id' => $values['contact_id'],
      'copy_email' => $values['copy_email'],
    ];
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/usermover/confirm', $valuesToConfirm));
  }

  public static function linkToUserLand() {
    $config = CRM_Core_Config::singleton();
    $url = NULL;

    // WordPress
    if ($config->userFramework == 'WordPress') {
      $url = $config->userFrameworkBaseURL . "wp-admin/users.php";
    }
    // Drupal and Backdrop
    elseif ($config->userFramework == 'Drupal' || $config->userFramework == 'Drupal8') {
      $url = $config->userFrameworkBaseURL . "admin/people";
    }
    // joomla
    elseif (get_class($config->userSystem) == 'CRM_Utils_System_Joomla') {
      $url = $config->userFrameworkBaseURL . "index.php?option=com_users&view=users";
    }
    return $url;
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
