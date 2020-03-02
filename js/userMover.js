CRM.$(function ($) {

  // Only display copy_email field if an id has been selected for uf_id
  var cmsUserSelected = function() {

    if ($('#uf_id').val() == '') {
      $('input#copy_email').parent().parent().hide();
      $('.userMoverHelp').text('Submitting this form will result in removing any and all connections between the selected CiviCRM and the CMS.');
    }
    else {
      $('input#copy_email').parent().parent().show();
      $('.userMoverHelp').text('Submitting this form will result in the CiviCRM contact being connected to the selected CMS User ID. All other connections to this CMS user will be removed. If the selected contact has an existing relationship to a user it will be removed.');
    }
  };

  cmsUserSelected();
  $('#uf_id').change(cmsUserSelected);
});
