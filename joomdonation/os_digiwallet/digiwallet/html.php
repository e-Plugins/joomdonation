<?php
/**
 * @version        1.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         DigiWallet.nl
 * @copyright      Copyright (C) 2020 DigiWallet.nl
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class DonationViewDigiwalletHtml extends OSFViewHtml
{

    const PAYMENT_METHOD = 'os_digiwallet';

    /**
     * Indicate that this view doesn't have a model, so controller don't need to create it.
     *
     * @var bool
     */
    public $hasModel = false;

    /**
     * Method to display the view
     */
    function display()
    {
        $app = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        
        $id = $jinput->getInt('id');
        if (! $id) {
            $app->redirect(JRoute::_('index.php?option=com_jdonation&view=donation'));
        }
        $row = JTable::getInstance('Donor', 'DonationTable');
        $row->load($id);
        if ($row->payment_method != self::PAYMENT_METHOD || $row->published) {
            $app->redirect(JRoute::_('index.php?option=com_jdonation&view=donation'));
        }
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() .'components/com_jdonation/view/digiwallet/css/digiwallet.css');
        $method = os_payments::getPaymentMethod($row->payment_method);
        $targetInfo = $method->__retrieveDigiwalletInformation("cart_id = " . $id);
        if($targetInfo) {
            $app->redirect(JRoute::_('index.php?option=com_jdonation&view=donation'));
        }
        echo $method->formOptions($row);
    }
}