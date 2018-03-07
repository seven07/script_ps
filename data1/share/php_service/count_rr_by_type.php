<?php
include_once("ip_functions.inc");
include_once('errors.inc');
include_once("dns_functions.inc");
include_once("ipm_functions.inc");
require_once 'ClassService.inc';

if (isset($ipm_argv['is_ipm_register']) && $ipm_argv['is_ipm_register'])
  {
    ipm_register_internal_service("rr_count","Count the number of RR for a type on a server");
    return;
  }

$dns_id = $ipm_argv['dns_id'];
$rr_type = strtoupper($ipm_argv['rr_type']);

$q_cnt_rr = 'select count (*) from rr where dnszone_id in (select oid from dnszone where dns_id='.$dns_id.' and row_enabled=\'1\'and dnszone_is_rpz=\'0\')
	    and rr_type_id = (select oid from rr_type where rr_type=\''.$rr_type.'\')';

$obj_cnt_rr = ipm_query_sql2($q_cnt_rr);
$res_obj_cnt_rr = ipm_fetch_result($obj_cnt_rr);

ipm_push_result("errno=0&total=".urlencode($res_obj_cnt_rr[count])."&dns_id=".urlencode($dns_id)."&rr_type=".urlencode($rr_type));
?>
