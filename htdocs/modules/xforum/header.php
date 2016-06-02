<?php

// $Id: header.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $



include_once '../../mainfile.php';

error_reporting(E_ALL);

$GLOBALS['myts'] = MyTextSanitizer::getInstance();

$module_handler = xoops_gethandler('module');
$config_handler = xoops_gethandler('config');
$GLOBALS['xforumModule'] = $module_handler->getByDirname('xforum');
$GLOBALS['xforumModuleConfig'] = $config_handler->getConfigList($GLOBALS['xforumModule']->getVar('mid')); 
	
xoops_load('pagenav');	
xoops_load('xoopslists');
xoops_load('xoopsformloader');

include_once $GLOBALS['xoops']->path('class'.DS.'xoopsmailer.php');
include_once $GLOBALS['xoops']->path('class'.DS.'xoopstree.php');
include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/vars.php";
include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/functions.php";
include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/functions.ini.php";

$GLOBALS['myts'] = MyTextSanitizer::getInstance();

// menumode cookie
if(isset($_REQUEST['menumode'])){
	$menumode = intval($_REQUEST['menumode']);
	forum_setcookie("M", $menumode, $forumCookie['expire']);
}else{
	$cookie_M = intval(forum_getcookie("M"));
	$menumode = ($cookie_M === null || !isset($valid_menumodes[$cookie_M]))?$GLOBALS['xforumModuleConfig']['menu_mode']:$cookie_M;
}

$menumode_other = array();
$menu_url = htmlSpecialChars(preg_replace("/&menumode=[^&]/", "", $_SERVER[ 'REQUEST_URI' ]));
$menu_url .= (false === strpos($menu_url, "?"))?"?menumode=":"&amp;menumode=";
foreach($valid_menumodes as $key=>$val){
	if($key != $menumode) $menumode_other[]=array("title"=>$val, "link"=>$menu_url.$key);
}

forum_welcome();
?>