<?php
include_once("ip_functions.inc");
include_once('errors.inc');
include_once("dns_functions.inc");
include_once("ipm_functions.inc");
require_once 'ClassService.inc';

if (isset($ipm_argv['is_ipm_register']) && $ipm_argv['is_ipm_register'])
  {
    ipm_register_internal_service("get_rr_type","return all the rr type present database");
    return;
  }

$q_get_rr_type = 'select * from rr_type';

$obj_get_rr_type = ipm_query_sql2($q_get_rr_type);
while ($res_obj_get_rr_type = ipm_fetch_result($obj_get_rr_type))
{
	ipm_push_result("errno=0&rr_type=".urlencode($res_obj_get_rr_type[rr_type]));
}
?>
