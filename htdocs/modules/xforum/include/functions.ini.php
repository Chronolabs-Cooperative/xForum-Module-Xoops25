<?php

// $Id: functions.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $

if (!defined('XOOPS_ROOT_PATH')){ exit(); }

if(defined("XFORUM_FUNCTIONS_INI")) return; 

define("XFORUM_FUNCTIONS_INI",1);

xoops_load('XoopsUserUtility');
xoops_load('XoopsCache');

if (!function_exists('forum_message')) {
	function forum_message( $message )
	{
		
		if(!empty($GLOBALS['xforumModuleConfig']["do_debug"])){
			if(is_array($message) || is_object($message)){
				echo "<div><pre>";print_r($message);echo "</pre></div>";
			}else{
				echo "<div>$message</div>";
			}
		}
		return;
	}
}
if (!function_exists('forum_load_config')) {
	function &forum_load_config()
	{
		static $moduleConfig;
		if(isset($moduleConfig)){
			return $moduleConfig;
		}
		
	    if(isset($GLOBALS['xforumModule']) && is_object($GLOBALS['xforumModule']) && $GLOBALS['xforumModule']->getVar("dirname", "n") == "xforum"){
		    if(!empty($GLOBALS['xforumModuleConfig'])) {
			    $moduleConfig = $GLOBALS['xforumModuleConfig'];
		    }else{
			    return null;
		    }
	    }else{
			$module_handler = xoops_gethandler('module');
			$module = $module_handler->getByDirname("xforum");
		
		    $config_handler = xoops_gethandler('config');
		    $criteria = new CriteriaCompo(new Criteria('conf_modid', $module->getVar('mid')));
		    $configs = $config_handler->getConfigs($criteria);
		    foreach(array_keys($configs) as $i){
			    $moduleConfig[$configs[$i]->getVar('conf_name')] = $configs[$i]->getConfValueForOutput();
		    }
		    unset($configs);
	    }
		if($customConfig = @include(XOOPS_ROOT_PATH."/modules/xforum/include/plugin.php")){
			$moduleConfig = array_merge($moduleConfig, $customConfig);
		}
	    return $moduleConfig;
	}
}

if (!function_exists('getConfigForBlock')) {
	function getConfigForBlock()
	{
		return forum_load_config();
		
		static $xforumConfig;
		if(isset($xforumConfig)){
			return $xforumConfig;
		}
		
	    if(is_object($GLOBALS['xforumModule']) && $GLOBALS['xforumModule']->getVar("dirname") == "xforum"){
		    $xforumConfig = $GLOBALS['xforumModuleConfig'];
	    }else{
			$module_handler = xoops_gethandler('module');
			$xforum = $module_handler->getByDirname('xforum');
		
		    $config_handler = xoops_gethandler('config');
		    $criteria = new CriteriaCompo(new Criteria('conf_modid', $xforum->getVar('mid')));
		    $criteria->add(new Criteria('conf_name', "('show_realname', 'subject_prefix', 'allow_require_reply')", "IN"));
		    $configs = $config_handler->getConfigs($criteria);
		    foreach(array_keys($configs) as $i){
			    $xforumConfig[$configs[$i]->getVar('conf_name')] = $configs[$i]->getConfValueForOutput();
		    }
		    unset($xforum, $configs);
	    }
	    return $xforumConfig;
	}
}

if (!function_exists('forum_load_lang_file')) {
	function forum_load_lang_file( $filename, $module = '', $default = 'english' )
	{
		return xoops_loadLanguage($filename, $module, $default);
	}
}

if (!function_exists('forum_getIP')) {
	function forum_getIP($asString = false)
	{
		return XoopsUserUtility::getIP($asString);
	}
}

if (!function_exists('forum_formatTimestamp')) {
	function forum_formatTimestamp($time, $format = "c", $timeoffset = "")
	{
		xoops_load('XoopsLocal');
		
		if(strtolower($format) == "reg" || strtolower($format) == "") {
			$format = "c";
		}
		if( (strtolower($format) == "custom" || strtolower($format) == "c") && !empty($GLOBALS['xforumModuleConfig']["formatTimestamp_custom"]) ) {
			$format = $GLOBALS['xforumModuleConfig']["formatTimestamp_custom"];
		}
		
		return XoopsLocal::formatTimestamp($time, $format, $timeoffset);
	}
}
?>