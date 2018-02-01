<?php

// script to count the object
$file = 'counters.txt';
file_put_contents($file, "Your architecture contains:\n");


echo "User: ipmadmin\r\n";
echo "Password: ";
system('stty -echo');
$user_passwd = trim(fgets(STDIN));
system('stty echo');
echo "\r\n";

// global predefined parameters
$user_login = 'ipmadmin';
$CUST_REST_IPAM_URL = '10.0.93.5';

//count spaces
$service_url = 'https://'.$CUST_REST_IPAM_URL.'/rest/ip_site_count/';
$space = rest_call ($service_url);
$spaces =$space[0]->total." space(s)\n";
file_put_contents($file, $spaces ,FILE_APPEND);

//count block
$service_url = 'https://'.$CUST_REST_IPAM_URL.'/rest/ip_block_count/';
$block_count = rest_call ($service_url);
$block = $block_count[0]->total." block(s)\n";
file_put_contents($file, $block ,FILE_APPEND);

//count subnet
$service_url = 'https://'.$CUST_REST_IPAM_URL.'/rest/ip_subnet_count/';
$subnet = rest_call ($service_url);
$subnet =$subnet[0]->total." subnet(s)\n";
file_put_contents($file, $subnet ,FILE_APPEND);

//count ip
$service_url = 'https://'.$CUST_REST_IPAM_URL.'/rest/ip_address_count/';
$ip_count = rest_call ($service_url);
$ip = $ip_count[0]->total." ip(s)\n";
file_put_contents($file, $ip ,FILE_APPEND);


function rest_call ($service_url) {

global $user_login, $user_passwd;

$ch = curl_init($service_url);
curl_setopt_array($ch, array(CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                             CURLOPT_HTTPGET => true,
                             CURLOPT_RETURNTRANSFER => true,
                             CURLOPT_HTTPHEADER => array('X-IPM-Username: '.base64_encode($user_login),
                             'X-IPM-Password: '.base64_encode($user_passwd)),
                             CURLOPT_SSL_VERIFYHOST => 0,
                             CURLOPT_SSL_VERIFYPEER => false)
                );
$response = curl_exec($ch);
$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);
$answer = (json_decode($response));

return($answer);

}

function rest_call_post ($service_url,$parameters) {

global $user_login, $user_passwd;

$ch = curl_init($service_url);
curl_setopt_array($ch, array(CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
			     CURLOPT_POST => TRUE,
                             CURLOPT_RETURNTRANSFER => true,
                             CURLOPT_HTTPHEADER => array('X-IPM-Username: '.base64_encode($user_login),
                             'X-IPM-Password: '.base64_encode($user_passwd)),
                             CURLOPT_SSL_VERIFYHOST => 0,
                             CURLOPT_SSL_VERIFYPEER => false)
                );
if ($parameters != NULL) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);
$answer = (json_decode($response));
return($answer);

}


function cust_encode_json($value)
    {
      return str_replace('"','\"',$value);
    }

function hex2ip ( $hexip )
{
    return strval(hexdec(substr($hexip,0,2))).".".strval(hexdec(substr($hexip,2,2))).".".strval(hexdec(substr($hexip,4,2))).".".strval(hexdec(substr($hexip,6,2)));
}
?>
