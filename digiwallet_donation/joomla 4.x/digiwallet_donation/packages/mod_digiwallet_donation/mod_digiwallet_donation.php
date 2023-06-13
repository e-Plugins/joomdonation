<?php
/**
 * @version     1.0.0
 * @package     mod_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$id = $label = null;
if($module->button_id) {
    // Get a db connection.
    $db = JFactory::getDbo();

    // Create a new query object.
    $query = $db->getQuery(true);

    // Select all records from the user profile table where key begins with "custom.".
    // Order it by the ordering field.
    $query->select(array(
        'id',
        'amount',
        'label',
        'state',
    ));

    $query->from('#__digiwallet_donation_buttons');
    $query->where("id='". $module->button_id . "' AND state = 1");

    // Reset the query using our newly populated query object.
    $db->setQuery($query);
    $db->execute();
    // Load the results as a list of stdClass objects.
    $button = $db->loadObject();
    if ($button) {
        $id = $button->id;
        $label = $button->label;
    }
}
require(JModuleHelper::getLayoutPath('mod_digiwallet_donation', 'default'));
