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
 * Digiwallet_donation list view
 */
class Digiwallet_donationViewDigiwallet_donation_buttons extends JViewLegacy
{
	protected $user;

	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->user	= JFactory::getUser();

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}
        
		Digiwallet_donationHelpersBackend::addSubmenu('digiwallet_donation_buttons');

		$this->addToolbar();

		$this->sortFields = $this->getSortFields();

        $this->sidebar = JHtmlSidebar::render();

		// Load the template header here to simplify the template
		$this->loadTemplateHeader();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/backend.php';

		$state	= $this->get('State');
		$canDo	= Digiwallet_donationHelpersBackend::getActions($state->get('filter.category_id'));

		$title = JText::_('COM_DIGIWALLET_DONATION_TITLE_DIGIWALLET_DONATION_BUTTONS');
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

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/digiwallet_donation_button';
        if (file_exists($formPath))
		{
            if ($canDo->get('core.create'))
			{
			    JToolBarHelper::addNew('digiwallet_donation_button.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit') && isset($this->items[0]))
			{
			    JToolBarHelper::editList('digiwallet_donation_button.edit','JTOOLBAR_EDIT');
		    }
        }

		if ($canDo->get('core.edit.state'))
		{
            if (isset($this->items[0]->state))
			{
			    JToolBarHelper::divider();
			    JToolBarHelper::custom('digiwallet_donation_buttons.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    JToolBarHelper::custom('digiwallet_donation_buttons.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            }
			else if (isset($this->items[0]))
			{
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'digiwallet_donation_buttons.delete','JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state))
			{
			    JToolBarHelper::divider();
			    JToolBarHelper::archiveList('digiwallet_donation_buttons.archive','JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out))
			{
            	JToolBarHelper::custom('digiwallet_donation_buttons.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
		}
        
        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state))
		{
		    if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
			    JToolBarHelper::deleteList('', 'digiwallet_donation_buttons.delete','JTOOLBAR_EMPTY_TRASH');
			    JToolBarHelper::divider();
		    }
			else if ($canDo->get('core.edit.state'))
			{
			    JToolBarHelper::trash('digiwallet_donation_buttons.trash','JTOOLBAR_TRASH');
			    JToolBarHelper::divider();
		    }
        }

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_digiwallet_donation');
		}
        
        //Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_digiwallet_donation&view=digiwallet_donation_buttons');
        
        $this->extra_sidebar = '';
        
		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)
		);
	}

	/**
	 * Get the fields for sorting
	 *
	 * @return	$sortFields		array	An array with the sort fields
	 */
	protected function getSortFields()
	{
		$sortFields = array(
			'a.id' => JText::_('COM_DIGIWALLET_DONATION_HEADING_BACKEND_LIST_ID'),
			'a.amount' => JText::_('COM_DIGIWALLET_DONATION_DIGIWALLET_DONATION_BUTTON_AMOUNT_LBL'),
			'a.label' => JText::_('COM_DIGIWALLET_DONATION_DIGIWALLET_DONATION_BUTTON_LABEL_LBL'),
			'a.created_by' => JText::_('COM_DIGIWALLET_DONATION_DIGIWALLET_DONATION_BUTTON_CREATED_BY_LBL'),
			'a.state' => JText::_('COM_DIGIWALLET_DONATION_DIGIWALLET_DONATION_BUTTON_STATE_LBL'),
			'a.ordering' => JText::_('COM_DIGIWALLET_DONATION_DIGIWALLET_DONATION_BUTTON_ORDERING_LBL'),
		);

		return $sortFields;
	}

	/**
	 * Load the template header data here to simplify the template
	 */
	protected function loadTemplateHeader()
	{
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		JHtml::_('bootstrap.tooltip');
		JHtml::_('behavior.multiselect');
		JHtml::_('formbehavior.chosen', 'select');

		$document = JFactory::getDocument();
		$document->addStyleSheet('components/com_digiwallet_donation/assets/css/digiwallet_donation.css');
		$document->addScript('components/com_digiwallet_donation/assets/js/list.js');

		$this->listOrder = $this->escape($this->state->get('list.ordering'));
		$this->listDirn = $this->escape($this->state->get('list.direction'));
		$this->user->authorise('core.edit.state', 'com_digiwallet_donation.category');
		$saveOrder = $this->listOrder == 'a.ordering';

		if ($saveOrder)
		{
			$saveOrderingUrl = 'index.php?option=com_digiwallet_donation&task=digiwallet_donation_buttons.saveOrderAjax&tmpl=component';
			JHtml::_('sortablelist.sortable', 'digiwallet_donation_buttonList', 'adminForm', strtolower($this->listDirn), $saveOrderingUrl);
		}

		$this->saveOrder = $saveOrder;
	}
}
