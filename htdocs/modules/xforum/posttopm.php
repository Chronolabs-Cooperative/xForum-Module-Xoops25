<?php

// $Id: posttonews.php,v 4.04 2005/05/21 13:26:07 wishcraft Exp $
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
include 'header.php';
$GLOBALS['xoopsOption']['output_type'] = 'plain';
include_once XOOPS_ROOT_PATH.'/header.php';

$post_id = !empty($_GET['post_id']) ? intval($_GET['post_id']) : 0;

$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$post_handler = xoops_getmodulehandler('post', 'xforum');
if ( !$post_id ) {
	$message = _MD_ERRORPOST;
}else{
    $GLOBALS['xforumtopic'] = $topic_handler->getByPost($post_id);
	if(!$GLOBALS['xforumtopic']->getVar('approved')){
		$message = _MD_NORIGHTTOVIEW;
	}else{
		$forum_id = $GLOBALS['xforumtopic']->getVar('forum_id');
		$GLOBALS['viewtopic_forum'] = $GLOBALS['forum_handler']->get($forum_id);
		if (!$forum_handler->getPermission($GLOBALS['viewtopic_forum'])){
			$message = _MD_NORIGHTTOACCESS;
		}elseif(!$topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "view")){
			$message = _MD_NORIGHTTOVIEW;
		}else{
			$post =  $post_handler->get($post_id);
			if(!$post->getVar('approved')){
				$message = _MD_NORIGHTTOVIEW;
			}else{
				$post_data  = $post_handler->getPostForPrint($post);
				$postdata  = str_replace("<br />", "\n\r", $post_data["text"]);
				$postdata  = str_replace("<br>", "\n\r", $postdata);
				$postdata  = "[quote]\n".forum_html2text($postdata)."\n[/quote]";
			}
		}
	}
}
$msg = empty($message)?$GLOBALS['myts']->displayTarea($postdata):$message;

include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");
$pmform = new XoopsThemeForm(_MD_PM, 'pmform', XOOPS_URL."/modules/pm/pmlite.php", 'post');
$pmform->addElement(new XoopsFormLabel(_MD_MESSAGEC, $msg));
$button_tray = new XoopsFormElementTray('');
if(isset($postdata)){
	$button_tray->addElement(new XoopsFormButton('', 'sendmod', _SUBMIT, 'submit'));
	$pmform->addElement(new XoopsFormHidden('to_userid', $post->getVar('uid')));
	$pmform->addElement(new XoopsFormHidden('post_id', $post_id));
	$pmform->addElement(new XoopsFormHidden('subject', $post->getVar('subject')));
	$data  = " \n \n--------------\n [url=".XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/viewtopic.php?post_id=".$post_id."] ".$post->getVar('subject')." [/url] \n".$postdata."";
	$pmform->addElement(new XoopsFormHidden('message', htmlspecialchars($data, ENT_QUOTES)));
}
$cancel_send = new XoopsFormButton('', 'cancel', _CANCEL, 'button');
$cancel_send->setExtra("onclick='javascript:window.close();'");
$button_tray->addElement($cancel_send);
$pmform->addElement($button_tray);
$pmform->display();
        //$pmform->assign($GLOBALS['xoopsTpl']);

include XOOPS_ROOT_PATH.'/footer.php';
?>