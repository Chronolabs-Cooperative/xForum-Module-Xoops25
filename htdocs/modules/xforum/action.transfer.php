<?php

// $Id: action.transfer.php,v 4.04 2008/06/05 16:23:24 wishcraft Exp $
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
//  Author: wishcraft (S.F.C., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
// ------------------------------------------------------------------------ //

include "header.php";
require_once(XOOPS_ROOT_PATH . "/class/xoopsformloader.php");

$xforum = intval( empty($_GET["forum"])?(empty($_POST["forum"])?0:$_POST["forum"]):$_GET["forum"] );
$topic_id = intval( empty($_GET["topic_id"])?(empty($_POST["topic_id"])?0:$_POST["topic_id"]):$_GET["topic_id"] );
$post_id = intval( empty($_GET["post_id"])?(empty($_POST["post_id"])?0:$_POST["post_id"]):$_GET["post_id"] );

if ( empty($post_id) )  {	
	if(empty($_SERVER['HTTP_REFERER'])){
		include XOOPS_ROOT_PATH."/header.php";
		xoops_error(_NOPERM);
		$GLOBALS['xoopsOption']['output_type'] = "plain";
		include XOOPS_ROOT_PATH."/footer.php";
		exit();
	}else{
		$ref_parser = parse_url($_SERVER['HTTP_REFERER']);
		$uri_parser = parse_url($_SERVER['REQUEST_URI']);
		if(
			(!empty($ref_parser['host']) && !empty($uri_parser['host']) && $uri_parser['host'] != $ref_parser['host']) 
			|| 
			($ref_parser["path"] != $uri_parser["path"])
		){
			include XOOPS_ROOT_PATH."/header.php";
			xoops_confirm(array(), "javascript: window.close();", sprintf(_MD_TRANSFER_DONE,""), _CLOSE, $_SERVER['HTTP_REFERER']);
			$GLOBALS['xoopsOption']['output_type'] = "plain";
			include XOOPS_ROOT_PATH."/footer.php";
			exit();
		}else{
			include XOOPS_ROOT_PATH."/header.php";
			xoops_error(_NOPERM);
			$GLOBALS['xoopsOption']['output_type'] = "plain";
			include XOOPS_ROOT_PATH."/footer.php";
			exit();
		}
	}
}

$post_handler = xoops_getmodulehandler('post', 'xforum');
$post =  $post_handler->get($post_id);
if(!$approved = $post->getVar('approved'))    die(_NOPERM);

$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$GLOBALS['xforumtopic'] = $topic_handler->getByPost($post_id);
$topic_id = $GLOBALS['xforumtopic']->getVar('topic_id');
if(!$approved = $GLOBALS['xforumtopic']->getVar('approved'))    die(_NOPERM);

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$xforum = ($xforum)?$xforum:$GLOBALS['xforumtopic']->getVar('forum_id');
$GLOBALS['viewtopic_forum'] = $GLOBALS['forum_handler']->get($xforum);
if (!$forum_handler->getPermission($GLOBALS['viewtopic_forum']))    die(_NOPERM);
if (!$topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "view"))   die(_NOPERM);
//if ( !$xforumdata =  $topic_handler->getViewData($topic_id, $xforum) )die(_NOPERM);

$op = empty($_POST["op"])?"":$_POST["op"];
$op = strtolower(trim($op));

$transfer_handler = xoops_getmodulehandler("transfer", "xforum");
$op_options	= $transfer_handler->getList();

// Display option form
if(empty($_POST["op"])){
	include XOOPS_ROOT_PATH."/header.php";
	echo "<div class=\"confirmMsg\" style=\"width: 80%; padding:20px;margin:10px auto; text-align:left !important;\"><h2>"._MD_TRANSFER_DESC."</h2><br />";
	echo "<form name=\"opform\" id=\"opform\" action=\"".xoops_getenv("PHP_SELF")."\" method=\"post\"><ul>\n";
	foreach($op_options as $value=>$title){
		echo "<li><a href=\"###\" onclick=\"document.forms.opform.op.value='".$value."'; document.forms.opform.submit();\">".$title."</a></li>\n";
	}
	echo "<input type=\"hidden\" name=\"forum\" id=\"forum\" value=\"".$xforum."\">";
	echo "<input type=\"hidden\" name=\"topic_id\" id=\"topic_id\" value=\"".$topic_id."\">";
	echo "<input type=\"hidden\" name=\"post_id\" id=\"post_id\" value=\"".$post_id."\">";
	echo "<input type=\"hidden\" name=\"op\" id=\"op\" value=\"\">";
	echo "</url></form></div>";
	$GLOBALS['xoopsOption']['output_type'] = "plain";
	include XOOPS_ROOT_PATH."/footer.php";
	exit();
}else{
	$data = array();
    $data["id"] = $post_id;
    $data["uid"] = $post->getVar("uid");
	$data["url"] = XOOPS_URL."/modules/xforum/viewtopic.php?topic_id=".$topic_id."&post_id=".$post_id;
	$post_data = $post->getPostBody();
	$data["author"] = $post_data["author"];
	$data["title"] = $post_data["subject"];
	$data["content"] = $post_data["text"];
	$data["time"] = formatTimestamp($post_data["date"]);
	
	switch($op){
	    case "pdf":
			$data['subtitle'] = $GLOBALS['xforumtopic']->getVar('topic_title');
		    break;
		
		// Use regular content
		default:
			break;
	}
	
	$ret = $transfer_handler->do_transfer($_POST["op"], $data);
	
	include XOOPS_ROOT_PATH."/header.php";
	$ret = empty($ret)?"javascript: window.close();":$ret;
	xoops_confirm(array(), "javascript: window.close();", sprintf(_MD_TRANSFER_DONE,$op_options[$op]), _CLOSE, $ret);
	include XOOPS_ROOT_PATH."/footer.php";
}
?>