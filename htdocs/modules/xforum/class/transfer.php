<?php

// $Id: transfer.php,v 4.03 2008/06/05 16:23:34 wishcraft Exp $
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
//                                                                          //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
//                                                                          //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
//                                                                          //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
//  Author: wishcraft (S.A.R., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
// ------------------------------------------------------------------------ //
/**
 * @package module::article
 * @copyright copyright &copy; 2005 XoopsForge.com
 */
 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

class xforumTransferHandler
{
	var $root_path;
	
    function xforumTransferHandler()
    {
		$current_path = __FILE__;
		if ( DIRECTORY_SEPARATOR != "/" ) $current_path = str_replace( strpos( $current_path, "\\\\", 2 ) ? "\\\\" : DIRECTORY_SEPARATOR, "/", $current_path);
		$this->root_path = dirname($current_path)."/transfer";
    }
    
    function &getList()
    {
    	$module_handler = xoops_gethandler("module");
		$criteria = new CriteriaCompo(new Criteria("isactive", 1));
		$module_list =array_keys( $module_handler->getList($criteria, true) );
		$_list = XoopsLists::getDirListAsArray($this->root_path."/");
		foreach($_list as $item){
			if(is_readable($this->root_path."/".$item."/config.php")){
				require($this->root_path."/".$item."/config.php");
				if(empty($config["level"])) continue;
				if(!empty($config["module"]) && !in_array($config["module"], $module_list)) continue;
				$list[$item] = $config["title"];
				unset($config);
			}
		}
		unset($_list);
		return $list;
    }

    /**
     * Transfer article content to another module or site
     *
     *@param	string	$item	name of the script for the transfer
     *@param	array	$data	associative array of title, uid, body, source (url of the article) and extra tags
     *return	mixed
     */
    function do_transfer($item, $data)
    {
	    $item = preg_replace("/[^a-z0-9_\-]/i", "", $item);
		if(!is_readable($this->root_path."/".$item."/index.php")) return false;
		require_once $this->root_path."/".$item."/index.php";
		$func = "transfer_".$item;
		if(!function_exists($func)) return false;
		$ret = $func($data);
	    return $ret;
    }
}
?>