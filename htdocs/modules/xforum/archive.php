<?php

// $Id: archive.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: wishcraft (S.F.C., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
// ------------------------------------------------------------------------- //

/*
 * The file is not ready yet
 * wishcraft
 */


include_once("header.php");
include XOOPS_ROOT_PATH."/header.php";
$xforum = isset($_GET['forum']) ? intval($_GET['forum']) : 0;
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if ($GLOBALS['xforumModuleConfig']['htaccess']) {
	if ($topic_id<>0) {
		$topic_handler = xoops_getmodulehandler('topic', 'xforum');
		$topic_obj = $topic_handler->get($topic_id);
		$url = $topic_obj->getARCHIVEURL();
	} else {
		$topic_handler = xoops_getmodulehandler('forum', 'xforum');
		$forum_obj = $topic_handler->get($xforum);
		$url = $forum_obj->getARCHIVEURL();
	}
	if (strpos($url, $_SERVER['REQUEST_URI'])==0) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}


if($xforum == 0)
{
	display_archive();
}

if ($xforum > 0 && $topic_id == 0)
{
	/*$permissions = get_forum_auth($xforum);
	if ($permissions['can_view'] == 0)
	{
		redirect_header('archive.php',2,_MD_NORIGHTTOACCESS);
		die();
	}*/

	display_forum_topics($xforum);
}

if ($xforum > 0 && $topic_id > 0)
{
	/*
	$permissions = get_forum_auth($xforum);
	if ($permissions['can_view'] == 0)
	{
		redirect_header('archive.php',2,_MD_NORIGHTTOACCESS);
		die();
	}
	*/
	display_topic($xforum, $topic_id, $content_only);
}


////////////////////////////////////////////////////////////////////
function display_archive()
{
	include_once( XOOPS_ROOT_PATH . "/header.php");

	echo "<table border='0' width='100%' cellpadding='5'>";
	echo "<tr><td align='left'>".forum_displayImage($GLOBALS['xforumImage']['f_open'])."&nbsp;&nbsp;<a href='".$xforumPath['url']."archive.php'>";
	echo _MD_XFORUM_ARCHIVE."</a>";
	echo "</td></tr></table><br />";

	echo "<table border='0' width='90%' cellpadding='5' align=center>";
	echo "<tr><td>";
	$sql = "SELECT * FROM ".$GLOBALS['xforumTable']['categories'];
	$result = $GLOBALS['xoopsDB']->query($sql);
	while ($row = $GLOBALS['xoopsDB']->fetch_object($result))
	{
		echo "<h3>".$row->cat_title."</h3>";
		display_archive_forums($row->cat_id);
	}
	echo "</td></tr></table>";

	include_once(XOOPS_ROOT_PATH."/footer.php");
}

function display_archive_forums($cat_id, $parent_forum = 0, $level=0)
{
	
	$sql = "SELECT forum_id, forum_name FROM ".$GLOBALS['xforumTable']['forums']." WHERE cat_id ='$cat_id' AND parent_forum=$parent_forum ORDER BY forum_id";
	if ($res = $GLOBALS['xoopsDB']->query($sql))
	{
		while (list($forum_id, $forum_name) = $GLOBALS['xoopsDB']->fetch_row($res))
		{
			$permissions = get_forum_auth($forum_id);
			if ($permissions['can_view'] == 0)
			{
				continue;
			}
			$name = $GLOBALS['myts']->htmlSpecialChars($forum_name);
			for ($i = 0; $i<($level*4+4); $i++)
				echo "&nbsp;";
			echo "<a href='archive.php?forum=$forum_id'><b>$name</b></a><br />";
			$newlevel = $level+1;
			display_archive_forums($cat_id, $forum_id, $newlevel);
		}
	}

}
////////////////////////////////////////////////////////////////////
function display_forum_topics($xforum)
{

	include_once(XOOPS_ROOT_PATH."/header.php");

	$q = "select * from ".$GLOBALS['xforumTable']['forums']." WHERE forum_id=".$xforum;
	$result = $GLOBALS['xoopsDB']->query($q);
	if(!$result)
		echo $GLOBALS['xoopsDB']->error();

	$xforumdata = $GLOBALS['xoopsDB']->fetch_array($result);
	echo "<table border='0' width='100%' cellpadding='5'>";
	echo "<tr><td align='left'>".forum_displayImage($GLOBALS['xforumImage']['f_open'])."&nbsp;&nbsp;<a href='".$xforumPath['url']."archive.php'>";
	echo _MD_XFORUM_ARCHIVE."</a>";
	if($xforumdata['parent_forum'] == 0)
	{
		echo "<br />&nbsp;&nbsp;&nbsp;".forum_displayImage($GLOBALS['xforumImage']['f_close'])."&nbsp;&nbsp;<strong>".$GLOBALS['myts']->htmlSpecialChars($xforumdata['forum_name'])."</strong><br />";
	}
	else
	{
		$q = "select forum_name from ".$GLOBALS['xforumTable']['forums']." WHERE forum_id=".$xforumdata['parent_forum'];
		$row = $GLOBALS['xoopsDB']->fetch_array($GLOBALS['xoopsDB']->query($q));
		echo "<br />&nbsp;&nbsp;&nbsp;".forum_displayImage($GLOBALS['xforumImage']['f_open'])."&nbsp;&nbsp;<a href='".$xforumPath['url']."archive.php?forum=".$xforumdata['parent_forum']."'>".$GLOBALS['myts']->htmlSpecialChars($row['forum_name'])."</a>";
		echo "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".forum_displayImage($GLOBALS['xforumImage']['f_close'])."&nbsp;&nbsp;<strong>".$GLOBALS['myts']->htmlSpecialChars($xforumdata['forum_name'])."</strong><br />";
	}
	echo "</td></tr></table><br />";

	echo "<table border='0' width='90%' cellpadding='5' align='center'>";
	echo "<tr><td>";
	$sql = "select * from ".$GLOBALS['xforumTable']['topics']." where forum_id=$xforum order by topic_last_post_id DESC";
	$result = $GLOBALS['xoopsDB']->query($sql);
	$counter = 1;
	while ($row = $GLOBALS['xoopsDB']->fetch_object($result))
	{
		echo "$counter.&nbsp;";
		echo "<a href='archive.php?forum=$xforum&amp;topic_id=".$row->topic_id."'>".$row->topic_title."</a>";
		echo "&nbsp;&nbsp;&nbsp;<a href='archive.php?forum=$xforum&amp;topic_id=".$row->topic_id."&amp;content_only=1' target='_blank'>"._MD_ARCHIVE_POPUP."</a>";
		echo "<br />";

		$counter++;
	}
	echo "</td></tr></table>";

	include_once(XOOPS_ROOT_PATH."/footer.php");
}
////////////////////////////////////////////////////////////////////
function display_topic($xforum, $topic_id, $content_only = 1)
{
		
	if($content_only==0)
	{
		include_once(XOOPS_ROOT_PATH."/header.php");
	}

	$q = "select * from ".$GLOBALS['xforumTable']['forums']." WHERE forum_id=".$xforum;
	$result = $GLOBALS['xoopsDB']->query($q);
	$xforumdata = $GLOBALS['xoopsDB']->fetch_array($result);

	$q = "select * from ".$GLOBALS['xforumTable']['topics']." WHERE topic_id=".$topic_id;
	$result = $GLOBALS['xoopsDB']->query($q);
	$topicdata = $GLOBALS['xoopsDB']->fetch_array($result);

	echo "<table border='0' width='100%' cellpadding='5'>";
	echo "<tr><td align='left'>".forum_displayImage($GLOBALS['xforumImage']['f_open'])."&nbsp;&nbsp;<a href='".$xforumPath['url']."archive.php'>";
	echo _MD_XFORUM_ARCHIVE."</a>";
	if($xforumdata['parent_forum'] == 0)
	{
		echo "<br />&nbsp;&nbsp;&nbsp;".forum_displayImage($GLOBALS['xforumImage']['f_open'])."&nbsp;&nbsp;<a href='archive.php?forum=$xforum'>".$GLOBALS['myts']->htmlSpecialChars($xforumdata['forum_name'])."</a>";
		echo "<br />".forum_displayImage($GLOBALS['xforumImage']['f_content'])."&nbsp;&nbsp;<strong>".$GLOBALS['myts']->htmlSpecialChars($topicdata['topic_title'])."</strong><br />";
	}
	else
	{
		$q = "select forum_name from ".$GLOBALS['xforumTable']['forums']." WHERE forum_id=".$xforumdata['parent_forum'];
		$row = $GLOBALS['xoopsDB']->fetch_array($GLOBALS['xoopsDB']->query($q));
		echo "<br />&nbsp;&nbsp;&nbsp;".forum_displayImage($GLOBALS['xforumImage']['f_open'])."&nbsp;&nbsp;<a href='".$xforumPath['url']."archive.php?forum=".$xforumdata['parent_forum']."'>".$GLOBALS['myts']->htmlSpecialChars($row['forum_name'])."</a>";
		echo "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".forum_displayImage($GLOBALS['xforumImage']['f_open'])."&nbsp;&nbsp;<a href='archive.php?forum=$xforum'>".$GLOBALS['myts']->htmlSpecialChars($xforumdata['forum_name'])."</a>";
		echo "<br />&nbsp;&nbsp;&nbsp;".forum_displayImage($GLOBALS['xforumImage']['f_content'])."&nbsp;&nbsp;<strong>".$GLOBALS['myts']->htmlSpecialChars($topicdata['topic_title'])."</strong><br />";
	}

	echo "</td></tr></table><br />";

// =============== LINK HEADER ===============
echo "<table border='0' width='640' cellpadding='5' cellspacing='0' bgcolor='#FFFFFF' align=center><tr><td>";
echo "<h3>"._MD_XFORUM." : ".$xforumdata['forum_name']."</h3>";
echo "<h3>"._MD_SUBJECT." : ".$topicdata['topic_title']."</h3>";
echo "<i><strong>".$GLOBALS['meta']['copyright']."<br /><a href=".XOOPS_URL.">".XOOPS_URL."</a>
<br /><br />"._MD_PRINT_TOPIC_LINK."<br />
<a href='".XOOPS_URL."/modules/".$GLOBALS['xforumModule']->dirname()."/viewtopic.php?topic_id=$topic_id&amp;forum=$xforum'>".XOOPS_URL."/modules/".$GLOBALS['xforumModule']->dirname()."/viewtopic.php?topic_id=$topic_id&amp;forum=$xforum</a>
</strong></i><br /><br />";
// ============= END LINK HEADER =============

	$xforumpost = new ForumPosts();
	$xforumpost->setOrder("post_time ASC");
	$xforumpost->setTopicId($topic_id);
	$xforumpost->setParent(0);

	$postsArray = $xforumpost->getAllPosts();
	$count = 0;
	echo "<table border='0' width='100%' cellpadding='5' cellspacing='0' bgcolor='#FFFFFF'><tr><td>";
	foreach ($postsArray as $obj)
	{
		if ( !($count % 2) )
		{
			$row_color = 1;
		}
		else
		{
			$row_color = 2;
		}
		echo "<tr><td>";
		$xforumpost->setType($obj->type);
		$obj->showPostForPrint($order);
		$count++;
		echo "</td></tr>";
	}
	echo "</table>";
	echo "</td></tr></table>";

	if($content_only==0)
	{
		include_once(XOOPS_ROOT_PATH."/footer.php");
	}
}

?>