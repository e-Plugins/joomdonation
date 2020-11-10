<?php
/**
 * @version		1.0
 * @package		Joomla
 * @subpackage	Joom Donation
 * @author		DigiWallet.nl
 * @copyright	Copyright (C) 2020 DigiWallet.nl
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

require_once (dirname(__FILE__) . '/digiwallet.class.php');
require(dirname(__FILE__) . '/vendor/autoload.php');
class os_digiwallet extends OSFPayment
{

    const DIGIWALLET_API = "https://api.digiwallet.nl/";
    const DIGIWALLET_EPS_METHOD = 'EPS';
    const DIGIWALLET_GIROPAY_METHOD = 'GIP';
    const DIGIWALLET_BANKWIRE_METHOD = 'BW';
    
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
        ),
        'EPS' => array(
            'name' => 'EPS'
        ),
        'GIP' => array(
            'name' => 'Giropay'
        )
    );

    public $salt = 'e381277';

    public $defaultRtlo = 93929;

    public $tpTable = '#__joomDonation_digiwallet';

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
        $this->setParameter('token', $params->get('tp_token', null));
        $this->setParameter('currency', 'EUR');
        $this->setParameter('language', 'nl');
        foreach ($this->listMethods as $id => $method) {
            $varName = 'tp_enable_' . strtolower($id);
            $this->setParameter($varName, $params->get($varName, 1));
        }
        $jlang = JFactory::getLanguage();
        $jlang->load('com_jdonation_payment_digiwallet', JPATH_SITE, null, true);
    }

    /**
     * Process Payment
     */
    public function processPayment($row, $data)
    {
        $app = JFactory::getApplication();
        $app->redirect(JURI::base() . 'index.php?option=com_jdonation&view=digiwallet&id=' . $row->id);
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
     * Submit post to digiwallet server
     */
    public function formOptions($row)
    {
        $transactionId = $bankId = $countryId = $bankUrl = $message = $result = $moreInformation = null;
        $error = '';
        if (! empty($_POST['payment_option_select'][$_POST['digiwallet_method']])) {
            $siteUrl = JURI::base();
            $payMethod = $_POST['digiwallet_method'];
            $rtlo = $this->getParameter('rtlo');
            $token = $this->getParameter('token');
            $option = (! empty($_POST['payment_option_select'][$_POST['digiwallet_method']]) ? $_POST['payment_option_select'][$_POST['digiwallet_method']] : false);
            $return_url = $siteUrl . 'index.php?option=com_jdonation&task=payment_confirm&payment_method=os_digiwallet&tp_method=' . $payMethod;
            $report_url = $siteUrl . 'index.php?option=com_jdonation&task=payment_confirm&payment_method=os_digiwallet&tp_method=' . $payMethod;
            $email = null;
            $user = JFactory::getUser();
            if(!empty($user->get('email'))) {
                $email = $user->get('email');
            } else {
                $email = $row->email;
            }
            $description = 'Donatie id: ' . $row->transaction_id;
            if (in_array($payMethod, ['EPS', 'GIP'])) {
                $digiwalletApi = new Digiwallet\Packages\Transaction\Client\Client(self::DIGIWALLET_API);
                $formParams = [
                    'outletId' => $rtlo,
                    'currencyCode' => $this->getParameter('currency'),
                    'consumerEmail' => $email,
                    'description' => $description,
                    'returnUrl' => $return_url,
                    'reportUrl' => $report_url,
                    'consumerIp' => $this->getCustomerIP(),
                    'suggestedLanguage' => $this->getCountry3Code($row->country),
                    'amountChangeable' => false,
                    'inputAmount' => $row->amount * 100,
                    'paymentMethods' => [
                        $payMethod
                    ],
                    'app_id' => DigiwalletCore::APP_ID,
                ];
                
                $request = new Digiwallet\Packages\Transaction\Client\Request\CreateTransaction($digiwalletApi, $formParams);
                $request->withBearer($token);
                /** @var \Digiwallet\Packages\Transaction\Client\Response\CreateTransaction $apiResult */
                $apiResult = $request->send();
                $result = 0 == $apiResult->status() ? true : false;
                $message = $apiResult->message();
                $transactionId = $apiResult->transactionId();
                $bankUrl = $apiResult->launchUrl();
            } else {
                $digiWallet = new DigiwalletCore($payMethod, $rtlo, $this->getParameter('language'));
                if ($option) {
                    if( $payMethod == 'IDE' )
                        $digiWallet->setBankId($option);
                    
                    if( $payMethod == 'DEB' )
                        $digiWallet->setCountryId($option);
                }
                $digiWallet->setAmount($row->amount * 100);
                $digiWallet->setDescription($description);
                $digiWallet->setReturnUrl($return_url);
                $digiWallet->setReportUrl($report_url);
                if ($email) {
                    $digiWallet->bindParam('email', $email);
                }
                $this->additionalParameters($row, $digiWallet);
                $result = $digiWallet->startPayment();
                $transactionId = $digiWallet->getTransactionId();
                $bankId = $digiWallet->getBankId();
                $countryId = $digiWallet->getCountryId();
                $bankUrl = $digiWallet->getBankUrl();
                $message = $digiWallet->getErrorMessage();
                $moreInformation = $digiWallet->getMoreInformation();
            }
            if ($result !== false) {
                $data["cart_id"] = $row->id;
                $data["rtlo"] = $rtlo;
                $data["paymethod"] = $payMethod;
                $data["transaction_id"] = $transactionId;
                $data["bank_id"] = $bankId;
                $data["country_id"] = $countryId;
                $data["description"] = $description;
                $data["amount"] = $row->amount;
                $data["more_information"] = $moreInformation;
                $this->__storeDigiwalletRequestData($data);
                
                //show instruction page if method == bw
                if ($payMethod == self::DIGIWALLET_BANKWIRE_METHOD) {
                    list($trxid, $accountNumber, $iban, $bic, $beneficiary, $bank) = explode("|", $digiWallet->getMoreInformation());
                    $html = '<div class="bankwire-info">
                                <h4>' . JText::_('JD_DIGIWALLET_BANKWIRE_RESPONSE_THANK_TEXT'). '</h4>
                                <p>' . JText::sprintf('JD_DIGIWALLET_BANKWIRE_RESPONSE_TEXT_1', $row->amount, $iban, $beneficiary) .'</p>
                                <p>' . JText::sprintf('JD_DIGIWALLET_BANKWIRE_RESPONSE_TEXT_2',$trxid, $row->email). '</p>
                                <p>' . JText::sprintf('JD_DIGIWALLET_BANKWIRE_RESPONSE_TEXT_3',$bic, $bank). ' </p>
                                <p>' . JText::_('JD_DIGIWALLET_BANKWIRE_RESPONSE_TEXT_4'). '</p>
                            </div>';
                    return $html;
                } else {
                    return header('Location: ' . $bankUrl);
                    exit();
                }
            } else {
                $error = $message;
            }
        }
        
        $html = '<p>' . JText::_('JD_DIGIWALLET_SELECT_METHOD').'</p>';
        $html .= '<form name="jd_form" id="jd_form" class="form form-horizontal digiwallet-frm" method="post" action="">';
        $html .= '<p class="text-error">{error}</p>';
        $html .= $this->makeOptions($row->amount);
        $html .= '<div class="form-group"><input type="submit" name="Submit" class="btn btn-primary" value="' . JText::_('JD_DIGIWALLET_PAY_BTN') .'" /></div>';
        $html .= '</form>';
        $html = str_replace('{error}', ((strlen($error) > 0) ? 'There was a problem: ' . $error : ''), $html);
        return $html;
    }
    
    /**
     *  Bind parameters
     */
    public function additionalParameters($row, $DigiwalletCore)
    {
        $db          = JFactory::getDbo();
        $query       = $db->getQuery(true);
        $query->select('*')
        ->from('#__jd_campaigns')
        ->where('id = ' . (int) $row->campaign_id);
        $db->setQuery($query);
        
        $rowCampaign = $db->loadObject();
        switch ($DigiwalletCore->getPayMethod()) {
            case 'IDE':
            case 'MRC':
            case 'DEB':
            case 'CC':
            case 'WAL':
            case 'PYP':
            case 'EPS':
            case 'GIP':
                break;
            case 'BW':
                $DigiwalletCore->bindParam('salt', $this->salt);
                $DigiwalletCore->bindParam('email', $row->email);
                $DigiwalletCore->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
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
                
                $DigiwalletCore->bindParam('billingstreet', $streetParts['street']);
                $DigiwalletCore->bindParam('billinghousenumber', $streetParts['houseNumber'].$streetParts['houseNumberAdd']);
                $DigiwalletCore->bindParam('billingpostalcode', $row->zip);
                $DigiwalletCore->bindParam('billingcity', $row->city);
                $DigiwalletCore->bindParam('billingpersonemail', $row->email);
                $DigiwalletCore->bindParam('billingpersoninitials', "");
                $DigiwalletCore->bindParam('billingpersongender', "");
                $DigiwalletCore->bindParam('billingpersonbirthdate', "");
                $DigiwalletCore->bindParam('billingpersonsurname', $row->last_name);
                $DigiwalletCore->bindParam('billingcountrycode', $billingCountry);
                $DigiwalletCore->bindParam('billingpersonlanguagecode', $billingCountry);
                $DigiwalletCore->bindParam('billingpersonphonenumber', self::format_phone($billingCountry, $row->phone));
                
                $DigiwalletCore->bindParam('shippingstreet', $streetParts['street']);
                $DigiwalletCore->bindParam('shippinghousenumber', $streetParts['houseNumber'].$streetParts['houseNumberAdd']);
                $DigiwalletCore->bindParam('shippingpostalcode', $row->zip);
                $DigiwalletCore->bindParam('shippingcity', $row->city);
                $DigiwalletCore->bindParam('shippingpersonemail', $row->email);
                $DigiwalletCore->bindParam('shippingpersoninitials', "");
                $DigiwalletCore->bindParam('shippingpersongender', "");
                $DigiwalletCore->bindParam('shippingpersonbirthdate', "");
                $DigiwalletCore->bindParam('shippingpersonsurname', $row->last_name);
                $DigiwalletCore->bindParam('shippingcountrycode', $shippingCountry);
                $DigiwalletCore->bindParam('shippingpersonlanguagecode', $shippingCountry);
                $DigiwalletCore->bindParam('shippingpersonphonenumber', self::format_phone($shippingCountry, $row->phone));
                
                $DigiwalletCore->bindParam('invoicelines', json_encode($invoicelines));
                $DigiwalletCore->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
                break;
        }
    }
    
    private static function format_phone($country, $phone) {
        $function = 'format_phone_' . strtolower($country);
        if(method_exists('os_digiwallet', $function)) {
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
            case 'EPS':
            case 'GIP':
                $trxid = $_REQUEST['transactionID'];
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
        $digiwalletInfo = $this->__retrieveDigiwalletInformation("transaction_id = '" . $trxid. "'");
        $row = JTable::getInstance('jdonation', 'Table');
        $row->load($digiwalletInfo->cart_id);
        
        if (! $digiwalletInfo)
            die('Transaction is not found');
        
        if ($row->published)
            die("Donation $digiwalletInfo->cart_id had been done");
        
        $this->checkPayment($digiwalletInfo, $row);
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
            case 'EPS':
            case 'GIP':
                $trxid = $_REQUEST['transactionID'];
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
        $digiwalletInfo = $this->__retrieveDigiwalletInformation("transaction_id = '" . $trxid. "'");
        if (!$digiwalletInfo)
            $app->redirect(JRoute::_('index.php?option=com_jdonation&view=donation'));
        $row = JTable::getInstance('jdonation', 'Table');
        $row->load($digiwalletInfo->cart_id);
        if (!$row->published) {
            $this->checkPayment($digiwalletInfo, $row);
            $row->load($digiwalletInfo->cart_id);
        }
        
        if ($row->published) {
            $app->redirect(JRoute::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id), false));
        } else {
            $digiwalletInfo = $this->__retrieveDigiwalletInformation("transaction_id = '" . $trxid. "'");
            $_SESSION['reason'] = $digiwalletInfo->message;
            $app->redirect(JRoute::_(DonationHelperRoute::getDonationFailureRoute($row->id, $row->campaign_id), false));
        }
    }

    public function checkPayment($digiwalletInfo, $row)
    {
        if (in_array($digiwalletInfo->paymethod, ['EPS', 'GIP'])) {
            $digiwalletApi = new Digiwallet\Packages\Transaction\Client\Client(self::DIGIWALLET_API);
            $request = new Digiwallet\Packages\Transaction\Client\Request\CheckTransaction($digiwalletApi);
            $request->withBearer($this->getParameter('token'));
            $request->withOutlet($this->getParameter('rtlo'));
            $request->withTransactionId($digiwalletInfo->transaction_id);
            /** @var \Digiwallet\Packages\Transaction\Client\Response\CheckTransaction $apiResult */
            $apiResult = $request->send();
            $apiStatus = $apiResult->getStatus();
            $isSuccess = (0 == $apiStatus && 'Completed' == $apiResult->getTransactionStatus()) ? true : false;
            $errorMessage = $apiResult->getMessage();
        } else {
            $digiwallet = new DigiwalletCore($digiwalletInfo->paymethod, $digiwalletInfo->rtlo, $this->getParameter('language'));
            $digiwallet->checkPayment($digiwalletInfo->transaction_id, $this->getAdditionParametersReport($digiwalletInfo));
            $isSuccess = $digiwallet->getPaidStatus();
            $errorMessage = $digiwallet->getErrorMessage();
        }
        
        if ($isSuccess) { //success
            $amountPaid = $digiwalletInfo->amount;
            if($digiwalletInfo->paymethod == self::DIGIWALLET_BANKWIRE_METHOD) {
                $consumber_info = $digiwallet->getConsumerInfo();
                if (!empty($consumber_info) && $consumber_info['bw_paid_amount'] > 0) {
                    $amountPaid = number_format($consumber_info['bw_paid_amount'] / 100, 2);
                }
            }
            $row->amount = $amountPaid;
            $this->onPaymentSuccess($row, $digiwalletInfo->transaction_id);
        } else {
            $this->updatePaymentInfo([
                "`message` = '$errorMessage'"
            ], $digiwalletInfo->transaction_id);
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
     * Build html for digiwallet plugin
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
            if ($this->getParameter($varName) == 1 && (($amount <= $method['max'] && $amount >= $method['min']) || in_array($id, ['EPS', 'GIP']))) {
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
                $html .= '<label class="control-label"><input id="digiwallet_method_' . $paymentOption . '" name="digiwallet_method" value="' . $paymentOption . '" ' . $checked_method . ' type="radio">' . 
                    '<img src="'. JURI::root() .'components/com_jdonation/view/digiwallet/images/' . $paymentOption .'.png"/>'.
                    '</label>';
                $html .= '<div class="controls">';
                if ($bankListCount == 0) {
                    $html .= JText::_('No banks found for this payment option');
                } else if ($bankListCount == 1) {
                    $html .= '<input value="' . $paymentOption . '" name="payment_option_select[' . $paymentOption . ']" type="hidden">';
                } else {
                    $html .= '<select data-method="digiwallet_method_' . $paymentOption . '" class="sel-payment-data" name="payment_option_select[' . $paymentOption . ']"
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
                $idealOBJ = new DigiwalletCore($method, $rtlo);
                return $idealOBJ->getBankList();
                break;
            case "DEB":
                $directEBankingOBJ = new DigiwalletCore($method, $rtlo);
                return $directEBankingOBJ->getCountryList();
                break;
            case "MRC":
            case "WAL":
            case "CC":
            case "BW":
            case "PYP":
            case "AFP":
            case "EPS":
            case "GIP":
                return array(
                    $method => $method
                );
                break;
            default:
        }
    }

    /**
     * Insert payment info into table #__joomDonation_digiwallet
     *
     * @param unknown $data
     * @return mixed
     */
    private function __storeDigiwalletRequestData($data)
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
     * Update digiwallet table
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
        if ($paymentTable->paymethod== self::DIGIWALLET_BANKWIRE_METHOD) {
            $checksum = md5($paymentTable->transaction_id. $paymentTable->rtlo. $this->salt);
            $param['checksum'] = $checksum;
        }
        
        return $param;
    }

    /**
     * Get payment info in table __joomDonation_digiwallet
     *
     * @param string $trxid            
     * @return mixed|void|NULL
     */
    public function __retrieveDigiwalletInformation($cond)
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
    
    /***
     * Get user's ip address
     * @return mixed
     */
    public function getCustomerIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
}
