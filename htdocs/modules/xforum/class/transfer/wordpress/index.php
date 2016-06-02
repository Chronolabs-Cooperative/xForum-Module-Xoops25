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

function transfer_wordpress(&$data)
{

	$_config = require(dirname(__FILE__)."/config.php");

		
	$hiddens["action"] = "post";
	$hiddens["post_status"] = "draft";
	$content = $data["content"] . "<p><a href=\"".$data["url"]."\">"._MORE."</a></p>";
	$hiddens["content"] = $content;
	$hiddens["post_title"] = $data["title"];
	$hiddens["post_author"] = empty($GLOBALS['xoopsUser'])?0:$GLOBALS['xoopsUser']->getVar("uid");
	//$hiddens["advanced"] = 1;
	$hiddens["save"] = 1;
	$hiddens["post_from_xoops"] = 1;
	
	include_once XOOPS_ROOT_PATH."/header.php";
	xoops_confirm($hiddens, XOOPS_URL."/modules/".$_config["module"]."/wp-admin/post.php", $_config["title"]);
	$GLOBALS["xoopsOption"]['output_type'] = "plain";
	include_once XOOPS_ROOT_PATH."/footer.php";
	exit();
}
?>