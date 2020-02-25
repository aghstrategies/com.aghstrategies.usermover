<?php

require_once 'usermover.civix.php';
use CRM_Usermover_ExtensionUtil as E;

function usermover_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values) {
  if ($objectName == 'Contact'  && CRM_Core_Permission::check('administer CiviCRM')) {
    if ($op == 'contact.custom.actions' || $op == 'view.contact.activity') {
      $ufMatch = CRM_Usermover_Form_UserMover::apiShortCut('UFMatch', 'getsingle', ['contact_id' => $objectId]);
      if (!empty($ufMatch['uf_id'])) {
        $url = CRM_Utils_System::url('civicrm/usermover', "reset=1&ufid={$ufMatch['uf_id']}&cid={$objectId}&ufname={$ufMatch['uf_name']}&id={$ufMatch['id']}");
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
    if (!empty($form->_submitValues['uf_id']) && empty($form->_submitValues['uf_name'])) {
      $form->setElementError('uf_name', 'You must enter a Unique User Name');
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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function usermover_civicrm_xmlMenu(&$files) {
  _usermover_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function usermover_civicrm_postInstall() {
  _usermover_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function usermover_civicrm_uninstall() {
  _usermover_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function usermover_civicrm_enable() {
  _usermover_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function usermover_civicrm_disable() {
  _usermover_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function usermover_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _usermover_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function usermover_civicrm_managed(&$entities) {
  _usermover_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function usermover_civicrm_caseTypes(&$caseTypes) {
  _usermover_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function usermover_civicrm_angularModules(&$angularModules) {
  _usermover_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function usermover_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _usermover_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function usermover_civicrm_entityTypes(&$entityTypes) {
  _usermover_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function usermover_civicrm_themes(&$themes) {
  _usermover_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function usermover_civicrm_preProcess($formName, &$form) {

} // */

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
