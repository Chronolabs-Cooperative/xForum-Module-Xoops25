<?php

// $Id: report.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $

include 'header.php';

if ( isset($_POST['submit']) ) {
	$GPC = "_POST";
} else {
	$GPC = "_GET";
}

foreach (array('forum', 'topic_id', 'post_id', 'order', 'pid') as $getint) {
    ${$getint} = isset(${$GPC}[$getint]) ? intval(${$GPC}[$getint]) : 0;
}
$GLOBALS['viewmode'] = (isset(${$GPC}['viewmode']) && ${$GPC}['viewmode'] != 'flat') ? 'thread' : 'flat';

if ( empty($forum) ) {
    redirect_header(XOOPS_URL."/index.php", 2, _MD_ERRORFORUM);
    exit();
} elseif ( empty($topic_id) ) {
    redirect_header("viewforum.php?forum=$forum", 2, _MD_ERRORTOPIC);
    exit();
} elseif ( empty($post_id) ) {
    redirect_header(XOOPS_URL."/modules/xforum/viewtopic.php?topic_id=$topic_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid", 2, _MD_ERRORPOST);
    exit();
}

if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = xoops_getmodulehandler('online', 'xforum');
	$online_handler->init($forum);
}

$GLOBALS['myts'] = MyTextSanitizer::getInstance();

if ( isset($_POST['submit']) ) {
	$report_handler = xoops_getmodulehandler('report', 'xforum');
	$report = $report_handler->create();
	$report->setVar('report_text', $_POST['report_text']);
	$report->setVar('post_id', $post_id);
	$report->setVar('report_time', time());
	$report->setVar('reporter_uid', is_object($GLOBALS['xoopsUser'])?$GLOBALS['xoopsUser']->getVar('uid'):0);
	$report->setVar('reporter_ip', forum_getIP());
	$report->setVar('report_result', 0);
	$report->setVar('report_memo', "");

    if ($report_id = $report_handler->insert($report)) {
	    $message = _MD_REPORTED;
    }else{
	    $message = _MD_REPORT_ERROR;
    }
	redirect_header(XOOPS_URL."/modules/xforum/viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode",2,$message);
    exit();
}else{

	// Disable cache
	$GLOBALS['xoopsConfig']["module_cache"][$GLOBALS['xforumModule']->getVar("mid")] = 0;
    include XOOPS_ROOT_PATH.'/header.php';
	include XOOPS_ROOT_PATH."/class/xoopsformloader.php";

	$report_form = new XoopsThemeForm('', 'reportform', 'report.php');

	$report_form->addElement(new XoopsFormText(_MD_REPORT_TEXT, 'report_text', 80, 255), true);

	$report_form->addElement(new XoopsFormHidden('pid', $pid));
	$report_form->addElement(new XoopsFormHidden('post_id', $post_id));
	$report_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
	$report_form->addElement(new XoopsFormHidden('forum', $forum));
	$report_form->addElement(new XoopsFormHidden('viewmode', $GLOBALS['viewmode']));
	$report_form->addElement(new XoopsFormHidden('order', $GLOBALS['order']));

	$button_tray = new XoopsFormElementTray('');
	$submit_button = new XoopsFormButton('', 'submit', _SUBMIT, "submit");
	$cancel_button = new XoopsFormButton('', 'cancel', _MD_CANCELPOST, 'button');
	$extra = "viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode";
	$cancel_button->setExtra("onclick='location=\"".$extra."\"'");
	$button_tray->addElement($submit_button);
	$button_tray->addElement($cancel_button);
	$report_form->addElement($button_tray);

	$report_form->display();

    $post_handler = xoops_getmodulehandler('post', 'xforum');
    $forumpost = $post_handler->get($post_id);
    $r_subject=$forumpost->getVar('subject', "E");
    if( $GLOBALS['xforumModuleConfig']['enable_karma'] && $forumpost->getVar('post_karma') > 0 ) {
        $r_message = sprintf(_MD_KARMA_REQUIREMENT, "***", $forumpost->getVar('post_karma'))."</div>";
    }elseif( $GLOBALS['xforumModuleConfig']['allow_require_reply'] && $forumpost->getVar('require_reply') ) {
        $r_message = _MD_REPLY_REQUIREMENT;
    }else{
	    $r_message = $forumpost->getVar('post_text');
    }

    $r_date = formatTimestamp($forumpost->getVar('post_time'));
    if($forumpost->getVar('uid')) {
	    $r_name =forum_getUnameFromId( $forumpost->getVar('uid'), $GLOBALS['xforumModuleConfig']['show_realname']);
    }else{
	    $poster_name = $forumpost->getVar('poster_name');
    	$r_name = (empty($poster_name))?$GLOBALS['xoopsConfig']['anonymous']:$GLOBALS['myts']->htmlSpecialChars($poster_name);
	}
    $r_content = _MD_SUBJECTC." ".$r_subject."<br />";
    $r_content .= _MD_BY." ".$r_name." "._MD_ON." ".$r_date."<br /><br />";
    $r_content .= $r_message;

    echo "<br /><table cellpadding='4' cellspacing='1' width='98%' class='outer'><tr><td class='head'>".$r_subject."</td></tr>";
    echo "<tr><td><br />".$r_content."<br /></td></tr></table>";

    include XOOPS_ROOT_PATH.'/footer.php';
}
?>