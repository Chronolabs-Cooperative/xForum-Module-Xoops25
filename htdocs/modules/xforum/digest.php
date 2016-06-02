<?php

// $Id: digest.php,v 4.04 2008/06/05 16:23:24 wishcraft Exp $

// Why the skip-DB-security check defined only for XMLRPC? We also need it!!! ~_*
if (!defined('XOOPS_XMLRPC')) define('XOOPS_XMLRPC', 1);
ob_start();
include_once("header.php");
if($GLOBALS['xforumModuleConfig']['email_digest'] ==0){
	echo "<br />Not set";
	return false;
}
$digest_handler = xoops_getmodulehandler('digest', 'xforum');
$msg = $digest_handler->process();
$msg .= ob_get_contents();
ob_end_clean();
echo "<br />".$msg;
?>