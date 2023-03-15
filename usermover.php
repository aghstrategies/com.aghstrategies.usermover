<?php

require_once 'usermover.civix.php';
use CRM_Usermover_ExtensionUtil as E;

function usermover_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values) {
  if ($objectName == 'Contact'  && CRM_Core_Permission::check('administer CiviCRM')) {
    if ($op == 'contact.custom.actions' || $op == 'view.contact.activity') {
      $ufMatch = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'getsingle', ['contact_id' => $objectId]);
      if (!empty($ufMatch['uf_id'])) {
        $url = CRM_Utils_System::url('civicrm/usermover', "reset=1&ufid={$ufMatch['uf_id']}&cid={$objectId}&id={$ufMatch['id']}");
        $links[] = array(
          'name' => ts('Reassign CMS User'),
          'url' => $url,
          'title' => 'Reassign CMS User',
          'class' => 'no-popup',
        );
      }
    }
  }
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @param string $formName
 * @param array $fields
 * @param array $files
 * @param CRM_Core_Form $form
 * @param array $errors
 */
function usermover_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Usermover_Form_UserMover') {
    // Check that the uf_name is legit before allowing the user to continue.
    if (!empty($form->_submitValues['uf_id'])) {

      // Get email associated with the user
      $recordToBeCreated = CRM_Usermover_Form_UserMover_Confirm::formatContactForDisplay($form->_submitValues);

      // check that this uf_name is unique
      $ufMatches = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'get', ['uf_name' => $recordToBeCreated['uf_name']]);
      if (!empty($ufMatches['values'])) {
        foreach ($ufMatches['values'] as $key => $ufDetails) {
          if ($ufDetails['uf_id'] == $form->_submitValues['uf_id']) {
            unset($ufMatches['values'][$key]);
          }
          if ($ufDetails['contact_id'] == $form->_submitValues['contact_id']) {
            unset($ufMatches['values'][$key]);
          }
        }
        if (count($ufMatches['values']) > 0) {
          $form->setElementError('uf_name', '"Unique Identifier in the CMS" is not unique... there is another record in the system using this UF_Name');
        }
      }

    }
  }
  return;
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function usermover_civicrm_config(&$config) {
  _usermover_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function usermover_civicrm_install() {
  _usermover_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function usermover_civicrm_enable() {
  _usermover_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function usermover_civicrm_navigationMenu(&$menu) {
  _usermover_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _usermover_civix_navigationMenu($menu);
} // */
