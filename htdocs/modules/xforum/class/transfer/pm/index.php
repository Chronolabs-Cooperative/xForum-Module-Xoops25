<?php

// $Id: index.php,v 4.03 2008/06/05 16:23:36 wishcraft Exp $
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

function transfer_pm(&$data)
{
	
	$_config = require(dirname(__FILE__)."/config.php");
	
	$hiddens["to_userid"] = $data["uid"];
	$hiddens["subject"] = $data["title"];
	$content  = str_replace("<br />", "\n\r", $data["content"]);
	$content  = str_replace("<br>", "\n\r", $content);
	$content  = "[quote]\n".forum_html2text($content)."\n[/quote]";
	$content = $data["title"]."\n\r".$content."\n\r\n\r"._MORE."\n\r".$data["url"];
	$hiddens["message"] = $content;
	
	include XOOPS_ROOT_PATH."/header.php";
	if(!empty($_config["module"]) && is_dir(XOOPS_ROOT_PATH."/modules/".$_config["module"])){
		$action = XOOPS_URL."/modules/".$_config["module"]."/pmlite.php";
	}else{
		$action = XOOPS_URL."/pmlite.php?send2=1&amp;to_userid=".$data["uid"];
	}
	xoops_confirm($hiddens, $action, $_config["title"]);
	$GLOBALS["xoopsOption"]['output_type'] = "plain";
	include XOOPS_ROOT_PATH."/footer.php";
	exit();
}
?>