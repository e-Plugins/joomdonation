<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Digiwallet Donation Model Configuration
 */
class DigiwalletDonationModelConfiguration extends JModelLegacy
{
	/**
	 * The item to hold data
	 *
	 * @return object
	 */
    var $_config = null;

	/**
	 * Get the data
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getConfiguration()
	{
	    if (isset($this->_config))
		{
		    return $this->_config;
		}
		
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = array();
		$query->select('*')
		->from('#__digiwallet_donation_configuration');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
		    $row   = $rows[$i];
		    $key   = $row->config_key;
		    $value = stripslashes($row->config_value);
		    $config[$key] = $value;
		}
		$this->_config = $config;
		return $this->_config;
	}
}
