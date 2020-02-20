CRM.$(function ($) {

  // Only display user name field if an id has been selected for uf_id
  var cmsUserSelected = function() {
    if ($('select#uf_id').val() == 'none') {
      $('input#uf_name').parent().parent().hide();
      $('.userMoverHelp').text('Submitting this form will result in removing any and all connections between the selected CiviCRM and the CMS.');
    }
    else {
      $('input#uf_name').parent().parent().show();
      $('.userMoverHelp').text('Submitting this form will result in the CiviCRM contact being connected to the selected CMS User ID. All other connections to this CMS user will be removed. If the selected contact has an existing relationship to a user it will be removed.');
    }
  };

  cmsUserSelected();
  $('select#uf_id').change(cmsUserSelected);
});
