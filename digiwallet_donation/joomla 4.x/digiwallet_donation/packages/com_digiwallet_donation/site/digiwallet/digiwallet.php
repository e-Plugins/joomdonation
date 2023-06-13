<?php
use Digiwallet\Packages\Transaction\Client\Client;
use Digiwallet\Packages\Transaction\Client\Request\CreateTransaction;
use Digiwallet\Packages\Transaction\Client\Request\CheckTransaction;

require_once JPATH_COMPONENT_SITE . '/digiwallet/digiwallet.class.php';
require_once JPATH_COMPONENT_SITE . '/vendor/autoload.php';

class DigiwalletDonation
{
    const DIGIWALLET_API = 'https://api.digiwallet.nl/';
    const DIGIWALLET_BANKWIRE_METHOD = 'BW';
    const DIGIWALLET_EPS_METHOD = 'EPS';
    const DIGIWALLET_GIROPAY_METHOD = 'GIP';
    const DIGIWALLET_STATUS_PENDING = 0;
    const DIGIWALLET_STATUS_FAILED = 1;
    const DIGIWALLET_STATUS_SUCCESS = 2;

    public $item;
    public $amount;
    public $configuration;
    public $digiwalletDonationTable = '#__digiwallet_donation';

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

    public function __construct($configuration = null, $item = null)
    {
        $this->amount = (float) @$item->amount;
        $this->configuration = $configuration;
        $this->item = $item;
    }


    public function startPayment($paymentMethod, $paymentOption=null)
    {
        $lang = JFactory::getLanguage();
        $transactionId = $bankUrl = $message = $result = $moreInformation = null;
        $rtlo = $this->configuration['dw_rtlo'];
        $token = $this->configuration['dw_token'];
        $description = 'Donate ' . date("Y-m-d H:i;s");
        // set return & report
        $urlCallback = JURI::base().substr(JRoute::_("index.php?option=com_digiwallet_donation&view=callback&paymethod={$paymentMethod}"), strlen(JURI::base(true)) + 1);
        if (in_array($paymentMethod, [self::DIGIWALLET_EPS_METHOD, self::DIGIWALLET_GIROPAY_METHOD])) {
            $digiwalletApi = new Client(self::DIGIWALLET_API);
            $formParams = [
                'outletId' => $rtlo,
                'currencyCode' => 'EUR',
                'description' => $description,
                'returnUrl' => $urlCallback,
                'reportUrl' => $urlCallback,
                'consumerIp' => $this->getCustomerIP(),
                'suggestedLanguage' => 'NLD',
                'amountChangeable' => false,
                'inputAmount' => round($this->amount * 100),
                'paymentMethods' => [
                    $paymentMethod,
                ],
                'app_id' => DigiWalletCore::APP_ID,
            ];

            $request = new CreateTransaction($digiwalletApi, $formParams);
            $request->withBearer($token);
            try {

                /** @var \Digiwallet\Packages\Transaction\Client\Response\CreateTransaction $apiResult */
                $apiResult = $request->send();
                $result = 0 == $apiResult->status() ? true : false;
                $message = $apiResult->message();
                $transactionId = $apiResult->transactionId();
                $bankUrl = $apiResult->launchUrl();
            } catch (\Exception $exception) {
                $result = false;
                $message = $exception->getMessage();
            }
        } else {
            $digiWalletCore = new DigiWalletCore($paymentMethod, $rtlo, $lang->getTag());
            $digiWalletCore->setAmount(round($this->amount * 100));
            $digiWalletCore->setDescription($description); // $order->id
            $digiWalletCore->setReturnUrl($urlCallback);
            $digiWalletCore->setReportUrl($urlCallback);
            if ($paymentOption) {
                if( $paymentMethod == 'IDE' ) {
                    $digiWalletCore->setBankId($paymentOption);
                } elseif( $paymentMethod == 'DEB' ) {
                    $digiWalletCore->setCountryId($paymentOption);
                }
            }
            $this->additionalParameters($digiWalletCore);
            $result = $digiWalletCore->startPayment();
            $transactionId = $digiWalletCore->getTransactionId();
            $bankUrl = $digiWalletCore->getBankUrl();
            $message = $digiWalletCore->getErrorMessage();
            $moreInformation = $digiWalletCore->getMoreInformation();
        }
        if ($result !== false) {
            $data["button_id"] = $this->item->id;
            $data["rtlo"] = $rtlo;
            $data["token"] = $token;
            $data["transaction_id"] = $transactionId;
            $data["amount"] = $this->amount;
            $data["payment_method"] = $paymentMethod;
            $data["bw_data"] = $moreInformation;
            $data["created_date"] = date("Y-m-d H:i:s");
            $row_id = $this->__storeDigiwalletRequestData($data);

            //show instruction page if method == bw
            if ($paymentMethod == self::DIGIWALLET_BANKWIRE_METHOD) {
                $bw_url = JURI::base().substr(JRoute::_("index.php?option=com_digiwallet_donation&view=bankwire&transaction_id={$transactionId}"), strlen(JURI::base(true)) + 1);
                header('Location: ' . $bw_url);
                exit();
            } else {
                header('Location: ' . $bankUrl);
                exit();
            }
        } else {
            return $message;
        }
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
     * Process report
     * checkPayment from api & update status
     */
    private function processReport()
    {
        $db = JFactory::getDBO();
        $tp_method = $_REQUEST['paymethod'];
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
        $donationInfo = $this->__retrieveDigiwalletInformation("transaction_id = '" . $trxid. "'");
        if (! $donationInfo) {
            die('Transaction is not found');
        }
        if ($donationInfo->status == self::DIGIWALLET_STATUS_SUCCESS) {
            die("Transaction $donationInfo->transaction_id had been done");
        }
        $this->checkPayment($donationInfo);
        die();
    }

    /**
     * Process return url
     * check status & redirect to result page
     */
    private function processReturn()
    {
        $app = JFactory::getApplication();
        $tp_method = $_REQUEST['paymethod'];
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
        $donationInfo = $this->__retrieveDigiwalletInformation("transaction_id = '" . $trxid. "'");
        if (!$donationInfo) {
            //redirect to home
            $app->redirect(JURI::root());
        }

        if ($donationInfo->status != self::DIGIWALLET_STATUS_SUCCESS) {
            $this->checkPayment($donationInfo);
            $donationInfo = $this->__retrieveDigiwalletInformation("transaction_id = '" . $trxid. "'");
        }
        $query = parse_url($this->configuration['dw_return'], PHP_URL_QUERY);
        if ($query) {
            $redirect_url = $this->configuration['dw_return'] . "&transaction_id={$trxid}";
        } else {
            $redirect_url = $this->configuration['dw_return'] . "?transaction_id={$trxid}";
        }
        if ($donationInfo->status == self::DIGIWALLET_STATUS_SUCCESS) {
            $app->redirect("$redirect_url&status=success");
        } else {
            $app->redirect("$redirect_url&status=false");
        }
    }
    public function checkPayment($donationInfo)
    {
        $isSuccess = false;
        $errorMessage = null;
        $amountPaid = null;
        if (in_array($donationInfo->payment_method, [self::DIGIWALLET_EPS_METHOD, self::DIGIWALLET_GIROPAY_METHOD])) {
            $digiwalletApi = new Client(self::DIGIWALLET_API);
            $request = new CheckTransaction($digiwalletApi);
            $request->withBearer($donationInfo->token);
            $request->withOutlet($donationInfo->rtlo);
            $request->withTransactionId($donationInfo->transaction_id);
            /** @var \Digiwallet\Packages\Transaction\Client\Response\CheckTransaction $apiResult */
            $apiResult = $request->send();
            $apiStatus = $apiResult->getStatus();
            $isSuccess = (0 == $apiStatus && 'Completed' == $apiResult->getTransactionStatus()) ? true : false;
            $errorMessage = $apiResult->getMessage();
        } else {
            $DigiWalletCore = new DigiWalletCore($donationInfo->payment_method, $donationInfo->rtlo);
            $DigiWalletCore->checkPayment($donationInfo->transaction_id, $this->getAdditionParametersReport($donationInfo));
            $isSuccess = $DigiWalletCore->getPaidStatus();
            $errorMessage = $DigiWalletCore->getErrorMessage();
        }

        if ($isSuccess) { //success
            $amountPaid = $donationInfo->amount;
            if($donationInfo->payment_method == self::DIGIWALLET_BANKWIRE_METHOD) {
                $consumber_info = $DigiWalletCore->getConsumerInfo();
                if (!empty($consumber_info) && $consumber_info['bw_paid_amount'] > 0) {
                    $amountPaid = number_format($consumber_info['bw_paid_amount'] / 100, 2);
                }
            }
            $this->updatePaymentInfo([
                "`amount_paid` = '$amountPaid', `status` = '" . self::DIGIWALLET_STATUS_SUCCESS . "', `payment_date` = '" . date("Y-m-d H:i:s") . "'"
            ], $donationInfo->transaction_id);
            echo 'Success';
        } else {
            $this->updatePaymentInfo([
                "`message` = '$errorMessage', `status` = '" . self::DIGIWALLET_STATUS_FAILED . "'"
            ], $donationInfo->transaction_id);
            echo $errorMessage;
        }
    }

    /**
     * addition params for report
     * @return array
     */
    protected function getAdditionParametersReport($donationInfo)
    {
        $param = [];
        if ($donationInfo->payment_method == self::DIGIWALLET_BANKWIRE_METHOD) {
            $checksum = md5($donationInfo->transaction_id. $donationInfo->rtlo. $this->salt);
            $param['checksum'] = $checksum;
        }

        return $param;
    }

    private function additionalParameters($digiwallet) {
        if ($digiwallet->getPayMethod() == 'AFP') {
            $digiwallet->bindParam('billingstreet', '');
            $digiwallet->bindParam('billinghousenumber', '');
            $digiwallet->bindParam('billingpostalcode', '');
            $digiwallet->bindParam('billingcity', '');
            $digiwallet->bindParam('billingpersonemail', '');
            $digiwallet->bindParam('billingpersoninitials', '');
            $digiwallet->bindParam('billingpersongender', '');
            $digiwallet->bindParam('billingpersonfirstname', '');
            $digiwallet->bindParam('billingpersonsurname', '');
            $digiwallet->bindParam('billingcountrycode', '');
            $digiwallet->bindParam('billingpersonlanguagecode', '');
            $digiwallet->bindParam('billingpersonbirthdate', "");
            $digiwallet->bindParam('billingpersonphonenumber', '');

            $digiwallet->bindParam('shippingstreet', '');
            $digiwallet->bindParam('shippinghousenumber', '');
            $digiwallet->bindParam('shippingpostalcode', '');
            $digiwallet->bindParam('shippingcity', '');
            $digiwallet->bindParam('shippingpersonemail', '');
            $digiwallet->bindParam('shippingpersoninitials', "");
            $digiwallet->bindParam('shippingpersongender', "");
            $digiwallet->bindParam('shippingpersonfirstname', '');
            $digiwallet->bindParam('shippingpersonsurname', '');
            $digiwallet->bindParam('shippingcountrycode', '');
            $digiwallet->bindParam('shippingpersonlanguagecode', '');
            $digiwallet->bindParam('shippingpersonbirthdate', "");
            $digiwallet->bindParam('shippingpersonphonenumber', '');

            // Getting the items in the order
            $invoicelines = array(array(
                'productCode' => 'Donation button 1',
                'productDescription' => '',
                'quantity' => 1,
                'price' => $this->amount, // Price without tax
                'taxCategory' => $digiwallet->getTax()
            ));

            $invoicelines[] = array(
                'productCode' => '000000',
                'productDescription' => "Other fees (shipping, additional fees)",
                'quantity' => 1,
                'price' =>  $this->amount,
                'taxCategory' => 3
            );

            $digiwallet->bindParam('invoicelines', json_encode($invoicelines));
            $digiwallet->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
        } else if ($digiwallet->getPayMethod() == 'BW') {
            $digiwallet->bindParam('salt', $this->salt);
            $digiwallet->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
        }
    }

    public function getBankArray()
    {
        $bankArrByPaymentOption = array();
        /* remove unwanted paymethods */
        foreach ($this->listMethods as $id => $method) {
            $varName = 'digiwallet_enable_' . strtolower($id);
            if ( isset($this->configuration[$varName]) &&
                $this->configuration[$varName] == 1 &&
                (in_array($id, [self::DIGIWALLET_EPS_METHOD, self::DIGIWALLET_GIROPAY_METHOD]) || ($this->amount <= $method['max'] && $this->amount >= $method['min']))) {
                $bankArrByPaymentOption[$id] = $this->paymentArraySelection($id, $this->configuration['dw_rtlo']);
            }
        }
        return $bankArrByPaymentOption;
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
                $idealOBJ = new DigiWalletCore($method, $rtlo);
                return $idealOBJ->getBankList();
                break;
            case "DEB":
                $directEBankingOBJ = new DigiWalletCore($method, $rtlo);
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
                return array($method => $method);
                break;
            default:
        }
    }

    /**
     * Insert payment info into table #__digiwallet_donation
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
        $query->insert($db->quoteName($this->digiwalletDonationTable))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        $db->execute();
        return $db->insertid();
    }
    /**
     * Update __digiwallet_donation table
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
        $query->update($db->quoteName($this->digiwalletDonationTable))
            ->set($set)
            ->where("transaction_id = '" . $trxid . "'");
        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        return $db->execute();
    }

    /**
     * Get payment info in table __digiwallet_donation
     *
     * @param string $cond
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
            'rtlo',
            'token',
            'amount',
            'amount_paid',
            'transaction_id',
            'payment_method',
            'bw_data',
            'status',
            'message',
        ));

        $query->from($this->digiwalletDonationTable);
        $query->where($cond);

        // Reset the query using our newly populated query object.
        $db->setQuery($query);
        $db->execute();
        // Load the results as a list of stdClass objects.
        return $db->loadObject();
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
} // end of class DigiwalletDonation
