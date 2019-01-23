<?php

namespace App\Sdks;

use AfricasTalking\SDK\AfricasTalking;

class SmsSdk
{

    protected $config;

    protected $driver;

    protected $gateway;

    public function __construct($driver = null)
    {
        try {
            $driver = $driver ? $driver : config("sms.default");
            $this->config = config("sms.drivers.{$driver}");
            $this->driver = $driver;
        } catch (\Exception $exception) {
            throw  $exception;
        }
    }

    public function send($to, $message)
    {
        switch ($this->driver) {
            case 'africastalking':
                return $this->africasTalking($to, $message);
            case 'olivetree':
                return $this->oliveTree($to, $message);
            default:
                return;
        }
    }

    /**
     * @param $recipients
     * @param $message
     * @param null $shortcode
     * @throws \Exception
     */
    public function africasTalking($recipients, $message, $shortcode = null)
    {
        try {
            $gateway = new AfricasTalking($this->getConfig('username'), $this->getConfig('api_key'));
            $sms = $gateway->sms();
            $results = $sms->send([
                'message' => $message,
                'to' => $this->prep_recipients($recipients)
            ]);

            return $results;
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @param $recipients
     * @param $message
     * @param null $shortcode
     * @throws \Exception
     */
    public function oliveTree($recipients, $message, $shortcode = null)
    {
        try {
            $url = $this->getConfig('url');
            $shortcode = $shortcode ? : $this->getConfig('shortcode');
            $params = [
                'MESSAGE' => $message,
                'MSISDN' => $recipients,
                "SOURCE" => $shortcode
            ];
            $response = \Httpful\Request::post($url, http_build_query($params))
                ->sendsType(\Httpful\Mime::FORM)->send();

            if (!$response->code == 200 || !$response->body) {
                \Log::error("SMS => ", [$response]);
                return false;
            }

            return $response->body;
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * @param null $key
     * @param null $default
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getConfig($key = null, $default = null)
    {
        if (!$key)
            return $this->config;

        return array_get($this->config, $key, $default);
    }

    /**
     * @param $recipients
     * @param bool $include_plus
     * @return string
     */
    public function prep_recipients($recipients, $include_plus = false)
    {
        $phone = encode_phone_number($recipients);
        $str = $include_plus ? "+" : "";

        return $str . $phone;
    }
}