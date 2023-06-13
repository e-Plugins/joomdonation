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

jimport('joomla.application.component.modeladmin');

/**
 * Digiwallet_donation model
 */
class Digiwallet_donationModelDigiwallet_donation_button extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_DIGIWALLET_DONATION';

	/**
	 * Returns a reference to the a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional
	 * @param	array	Configuration array for model. Optional
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'digiwallet_donation_button', $prefix = 'Digiwallet_donationTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form
		$form = $this->loadForm('com_digiwallet_donation.digiwallet_donation_button', 'digiwallet_donation_button', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form
	 *
	 * @return	mixed	The data for the form
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data
		$data = JFactory::getApplication()->getUserState('com_digiwallet_donation.edit.digiwallet_donation_button.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__digiwallet_donation_buttons');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
}
