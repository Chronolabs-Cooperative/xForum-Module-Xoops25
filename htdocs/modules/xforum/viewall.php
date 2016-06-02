<?php

// $Id: viewall.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $


include "header.php";

$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "digest", "unreplied", "unread")))? $_GET['type'] : "active";
$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : 0;
$mode = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?2:$mode;

$GLOBALS['isadmin'] = forum_isAdmin();
/* Only admin has access to admin mode */
if(!$isadmin){
	$type = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?"":$type;
	$mode = 0;
}

$sortorder = (!isset($_GET['sortorder']) || $_GET['sortorder'] != "ASC") ? "DESC" : "ASC";
$since = isset($_GET['since']) ? intval($_GET['since']) : $GLOBALS['xforumModuleConfig']["since_default"];
$GLOBALS['start'] = !empty($_GET['start']) ? intval($_GET['start']) : 0;
if ( !isset($_GET['sortname']) || !in_array($_GET['sortname'], array_keys($sel_sort_array)) ) {
	$sortname = "p.post_time";
} else {
	$sortname = $_GET['sortname'];
}

if ($GLOBALS['xforumModuleConfig']['htaccess']&&empty($_REQUEST['submit'])) {
	$url = XOOPS_URL.'/'.$GLOBALS['xforumModuleConfig']['baseurl'].'/viewall,'.$type.','.$mode.','.$start.','.$since.','.$sortname.','.$sortorder.$GLOBALS['xforumModuleConfig']['endofurl'];
	if (strpos($url, $_SERVER['REQUEST_URI'])==0) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}


$GLOBALS['xoopsOption']['template_main'] = 'xforum_viewall.html';

include XOOPS_ROOT_PATH."/header.php";

if(!empty($GLOBALS['xforumModuleConfig']['rss_enable'])){
	$GLOBALS['xoTheme']->addLink('alternate', XOOPS_URL.'/modules/'.$GLOBALS['xforumModule']->getVar('dirname').'/rss.php', array('type'=>"application/rss+xml", 'title' => $GLOBALS['xforumModule']->getVar('name')));
}


$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$GLOBALS['viewall_forums'] = $GLOBALS['forum_handler']->getForums(0,'access', array("forum_id", "cat_id", "forum_name")); // get all accessible forums

if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = xoops_getmodulehandler('online', 'xforum');
	$online_handler->init();
    $GLOBALS['xoopsTpl']->assign('online', $online_handler->show_online());
}
$GLOBALS['xoopsTpl']->assign('forum_index_title', sprintf(_MD_XFORUMINDEX,htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));
$GLOBALS['xoopsTpl']->assign('folder_topic', forum_displayImage($GLOBALS['xforumImage']['folder_topic']));

$sel_sort_array = array("t.topic_title"=>_MD_TOPICTITLE, "u.uname"=>_MD_TOPICPOSTER, "t.topic_time"=>_MD_TOPICTIME, "t.topic_replies"=>_MD_NUMBERREPLIES, "t.topic_views"=>_MD_VIEWS, "p.post_time"=>_MD_LASTPOSTTIME);

$forum_selection_sort = '<select name="sortname">';
foreach ( $sel_sort_array as $sort_k => $sort_v ) {
	$forum_selection_sort .= '<option value="'.$sort_k.'"'.(($sortname == $sort_k) ? ' selected="selected"' : '').'>'.$sort_v.'</option>';
}
$forum_selection_sort .= '</select>';
$GLOBALS['xoopsTpl']->assign_by_ref('forum_selection_sort', $forum_selection_sort);


$forum_selection_order = '<select name="sortorder">';
$forum_selection_order .= '<option value="ASC"'.(($sortorder == "ASC") ? ' selected="selected"' : '').'>'._MD_ASCENDING.'</option>';
$forum_selection_order .= '<option value="DESC"'.(($sortorder == "DESC") ? ' selected="selected"' : '').'>'._MD_DESCENDING.'</option>';
$forum_selection_order .= '</select>';

// assign to template
$GLOBALS['xoopsTpl']->assign_by_ref('forum_selection_order', $forum_selection_order);


$forum_selection_since = forum_sinceSelectBox($since);

// assign to template
$GLOBALS['xoopsTpl']->assign('forum_selection_since', $forum_selection_since);
$GLOBALS['xoopsTpl']->assign('h_topic_link', XOOPS_URL."/modules/xforum/viewall.php?sortname=t.topic_title&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_title" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_reply_link', XOOPS_URL."/modules/xforum/viewall.php?sortname=t.topic_replies&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_replies" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_poster_link', XOOPS_URL."/modules/xforum/viewall.php?sortname=u.uname&amp;since=$since&amp;sortorder=". (($sortname == "u.uname" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_views_link', XOOPS_URL."/modules/xforum/viewall.php?sortname=t.topic_views&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_views" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_forum_link', XOOPS_URL."/modules/xforum/viewall.php?sortname=t.forum_id&amp;since=$since&amp;sortorder=". (($sortname == "t.forum_id" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_ratings_link', XOOPS_URL."/modules/xforum/viewall.php?sortname=t.topic_ratings&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_ratings" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_date_link', XOOPS_URL."/modules/xforum/viewall.php?sortname=p.post_time&amp;since=$since&amp;sortorder=". (($sortname == "p.post_time" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('forum_since', $since); // For $since in search.php

$startdate = empty($since)?0:(time() - forum_getSinceTime($since));

$all_link = XOOPS_URL."/modules/xforum/viewall.php?start=$start&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since";
$post_link = XOOPS_URL."/modules/xforum/viewpost.php?since=$since";
$newpost_link = XOOPS_URL."/modules/xforum/viewpost.php?new=1&amp;since=$since";
$digest_link = XOOPS_URL."/modules/xforum/viewall.php?start=$start&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=digest";
$unreplied_link = XOOPS_URL."/modules/xforum/viewall.php?start=$start&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=unreplied";
$unread_link = XOOPS_URL."/modules/xforum/viewall.php?start=$start&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=unread";
switch($type){
	case 'digest':
		$current_type = _MD_DIGEST;
		$current_link = $digest_link;
		break;
	case 'unreplied':
		$current_type = _MD_UNREPLIED;
		$current_link = $unreplied_link;
		break;
	case 'unread':
		$current_type = _MD_UNREAD;
		$current_link = $unread_link;
		break;
	case 'active':
		$current_type = _MD_ALL. ' ['._MD_TYPE_ADMIN.']';
		$current_link = $all_link.'&amp;type='.$type;
		break;
	case 'pending':
		$current_type = _MD_ALL. ' ['._MD_TYPE_PENDING.']';
		$current_link = $all_link.'&amp;type='.$type;
		break;
	case 'deleted':
		$current_type = _MD_ALL. ' ['._MD_TYPE_DELETED.']';
		$current_link = $all_link.'&amp;type='.$type;
		break;
	default:
		$type = 'all';
		$current_type = _MD_ALL;
		$current_link = $all_link;
		break;
	}

list($allTopics, $sticky) = $GLOBALS['forum_handler']->getAllTopics($GLOBALS['viewall_forums'], $startdate, $GLOBALS['start'], $sortname, $sortorder, $type);
$GLOBALS['xoopsTpl']->assign_by_ref('topics', $allTopics);
unset($allTopics);
$GLOBALS['xoopsTpl']->assign('sticky', $sticky);
$GLOBALS['xoopsTpl']->assign('rating_enable', $GLOBALS['xforumModuleConfig']['rating_enabled']);
$GLOBALS['xoopsTpl']->assign('img_newposts', forum_displayImage($GLOBALS['xforumImage']['newposts_topic']));
$GLOBALS['xoopsTpl']->assign('img_hotnewposts', forum_displayImage($GLOBALS['xforumImage']['hot_newposts_topic']));
$GLOBALS['xoopsTpl']->assign('img_folder', forum_displayImage($GLOBALS['xforumImage']['folder_topic']));
$GLOBALS['xoopsTpl']->assign('img_hotfolder', forum_displayImage($GLOBALS['xforumImage']['hot_folder_topic']));
$GLOBALS['xoopsTpl']->assign('img_locked', forum_displayImage($GLOBALS['xforumImage']['locked_topic']));

$GLOBALS['xoopsTpl']->assign('img_sticky', forum_displayImage($GLOBALS['xforumImage']['folder_sticky'],_MD_TOPICSTICKY));
$GLOBALS['xoopsTpl']->assign('img_digest', forum_displayImage($GLOBALS['xforumImage']['folder_digest'],_MD_TOPICDIGEST));
$GLOBALS['xoopsTpl']->assign('img_poll', forum_displayImage($GLOBALS['xforumImage']['poll'],_MD_TOPICHASPOLL));
$GLOBALS['xoopsTpl']->assign('all_link', $all_link);
$GLOBALS['xoopsTpl']->assign('post_link', $post_link);
$GLOBALS['xoopsTpl']->assign('newpost_link', $newpost_link);
$GLOBALS['xoopsTpl']->assign('digest_link', $digest_link);
$GLOBALS['xoopsTpl']->assign('unreplied_link', $unreplied_link);
$GLOBALS['xoopsTpl']->assign('unread_link', $unread_link);
$GLOBALS['xoopsTpl']->assign('current_type', $current_type);
$GLOBALS['xoopsTpl']->assign('current_link', $current_link);

$all_topics = $GLOBALS['forum_handler']->getTopicCount($GLOBALS['viewall_forums'], $startdate, $type);
unset($GLOBALS['viewall_forums']);
if ( $all_topics > $GLOBALS['xforumModuleConfig']['topics_per_page']) {
	$nav = new XoopsPageNav($all_topics, $GLOBALS['xforumModuleConfig']['topics_per_page'], $GLOBALS['start'], "start", 'sortname='.$sortname.'&amp;sortorder='.$sortorder.'&amp;since='.$since."&amp;type=$type&amp;mode=".$mode);
	$GLOBALS['xoopsTpl']->assign('forum_pagenav', $nav->renderNav(4));
} else {
	$GLOBALS['xoopsTpl']->assign('forum_pagenav', '');
}
if(!empty($GLOBALS['xforumModuleConfig']['show_jump'])){
	$GLOBALS['xoopsTpl']->assign('forum_jumpbox', forum_make_jumpbox());
}
$GLOBALS['xoopsTpl']->assign('down',forum_displayImage($GLOBALS['xforumImage']['doubledown']));
$GLOBALS['xoopsTpl']->assign('menumode',$menumode);
$GLOBALS['xoopsTpl']->assign('menumode_other',$menumode_other);

$GLOBALS['xoopsTpl']->assign('mode', $mode);
$GLOBALS['xoopsTpl']->assign('type', $type);
$GLOBALS['xoopsTpl']->assign('viewer_level', ($isadmin)?2:(is_object($GLOBALS['xoopsUser'])?1:0) );

$GLOBALS['xoopsTpl']->assign('xoops_pagetitle', $GLOBALS['xforumModule']->getVar('name'). ' - ' .$current_type);

include XOOPS_ROOT_PATH."/footer.php";
?>