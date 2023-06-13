<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Digiwallet_donation detail controller
 */
class Digiwallet_donationControllerDigiwallet_donation_button extends JControllerForm
{
    function __construct()
    {
        $this->view_list = 'digiwallet_donation_buttons';
        parent::__construct();
    }
}
