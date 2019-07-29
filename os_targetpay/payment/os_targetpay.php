<?php
/**
 * @version		1.0
 * @package		Joomla
 * @subpackage	Joom Donation
 * @author		DigiWallet.nl
 * @copyright	Copyright (C) 2017 DigiWallet.nl
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

require_once (dirname(__FILE__) . '/targetpay.class.php');

class os_targetpay extends OSFPayment
{

    const TARGETPAY_BANKWIRE_METHOD = 'BW';
    
    public $listMethods = array(
        "IDE" => array(
            'name' => 'iDEAL',
            'min' => 0.84,
            'max' => 10000
        ),
        "MRC" => array(
            'name' => 'Bancontact',
            'min' => 0.49,
            'max' => 10000
        ),
        "DEB" => array(
            'name' => 'Sofort Banking',
            'min' => 0.1,
            'max' => 5000
        ),
        'WAL' => array(
            'name' => 'Paysafecard',
            'min' => 0.1,
            'max' => 150
        ),
        'CC' => array(
            'name' => 'Creditcard',
            'min' => 1,
            'max' => 10000
        ),
        'AFP' => array(
            'name' => 'Afterpay',
            'min' => 5,
            'max' => 10000
        ),
        'PYP' => array(
            'name' => 'Paypal',
            'min' => 0.84,
            'max' => 10000
        ),
        'BW' => array(
            'name' => 'Bankwire',
            'min' => 0.84,
            'max' => 10000
        )
    );

    public $salt = 'e381277';

    public $defaultRtlo = 93929;

    public $tpTable = '#__joomDonation_targetpay';

    /**
     * Constructor functions, init some parameter
     *
     * @param JRegistry $params            
     * @param array $config            
     */
    public function __construct($params, $config = array())
    {
        parent::__construct($params, $config);
        $this->setParameter('rtlo', $params->get('tp_rtlo', $this->defaultRtlo));
        $this->setParameter('currency', 'EUR');
        $this->setParameter('language', 'nl');
        foreach ($this->listMethods as $id => $method) {
            $varName = 'tp_enable_' . strtolower($id);
            $this->setParameter($varName, $params->get($varName, 1));
        }
        $jlang = JFactory::getLanguage();
        $jlang->load('com_jdonation_payment_targetpay', JPATH_SITE, null, true);
    }

    /**
     * Process Payment
     */
    public function processPayment($row, $data)
    {
        $app = JFactory::getApplication();
        $app->redirect(JURI::base() . 'index.php?option=com_jdonation&view=targetpay&id=' . $row->id);
    }

    /**
     * Confirm payment process
     *
     * @return boolean : true if success, otherwise return false
     */
    public function verifyPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processReport();
        } else {
            $this->processReturn();
        }
    }

    /**
     * Submit post to targetpay server
     */
    public function formOptions($row)
    {
        $error = '';
        if (! empty($_POST['payment_option_select'][$_POST['targetpay_method']])) {
            $siteUrl = JURI::base();
            $option = (! empty($_POST['payment_option_select'][$_POST['targetpay_method']]) ? $_POST['payment_option_select'][$_POST['targetpay_method']] : false);
            $TargetPayCore = new TargetPayCore($_POST['targetpay_method'], $this->getParameter('rtlo'), $this->getParameter('language'));
            $return_url = $siteUrl . 'index.php?option=com_jdonation&task=payment_confirm&payment_method=os_targetpay&tp_method=' . $TargetPayCore->getPayMethod();
            $report_url = $siteUrl . 'index.php?option=com_jdonation&task=payment_confirm&payment_method=os_targetpay&tp_method=' . $TargetPayCore->getPayMethod();
            if ($option) {
                if( $TargetPayCore->getPayMethod() == 'IDE' )
                    $TargetPayCore->setBankId($option);
                
                if( $TargetPayCore->getPayMethod() == 'DEB' )
                    $TargetPayCore->setCountryId($option);
            }
            $TargetPayCore->setAmount($row->amount * 100);
            $description = 'Donatie id: ' . $row->transaction_id;
            $TargetPayCore->setDescription($description);
            $TargetPayCore->setReturnUrl($return_url);
            $TargetPayCore->setReportUrl($report_url);
            $email = null;
            $user = JFactory::getUser();
            if(!empty($user->get('email'))) {
                $email = $user->get('email');
            } else {
                $email = $row->email;
            }
            if ($email) {
                $TargetPayCore->bindParam('email', $email);
            }
            $this->additionalParameters($row, $TargetPayCore);
            
            $result = @$TargetPayCore->startPayment();
            if ($result !== false) {
                $data["cart_id"] = $row->id;
                $data["rtlo"] = $this->getParameter('rtlo');
                $data["paymethod"] = $TargetPayCore->getPayMethod();
                $data["transaction_id"] = $TargetPayCore->getTransactionId();
                $data["bank_id"] = $TargetPayCore->getBankId();
                $data["country_id"] = $TargetPayCore->getCountryId();
                $data["description"] = $TargetPayCore->getDescription();
                $data["amount"] = $row->amount;
                $this->__storeTargetpayRequestData($data);
                
                //show instruction page if method == bw
                if ($TargetPayCore->getPayMethod() == self::TARGETPAY_BANKWIRE_METHOD) {
                    list($trxid, $accountNumber, $iban, $bic, $beneficiary, $bank) = explode("|", $TargetPayCore->getMoreInformation());
                    $html = '<div class="bankwire-info">
                                <h4>' . JText::_('JD_TARGETPAY_BANKWIRE_RESPONSE_THANK_TEXT'). '</h4>
                                <p>' . JText::sprintf('JD_TARGETPAY_BANKWIRE_RESPONSE_TEXT_1', $row->amount, $iban, $beneficiary) .'</p>
                                <p>' . JText::sprintf('JD_TARGETPAY_BANKWIRE_RESPONSE_TEXT_2',$trxid, $row->email). '</p>
                                <p>' . JText::sprintf('JD_TARGETPAY_BANKWIRE_RESPONSE_TEXT_3',$bic, $bank). ' </p>
                                <p>' . JText::_('JD_TARGETPAY_BANKWIRE_RESPONSE_TEXT_4'). '</p>
                            </div>';
                    return $html;
                } else {
                    return header('Location: ' . $TargetPayCore->getBankUrl());
                    exit();
                }
            } else {
                $error = $TargetPayCore->getErrorMessage();
            }
        }
        
        $html = '<p>' . JText::_('JD_TARGETPAY_SELECT_METHOD').'</p>';
        $html .= '<form name="jd_form" id="jd_form" class="form form-horizontal targetpay-frm" method="post" action="">';
        $html .= '<p class="text-error">{error}</p>';
        $html .= $this->makeOptions($row->amount);
        $html .= '<div class="form-group"><input type="submit" name="Submit" class="btn btn-primary" value="' . JText::_('JD_TARGETPAY_PAY_BTN') .'" /></div>';
        $html .= '</form>';
        $html = str_replace('{error}', ((strlen($error) > 0) ? 'There was a problem: ' . $error : ''), $html);
        return $html;
    }
    
    /**
     *  Bind parameters
     */
    public function additionalParameters($row, $TargetPayCore)
    {
        $db          = JFactory::getDbo();
        $query       = $db->getQuery(true);
        $query->select('*')
        ->from('#__jd_campaigns')
        ->where('id = ' . (int) $row->campaign_id);
        $db->setQuery($query);
        
        $rowCampaign = $db->loadObject();
        switch ($TargetPayCore->getPayMethod()) {
            case 'IDE':
            case 'MRC':
            case 'DEB':
            case 'CC':
            case 'WAL':
            case 'PYP':
                break;
            case 'BW':
                $TargetPayCore->bindParam('salt', $this->salt);
                $TargetPayCore->bindParam('email', $row->email);
                $TargetPayCore->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
                break;
            case 'AFP':
                // Getting the items in the order
                $invoicelines[] = [
                'productCode' => $row->campaign_id,
                'productDescription' => $rowCampaign->title,
                'quantity' => 1,
                'price' => $row->amount,
                'taxCategory' => 4 //no tax for donation
                ];
                
                $billingCountry = $this->getCountry3Code($row->country);
                $shippingCountry = $billingCountry = ($billingCountry == 'BEL' ? 'BEL' : 'NLD');
                $streetParts = self::breakDownStreet($row->address);
                
                $TargetPayCore->bindParam('billingstreet', $streetParts['street']);
                $TargetPayCore->bindParam('billinghousenumber', $streetParts['houseNumber'].$streetParts['houseNumberAdd']);
                $TargetPayCore->bindParam('billingpostalcode', $row->zip);
                $TargetPayCore->bindParam('billingcity', $row->city);
                $TargetPayCore->bindParam('billingpersonemail', $row->email);
                $TargetPayCore->bindParam('billingpersoninitials', "");
                $TargetPayCore->bindParam('billingpersongender', "");
                $TargetPayCore->bindParam('billingpersonbirthdate', "");
                $TargetPayCore->bindParam('billingpersonsurname', $row->last_name);
                $TargetPayCore->bindParam('billingcountrycode', $billingCountry);
                $TargetPayCore->bindParam('billingpersonlanguagecode', $billingCountry);
                $TargetPayCore->bindParam('billingpersonphonenumber', self::format_phone($billingCountry, $row->phone));
                
                $TargetPayCore->bindParam('shippingstreet', $streetParts['street']);
                $TargetPayCore->bindParam('shippinghousenumber', $streetParts['houseNumber'].$streetParts['houseNumberAdd']);
                $TargetPayCore->bindParam('shippingpostalcode', $row->zip);
                $TargetPayCore->bindParam('shippingcity', $row->city);
                $TargetPayCore->bindParam('shippingpersonemail', $row->email);
                $TargetPayCore->bindParam('shippingpersoninitials', "");
                $TargetPayCore->bindParam('shippingpersongender', "");
                $TargetPayCore->bindParam('shippingpersonbirthdate', "");
                $TargetPayCore->bindParam('shippingpersonsurname', $row->last_name);
                $TargetPayCore->bindParam('shippingcountrycode', $shippingCountry);
                $TargetPayCore->bindParam('shippingpersonlanguagecode', $shippingCountry);
                $TargetPayCore->bindParam('shippingpersonphonenumber', self::format_phone($shippingCountry, $row->phone));
                
                $TargetPayCore->bindParam('invoicelines', json_encode($invoicelines));
                $TargetPayCore->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
                break;
        }
    }
    
    private static function format_phone($country, $phone) {
        $function = 'format_phone_' . strtolower($country);
        if(method_exists('os_targetpay', $function)) {
            return self::$function($phone);
        } else {
            echo "unknown phone formatter for country: ". $function;
            exit;
        }
        return $phone;
    }
    
    private static function format_phone_nld($phone) {
        // note: making sure we have something
        if(!isset($phone{3})) { return ''; }
        // note: strip out everything but numbers
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $length = strlen($phone);
        switch($length) {
            case 9:
                return "+31".$phone;
                break;
            case 10:
                return "+31".substr($phone, 1);
                break;
            case 11:
            case 12:
                return "+".$phone;
                break;
            default:
                return $phone;
                break;
        }
    }
    
    private static function format_phone_bel($phone) {
        // note: making sure we have something
        if(!isset($phone{3})) { return ''; }
        // note: strip out everything but numbers
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $length = strlen($phone);
        switch($length) {
            case 9:
                return "+32".$phone;
                break;
            case 10:
                return "+32".substr($phone, 1);
                break;
            case 11:
            case 12:
                return "+".$phone;
                break;
            default:
                return $phone;
                break;
        }
    }
    
    private static function breakDownStreet($street)
    {
        $out = [];
        $addressResult = null;
        preg_match("/(?P<address>\D+) (?P<number>\d+) (?P<numberAdd>.*)/", $street, $addressResult);
        if(!$addressResult) {
            preg_match("/(?P<address>\D+) (?P<number>\d+)/", $street, $addressResult);
        }
        $out['street'] = array_key_exists('address', $addressResult) ? $addressResult['address'] : null;
        $out['houseNumber'] = array_key_exists('number', $addressResult) ? $addressResult['number'] : null;
        $out['houseNumberAdd'] = array_key_exists('numberAdd', $addressResult) ? trim(strtoupper($addressResult['numberAdd'])) : null;
        return $out;
    }
    
    /**
     * Process report
     * checkPayment from api & update status
     */
    private function processReport()
    {
        $db = JFactory::getDBO();
        $tp_method = $_REQUEST['tp_method'];
        switch ($tp_method) {
            case 'PYP':
                $trxid = $_REQUEST['acquirerID'];
                break;
            case 'AFP':
                $trxid = $_REQUEST['invoiceID'];
                break;
            case 'IDE':
            case 'MRC':
            case 'DEB':
            case 'CC':
            case 'WAL':
            case 'BW':
            default:
                $trxid = $_REQUEST['trxid'];
        }
        $targetInfo = $this->__retrieveTargetpayInformation("transaction_id = '" . $trxid. "'");
        $row = JTable::getInstance('jdonation', 'Table');
        $row->load($targetInfo->cart_id);
        
        if (! $targetInfo)
            die('Transaction is not found');
        
        if ($row->published)
            die("Donation $targetInfo->cart_id had been done");
        
        $this->checkPayment($targetInfo, $row);
        die('Done');
    }

    /**
     * Process return url
     * check status & redirect to result page
     */
    private function processReturn()
    {
        $app = JFactory::getApplication();
        $tp_method = $_REQUEST['tp_method'];
        switch ($tp_method) {
            case 'PYP':
                $trxid = $_REQUEST['paypalid'];
                break;
            case 'AFP':
                $trxid = $_REQUEST['invoiceID'];
                break;
            case 'IDE':
            case 'MRC':
            case 'DEB':
            case 'CC':
            case 'WAL':
            case 'BW':
            default:
                $trxid = $_REQUEST['trxid'];
        }
        $targetInfo = $this->__retrieveTargetpayInformation("transaction_id = '" . $trxid. "'");
        if (!$targetInfo)
            $app->redirect(JRoute::_('index.php?option=com_jdonation&view=donation'));
        $row = JTable::getInstance('jdonation', 'Table');
        $row->load($targetInfo->cart_id);
        if (!$row->published) {
            $this->checkPayment($targetInfo, $row);
            $row->load($targetInfo->cart_id);
        }
        
        if ($row->published) {
            $app->redirect(JRoute::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id), false));
        } else {
            $targetInfo = $this->__retrieveTargetpayInformation("transaction_id = '" . $trxid. "'");
            $_SESSION['reason'] = $targetInfo->message;
            $app->redirect(JRoute::_(DonationHelperRoute::getDonationFailureRoute($row->id, $row->campaign_id), false));
        }
    }

    public function checkPayment($targetInfo, $row)
    {
        $TargetPayCore = new TargetPayCore($targetInfo->paymethod, $targetInfo->rtlo, $this->getParameter('language'));
        $TargetPayCore->checkPayment($targetInfo->transaction_id, $this->getAdditionParametersReport($targetInfo));
        if ($TargetPayCore->getPaidStatus()) { //success
            $amountPaid = $targetInfo->amount;
            if($targetInfo->paymethod == self::TARGETPAY_BANKWIRE_METHOD) {
                $consumber_info = $TargetPayCore->getConsumerInfo();
                if (!empty($consumber_info) && $consumber_info['bw_paid_amount'] > 0) {
                    $amountPaid = number_format($consumber_info['bw_paid_amount'] / 100, 2);
                }
            }
            $row->amount = $amountPaid;
            $this->onPaymentSuccess($row, $targetInfo->transaction_id);
        } else {
            $errorMessage = $TargetPayCore->getErrorMessage();
            $this->updatePaymentInfo([
                "`message` = '$errorMessage'"
            ], $targetInfo->transaction_id);
            echo $errorMessage;
        }
    }
    
    /**
     * Make hidden field from array
     *
     * @param array $arr            
     * @return string
     */
    private function makeHiddenFields($arr)
    {
        $hidden = '';
        foreach ($arr as $key => $value) {
            if ($key !== 'Submit') {
                $hidden .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />';
            }
        }
        return $hidden;
    }

    /**
     * Build html for targetpay plugin
     *
     * @return string
     */
    private function makeOptions($amount)
    {
        $html = '';
        $payment_method = 'IDE';
        
        $bankArrByPaymentOption = array();
        /* remove unwanted paymethods */
        foreach ($this->listMethods as $id => $method) {
            $varName = 'tp_enable_' . strtolower($id);
            if ($this->getParameter($varName) == 1 && ($amount <= $method['max'] && $amount >= $method['min'])) {
                $bankArrByPaymentOption[$id] = $this->paymentArraySelection($id, $this->getParameter('rtlo'));
            }
        }
        if (! empty($bankArrByPaymentOption)) {
            foreach ($bankArrByPaymentOption as $paymentOption => $bankCodesArr) {
                $checked_method = '';
                $bankListCount = count($bankCodesArr);
                if ($paymentOption == $payment_method) {
                    $checked_method = 'checked="checked"';
                }
                $html .= '<div class="control-group">';
                $html .= '<label class="control-label"><input id="targetpay_method_' . $paymentOption . '" name="targetpay_method" value="' . $paymentOption . '" ' . $checked_method . ' type="radio">' . 
                    '<img src="'. JURI::root() .'components/com_jdonation/view/targetpay/images/' . $paymentOption .'.png"/>'.
                    '</label>';
                $html .= '<div class="controls">';
                if ($bankListCount == 0) {
                    $html .= JText::_('No banks found for this payment option');
                } else if ($bankListCount == 1) {
                    $html .= '<input value="' . $paymentOption . '" name="payment_option_select[' . $paymentOption . ']" type="hidden">';
                } else {
                    $html .= '<select data-method="targetpay_method_' . $paymentOption . '" class="sel-payment-data" name="payment_option_select[' . $paymentOption . ']"
                        onclick="jQuery(\'#\' + jQuery(this).data(\'method\')).prop(\'checked\',\'checked\');">';
                    foreach ($bankCodesArr as $key => $value) {
                        $html .= '<option value="' . $key . '">' . $value . '</option>';
                    }
                    $html .= '</select>';
                }
                $html .= '</div></div>';
            }
        }
        
        return $html;
    }

    /**
     * Get array option of method
     *
     * @param string $method
     * @param string $rtlo
     * @return array
     */
    private function paymentArraySelection($method, $rtlo)
    {
        switch ($method) {
            case "IDE":
                $idealOBJ = new TargetPayCore($method, $rtlo);
                return $idealOBJ->getBankList();
                break;
            case "DEB":
                $directEBankingOBJ = new TargetPayCore($method, $rtlo);
                return $directEBankingOBJ->getCountryList();
                break;
            case "MRC":
            case "WAL":
            case "CC":
            case "BW":
            case "PYP":
            case "AFP":
                return array(
                    $method => $method
                );
                break;
            default:
        }
    }

    /**
     * Insert payment info into table #__joomDonation_targetpay
     *
     * @param unknown $data
     * @return mixed
     */
    private function __storeTargetpayRequestData($data)
    {
        // Get a db connection.
        $db = JFactory::getDbo();
        
        // Create a new query object.
        $query = $db->getQuery(true);
        
        foreach ($data as $key => $value) {
            $columns[] = $key;
            $values[] = $db->quote($value);
        }
        
        // Prepare the insert query.
        $query->insert($db->quoteName($this->tpTable))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        
        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        $db->execute();
        return $db->insertid();
    }

    /**
     * Update targetpay table
     *
     * @param string $trxid
     *
     * @return mixed
     */
    private function updatePaymentInfo($set, $trxid)
    {
        // Get a db connection.
        $db = JFactory::getDbo();
        
        // Create a new query object.
        $query = $db->getQuery(true);
        // Prepare the update query.
        $query->update($db->quoteName($this->tpTable))
            ->set($set)
            ->where("transaction_id = '" . $trxid . "'");
        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        return $db->execute();
    }
    
    /**
     * addition params for report
     * @return array
     */
    protected function getAdditionParametersReport($paymentTable)
    {
        $param = [];
        if ($paymentTable->paymethod== self::TARGETPAY_BANKWIRE_METHOD) {
            $checksum = md5($paymentTable->transaction_id. $paymentTable->rtlo. $this->salt);
            $param['checksum'] = $checksum;
        }
        
        return $param;
    }

    /**
     * Get payment info in table __joomDonation_targetpay
     *
     * @param string $trxid            
     * @return mixed|void|NULL
     */
    public function __retrieveTargetpayInformation($cond)
    {
        // Get a db connection.
        $db = JFactory::getDbo();
        
        // Create a new query object.
        $query = $db->getQuery(true);
        
        // Select all records from the user profile table where key begins with "custom.".
        // Order it by the ordering field.
        $query->select(array(
            'id',
            'cart_id',
            'rtlo',
            'paymethod',
            'transaction_id',
            'bank_id',
            'description',
            'amount',
            'message',
        ));
        
        $query->from($this->tpTable);
        $query->where($cond);
        
        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        $db->execute();
        // Load the results as a list of stdClass objects.
        return $db->loadObject();
    }
    
    /**
     * Get country 3 code
     *
     * @param string $countryName
     *
     * @return string
     */
    public static function getCountry3Code($countryName)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('country_3_code')
        ->from('#__jd_countries')
        ->where('LOWER(name) = ' . $db->quote(JString::strtolower($countryName)));
        $db->setQuery($query);
        $countryCode = $db->loadResult();
        if (!$countryCode)
        {
            $countryCode = 'NLD';
        }
        
        return $countryCode;
    }
}
