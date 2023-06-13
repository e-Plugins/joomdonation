Joomla.submitbutton = function(task)
{
    var backendDetail = jQuery('#validation-form-failed').data('backend-detail');
    if (task == backendDetail + '.cancel' || document.formvalidator.isValid(document.id(backendDetail + '-form'))) {
        Joomla.submitform(task, document.getElementById(backendDetail + '-form'));
    }
    else {
        alert(jQuery('#validation-form-failed').data('message'));
    }
}
