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
 * Digiwallet_donation detail view
 */
class Digiwallet_donationViewDigiwallet_donation_configuration extends JViewLegacy
{

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // Load the component params
        $this->component_params = JComponentHelper::getParams('com_digiwallet_donation');

        Digiwallet_donationHelpersBackend::addSubmenu('digiwallet_donation_configuration');

        $this->addToolbar();

        $this->sidebar = JHtmlSidebar::render();

        // Load the template header here to simplify the template
        $this->loadTemplateHeader();
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
        $this->component_params->def('configuration', $config);
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', false);

        $user		= JFactory::getUser();

        if (isset($this->item->checked_out))
        {
            $checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        }
        else
        {
            $checkedOut = false;
        }

        $canDo = Digiwallet_donationHelpersBackend::getActions();

        $title = JText::_('COM_DIGIWALLET_DONATION_TITLE_DIGIWALLET_DONATION_CONFIGURATION');
        $icon = 'fa fa-file-alt';

        $layout = new JLayoutFile('joomla.toolbar.title');
        $html = $layout->render([
            'title' => $title,
            'icon' => $icon
        ]);

        $app = JFactory::getApplication();
        $app->JComponentTitle = str_replace('icon-', '', $html);
        $title = strip_tags($title) . ' - ' . $app->get('sitename') . ' - ' . JText::_('JADMINISTRATION');
        JFactory::getDocument()->setTitle($title);

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
        {
            JToolBarHelper::apply('digiwallet_donation_configuration.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('digiwallet_donation_configuration.save', 'JTOOLBAR_SAVE');
        }

        if (empty($this->item->id))
        {
            JToolBarHelper::cancel('digiwallet_donation_configuration.cancel', 'JTOOLBAR_CANCEL');
        }
        else
        {
            JToolBarHelper::cancel('digiwallet_donation_configuration.cancel', 'JTOOLBAR_CLOSE');
        }

    }

    /**
     * Load the template header data here to simplify the template
     */
    protected function loadTemplateHeader()
    {
        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('behavior.keepalive');

        // Import CSS
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_digiwallet_donation/assets/css/digiwallet_donation.css');
        $document->addScript('components/com_digiwallet_donation/assets/js/detail.js');
    }
}
