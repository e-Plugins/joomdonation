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

jimport('joomla.application.component.view');

/**
 * Digiwallet_donation callback view
 */
class Digiwallet_donationViewCallback extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
		$app	= JFactory::getApplication();
        require_once JPATH_COMPONENT_SITE . '/digiwallet/digiwallet.php';
        require_once JPATH_COMPONENT_SITE . '/models/digiwallet_donation_configuration.php';
        $configuration = (new DigiwalletDonationModelConfiguration())->getConfiguration();
        $digiwallet = new DigiwalletDonation($configuration);
        return $digiwallet->verifyPayment();
    }
}
