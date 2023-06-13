Joomla.submitbutton = function(task)
{
    var backendDetail = jQuery('#validation-form-failed').data('backend-detail');
    Joomla.submitform(task, document.getElementById(backendDetail + '-form'));
}
