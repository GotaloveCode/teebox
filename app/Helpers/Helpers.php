<?php

use App\Code;

function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int)($log / 8) + 1; // length in bytes
    $bits = (int)$log + 1; // length in bits
    $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length = 6, $type = 'capnum', $prefix = '')
{
    switch ($type) {
        case 'alnum':
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'capnum':
            $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'alpha':
            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'hexdec':
            $pool = '0123456789abcdef';
            break;
        case 'numeric':
            $pool = '0123456789';
            break;
        case 'nozero':
            $pool = '123456789';
            break;
        case 'distinct':
            $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
            break;
        default:
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
    }

    $token = "";

    $max = strlen($pool);

    for ($i = 0; $i < $length; $i++) {
        $token .= $pool[crypto_rand_secure(0, $max - 1)];
    }

    return $prefix . $token;
}


function generateCode($filteritem = "email")
{
    $filter = 'code_email';
    if($filteritem != "email"){
        $filter = 'code_phone';
        do {
            $code = getToken(6,'numeric');
        } while (Code::where($filter, $code)->count() > 0);
    }else{
        do {
            $code = getToken();
        } while (Code::where($filter, $code)->count() > 0);
    }

    return $code;
}

if (!function_exists('encode_phone_number')){
    /**
     * @param $number
     * @param string $code
     * @return mixed|string
     */
    function encode_phone_number($number, $country  = 'KE')
    {
        $phone  = phone($number, $country, 'E164');
        // remove preceding plus if it exists
        $number = preg_replace('/^\+/', '', $phone);

        return $number;
    }
}
