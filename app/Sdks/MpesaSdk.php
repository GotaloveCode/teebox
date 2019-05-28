<?php

namespace App\Sdks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Safaricom\Mpesa\Mpesa;

class MpesaSdk
{
    protected $app;
    protected $payload = [];
    protected $access_token;
    protected $response;
    protected $transaction_type;
    protected $expires_in = null;
    protected $authed_at = null;
    protected $config = [];
    protected $whitelist = [
        '196.201.214.206',
        '196.201.214.207',
        '196.201.214.208',
        '35.177.159.183'
    ];

    public function __construct($app = null, $auth = false, array $opts = [])
    {
        $app = $app ?: config('mpesa.default');
        $this->app = $app;
        $this->config = config("mpesa.apps.{$app}");

        if ($auth)
            $this->auth();
    }

    public function getConfig($key = null, $default = null)
    {
        if (!$key)
            return $this->config;

        return array_get($this->config, $key, $default);
    }

    public function stk_push($phone, $amount, $ref, $description)
    {
        $phone = encode_phone_number($phone);
        $mpesa = new Mpesa();
        $callback = url("api/ipn/stk/{$ref}");
        $shortcode = $this->getConfig('shortcode');
        $passkey = $this->getConfig('lipa_na_mpesa_passkey');
        $stkPushSimulation = $mpesa->STKPushSimulation(
            $shortcode, $passkey,
            'CustomerPayBillOnline', $amount, $phone,
            $shortcode, $phone, $callback,
            $ref, $description,
            $description, $description
        );
        return $stkPushSimulation;
    }

    /**
     * Check if is authed and is still valid
     *
     * @return boolean
     */
    public function is_authed(): bool
    {
        // session has not been authed yet
        if (!$this->access_token)
            return false;
        return time() < ($this->authed_at + $this->expires_in);

    }

    /**
     * Get oauth access token
     *
     * todo: find a better way to manage the authentication i.e store in session
     *
     * @return $this
     * @throws \Exception
     */
    public function auth()
    {
        // skip authentication if access token is still valid
        if ($this->is_authed())
            return $this;

        $url = $this->get_url_for('auth');
        $consumer_key = $this->getConfig('consumer_key');
        $consumer_secret = $this->getConfig('consumer_secret');
        $credentials = base64_encode("{$consumer_key}:{$consumer_secret}");

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic {$credentials}",
                "cache-control: no-cache",
            ],
        ]);

        $curl_response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            throw $err;
        }

        try {
            $result = json_decode($curl_response, true);
            $this->authed_at = time();
            $this->access_token = $result['access_token'];
            $this->expires_in = $result['expires_in'];

            return $this;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function register_url()
    {
        $validation_url = url('api/ipn/validation');
        $confirmation_url = url('api/ipn/confirmation');

        $this->transaction_type = __FUNCTION__;
        $this->payload = [
            'ShortCode' => $this->getConfig('shortcode'),
            'ResponseType' => 'Cancelled',
            'ConfirmationURL' => $confirmation_url,
            'ValidationURL' => $validation_url,
        ];

        return $this;
    }

    public function c2b_simulate($amount, $bill_ref_number)
    {
        $this->transaction_type = __FUNCTION__;
        $this->payload = [
            'ShortCode' => env('mpesa_shortcode'),
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'Msisdn' => "254708374149",
            'BillRefNumber' => $bill_ref_number
        ];

        return $this;
    }

    public function b2c($amount, $receiver, $remarks, $occasion = null)
    {

        $initiator = $this->getConfig('initiator_name');
        $partyA = $this->getConfig('shortcode');
        $partyB = encode_phone_number($receiver);
        $security_credential = $this->getConfig('initiator_password');
        $encrypted_credential = $this->compute_security_credentials($security_credential);

        $mpesa = new Mpesa($this->getConfig('consumer_key'),
            $this->getConfig('consumer_secret'));

        $b2cTransaction = $mpesa->b2c(
            $initiator,
            $encrypted_credential,
            'SalaryPayment',
            $amount,
            $partyA,
            $partyB,
            $remarks,
            url("api/ipn/b2c-timeout"),
            url("api/ipn/b2c"),
            $occasion
        );

        return json_decode($b2cTransaction, true);
    }

    public function b2b($amount, $partyB, $remarks, $occasion = null)
    {

        $initiator = $this->getConfig('initiator_name');
        $partyA = $this->getConfig('shortcode');
        $security_credential = $this->getConfig('initiator_password');
        $encrypted_credential = $this->compute_security_credentials($security_credential);

        $mpesa = new Mpesa($this->getConfig('consumer_key'),
            $this->getConfig('consumer_secret'));
        $b2cTransaction = $mpesa->b2b(
            $initiator,
            $encrypted_credential,
            $amount,
            $partyA,
            $partyB,
            $remarks,
            url("api/ipn/b2c-timeout"),
            url("api/ipn/b2c"),
            str_random(7), 'BusinessPayBill', 4, 4
        );

        return $b2cTransaction;
    }

    public function balance($partyA = null, $partyType = 4)
    {

        $initiator = $this->getConfig('initiator_name');
        $partyA = $partyA ? encode_phone_number($partyA) : $this->getConfig('shortcode');
        $security_credential = $this->getConfig('initiator_password');
        $encrypted_credential = $this->compute_security_credentials($security_credential);

        $mpesa = new Mpesa($this->getConfig('consumer_key'),
            $this->getConfig('consumer_secret'));

        $b2cTransaction = $mpesa->accountBalance(
            'AccountBalance',
            $initiator,
            $encrypted_credential,
            $partyA,
            $partyType,
            "AccountBalance Query",
            url("api/ipn/b2c-timeout"),
            url("api/ipn/b2c")
        );

        return json_decode($b2cTransaction, true);
    }

    /**
     * Send request
     *
     * @return mixed
     */
    public function send()
    {
        $url = $this->get_url_for($this->transaction_type);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', "Authorization:Bearer {$this->access_token}")); //setting custom header


        $curl_post_data = $this->payload;

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);

        return json_decode($curl_response, true);
    }

    public function get_url_for($key, $default = null)
    {
        if ($this->getConfig('status') != 'live') {
            $urls = [
                'auth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
                'register_url' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
                'c2b_simulate' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate'
            ];
        } else {
            $urls = [
                'auth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
                'register_url' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl',
                'c2b_simulate' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/simulate'
            ];
        }

        return array_get($urls, $key, $default);
    }

    /**
     * @return mixed
     */
    public function getTransactionType()
    {
        return $this->transaction_type;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }


    public static function process_stk($payload)
    {
        try {
            $data = $payload;

            $meta = $data['Body']['stkCallback']['CallbackMetadata']['Item'];

            $return = [
                'amount' => $meta[0]['Value'],
                'transaction_number' => $meta[1]['Value'],
                'account' => time(),
                'source' => $meta[4]['Value'],
                'date_paid' => carbon($meta[3]['Value'])
            ];

            return $return;

        } catch (\Exception $e) {

            \Log::info('This is not possible for me why?' . $e->getMessage());
            return false;
        }
    }

    public static function process_c2b_callback($payload)
    {
        try {
            $bill_ref = $payload['BillRefNumber'];

            $name = array_get($payload, 'FirstName') . " " .
                array_get($payload, 'MiddleName') . " "
                . array_get($payload, 'LastName');

            return [
                'transaction_number' => $payload['TransID'],
                'transaction_date' => carbon($payload['TransTime']),
                'amount' => $payload['TransAmount'],
                'phone' => $payload['MSISDN'],
                'account' => $bill_ref,
                'name' => $name
            ];
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }


    public function compute_security_credentials($password)
    {
        $env = $this->getConfig('status') == 'live' ? 'live' : 'sandbox';

        $cert = storage_path("mpesa_{$env}_cert.cer");

        $publicKey = file_get_contents($cert);

        openssl_public_encrypt($password, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function validate_request(Request $request)
    {
        $ips  = json_encode($request->ips());
        Log::info("Validating ips => {$ips}", []);

        if(config('app.env') == 'local')
            return true;

        return in_array($request->ip(), $this->whitelist);
    }

}