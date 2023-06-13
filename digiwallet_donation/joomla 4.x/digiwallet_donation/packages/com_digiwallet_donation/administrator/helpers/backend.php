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

/**
 * Digiwallet_donation helper class
 */
class Digiwallet_donationHelpersBackend
{
	/**
	 * Add the submenus
	 */
	public static function addSubmenu($name = '')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_DIGIWALLET_DONATION_TITLE_DIGIWALLET_DONATION_BUTTONS'),
			'index.php?option=com_digiwallet_donation&view=digiwallet_donation_buttons',
			$name == 'digiwallet_donation_buttons'
		);
		JHtmlSidebar::addEntry(
		    JText::_('COM_DIGIWALLET_DONATION_TITLE_DIGIWALLET_DONATION_CONFIGURATION'),
		    'index.php?option=com_digiwallet_donation&view=digiwallet_donation_configuration',
		    $name == 'digiwallet_donation_configuration'
		    );
	}

	/**
	 * Gets a list of the actions that can be performed
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_digiwallet_donation';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
	
	/**
	 * Build the query for search from the search columns
	 *
	 * @param	string		$searchWord		Search for this text

	 * @param	string		$searchColumns	The columns in the DB to search for
	 *
	 * @return	string		$query			Append the search to this query
	 */
	public static function buildSearchQuery($searchWord, $searchColumns, $query)
	{
		$db = JFactory::getDbo();

		$where = array();

		foreach ($searchColumns as $i => $searchColumn)
		{
			$where[] = $db->qn($searchColumn) . ' LIKE ' . $db->q('%' . $db->escape($searchWord, true) . '%');
		}

		if (!empty($where))
		{
			$query->where('(' . implode(' OR ', $where) . ')');
		}

		return $query;
	}
}
