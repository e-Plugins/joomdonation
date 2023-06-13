<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation_1.0.0_j3x
 * @copyright   Copyright (C) 2020 e-plugins.nl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      DigiWallet.nl <techsupport@targetmedia.nl> - https://www.digiwallet.nl
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Digiwallet_donation detail model
 */
class Digiwallet_donationModelDigiwallet_donation_button extends JModelLegacy
{
	/**
	 * The item to hold data
	 *
	 * @return object
	 */
    var $_item = null;

	/**
	 * Get the data
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getItem()
	{
		if (isset($this->_item))
		{
			return $this->_item;
		}

		$app = JFactory::getApplication();

		$id = $app->input->getInt('id');
		$params = $app->getParams();

		$paramId = $params->get('id');

		if ($paramId)
		{
			$id = $paramId;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.amount,a.label, a.state');
		$query->select('a.ordering');

		$query->from('#__digiwallet_donation_buttons as a');

		$query->select('c.name as created_by');
		$query->leftJoin($this->_db->qn('#__users') . ' AS c ON c.id = a.created_by');

		$query->where('a.id = ' . intval($id) . ' AND a.state = 1');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			JError::raiseError(500, $e->getMessage());
		}

		$this->_item = $db->loadObject();

		include_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/form/digiwallet_donation_button.php';
		$form = new FormDigiwallet_donation_buttonDigiwallet_donation;
		if(!$this->_item) {
		    return null;
		}
		return $form->appendFieldOptions([$this->_item])->getOne();
	}
}
