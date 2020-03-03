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
        $('label[for="copy_email"]').text("The email address associated with this user (" + result.uf_name + ") does not exist on the selected contact. Check this box to copy this email address to the CiviCRM Contact.");
        CRM.api3('Email', 'get', {
          "contact_id": contact_id,
          "email": result.uf_name
        }).then(function(result) {
          if (result.is_error == 0) {
            if (result.count > 0) {
              $('input#copy_email').parent().parent().hide();
              $('input#copy_email').val('');
            }
          }
        }, function(error) {
          console.log(error);
        });
      }, function(error) {
        console.log(error);
      });

      $('input#copy_email').parent().parent().show();
      $('.userMoverHelp').text('Submitting this form will result in the CiviCRM contact being connected to the selected CMS User ID. All other connections to this CMS user will be removed. If the selected contact has an existing relationship to a user it will be removed.');
    }
  };

  cmsUserSelected();
  $('#uf_id').change(cmsUserSelected);
});
