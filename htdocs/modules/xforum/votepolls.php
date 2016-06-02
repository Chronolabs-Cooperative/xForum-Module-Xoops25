<?php

// $Id: votepolls.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $
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

include("header.php");

include_once XOOPS_ROOT_PATH."/modules/xoopspoll/include/constants.php";
include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspoll.php";
include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspolloption.php";
include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspolllog.php";
include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspollrenderer.php";

if ( !empty($_POST['poll_id']) ) {
	$poll_id = intval($_POST['poll_id']);
} elseif (!empty($_GET['poll_id'])) {
	$poll_id = intval($_GET['poll_id']);
}
if ( !empty($_POST['topic_id']) ) {
	$topic_id = intval($_POST['topic_id']);
} elseif (!empty($_GET['topic_id'])) {
	$topic_id = intval($_GET['topic_id']);
}
if ( !empty($_POST['forum']) ) {
	$xforum = intval($_POST['forum']);
} elseif (!empty($_GET['forum'])) {
	$xforum = intval($_GET['forum']);
}

$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$topic_obj = $topic_handler->get($topic_id);
if (!$topic_handler->getPermission($topic_obj->getVar("forum_id"), $topic_obj->getVar('topic_status'), "vote")){
    	redirect_header("javascript:history.go(-1);", 2, _NOPERM);
}

if ( !empty($_POST['option_id']) ) {
	$mail_author = false;
	$poll = new XoopsPoll($poll_id);

		if ( is_object($GLOBALS['xoopsUser']) ) {
			if ( XoopsPollLog::hasVoted($poll_id, $_SERVER['REMOTE_ADDR'], $GLOBALS['xoopsUser']->getVar("uid")) ) {
				$msg = _PL_ALREADYVOTED;
				setcookie("xf_polls[$poll_id]", 1);
			} else {
				$poll->vote($_POST['option_id'], '', $GLOBALS['xoopsUser']->getVar("uid"));
				$poll->updateCount();
				$msg = _PL_THANKSFORVOTE;
				setcookie("xf_polls[$poll_id]", 1);
			}
		} else {
			if ( XoopsPollLog::hasVoted($poll_id, $_SERVER['REMOTE_ADDR']) ) {
				$msg = _PL_ALREADYVOTED;
				setcookie("xf_polls[$poll_id]", 1);
			} else {
				$poll->vote($_POST['option_id'], $_SERVER['REMOTE_ADDR']);
				$poll->updateCount();
				$msg = _PL_THANKSFORVOTE;
				setcookie("xf_polls[$poll_id]", 1);
			}
		}

	redirect_header("viewtopic.php?topic_id=$topic_id&amp;forum=$xforum&amp;poll_id=$poll_id&amp;pollresult=1", 1, $msg);
	exit();
}
redirect_header("viewtopic.php?topic_id=$topic_id&amp;forum=$xforum", 1, "You must choose an option !!");

?>
