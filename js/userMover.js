CRM.$(function ($) {

  // Only display copy_email field if an id has been selected for uf_id
  var cmsUserSelected = function() {

    if ($('#uf_id').val() == '') {
      $('input#copy_email').parent().parent().hide();
      $('input#copy_email').val('');
      $('.userMoverHelp').text('Submitting this form will result in removing any and all connections between the selected CiviCRM and the CMS.');
    }
    else {
      // Get the email associated with this user
      var uf_id = $('#uf_id').val();
      var contact_id = $('#contact_id').val();
      CRM.api3('UserMover', 'getsingle', {
        "uf_id": uf_id
      }).then(function(result) {
        $('label[for="copy_email"]').text("Copy the user email address (" + result.uf_name + ") to the CiviCRM contact if it is not already there.");
      }, function(error) {
        // oops
      });

      $('input#copy_email').parent().parent().show();
      $('.userMoverHelp').text('Submitting this form will result in the CiviCRM contact being connected to the selected CMS User ID. All other connections to this CMS user will be removed. If the selected contact has an existing relationship to a user it will be removed.');
    }
  };

  cmsUserSelected();
  $('#uf_id').change(cmsUserSelected);
});
