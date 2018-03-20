<?php

/*
**
** Functions
**
*/
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

/*
**
** Procedural part
**
*/
echo "User: ipmadmin\r\n";
echo "Password: ";
system('stty -echo');
$user_passwd = trim(fgets(STDIN));
//$user_passwd = fgets(STDIN);
system('stty echo');
echo "\r\n";

// global predefined parameters
$user_login = 'ipmadmin';

date_default_timezone_set('UTC');

if(file_exists("/SOLIDSERVERVERSION"))
    {
        if($argv[1] == NULL)
	{
        	$CUST_REST_IPAM_URL = '127.0.0.1';
	}
	else
	{
		 $CUST_REST_IPAM_URL = $argv[1];
	}
        $service_url = 'https://'.$CUST_REST_IPAM_URL.'/rest/member_list/WHERE/member_is_me%3D1';
        $member = rest_call ($service_url);
        $version = $member[0]->member_version;
        $file = '/data1/exports/counters-'.$version.'-'.date(YmdHi).'.txt';
    }
else
    {
        $CUST_REST_IPAM_URL = $argv[1];
        $service_url = 'https://'.$CUST_REST_IPAM_URL.'/rest/member_list/WHERE/member_is_me%3D1';
        $member = rest_call ($service_url);
        $version = $member[0]->member_version;
        $file = 'counters-'.$version.'-'.date(dmYHi).'.txt';
    }

file_put_contents($file, "\nYour configuration contains:\n\n");

//$version = $member[0]->member_version;
$split = explode(".",$version);
$branch = $split[0];

//$services = array();
//"IPAM"
$i=0;
$services[$i] = array("/rest/ip_site_count/", "space(s)", 'IPAM');
$i++;

//IPAM IPv4
if($branch < 6)
    {
        $services[$i] = array("/rest/ip_block_count/", "block(s)", 'IPAMv4');
	      $i++;
        $services[$i] = array("/rest/ip_subnet_count/", "subnet(s)", 'IPAMv4');
	$i++;
	$services[$i] = array("/rest/ip_block_subnet_count/", "block(s)/subnet(s)", 'IPAMv4');
	$i++;
	$services[$i] = array("/rest/ip_pool_count/", "pool(s)", 'IPAMv4');
	$i++;
        $services[$i] = array("/rest/ip_address_count/WHERE/ip_id%3E0", "used addresse(s), empty result means that there is no subnet\n", 'IPAMv4');
	$i++;
    }
else
    {
        $services[$i] = array("/rest/ip_block_subnet_count/", "network(s)", 'IPAMv4');
	$i++;
	$services[$i] = array("/rest/ip_pool_count/", "pool(s)", 'IPAMv4');
	$i++;
        $services[$i] = array("/rest/ip_address_count/WHERE/ip_id%3E0", "used addresse(s), empty result means that there is no subnet\n", 'IPAMv4');
	$i++;
    }

//IPAM IPv6
if($branch < 6)
    {
        $services[$i] = array("/rest/ip6_block6_count/", "IPv6 block(s)",'IPAMv6');
	$i++;
        $services[$i] = array("/rest/ip6_subnet6_count/", "IPv6 subnet(s)",'IPAMv6');
	$i++;
	$services[$i] = array("/rest/ip6_pool6_count/", "IPv6 pool(s)",'IPAMv6');
	$i++;
        $services[$i] = array("/rest/ip6_address6_count/WHERE/ip6_id%3E0", "IPv6 addresse(s) used, empty result means that there is no subnet\n",'IPAMv6');
	$i++;
    }
else
    {
        $services[$i] = array("/rest/ip6_block6_subnet6_count/", "IPv6 network(s)",'IPAMv6');
	$i++;
	$services[$i] = array("/rest/ip6_pool6_count/", "IPv6 pool(s)",'IPAMv6');
	$i++;
        $services[$i] = array("/rest/ip6_address6_count/", "used IPv6 addresse(s), empty result means that there is no subnet\n",'IPAMv6');
	$i++;
    }


//DNS
$services[$i] = array("/rest/dns_server_count/", "DNS server(s)", 'DNS');
$i++;
$services[$i] = array("/rest/dns_server_count/WHERE/vdns_parent_id%3D0", "DNS Smart or standalone server(s)", 'DNS');
$i++;
$partial_url = "/rest/dns_server_list/WHERE/vdns_parent_id%3D0";
$service_url = 'https://'.$CUST_REST_IPAM_URL.$partial_url;
$dns_servers = rest_call ($service_url);
$nb_parent_dns = sizeof($dns_servers);
$j = $nb_parent_dns-1;

$partial_url = "/rpc/get_rr_type.php";
$service_url = 'https://'.$CUST_REST_IPAM_URL.$partial_url;
$rr_types = rest_call ($service_url);
$nb_rr_type = sizeof($rr_types);

while($j >= 0)
    {
        $services[$i] = array("/rest/dns_view_count/WHERE/dns_id%3D".$dns_servers[$j]->dns_id, "DNS view(s) in ".$dns_servers[$j]->dns_name, 'DNS');
        $i++;
        $services[$i] = array("/rest/dns_zone_count/WHERE/dns_id%3D".$dns_servers[$j]->dns_id, "DNS zone(s)", 'DNS');
        $i++;
        $services[$i] = array("/rest/dns_rr_count/WHERE/dns_id%3D".$dns_servers[$j]->dns_id, "DNS resource records(s)\n", 'DNS');
        $i++;

	$k = $nb_rr_type-1;
	while ($k >= 0)
	      {
			$services[$i] = array("/rpc/count_rr_by_type.php?rr_type=".$rr_types[$k]->rr_type."&dns_id=".$dns_servers[$j]->dns_id, " ".$rr_types[$k]->rr_type, 'DNS_AA');
	                $i++;
			$k--;
	      }
//	$services[$i] = array("/rest/dns_rr_count/WHERE/dns_id%3D".$dns_servers[$j]->dns_id, "DNS resource record(s) in total\n", 'DNS');
	$i++;
	$j--;
    }

//DHCP
$services[$i] = array("/rest/dhcp_server_count/", "DHCP server(s)", 'DHCP');
$i++;
$services[$i] = array("/rest/dhcp_server_count/WHERE/vdhcp_parent_id%3D0", "DHCP Smart or standalone server(s)", 'DHCP');
$i++;
$partial_url = "/rest/dhcp_server_list/WHERE/vdhcp_parent_id%3D0";
$service_url = 'https://'.$CUST_REST_IPAM_URL.$partial_url;
$dhcp_servers = rest_call ($service_url);
$nb_parent_dhcp = sizeof($dhcp_servers);
$j = $nb_parent_dhcp-1;
while($j >= 0)
    {
        $services[$i] = array("/rest/dhcp_scope_count/WHERE/dhcp_id%3D".$dhcp_servers[$j]->dhcp_id, "DHCP scope(s) in ".$dhcp_servers[$j]->dhcp_name, 'DHCP');
        $i++;
        $services[$i] = array("/rest/dhcp_group_count/WHERE/dhcp_id%3D".$dhcp_servers[$j]->dhcp_id, "DHCP group(s) in ".$dhcp_servers[$j]->dhcp_name, 'DHCP');
        $i++;
	$services[$i] = array("/rest/dhcp_range_count/WHERE/dhcp_id%3D".$dhcp_servers[$j]->dhcp_id, "DHCP range(s) in ".$dhcp_servers[$j]->dhcp_name, 'DHCP');
        $i++;
	$services[$i] = array("/rest/dhcp_range_lease_count/WHERE/dhcp_id%3D".$dhcp_servers[$j]->dhcp_id, "DHCP lease(s) in ".$dhcp_servers[$j]->dhcp_name, 'DHCP');
        $i++;
        $services[$i] = array("/rest/dhcp_static_count/WHERE/dhcp_id%3D".$dhcp_servers[$j]->dhcp_id, "DHCP static(s) in ".$dhcp_servers[$j]->dhcp_name."\n", 'DHCP');
        $i++;
        $j--;
    }

//Netchange
$services[$i] = array("/rest/iplnetdev_count/", "device(s) via Netchange", 'NetChange');
$i++;
$services[$i] = array("/rest/iplnetdevroute_count/", "route(s) via Netchange ", 'NetChange');
$i++;
$services[$i] = array("/rest/iplnetdevvlan_count/", "VLAN(s) via Netchange", 'NetChange');
$i++;
$services[$i] = array("/rest/iplport_count/", "port(s) via Netchange", 'NetChange');
$i++;
$services[$i] = array("/rest/ipldev_count/", "item(s) via Netchange\n", 'NetChange');
$i++;

//VLAN Manager
$services[$i] = array("/rest/vlmdomain_count/", "VLAN Domain(s)", 'Vlan');
$i++;
$services[$i] = array("/rest/vlmrange_count/", "VLAN Range(s)", 'Vlan');
$i++;
$services[$i] = array("/rest/vlmvlan_count/WHERE/vlmvlan_name%20is%20not%20null", "VLAN(s) used\n", 'Vlan');
$i++;


/*
**
** Dump part
**
*/
$flag_dhcp = FALSE;

//count objects
foreach ($services as $n)
{
	$partial_url = $n[0];
	$obj_desc = $n[1];
	$obj_type = $n[2];


	$service_url = 'https://'.$CUST_REST_IPAM_URL.$partial_url;
	$count_out = rest_call ($service_url);

	$count_str = "";
	if ($obj_type == "DNS_AA")  {
		$count_str .= "\t";
  	}

	if ($obj_type == "DHCP" && $flag_dhcp === FALSE)  {
    		$count_str .= "\n";
		$flag_dhcp = TRUE;
  	}

	$count_str .="\t".$count_out[0]->total."\t".$obj_desc."\n";
	file_put_contents($file, $count_str ,FILE_APPEND);
}

if(file_exists("/SOLIDSERVERVERSION"))
system ("cat ".$file);
?>
