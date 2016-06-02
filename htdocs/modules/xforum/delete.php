<?php

// $Id: delete.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $
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

include 'header.php';

$ok = isset($_POST['ok']) ? intval($_POST['ok']) : 0;
foreach (array('forum', 'topic_id', 'post_id', 'order', 'pid', 'act') as $getint) {
    ${$getint} = isset($_POST[$getint]) ? intval($_POST[$getint]) : 0;

}
foreach (array('forum', 'topic_id', 'post_id', 'order', 'pid', 'act') as $getint) {
    ${$getint} = (${$getint})?${$getint}:(isset($_GET[$getint]) ? intval($_GET[$getint]) : 0);
}
$GLOBALS['viewmode'] = (isset($_GET['viewmode']) && $_GET['viewmode'] != 'flat') ? 'thread' : 'flat';
$GLOBALS['viewmode'] = ($viewmode)?$viewmode: (isset($_POST['viewmode'])?$_POST['viewmode'] : 'flat');

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$post_handler = xoops_getmodulehandler('post', 'xforum');

if ( !empty($post_id) ) {
    $topic = $topic_handler->getByPost($post_id);
} else {
    $topic = $topic_handler->get($topic_id);
}
$topic_id = $topic->getVar('topic_id');
if ( !$topic_id ) {
	$redirect = empty($forum)?"index.php":'viewforum.php?forum='.$forum;
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
    exit();
}

$forum = $topic->getVar('forum_id');
$forum_obj = $GLOBALS['forum_handler']->get($forum);
if (!$forum_handler->getPermission($forum_obj)){
    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
    exit();
}

$GLOBALS['isadmin'] = forum_isAdmin($forum_obj);
$uid = is_object($GLOBALS['xoopsUser'])? $GLOBALS['xoopsUser']->getVar('uid'):0;

$forumpost = $post_handler->get($post_id);
$GLOBALS['topic_status'] = $topic->getVar('topic_status');
if ( $topic_handler->getPermission($topic->getVar("forum_id"), $GLOBALS['topic_status'], 'delete')
	&& ( $GLOBALS['isadmin'] || $forumpost->checkIdentity() )){}
else{
	redirect_header("viewtopic.php?topic_id=$topic_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid&amp;forum=$forum", 2, _MD_DELNOTALLOWED);
    exit();
}

if (!$isadmin && !$forumpost->checkTimelimit('delete_timelimit')){
	redirect_header("viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid",2,_MD_TIMEISUPDEL);
	exit();
}

if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = xoops_getmodulehandler('online', 'xforum');
	$online_handler->init($forum_obj);
}

if ( $ok ) {
    $isDeleteOne = (FORUM_DELETEONE == $ok)? true : false;
    /*
    if($forumpost->isTopic() && $topic->getVar("topic_replies")==0) $isDeleteOne=false;
    if($isDeleteOne && $forumpost->isTopic() && $topic->getVar("topic_replies")>0){
    	$post_handler->emptyTopic($forumpost);
    }else{
	*/    
	    $post_handler->delete($forumpost, $isDeleteOne);
		$GLOBALS['forum_handler']->synchronization($forum);
		$topic_handler->synchronization($topic_id);
    //}

    if ( $isDeleteOne ){
        redirect_header("viewtopic.php?topic_id=$topic_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid&amp;forum=$forum", 2, _MD_POSTDELETED);
    }else{
        redirect_header("viewforum.php?forum=$forum", 2, _MD_POSTSDELETED);
    }
	exit();

} else {
    include XOOPS_ROOT_PATH."/header.php";
	xoops_confirm(array('post_id' => $post_id, 'viewmode' => $GLOBALS['viewmode'], 'order' => $GLOBALS['order'], 'forum' => $forum, 'topic_id' => $topic_id, 'ok' => FORUM_DELETEONE), 'delete.php', _MD_DEL_ONE);
	if($isadmin){
    	xoops_confirm(array('post_id' => $post_id, 'viewmode' => $GLOBALS['viewmode'], 'order' => $GLOBALS['order'], 'forum' => $forum, 'topic_id' => $topic_id, 'ok' => FORUM_DELETEALL), 'delete.php', _MD_DEL_RELATED);
	}
	include XOOPS_ROOT_PATH.'/footer.php';
}
?>