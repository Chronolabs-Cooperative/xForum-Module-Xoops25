<?php

// $Id: ratethread.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $


include 'header.php';

$ratinguser = is_object($GLOBALS['xoopsUser'])?$GLOBALS['xoopsUser'] -> getVar('uid'):0;
$anonwaitdays = 1;
$ip = forum_getIP(true);
foreach(array("topic_id", "rate", "forum") as $var){
	${$var} = isset($_POST[$var]) ? intval($_POST[$var]) : (isset($_GET[$var])?intval($_GET[$var]):0);
}

$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$topic_obj = $topic_handler->get($topic_id);
if (!$topic_handler->getPermission($topic_obj->getVar("forum_id"), $topic_obj->getVar('topic_status'), "post")
	&&
	!$topic_handler->getPermission($topic_obj->getVar("forum_id"), $topic_obj->getVar('topic_status'), "reply")
){
	redirect_header("javascript:history.go(-1);", 2, _NOPERM);
}

if (empty($rate)){
	redirect_header(XOOPS_URL."/modules/xforum/viewtopic.php?topic_id=".$topic_id."&amp;forum=".$xforum."", 4, _MD_NOVOTERATE);
    exit();
}
$rate_handler = xoops_getmodulehandler("rate", $GLOBALS['xforumModule']->getVar("dirname"));
if ($ratinguser != 0) {
	// Check if Topic POSTER is voting (UNLESS Anonymous users allowed to post)
    $crit_post = New CriteriaCompo(new Criteria("topic_id", $topic_id));
    $crit_post->add(new Criteria("post_uid", $ratinguser));
    $post_handler = xoops_getmodulehandler("post", $GLOBALS['xforumModule']->getVar("dirname"));
    if($post_handler->getCount($crit_post)){
        redirect_header("viewtopic.php?topic_id=".$topic_id."&amp;forum=".$xforum."", 4, _MD_CANTVOTEOWN);
        exit();
    }
    // Check if REG user is trying to vote twice.
    $crit_rate = New CriteriaCompo(new Criteria("topic_id", $topic_id));
    $crit_rate->add(new Criteria("ratinguser", $ratinguser));
    if($rate_handler->getCount($crit_rate)){
        redirect_header("viewtopic.php?topic_id=".$topic_id."&amp;forum=".$xforum."", 4, _MD_VOTEONCE);
        exit();
    }
}else{
    // Check if ANONYMOUS user is trying to vote more than once per day.
    $crit_rate = New CriteriaCompo(new Criteria("topic_id", $topic_id));
    $crit_rate->add(new Criteria("ratinguser", $ratinguser));
    $crit_rate->add(new Criteria("ratinghostname", $ip));
    $crit_rate->add(new Criteria("ratingtimestamp", time() - (86400 * $anonwaitdays), ">"));
    if($rate_handler->getCount($crit_rate)){
        redirect_header("viewtopic.php?topic_id=".$topic_id."&amp;forum=".$xforum."", 4, _MD_VOTEONCE);
        exit();
    }
}
$rate_obj = $rate_handler->create();
$rate_obj->setVar("rating", $rate*2);
$rate_obj->setVar("topic_id", $topic_id);
$rate_obj->setVar("ratinguser", $ratinguser);
$rate_obj->setVar("ratinghostname", $ip);
$rate_obj->setVar("ratingtimestamp", time());

$ratingid = $rate_handler->insert($rate_obj);;

forum_updaterating($topic_id);
$ratemessage = _MD_VOTEAPPRE . "<br />" . sprintf(_MD_THANKYOU, $GLOBALS['xoopsConfig']['sitename']);
redirect_header("viewtopic.php?topic_id=".$topic_id."&amp;forum=".$xforum."", 2, $ratemessage);
exit();

include 'footer.php';
?>