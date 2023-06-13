<?php
/**
 * @version     1.0.0
 * @package     com_digiwallet_donation
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
class Digiwallet_donationViewDonation_form extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        require_once JPATH_COMPONENT_SITE . '/models/digiwallet_donation_button.php';
        require_once JPATH_COMPONENT_SITE . '/digiwallet/digiwallet.php';
        require_once JPATH_COMPONENT_SITE . '/models/digiwallet_donation_configuration.php';
        $this->setModel(new Digiwallet_donationModelDigiwallet_donation_button(), true);
		$app	= JFactory::getApplication();
        $user	= JFactory::getUser();
        $input  = $app->input;

        $this->item 				= $this->get('Item');
        $this->params 				= $app->getParams('com_digiwallet_donation');
        
        // Throw exeption if errors
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors));
        }

        $configuration = (new DigiwalletDonationModelConfiguration())->getConfiguration();
        $digiwallet = new DigiwalletDonation($configuration, $this->item);
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->item) {
            if ($pay_method = $input->get('pay_method')) {
                $payOption = @$_POST['payment_option_select'][$pay_method];
                $this->params->def('selectdMethod', $pay_method);
                $this->params->def('payOption', $payOption);
                if (in_array($pay_method, ['IDE', 'DEB']) && empty($payOption)) {
                    if ($pay_method == 'IDE') {
                        $option_name = JText::_('DIGIWALLET_DONATION_BANK');
                    } else {
                        $option_name = JText::_('DIGIWALLET_DONATION_COUNTRY');
                    }
                    $error = JText::sprintf('DIGIWALLET_DONATION_ERROR_MISSING_CHOOSE_PAYMENT_OPTION', $option_name, $digiwallet->listMethods[$pay_method]['name']);
                }
            } else {
                $error = JText::_('DIGIWALLET_DONATION_ERROR_MISSING_CHOOSE_PAYMENT_METHOD');
            }
            if (!$error) {
                $result = $digiwallet->startPayment($pay_method, $payOption);
                $error = $result;
            }
        }
        // Load the template header here to simplify the template
        $this->loadTemplateHeader();
        $this->_prepareDocument();
        require_once JPATH_COMPONENT_SITE . '/digiwallet/digiwallet.php';
        $bankArray = $digiwallet->getBankArray();
        $this->params->def('bankArr', $bankArray);
        $this->params->def('error', $error);
        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function _prepareDocument()
    {
        $app   = JFactory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu)
        {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else
        {
            $this->params->def('page_heading', JText::_('COM_DIGIWALLET_DONATION_DEFAULT_PAGE_TITLE'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title))
        {
            $title = $app->get('sitename');
        }
        elseif ($app->get('sitename_pagetitles', 0) == 1)
        {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2)
        {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description'))
        {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots'))
        {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }

    /**
     * Load the template header data here to simplify the template
     */
    protected function loadTemplateHeader()
    {
        JHtml::_('jquery.framework');

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_digiwallet_donation/assets/css/digiwallet_donation.css');
        $document->addScript('components/com_digiwallet_donation/assets/js/detail.js');
    }
}
