CRM.$(function ($) {

  // Only display user name field if an id has been selected for uf_id
  var cmsUserSelected = function() {
    if ($('select#uf_id').val() == 'none') {
      $('input#uf_name').parent().parent().hide();
    }
    else {
      $('input#uf_name').parent().parent().show();
    }
  };

  cmsUserSelected();
  $('select#uf_id').change(cmsUserSelected);
});
