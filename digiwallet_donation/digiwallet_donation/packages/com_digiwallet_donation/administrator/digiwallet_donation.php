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

$user = JFactory::getUser();

// Authorize
if (!$user->authorise('core.manage', 'com_digiwallet_donation'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

$document = JFactory::getDocument();
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css');

// Register class prefix
JLoader::registerPrefix('Digiwallet_donation', JPATH_COMPONENT_ADMINISTRATOR);

// Load the controller
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Digiwallet_donation');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
