<?php

// $Id: edit.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $

include 'header.php';
// Disable cache
$GLOBALS['xoopsConfig']["module_cache"][$GLOBALS['xforumModule']->getVar("mid")] = 0;
include XOOPS_ROOT_PATH."/header.php";
foreach (array('forum', 'topic_id', 'post_id', 'order', 'pid') as $getint) {
    ${$getint} = isset($_GET[$getint]) ? intval($_GET[$getint]) : 0;
}
$GLOBALS['viewmode'] = (isset($_GET['viewmode']) && $_GET['viewmode'] != 'flat') ? 'thread' : 'flat';
if ( empty($forum) ) {
    redirect_header(XOOPS_URL."/index.php", 2, _MD_ERRORFORUM);
    exit();
} elseif ( empty($post_id) ) {
    redirect_header("viewforum.php?forum=$forum", 2, _MD_ERRORPOST);
    exit();
} else {
	
    $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$topic_handler = xoops_getmodulehandler('topic', 'xforum');
    $post_handler = xoops_getmodulehandler('post', 'xforum');
    
    $forumpost = $post_handler->get($post_id);
    $forum_obj = $GLOBALS['forum_handler']->get($forumpost->getVar("forum_id"));
    
	if ($GLOBALS['xforumModuleConfig']['htaccess']) {
		$url = $forum_obj->getEDITURL($post_id);
		if (strpos($url, $_SERVER['REQUEST_URI'])==0) {
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header('Location: '.$url);
			exit(0);
		}
	}

    
	if (!$forum_handler->getPermission($forum_obj)){
	    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
	    exit();
	}

	if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
		$online_handler = xoops_getmodulehandler('online', 'xforum');
		$online_handler->init($forum_obj);
	}
	$GLOBALS['isadmin'] = forum_isAdmin($forum_obj);
	$uid = is_object($GLOBALS['xoopsUser'])? $GLOBALS['xoopsUser']->getVar('uid'):0;

	$GLOBALS['topic_status'] = $topic_handler->get($topic_id,'topic_status');
	if ( $topic_handler->getPermission($forum_obj, $GLOBALS['topic_status'], 'edit')
		&& ( $GLOBALS['isadmin'] || $forumpost->checkIdentity()) ) {}
	else{
	    redirect_header("viewtopic.php?forum=".$forum_obj->getVar('forum_id')."&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid",2,_MD_NORIGHTTOEDIT);
	    exit();
	}
    if(!$isadmin && !$forumpost->checkTimelimit('edit_timelimit')){
		redirect_header("viewtopic.php?forum=".$forum_obj->getVar('forum_id')."&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid",2,_MD_TIMEISUP);
    	exit();
	}
    $post_id2 = $forumpost->getVar('pid');

    $dohtml = $forumpost->getVar('dohtml');
    $dosmiley = $forumpost->getVar('dosmiley');
    $doxcode = $forumpost->getVar('doxcode');
    $dobr = $forumpost->getVar('dobr');
    $icon = $forumpost->getVar('icon');
    $attachsig = $forumpost->getVar('attachsig');
    $topic_id=$forumpost->getVar('topic_id');
    $istopic = ( $forumpost->istopic() )?1:0;
    $isedit =1;
    $subject_pre="";
    $subject=$forumpost->getVar('subject', "E");
    $message=$forumpost->getVar('post_text', "E");
    $poster_name=$forumpost->getVar('poster_name', "E");
    $attachments=$forumpost->getAttachment();
    $post_karma=$forumpost->getVar('post_karma');
    $require_reply=$forumpost->getVar('require_reply');
    $tags=$forumpost->getVar('tags', "E");
    $hidden = "";

    include 'include/forumform.inc.php';
    if (!$istopic) {
        $forumpost2 = $post_handler->get($post_id2);

	    $r_message = $forumpost2->getVar('post_text');

    	$GLOBALS['isadmin'] = 0;
    	if($forumpost2->getVar('uid')) {
	    	$r_name = forum_getUnameFromId( $forumpost2->getVar('uid'), $GLOBALS['xforumModuleConfig']['show_realname']);
			if (forum_isAdmin($forum_obj, $forumpost2->getVar('uid'))) $GLOBALS['isadmin'] = 1;
    	}else{
	    	$poster_name = $forumpost2->getVar('poster_name');
    		$r_name = (empty($poster_name))?$GLOBALS['xoopsConfig']['anonymous']:$poster_name;
		}
		$r_date = formatTimestamp($forumpost2->getVar('post_time'));
	    $r_subject = $forumpost2->getVar('subject');

        $r_content = _MD_BY." ".$r_name." "._MD_ON." ".$r_date."<br /><br />";
        $r_content .= $r_message;
        $r_subject=$forumpost2->getVar('subject');
        echo "<table cellpadding='4' cellspacing='1' width='98%' class='outer'><tr><td class='head'>".$r_subject."</td></tr>";
        echo "<tr><td><br />".$r_content."<br /></td></tr></table>";
    }

    include XOOPS_ROOT_PATH.'/footer.php';
}
?>