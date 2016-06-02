<?php

// $Id: viewforum.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $


include "header.php";

$forum_id = intval(!empty($_GET['forum_id'])?$_GET['forum_id']:$_GET['forum']);
$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "digest", "unreplied", "unread")))? $_GET['type'] : "";
$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : 0;
$mode = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?2:$mode;
if ( !isset($_GET['sortname']) || $_GET['sortname'] == 'f.forum_order') {
	$sortname = "p.post_time";
} else {
	$sortname = $_GET['sortname'];
}
$sortorder = (!isset($_GET['sortorder']) || $_GET['sortorder'] != "ASC") ? "DESC" : "ASC";
$since = isset($_GET['since']) ? intval($_GET['since']) : $GLOBALS['xforumModuleConfig']["since_default"];

if (isset($_GET['mark_read'])){
    if(1 == intval($_GET['mark_read'])){ // marked as read
	    $markvalue = 1;
	    $markresult = _MD_MARK_READ;
    }else{ // marked as unread
	    $markvalue = 0;
	    $markresult = _MD_MARK_UNREAD;
    }
	forum_setRead_topic($markvalue, $forum_id);
	$url = XOOPS_URL."/modules/xforum/viewforum.php?start=".$_GET['start']."&amp;forum=".$forum_id."&amp;sortname=".$_GET['sortname']."&amp;sortorder=".$_GET['sortorder']."&amp;since=".$_GET['since'];
    redirect_header($url,2, $markresult);
}

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');

$forum_obj = $GLOBALS['forum_handler']->get($forum_id);
// Thanks Raz - 2011 - Got to watch that ctrl + d in eclipse near the save 

if (!$forum_handler->getPermission($forum_obj)){
    redirect_header(XOOPS_URL."/index.php", 10, _MD_NORIGHTTOACCESS);
    exit();
}

if ($GLOBALS['xforumModuleConfig']['htaccess']) {
	if (!is_object($forum_obj))
		$forum_obj = $GLOBALS['forum_handler']->get($_REQUEST['forum']);
	$url = $forum_obj->getURL();
	
	if (strpos($url, $_SERVER['REQUEST_URI'])==0) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}

forum_setRead("forum", $forum_id, $forum_obj->getVar("forum_last_post_id"));

$xoops_pagetitle = $forum_obj->getVar('forum_name') . " [" .$GLOBALS['xforumModule']->getVar('name')."]";

$GLOBALS['xoopsOption']['template_main'] = 'xforum_viewforum.html';
$GLOBALS['xoopsOption']['xoops_pagetitle']= $xoops_pagetitle;

include XOOPS_ROOT_PATH."/header.php";

if(!empty($GLOBALS['xforumModuleConfig']['rss_enable'])){
	$GLOBALS['xoTheme']->addLink('alternate', $forum_obj->getRSSURL(), array('type'=>"application/rss+xml", 'title' => $GLOBALS['xforumModule']->getVar('name').' - '.$forum_obj->getVar('forum_name')));
}
if(!empty($GLOBALS['xforumModuleConfig']['pngforie_enabled'])){
	$GLOBALS['xoTheme']->addStylesheet(null,"text/css",'img {behavior:url("'.XOOPS_URL.'/modules/xforum/include/pngbehavior.htc");}');
}
$GLOBALS['xoTheme']->addScript(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/js/xforum_toggle.js");
$GLOBALS['xoTheme']->addScript(null, 'text/javascript', 'var toggle_cookie="'.$forumCookie['prefix'].'G'.'";');
if($menumode==2){
	$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/language/" . $GLOBALS['xoopsConfig']['language'] . "/forum_menu_hover.css", "text/css");
	$GLOBALS['xoTheme']->addStylesheet(null,"text/css",'body {behavior:url("'.XOOPS_URL.'/modules/xforum/include/xforum.htc");}');
}
if($menumode==1){
	$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/language/" . $GLOBALS['xoopsConfig']['language'] . "/forum_menu_click.css", "text/css");
	$GLOBALS['xoTheme']->addScript(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/js/forum_menu_click.js", 'text/javascript');
}
$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/language/" . $GLOBALS['xoopsConfig']['language'] . "/xforum.css", "text/css");

$GLOBALS['xoopsTpl']->assign('xoops_pagetitle', $xoops_pagetitle);
$GLOBALS['xoopsTpl']->assign("forum_id", $forum_obj->getVar('forum_id'));

$GLOBALS['isadmin'] = forum_isAdmin($forum_obj);
$GLOBALS['xoopsTpl']->assign('viewer_level', ($isadmin)?2:(is_object($GLOBALS['xoopsUser'])?1:0) );
/* Only admin has access to admin mode */
if(!$isadmin){
	$type = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?"":$type;
	$mode = 0;
}
$GLOBALS['xoopsTpl']->assign('mode', $mode);
$GLOBALS['xoopsTpl']->assign('type', $type);

if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = xoops_getmodulehandler('online', 'xforum');
	$online_handler->init($forum_obj);
    $GLOBALS['xoopsTpl']->assign('online', $online_handler->show_online());
}

$getpermission = xoops_getmodulehandler('permission', 'xforum');
$permission_set = $getpermission->getPermissions("forum", $forum_obj->getVar('forum_id'));

if ($forum_handler->getPermission($forum_obj, "post")){
	$GLOBALS['xoopsTpl']->assign('forum_newtopic', "<a href=\"".XOOPS_URL."/modules/xforum/newtopic.php?forum=".$forum_obj->getVar('forum_id')."\">".forum_displayImage($GLOBALS['xforumImage']['t_new'],_MD_ADDTOPIC)."</a>");
	if ($forum_handler->getPermission($forum_obj, "addpoll") && $forum_obj->getVar('allow_polls') == 1){
		$t_poll = forum_displayImage($GLOBALS['xforumImage']['t_poll'],_MD_ADDPOLL);
		$GLOBALS['xoopsTpl']->assign('forum_addpoll', "<a href=\"newtopic.php?op=add&amp;forum=".$forum_obj->getVar('forum_id')."\">".$t_poll."</a>&nbsp;");
 	}
} else {
    if ( !empty($GLOBALS['xforumModuleConfig']["show_reg"]) && !is_object($GLOBALS['xoopsUser'])) {
	    $redirect = preg_replace("|(.*)\/modules\/xforum\/(.*)|", "\\1/modules/xforum/newtopic.php?forum=".$forum_obj->getVar('forum_id'), htmlspecialchars($xoopsRequestUri));
		$GLOBALS['xoopsTpl']->assign('forum_newtopic', '_inactive');
		$GLOBALS['xoopsTpl']->assign('forum_post_or_register', '<a href="'.XOOPS_URL.'/user.php?xoops_redirect='.$redirect.'">'._MD_REGTOPOST.'</a>');
		$GLOBALS['xoopsTpl']->assign('forum_addpoll', "");
	} else {
		$GLOBALS['xoopsTpl']->assign('forum_newtopic', '_inactive');
		$GLOBALS['xoopsTpl']->assign('forum_post_or_register', "");
		$GLOBALS['xoopsTpl']->assign('forum_addpoll', "");
	}
}

if($forum_obj->getVar('parent_forum')){
	$parent_forum_obj = $GLOBALS['forum_handler']->get($forum_obj->getVar('parent_forum'), array("forum_name"));
	$parentforum = array("id"=>$forum_obj->getVar('parent_forum'), "name"=>$parent_forum_obj->getVar("forum_name"));
	unset($parent_forum_obj);
	$GLOBALS['xoopsTpl']->assign_by_ref("parentforum", $parentforum);
}else{
	$criteria = new Criteria("parent_forum", $forum_id);
	$criteria->setSort("forum_order");
	if($xforums_obj = $GLOBALS['forum_handler']->getAll($criteria)){
		$subforum_array = $GLOBALS['forum_handler']->display($xforums_obj);
		$subforum = array_values($subforum_array[$forum_id]);
		unset($xforums_obj, $subforum_array);
		$GLOBALS['xoopsTpl']->assign_by_ref("subforum", $subforum);
	}
}

$category_handler = xoops_getmodulehandler("category");
$category_obj = $category_handler->get($forum_obj->getVar("cat_id"), array("cat_title"));
$GLOBALS['xoopsTpl']->assign('category', array("id" => $forum_obj->getVar("cat_id"), "title" => $category_obj->getVar('cat_title')));

$GLOBALS['xoopsTpl']->assign('forum_index_title', sprintf(_MD_XFORUMINDEX,htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));
$GLOBALS['xoopsTpl']->assign('folder_topic', forum_displayImage($GLOBALS['xforumImage']['folder_topic']));
$GLOBALS['xoopsTpl']->assign('forum_name', $forum_obj->getVar('forum_name'));
$GLOBALS['xoopsTpl']->assign('forum_moderators', $forum_obj->disp_forumModerators());


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

$GLOBALS['xoopsTpl']->assign_by_ref('forum_selection_order', $forum_selection_order);

$forum_selection_since = forum_sinceSelectBox($since);

$GLOBALS['xoopsTpl']->assign_by_ref('forum_selection_since', $forum_selection_since);
$GLOBALS['xoopsTpl']->assign('h_topic_link', XOOPS_URL."/modules/xforum/viewforum.php?forum=$forum_id&amp;sortname=t.topic_title&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_title" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_reply_link', XOOPS_URL."/modules/xforum/viewforum.php?forum=$forum_id&amp;sortname=t.topic_replies&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_replies" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_poster_link', XOOPS_URL."/modules/xforum/viewforum.php?forum=$forum_id&amp;sortname=u.uname&amp;since=$since&amp;sortorder=". (($sortname == "u.uname" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_views_link', XOOPS_URL."/modules/xforum/viewforum.php?forum=$forum_id&amp;sortname=t.topic_views&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_views" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_rating_link', XOOPS_URL."/modules/xforum/viewforum.php?forum=$forum_id&amp;sortname=t.topic_ratings&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_ratings" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_date_link', XOOPS_URL."/modules/xforum/viewforum.php?forum=$forum_id&amp;sortname=p.post_time&amp;since=$since&amp;sortorder=". (($sortname == "p.post_time" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('h_publish_link', XOOPS_URL."/modules/xforum/viewforum.php?forum=$forum_id&amp;sortname=t.topic_time&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_time" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('forum_since', $since); // For $since in search.php

$startdate = empty($since)?0:(time() - forum_getSinceTime($since));
$GLOBALS['start'] = !empty($_GET['start']) ? intval($_GET['start']) : 0;
list($allTopics, $sticky) = $GLOBALS['forum_handler']->getAllTopics($forum_id,$startdate,$start,$sortname,$sortorder,$type,$GLOBALS['xforumModuleConfig']['post_excerpt']);
$GLOBALS['xoopsTpl']->assign_by_ref('topics', $allTopics);

//unset($allTopics);
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

$mark_read_link = XOOPS_URL."/modules/xforum/viewforum.php?mark_read=1&amp;start=$start&amp;forum=".$forum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=$type";
$mark_unread_link = XOOPS_URL."/modules/xforum/viewforum.php?mark_read=2&amp;start=$start&amp;forum=".$forum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=$type";
$GLOBALS['xoopsTpl']->assign('mark_read', $mark_read_link);
$GLOBALS['xoopsTpl']->assign('mark_unread', $mark_unread_link);

$GLOBALS['xoopsTpl']->assign('post_link', XOOPS_URL."/modules/xforum/viewpost.php?forum=".$forum_obj->getVar('forum_id'));
$GLOBALS['xoopsTpl']->assign('newpost_link', XOOPS_URL."/modules/xforum/viewpost.php?type=new&amp;forum=".$forum_obj->getVar('forum_id'));
$GLOBALS['xoopsTpl']->assign('all_link', XOOPS_URL."/modules/xforum/viewforum.php?start=$start&amp;forum=".$forum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since");
$GLOBALS['xoopsTpl']->assign('digest_link', XOOPS_URL."/modules/xforum/viewforum.php?start=$start&amp;forum=".$forum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=digest");
$GLOBALS['xoopsTpl']->assign('unreplied_link', XOOPS_URL."/modules/xforum/viewforum.php?start=$start&amp;forum=".$forum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=unreplied");
$GLOBALS['xoopsTpl']->assign('unread_link', XOOPS_URL."/modules/xforum/viewforum.php?start=$start&amp;forum=".$forum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=unread");
switch($type){
	case 'digest':
		$current_type = '['._MD_DIGEST.']';
		break;
	case 'unreplied':
		$current_type = '['._MD_UNREPLIED.']';
		break;
	case 'unread':
		$current_type = '['._MD_UNREAD.']';
		break;
	case 'active':
		$current_type = '['._MD_TYPE_ADMIN.']';
		break;
	case 'pending':
		$current_type = '['._MD_TYPE_PENDING.']';
		break;
	case 'deleted':
		$current_type = '['._MD_TYPE_DELETED.']';
		break;
	default:
		$current_type = '';
		break;
	}
$GLOBALS['xoopsTpl']->assign('forum_topictype', $current_type);

$all_topics = $GLOBALS['forum_handler']->getTopicCount($forum_obj,$startdate,$type);
if ( $all_topics > $GLOBALS['xforumModuleConfig']['topics_per_page']) {
	$nav = new XoopsPageNav($all_topics, $GLOBALS['xforumModuleConfig']['topics_per_page'], $GLOBALS['start'], "start", 'forum='.$forum_obj->getVar('forum_id').'&amp;sortname='.$sortname.'&amp;sortorder='.$sortorder.'&amp;since='.$since."&amp;type=$type&amp;mode=".$mode);
	$GLOBALS['xoopsTpl']->assign('forum_pagenav', $nav->renderNav(4));
} else {
	$GLOBALS['xoopsTpl']->assign('forum_pagenav', '');
}

if(!empty($GLOBALS['xforumModuleConfig']['show_jump'])){
	$GLOBALS['xoopsTpl']->assign('forum_jumpbox', forum_make_jumpbox($forum_obj->getVar('forum_id')));
}
$GLOBALS['xoopsTpl']->assign('down',forum_displayImage($GLOBALS['xforumImage']['doubledown']));
$GLOBALS['xoopsTpl']->assign('menumode',$menumode);
$GLOBALS['xoopsTpl']->assign('menumode_other',$menumode_other);

if($GLOBALS['xforumModuleConfig']['show_permissiontable']){
	$permission_table =  $getpermission->permission_table($permission_set,$forum_obj->getVar('forum_id'), false, $GLOBALS['isadmin']);
	$GLOBALS['xoopsTpl']->assign_by_ref('permission_table', $permission_table);
	unset($permission_table);
}

if ($GLOBALS['xforumModuleConfig']['rss_enable'] == 1) {
	$GLOBALS['xoopsTpl']->assign("rss_button","<div align='right'><a href='".$forum_obj->getRSSURL()."' title='RSS feed' target='_blank'>".forum_displayImage($GLOBALS['xforumImage']['rss'], 'RSS feed')."</a></div>");
}

include XOOPS_ROOT_PATH."/footer.php";
?>