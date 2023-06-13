<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class Digiwallet_donationModelConfiguration extends JModelAdmin
{

	public function __construct($config = array())
	{
		$config['table'] = '#__digiwallet_donation_configuration';
		parent::__construct($config);
	}

	public function getForm($data = array(), $loadData = true)
	{
	    // Get the form
	    $form = $this->loadForm('com_digiwallet_donation.digiwallet_donation_configuration', 'digiwallet_donation_configuration', array('control' => 'jform', 'load_data' => $loadData));
	    if (empty($form))
	    {
	        return false;
	    }
	    
	    return $form;
	}
	/**
	 * Get configuration data
	 */
	function getData()
	{
	    $db     = JFactory::getDbo();
	    $query  = $db->getQuery(true);
	    $config = new stdClass();
	    $query->select('*')
	    ->from('#__digiwallet_donation_configuration');
	    $db->setQuery($query);
	    $rows = $db->loadObjectList();
	    for ($i = 0, $n = count($rows); $i < $n; $i++)
	    {
	        $row   = $rows[$i];
	        $key   = $row->config_key;
	        $value = stripslashes($row->config_value);
	        if ($nl2br)
	        {
	            $value = nl2br($value);
	        }
	        $config->$key = $value;
	    }
	    return $config;
	}

	/**
	 * Store the configuration data
	 *
	 * @param array $post
	 */
	function store($data)
	{
		$db  = $this->getDbo();
		$db->truncateTable('#__digiwallet_donation_configuration');
		foreach ($data as $key => $value)
		{
	       $db->setQuery("Insert into #__digiwallet_donation_configuration (config_key, config_value) values ('$key', '$value')");
	       $db->execute();
		}
		return true;
	}
}