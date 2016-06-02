<?php

// $Id: newtopic.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $
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

foreach (array('forum', 'order') as $getint) {
    ${$getint} = isset($_GET[$getint]) ? intval($_GET[$getint]) : 0;
}
if (isset($_GET['op'])) $op = $_GET['op'];
$GLOBALS['viewmode'] = (isset($_GET['viewmode']) && $_GET['viewmode'] != 'flat') ? 'thread' : 'flat';
if ( empty($forum) ) {
    redirect_header(XOOPS_URL."/index.php", 2, _MD_ERRORFORUM);
    exit();
}
    $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
    $forum_obj = $GLOBALS['forum_handler']->get($forum);
	if (!$forum_handler->getPermission($forum_obj)){
	    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
	    exit();
	}

	$topic_handler = xoops_getmodulehandler('topic', 'xforum');
	if (!$topic_handler->getPermission($forum_obj, 0, 'post')) {
        redirect_header("viewforum.php?order=$order&amp;viewmode=$viewmode&amp;forum=".$forum_obj->getVar('forum_id'),2,_MD_NORIGHTTOPOST);
	    exit();
	}

	if ($GLOBALS['xforumModuleConfig']['htaccess']) {
		$url = $forum_obj->getNEWTOPICURL();
		if (strpos($url, $_SERVER['REQUEST_URI'])==0) {
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header('Location: '.$url);
			exit(0);
		}
	}

	if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
		$online_handler = xoops_getmodulehandler('online', 'xforum');
		$online_handler->init($forum_obj);
	}

    $istopic = 1;
    $pid=0;
    $subject = "";
    $message = "";
    $GLOBALS['myts'] = MyTextSanitizer::getInstance();
    $hidden = "";
    $subject_pre="";
    $dohtml = 1;
    $dosmiley = 1;
    $doxcode = 1;
    $dobr = 1;
    $icon = '';
    $post_karma = 0;
    $require_reply = 0;
    $attachsig = (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->getVar('attachsig')) ? 1 : 0;
    unset($post_id);
    unset($topic_id);


	// Disable cache
	$GLOBALS['xoopsConfig']["module_cache"][$GLOBALS['xforumModule']->getVar("mid")] = 0;
    include XOOPS_ROOT_PATH.'/header.php';
    if ($GLOBALS['xforumModuleConfig']['disc_show'] == 1 or $GLOBALS['xforumModuleConfig']['disc_show'] == 3 ){
	    echo "<div class=\"confirmMsg\">".$GLOBALS['xforumModuleConfig']['disclaimer']."</div><br clear=\"both\">";
    }

    include 'include/forumform.inc.php';
    include XOOPS_ROOT_PATH.'/footer.php';
?>