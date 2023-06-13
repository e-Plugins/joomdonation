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
 * Digiwallet_donation bankwire view
 */
class Digiwallet_donationViewBankwire extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $app	= JFactory::getApplication();
        $input  = $app->input;
        $this->params = $app->getParams('com_digiwallet_donation');
        $error = false;
        $trxid = $input->get('transaction_id');
        if (!$trxid) {
            $error = true;
        }
        require_once JPATH_COMPONENT_SITE . '/digiwallet/digiwallet.php';
        $digiwallet = new DigiwalletDonation();
        $paymentInfo = $digiwallet->__retrieveDigiwalletInformation("transaction_id = '" . $trxid. "'");
        if (!$paymentInfo) {
            $error = true;
        }
        if($error) {
            $this->set('Errors', ['Transaction not found']);
        }
        $errors = $this->get('Errors');
        $errors = empty($errors) ? [] : $errors;
        // Throw exeption if errors
        if (@count($errors))
        {
            throw new Exception(implode("\n", $errors));
        }
        list($trxid, $accountNumber, $iban, $bic, $beneficiary, $bank) = explode("|", $paymentInfo->bw_data);
        $html = '<div class="bankwire-info">
                        <h4>' . JText::_('COM_DIGIWALLET_DONATION_BANKWIRE_RESPONSE_THANK_TEXT'). '</h4>
                        <p>' . JText::sprintf('COM_DIGIWALLET_DONATION_BANKWIRE_RESPONSE_TEXT_1', $paymentInfo->amount, $iban, $beneficiary) .'</p>
                        <p>' . JText::sprintf('COM_DIGIWALLET_DONATION_BANKWIRE_RESPONSE_TEXT_2',$trxid, ''). '</p>
                        <p>' . JText::sprintf('COM_DIGIWALLET_DONATION_BANKWIRE_RESPONSE_TEXT_3',$bic, $bank). ' </p>
                        <p>' . JText::_('COM_DIGIWALLET_DONATION_BANKWIRE_RESPONSE_TEXT_4'). '</p>
                    </div>';
        $this->params->def('html', $html);
        parent::display($tpl);
    }
}
