<?php

// script to count the object

echo "User: ipmadmin\r\n";
echo "Password: ";
system('stty -echo');
$user_passwd = trim(fgets(STDIN));
system('stty echo');
echo "\r\n";

// global predefined parameters
$user_login = 'ipmadmin';
$CUST_REST_IPAM_URL = '10.0.93.5';

//get SOLIDSERVER version 

$service_url = 'https://'.$CUST_REST_IPAM_URL.'rest/member_list/WHERE/member_is_me%3D1';
$member = rest_call ($service_url);
$version = $member->member_version;


$file = 'counters-'.$member->member_version.'-'.date(dmYHi).'.txt';
file_put_contents($file, "Your architecture contains:\n");


$services = array (
//"IPAM"
"cnt_space" => array ("/rest/ip_site_count/", " space(s)\n"),

//IPAM v4
"cnt_block" => array ("/rest/ip_block_count/", " block(s)"),
"cnt_subnet" => array ("/rest/ip_subnet_count/", " subnet(s)"),
"cnt_addr" => array("/rest/ip_address_count/WHERE/ip_id%3E0", " used addresse(s), empty result means that there is no subnet\n"),

//IPAM v6
"cnt_block6" => array ("/rest/ip6_block6_count/", " IPv6 block(s)"),
"cnt_subnet6" => array ("/rest/ip6_subnet6_count/", " IPv6 subnet(s)"),
"cnt_addr6" => array("/rest/ip6_address6_count/WHERE/ip_id%3E0", " used IPv6 addresse(s), empty result means that there is no subnet\n"),


//DNS


//DHCP 
//"cnt_dhcp_server" => array ("/rest/dhcp_server_count/", " DHCP server(s)"),
//"cnt_dhcp_group" => array ("/rest/dhcp_group_count/", " DHCP group(s)"),

//"cnt_dhcp_server6" => array ("/rest/dhcp6_server6_count/", " DHCP IPv6 server(s)"),
//"cnt_dhcp_group6" => array ("/rest/dhcp6_group6_count/", " DHCP IPv6 group(s)"),


//Netchange
"cnt_netchange_device" => array ("/rest/iplnetdev_count/", " Device(s) via Netchange"),
"cnt_netchange_vlan" => array ("/rest/iplnetdevvlan_count/", " VLAN(s) via Netchange"),
"cnt_netchange_port" => array ("/rest/iplport_count/", " Port(s) via Netchange"),
"cnt_netchange_items" => array ("/rest/ipldev_count/", " item(s) via Netchange\n"),

//VLAN Manager
"cnt_vlan_domain" => array ("/rest/vlmdomain_count/", "  Vlan Domain(s)"),
"cnt_vlan_range" => array ("/rest/vlmrange_count/", "  Vlan Range(s)"),
"cnt_vlan_vlan" => array ("/rest/vlmvlan_count/WHERE/vlmvlan_name%20is%20not%20null", " used vlan(s)\n"),
);


//count objects
foreach ($services as $n){
$partial_url = $n[0];
$obj_desc = $n[1];

$service_url = 'https://'.$CUST_REST_IPAM_URL.$partial_url;
$count_out = rest_call ($service_url);
$count_str =$count_out[0]->total.$obj_desc."\n";
file_put_contents($file, $count_str ,FILE_APPEND);

}

system ("cat ".$file);

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
