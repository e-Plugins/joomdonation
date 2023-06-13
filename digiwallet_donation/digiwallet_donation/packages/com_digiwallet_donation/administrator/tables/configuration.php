<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class DonationTableConfiguration extends JTable
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 *
	 */
	function __construct($db)
	{
		parent::__construct('#__digiwallet_donation_configuration', 'id', $db);
	}
}