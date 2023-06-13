<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * The form field implementation
 */
class JFormFieldcreatedby extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'createdby';

	/**
	 * Method to get the field input markup
	 *
	 * @return	string	The field input markup
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Get the current user
		$user = JFactory::getUser();

		// Set this to be sure the user texts are displayed as default
		$userExists = true;

		// If the value is set
		if ($this->value)
		{
			// Look for the user in the DB
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id')
				->from('#__users')
				->where($db->qn('id') . ' = ' . $db->q($this->value));
			$db->setQuery($query);
			$userId = $db->loadResult();

			// If the user exists in the DB
			if ($userId)
			{
				// Get the user from the value in the input box
				$user = JFactory::getUser($this->value);
			}
			else
			{
				$userExists = false;

				// Otherwise set the value to the current user
				$this->value = $user->id;
			}
		}
		else
		{
			// Set the value to the current user
			$this->value = $user->id;
		}

		// If the user ID exists in the DB (this user exists)
		if ($userExists)
		{
			$html = $user->name . " (" . $user->username . ")";
		}

		$html .= '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" />';

		return $html;
	}
}
