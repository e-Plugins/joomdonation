Joomla.orderTable = function() {
    table = document.getElementById("sortTable");
    direction = document.getElementById("directionTable");
    order = table.options[table.selectedIndex].value;
    if (order != jQuery('#adminForm').data('list-order')) {
        dirn = 'asc';
    } else {
        dirn = direction.options[direction.selectedIndex].value;
    }
    Joomla.tableOrdering(order, dirn, '');
}
jQuery(document).ready(function(){
    jQuery('#filter-bar button[type=button]').click(function() {
        jQuery('#filter_search').val('');
        jQuery('#adminForm').submit();
    });
});
