<?php

// $Id: viewtopic.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $

include 'header.php';
// To enable image auto-resize by js
	
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$post_id = !empty($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$forum_id = !empty($_GET['forum']) ? intval($_GET['forum']) : 0;
$move = isset($_GET['move'])? strtolower($_GET['move']) : '0';
$GLOBALS['start'] = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted")))? $_GET['type'] : "0";
$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : (!empty($type)?2:0);
$GLOBALS['order'] = (isset($_GET['order']) && in_array(strtoupper($_GET['order']),array("DESC","ASC")))?$_GET['order']:"ASC";

if ( !$topic_id && !$post_id ) {
	$redirect = empty($forum_id)?XOOPS_URL."/modules/xforum/index.php":XOOPS_URL.'/modules/xforum/viewforum.php?forum='.$forum_id;
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}

$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$post_handler = xoops_getmodulehandler('post', 'xforum');
if ( !empty($post_id) ) {
    $GLOBALS['xforumtopic'] = $topic_handler->getByPost($post_id);
    $xforumpost = $post_handler->get($post_id);
    $GLOBALS['total_posts'] = $xforumpost->getPostCount($type);
    if ($start==0) {
    	if ( $GLOBALS['total_posts'] > $GLOBALS['xforumModuleConfig']['posts_per_page'] )  {
			for($u=0; $u<$xforumpost->getPostNumber(); $u++){
				$i++;
				if ($i>=$GLOBALS['xforumModuleConfig']['posts_per_page']) {
					$i=0;
					$GLOBALS['start'] = $GLOBALS['start'] + $GLOBALS['xforumModuleConfig']['posts_per_page'];
				}				
			}
    	}	
    }
    $_GET['start']=$start;
} elseif(!empty($move)&&$move!=0) {
    $GLOBALS['xforumtopic'] = $topic_handler->getByMove($topic_id, ($move == "prev")?-1:1, $forum_id);
    $topic_id = $GLOBALS['xforumtopic']->getVar("topic_id");
    $GLOBALS['total_posts'] = $topic_handler->getPostCount($GLOBALS['xforumtopic'], $type);
} else {
    $GLOBALS['xforumtopic'] = $topic_handler->get($topic_id);
    $GLOBALS['total_posts'] = $topic_handler->getPostCount($GLOBALS['xforumtopic'], $type);
}
if ( !is_object($GLOBALS['xforumtopic']) || !$topic_id = $GLOBALS['xforumtopic']->getVar('topic_id') ) {
    redirect_header(XOOPS_URL.'/modules/xforum/viewforum.php?forum='.$forum_id, 2, _MD_ERRORTOPIC);
}

if ($GLOBALS['xforumModuleConfig']['htaccess']) {
	if (is_object($xforumpost)) {
		$url = $xforumpost->getURL();
		if (!strpos($url, $_SERVER['REQUEST_URI'])) {
			echo $_SERVER['REQUEST_URI'].'<br/>';
			echo $url;
			exit;
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header('Location: '.$url);
			exit(0);
		}
	} else {
		$url = $GLOBALS['xforumtopic']->getURL();
		if (!strpos($url, $_SERVER['REQUEST_URI'])) {
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header('Location: '.$url);
			exit(0);
		}
	}
}

$forum_id = $GLOBALS['xforumtopic']->getVar('forum_id');
$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$GLOBALS['viewtopic_forum'] = $GLOBALS['forum_handler']->get($forum_id);

$GLOBALS['isadmin'] = forum_isAdmin($GLOBALS['viewtopic_forum']);

if(!$isadmin && $GLOBALS['xforumtopic']->getVar('approved')<0 ){
    redirect_header($GLOBALS['viewtopic_forum']->getURL(),2,_MD_NORIGHTTOVIEW);
    exit();
}
if (!$forum_handler->getPermission($GLOBALS['viewtopic_forum'])){
    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
    exit();
}
/* Only admin has access to admin mode */
if(!$isadmin){
	$type = "";
	$mode = 0;
}
if($mode){
	$_GET['viewmode'] = "flat";
}

$perm = xoops_getmodulehandler('permission', 'xforum');
$permission_set = $perm->getPermissions('forum', $forum_id);

if (!$topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "view")){
    redirect_header($GLOBALS['viewtopic_forum']->getURL(), 2, _MD_NORIGHTTOVIEW);
    exit();
}

$karma_handler = xoops_getmodulehandler('karma', 'xforum');
$GLOBALS['user_karma'] = $karma_handler->getUserKarma();

$valid_modes = array("flat", "thread", "compact");
$viewmode_cookie = forum_getcookie("V");
if(isset($_GET['viewmode']) && in_array($_GET['viewmode'], $valid_modes)) {
	forum_setcookie("V", $_GET['viewmode'], $forumCookie['expire']);
}
$GLOBALS['viewmode'] = isset($_GET['viewmode'])? $_GET['viewmode'] : 
			(
				!empty($viewmode_cookie)?
				$viewmode_cookie:
				(
					@$valid_modes[$GLOBALS['xforumModuleConfig']['view_mode']-1]
				)
			);
$GLOBALS['viewmode'] = in_array($viewmode, $valid_modes)?$viewmode:"flat";

if ($viewmode == "thread") {
    $GLOBALS['xoopsOption']['template_main'] = 'xforum_viewtopic_thread.html';
    if(!empty($GLOBALS['xforumModuleConfig']["posts_for_thread"]) && $GLOBALS['total_posts'] > $GLOBALS['xforumModuleConfig']["posts_for_thread"]) {
	    redirect_header("viewtopic.php?topic_id=$topic_id&amp;viewmode=flat", 2, _MD_EXCEEDTHREADVIEW);
	    exit();
    }
	$postsArray = $topic_handler->getAllPosts($GLOBALS['xforumtopic'], $GLOBALS['order'], $GLOBALS['total_posts'], $GLOBALS['start'], 0, $type);
} else {
    $GLOBALS['xoopsOption']['template_main'] = 'xforum_viewtopic_flat.html';
    $postsArray = $topic_handler->getAllPosts($GLOBALS['xforumtopic'], $GLOBALS['order'], $GLOBALS['xforumModuleConfig']['posts_per_page'], $GLOBALS['start'], $post_id, $type);
}

$GLOBALS['xforumtopic']->incrementCounter();
forum_setRead("topic", $topic_id, $GLOBALS['xforumtopic']->getVar("topic_last_post_id"));
$xoops_pagetitle = $GLOBALS['xforumtopic']->getVar('topic_title') . ' [' . $GLOBALS['xforumModule']->getVar('name') ." - ". $GLOBALS['viewtopic_forum']->getVar('forum_name') . "]";
$GLOBALS['xoopsOption']['xoops_pagetitle']= $xoops_pagetitle;

include XOOPS_ROOT_PATH."/header.php";

if(!empty($GLOBALS['xforumModuleConfig']['rss_enable'])){
	$GLOBALS['xoTheme']->addLink('alternate', $GLOBALS['viewtopic_forum']->getRSSURL(), array('type'=>"application/rss+xml", 'title' => $GLOBALS['xforumModule']->getVar('name')));
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

if($GLOBALS['xoopsTpl']->xoops_canUpdateFromFile() && is_dir(XOOPS_THEME_PATH."/".$GLOBALS['xoopsConfig']['theme_set']."/templates/".$GLOBALS['xforumModule']->getVar("dirname"))){
	$GLOBALS['xoopsTpl']->assign('forum_template_path', XOOPS_THEME_PATH."/".$GLOBALS['xoopsConfig']['theme_set']."/templates/".$GLOBALS['xforumModule']->getVar("dirname"));
}else{
	$GLOBALS['xoopsTpl']->assign('forum_template_path', XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/templates");
}
$GLOBALS['xoopsTpl']->assign('xoops_pagetitle', $xoops_pagetitle);
if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = xoops_getmodulehandler('online', 'xforum');
	$online_handler->init($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']);
    $GLOBALS['xoopsTpl']->assign('online', $online_handler->show_online());
}

if($GLOBALS['viewtopic_forum']->getVar('parent_forum') > 0){
    $q = "select forum_name from ".$GLOBALS['xoopsDB']->prefix('xf_forums')." WHERE forum_id=".$GLOBALS['viewtopic_forum']->getVar('parent_forum');
    $row = $GLOBALS['xoopsDB']->fetchArray($GLOBALS['xoopsDB']->query($q));
    $GLOBALS['xoopsTpl']->assign(array('parent_forum' => $GLOBALS['viewtopic_forum']->getVar('parent_forum'), 'parent_name' => $GLOBALS['myts']->htmlSpecialChars($row['forum_name'])));
}

$topic_prefix = "";
if($GLOBALS['xforumtopic']->getVar('topic_subject')
 && !empty($GLOBALS['xforumModuleConfig']['subject_prefix'])
 && $GLOBALS['viewtopic_forum']->getVar("allow_subject_prefix")
){
    $subjectpres = explode(',', $GLOBALS['xforumModuleConfig']['subject_prefix']);
	$topic_prefix = $subjectpres[intval($GLOBALS['xforumtopic']->getVar('topic_subject'))]." ";
}
$GLOBALS['xoopsTpl']->assign(array(
	'topic_title' 		=> '<a href="'.$GLOBALS['xforumtopic']->getURL().'">'. $topic_prefix.$GLOBALS['xforumtopic']->getVar('topic_title').'</a>', 
	'forum_name' 		=> $GLOBALS['viewtopic_forum']->getVar('forum_name'), 
	'lang_nexttopic' 	=> _MD_NEXTTOPIC, 
	'lang_prevtopic' 	=> _MD_PREVTOPIC
	));

$category_handler = xoops_getmodulehandler("category");
$category_obj = $category_handler->get($GLOBALS['viewtopic_forum']->getVar("cat_id"), array("cat_title"));
$GLOBALS['xoopsTpl']->assign('category', array("id" => $GLOBALS['viewtopic_forum']->getVar("cat_id"), "title" => $category_obj->getVar('cat_title')));

$GLOBALS['xoopsTpl']->assign('folder_topic', forum_displayImage($GLOBALS['xforumImage']['folder_topic']));

$GLOBALS['xoopsTpl']->assign('topic_id', $topic_id);
$GLOBALS['xoopsTpl']->assign('forum_id', $forum_id);

if ($order == 'DESC') {
	$order_current = 'DESC';
    $GLOBALS['xoopsTpl']->assign(array('order_current' => 'DESC'));
} else {
	$order_current = 'ASC';
    $GLOBALS['xoopsTpl']->assign(array('order_current' => 'ASC'));
}

$t_new = forum_displayImage($GLOBALS['xforumImage']['t_new'],_MD_POSTNEW);
$t_reply = forum_displayImage($GLOBALS['xforumImage']['t_reply'],_MD_REPLY);

if ($topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "post")){
    $GLOBALS['xoopsTpl']->assign('forum_post_or_register', "<a href=\"".XOOPS_URL."/modules/xforum/newtopic.php?forum=".$forum_id."\">".$t_new."</a>");
} elseif ( !empty($GLOBALS['xforumModuleConfig']["show_reg"]) ) {
    if($GLOBALS['xforumtopic']->getVar('topic_status')){
    	$GLOBALS['xoopsTpl']->assign('forum_post_or_register', _MD_TOPICLOCKED);
    }elseif ( !is_object($GLOBALS['xoopsUser'])) {
    	$GLOBALS['xoopsTpl']->assign('forum_post_or_register', '<a href="'.XOOPS_URL.'/user.php?xoops_redirect='.htmlspecialchars($xoopsRequestUri).'">'._MD_REGTOPOST.'</a>');
	}
} else {
    $GLOBALS['xoopsTpl']->assign('forum_post_or_register', '');
}
if ($topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "reply")){
    $GLOBALS['xoopsTpl']->assign('forum_reply', "<a href=\"".XOOPS_URL."/modules/xforum/reply.php?forum=".$forum_id."&amp;topic_id=".$topic_id."\">".$t_reply."</a>");
}

$poster_array = array();
$require_reply = false;
foreach ($postsArray as $eachpost) {
	if($eachpost->getVar('uid')>0) $poster_array[$eachpost->getVar('uid')] = 1;
	if($eachpost->getVar('require_reply')>0) $require_reply = true;
}
$userid_array=array();
$GLOBALS['online'] = array();
if(count($poster_array)>0){
	$member_handler = xoops_gethandler('member');
	$userid_array = array_keys($poster_array);
	$user_criteria = "(".implode(",",$userid_array).")";
	$users = $member_handler->getUsers( new Criteria('uid', $user_criteria, 'IN'), true);
}else{
	$user_criteria = '';
	$users = null;
}

if ($GLOBALS['xforumModuleConfig']['wol_enabled'] && $online_handler){
	$GLOBALS['online'] = $online_handler->checkStatus(array_keys($poster_array));
}

if($GLOBALS['xforumModuleConfig']['groupbar_enabled']){
	$groups_disp = array();
	$groups = $member_handler->getGroups();
	$count = count($groups);
	for ($i = 0; $i < $count; $i++) {
		$groups_disp[$groups[$i]->getVar('groupid')] = $groups[$i]->getVar('name');
	}
	unset($groups);
}

$GLOBALS['viewtopic_users'] = array();
if(count($userid_array)>0){
	$user_handler = xoops_getmodulehandler('user', 'xforum');
	$user_handler->setUsers($users);
	$user_handler->setGroups($groups_disp);
	$user_handler->setStatus($online);
	foreach($userid_array as $userid){
		$GLOBALS['viewtopic_users'][$userid] = $user_handler->get($userid);
	    if (!$GLOBALS['viewtopic_forum']->getVar('allow_sig')) {
	        $GLOBALS['viewtopic_users'][$userid]["signature"] = "";
	    }
	}
}
unset($users);
unset($groups_disp);

if($GLOBALS['xforumModuleConfig']['allow_require_reply'] && $require_reply){
	if(!empty($GLOBALS['xforumModuleConfig']['cache_enabled'])){
		$GLOBALS['viewtopic_posters'] = forum_getsession("t".$topic_id, true);
		if(!is_array($GLOBALS['viewtopic_posters']) || count($GLOBALS['viewtopic_posters'])==0){
			$GLOBALS['viewtopic_posters'] = $topic_handler->getAllPosters($GLOBALS['xforumtopic']);
			forum_setsession("t".$topic_id, $GLOBALS['viewtopic_posters']);
		}
	}else{
		$GLOBALS['viewtopic_posters'] = $topic_handler->getAllPosters($GLOBALS['xforumtopic']);
	}
}else{
	$GLOBALS['viewtopic_posters'] =array();
}
if ($viewmode == "thread") {
	if(!empty($post_id)){
		$post_handler = xoops_getmodulehandler('post', 'xforum');
		$currentPost = $post_handler -> get($post_id);
		
		if(!$isadmin && $currentPost->getVar('approved')<0 ){
		    redirect_header("viewtopic.php?topic_id=".$topic_id, 2, _MD_NORIGHTTOVIEW);
		    exit();
		}

		$topPost = $topic_handler->getTopPost($topic_id);
	    $top_pid = $topPost->getVar('post_id');
	    unset($topPost);
	}else{
		$currentPost = $topic_handler->getTopPost($topic_id);
	    $top_pid = $currentPost->getVar('post_id');
	}

    $GLOBALS['xoopsTpl']->append('topic_posts', $currentPost->showPost($isadmin));

    $postArray = $topic_handler->getPostTree($postsArray);
    if ( count($postArray) > 0 ) {
        foreach ($postArray as $treeItem) {
            $topic_handler->showTreeItem($GLOBALS['xforumtopic'], $treeItem);
            if($treeItem['post_id'] == $post_id) $treeItem['subject'] = '<strong>'.$treeItem['subject'].'</strong>';
            $GLOBALS['xoopsTpl']->append("topic_trees", array("post_id" => $treeItem['post_id'], "post_time" => $treeItem['post_time'], "post_image" => $treeItem['icon'], "post_title" => $treeItem['subject'], "post_prefix" => $treeItem['prefix'], "poster" => $treeItem['poster']));
        }
        unset($postArray);
    }
}
else {

    foreach ($postsArray as $eachpost) {
    	$GLOBALS['xoopsTpl']->append('topic_posts', $eachpost->showPost($isadmin));
    }

    if ( $GLOBALS['total_posts'] > $GLOBALS['xforumModuleConfig']['posts_per_page'] ) {
        $nav = new XoopsPageNav($total_posts, $GLOBALS['xforumModuleConfig']['posts_per_page'], $GLOBALS['start'], "start", 'topic_id='.$topic_id.'&amp;viewmode='.$viewmode.'&amp;order='.$order.'&amp;type='.$type."&amp;mode=".$mode);
        $GLOBALS['xoopsTpl']->assign('forum_page_nav', $nav->renderNav(4));
    } else {
        $GLOBALS['xoopsTpl']->assign('forum_page_nav', '');
    }
}
unset($postsArray);

$GLOBALS['xoopsTpl']->assign('topic_print_link', "".XOOPS_URL."/modules/xforum/print.php?form=1&amp;topic_id=$topic_id&amp;forum=".$forum_id."&amp;order=$order&amp;start=$start");

$admin_actions = array();

$ad_merge = "";
$ad_move = "";
$ad_delete = "";
$ad_lock = "";
$ad_unlock = "";
$ad_sticky = "";
$ad_unsticky = "";
$ad_digest = "";
$ad_undigest = "";

$admin_actions['merge'] = array(
	"link" => $GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=merge&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
	"name" => _MD_MERGETOPIC,
	"image" => $ad_merge);
$admin_actions['move'] = array(
	"link" => $GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=move&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
	"name" => _MD_MOVETOPIC,
	"image" => $ad_move);
$admin_actions['delete'] = array(
	"link" => $GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=delete&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
	"name" => _MD_DELETETOPIC,
	"image" => $ad_delete);
if ( !$GLOBALS['xforumtopic']->getVar('topic_status') ){
    $admin_actions['lock'] = array(
    	"link"=>$GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=lock&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
    	"image"=>$ad_lock,
    	"name"=>_MD_LOCKTOPIC);
}else{
    $admin_actions['unlock'] = array(
    	"link"=>$GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=unlock&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
    	"image"=>$ad_unlock,
    	"name"=>_MD_UNLOCKTOPIC);
}
if ( !$GLOBALS['xforumtopic']->getVar('topic_sticky') ){
    $admin_actions['sticky'] = array(
    	"link"=>$GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=sticky&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
    	"image"=>$ad_sticky,
    	"name"=>_MD_STICKYTOPIC);
}else{
    $admin_actions['unsticky'] = array(
    	"link"=>$GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=unsticky&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
    	"image"=>$ad_unsticky,
    	"name"=>_MD_UNSTICKYTOPIC);
}
if ( !$GLOBALS['xforumtopic']->getVar('topic_digest') ){
    $admin_actions['digest'] = array(
    	"link"=>$GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=digest&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
    	"image"=>$ad_digest,
    	"name"=>_MD_DIGESTTOPIC);
}else{
    $admin_actions['undigest'] = array(
    	"link"=>$GLOBALS['xforumUrl']['root'].'/topicmanager.php?mode=undigest&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
    	"image"=>$ad_undigest,
    	"name"=>_MD_UNDIGESTTOPIC);
}
$GLOBALS['xoopsTpl']->assign_by_ref('admin_actions', $admin_actions);

$GLOBALS['xoopsTpl']->assign('viewer_level', ($isadmin)?2:(is_object($GLOBALS['xoopsUser'])?1:0) );

if($GLOBALS['xforumModuleConfig']['show_permissiontable']){
	$permission_table = $perm->permission_table($permission_set,$GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), $GLOBALS['isadmin']);
	$GLOBALS['xoopsTpl']->assign_by_ref('permission_table', $permission_table);
	unset($permission_table);
}

///////////////////////////////
// show Poll
if ( $GLOBALS['viewtopic_forum']->getVar('allow_polls') ):
if(
	(
		$GLOBALS['xforumtopic']->getVar('topic_haspoll')
		&& $topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "vote")
	)
	|| $topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "addpoll")
){
	@include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspoll.php";
	@include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspolloption.php";
	@include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspolllog.php";
	@include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspollrenderer.php";
}

if ( $GLOBALS['xforumtopic']->getVar('topic_haspoll') 
	&& $topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "vote")
){

	$GLOBALS['xoopsTpl']->assign('topic_poll', 1);
	$poll = new XoopsPoll($GLOBALS['xforumtopic']->getVar('poll_id'));
	$renderer = new XoopsPollRenderer($poll);
	$uid = is_object($GLOBALS['xoopsUser'])?$GLOBALS['xoopsUser']->getVar("uid"):0;
	if ( XoopsPollLog::hasVoted($GLOBALS['xforumtopic']->getVar('poll_id'), $_SERVER['REMOTE_ADDR'], $uid) ) {
		$renderer->assignResults($GLOBALS['xoopsTpl']);
		//pollresults($GLOBALS['xforumtopic']->getVar('poll_id'));
		$GLOBALS['xoopsTpl']->assign('topic_pollresult', 1);
		setcookie("xf_polls[".$GLOBALS['xforumtopic']->getVar("poll_id")."]", 1);
	} else {
	    $renderer->assignForm($GLOBALS['xoopsTpl']);
	    $GLOBALS['xoopsTpl']->assign('lang_vote' , _PL_VOTE);
	    $GLOBALS['xoopsTpl']->assign('lang_results' , _PL_RESULTS);
		//pollview($GLOBALS['xforumtopic']->getVar('poll_id'));
		setcookie("xf_polls[".$GLOBALS['xforumtopic']->getVar("poll_id")."]", 1);
	}
}
if ($topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "addpoll")
){
	if(!$GLOBALS['xforumtopic']->getVar('topic_haspoll')){	
		if( is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->getVar("uid")==$GLOBALS['xforumtopic']->getVar("topic_poster") ){
			$t_poll = forum_displayImage($GLOBALS['xforumImage']['t_poll'],_MD_ADDPOLL);
			$GLOBALS['xoopsTpl']->assign('forum_addpoll', "<a href=\"".XOOPS_URL."/modules/xforum/polls.php?op=add&amp;forum=".$forum_id."&amp;topic_id=".$topic_id."\">".$t_poll."</a>&nbsp;");
		}
	}elseif($isadmin
		|| (is_object($poll) && is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->getVar("uid")==$poll->getVar("user_id") )
	){
		$poll_edit = "";
	    $poll_delete = "";
	    $poll_restart = "";

		$adminpoll_actions = array();
	    $adminpoll_actions['editpoll'] = array(
	    	"link" => $GLOBALS['xforumUrl']['root'].'/polls.php?op=edit&amp;poll_id='.$GLOBALS['xforumtopic']->getVar('poll_id').'&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
	    	"image" => $poll_edit,
	    	"name" => _MD_EDITPOLL);
	    $adminpoll_actions['deletepoll'] = array(
	    	"link" => $GLOBALS['xforumUrl']['root'].'/polls.php?op=delete&amp;poll_id='.$GLOBALS['xforumtopic']->getVar('poll_id').'&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
	    	"image" => $poll_delete,
	    	"name" => _MD_DELETEPOLL);
	    $adminpoll_actions['restartpoll'] = array(
	    	"link" => $GLOBALS['xforumUrl']['root'].'/polls.php?op=restart&amp;poll_id='.$GLOBALS['xforumtopic']->getVar('poll_id').'&amp;topic_id='.$topic_id.'&amp;forum='.$forum_id,
	    	"image" => $poll_restart,
	    	"name" => _MD_RESTARTPOLL);

	    $GLOBALS['xoopsTpl']->assign_by_ref('adminpoll_actions', $adminpoll_actions);	
	    unset($adminpoll_actions);
	}
}
if(isset($poll)) unset($poll);
endif;

$GLOBALS['xoopsTpl']->assign('p_up',forum_displayImage($GLOBALS['xforumImage']['p_up'],_MD_TOP));
$GLOBALS['xoopsTpl']->assign('rating_enable', $GLOBALS['xforumModuleConfig']['rating_enabled']);
$GLOBALS['xoopsTpl']->assign('groupbar_enable', $GLOBALS['xforumModuleConfig']['groupbar_enabled']);
$GLOBALS['xoopsTpl']->assign('anonymous_prefix', $GLOBALS['xforumModuleConfig']['anonymous_prefix']);

$GLOBALS['xoopsTpl']->assign('threaded',forum_displayImage($GLOBALS['xforumImage']['threaded']));
$GLOBALS['xoopsTpl']->assign('flat',forum_displayImage($GLOBALS['xforumImage']['flat']));
$GLOBALS['xoopsTpl']->assign('left',forum_displayImage($GLOBALS['xforumImage']['left']));
$GLOBALS['xoopsTpl']->assign('right',forum_displayImage($GLOBALS['xforumImage']['right']));
$GLOBALS['xoopsTpl']->assign('down',forum_displayImage($GLOBALS['xforumImage']['doubledown']));
$GLOBALS['xoopsTpl']->assign('down2',forum_displayImage($GLOBALS['xforumImage']['down']));
$GLOBALS['xoopsTpl']->assign('up',forum_displayImage($GLOBALS['xforumImage']['up']));
$GLOBALS['xoopsTpl']->assign('printer',forum_displayImage($GLOBALS['xforumImage']['printer']));
$GLOBALS['xoopsTpl']->assign('personal',forum_displayImage($GLOBALS['xforumImage']['personal']));
$GLOBALS['xoopsTpl']->assign('post_content',forum_displayImage($GLOBALS['xforumImage']['post_content']));

if(!empty($GLOBALS['xforumModuleConfig']['rating_enabled'])){
	$GLOBALS['xoopsTpl']->assign('votes',$GLOBALS['xforumtopic']->getVar('votes'));
	$rating = number_format($GLOBALS['xforumtopic']->getVar('rating')/2, 0);
	if ( $rating < 1 ) {
		$rating_img = forum_displayImage($GLOBALS['xforumImage']['blank']);
	}else{
		$rating_img  = forum_displayImage($GLOBALS['xforumImage']['rate'.$rating]);
	}
	$GLOBALS['xoopsTpl']->assign('rating_img',$rating_img);
	$GLOBALS['xoopsTpl']->assign('rate1',forum_displayImage($GLOBALS['xforumImage']['rate1'],_MD_RATE1));
	$GLOBALS['xoopsTpl']->assign('rate2',forum_displayImage($GLOBALS['xforumImage']['rate2'],_MD_RATE2));
	$GLOBALS['xoopsTpl']->assign('rate3',forum_displayImage($GLOBALS['xforumImage']['rate3'],_MD_RATE3));
	$GLOBALS['xoopsTpl']->assign('rate4',forum_displayImage($GLOBALS['xforumImage']['rate4'],_MD_RATE4));
	$GLOBALS['xoopsTpl']->assign('rate5',forum_displayImage($GLOBALS['xforumImage']['rate5'],_MD_RATE5));
}

// create jump box
if(!empty($GLOBALS['xforumModuleConfig']['show_jump'])){
	$GLOBALS['xoopsTpl']->assign('forum_jumpbox', forum_make_jumpbox($forum_id));
}
$GLOBALS['xoopsTpl']->assign(array('lang_forum_index' => sprintf(_MD_XFORUMINDEX,htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)), 'lang_from' => _MD_FROM, 'lang_joined' => _MD_JOINED, 'lang_posts' => _MD_POSTS, 'lang_poster' => _MD_POSTER, 'lang_thread' => _MD_THREAD, 'lang_edit' => _EDIT, 'lang_delete' => _DELETE, 'lang_reply' => _REPLY, 'lang_postedon' => _MD_POSTEDON,'lang_groups' => _MD_GROUPS));

$viewmode_options = array();
if($viewmode=="thread"){
	$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=flat&amp;topic_id=".$topic_id."&amp;forum=".$forum_id, "title"=>_FLAT);
	$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=compact&amp;topic_id=".$topic_id."&amp;forum=".$forum_id,	"title"=>_MD_COMPACT);
}elseif($viewmode=="compact"){
	$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=thread&amp;topic_id=".$topic_id."&amp;forum=".$forum_id,"title"=>_THREADED);
	$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=flat&amp;order=".$order_current."&amp;topic_id=".$topic_id."&amp;forum=".$forum_id,	"title"=>_FLAT);
	if ($order == 'DESC') {
		$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=compact&amp;order=ASC&amp;topic_id=".$topic_id."&amp;forum=".$forum_id,"title"=>_OLDESTFIRST);
	} else {
		$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=compact&amp;order=DESC&amp;topic_id=".$topic_id."&amp;forum=".$forum_id,"title"=>_NEWESTFIRST);
	}
}else{
	$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=thread&amp;topic_id=".$topic_id."&amp;forum=".$forum_id,"title"=>_THREADED);
	$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=compact&amp;order=".$order_current."&amp;topic_id=".$topic_id."&amp;forum=".$forum_id, "title"=>_MD_COMPACT);
	if ($order == 'DESC') {
		$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=flat&amp;order=ASC&amp;type=$type&amp;topic_id=".$topic_id."&amp;forum=".$forum_id, "title"=>_OLDESTFIRST);
	} else {
		$viewmode_options[]= array("link"=>$GLOBALS['xforumUrl']['root']."/viewtopic.php?viewmode=flat&amp;order=DESC&amp;type=$type&amp;topic_id=".$topic_id."&amp;forum=".$forum_id, "title"=>_NEWESTFIRST);
	}
}
switch($type){
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
$GLOBALS['xoopsTpl']->assign('topictype', $current_type);

$GLOBALS['xoopsTpl']->assign('mode', $mode);
$GLOBALS['xoopsTpl']->assign('type', $type);
$GLOBALS['xoopsTpl']->assign('viewmode_compact', ($viewmode=="compact")?1:0);
$GLOBALS['xoopsTpl']->assign_by_ref('viewmode_options', $viewmode_options);
unset($viewmode_options);
$GLOBALS['xoopsTpl']->assign('menumode',$menumode);
$GLOBALS['xoopsTpl']->assign('menumode_other',$menumode_other);

if( !empty($GLOBALS['xforumModuleConfig']['quickreply_enabled'])
	&& $topic_handler->getPermission($GLOBALS['viewtopic_forum'], $GLOBALS['xforumtopic']->getVar('topic_status'), "reply")
){
	include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";

	$forum_form = new XoopsThemeForm(_MD_POSTREPLY, 'quick_reply', XOOPS_URL."/modules/xforum/post.php", 'post', true);

	if(!is_object($GLOBALS['xoopsUser'])){
		$config_handler = xoops_gethandler('config');
		$user_tray = new XoopsFormElementTray(_MD_ACCOUNT);
		$user_tray->addElement(new XoopsFormText(_MD_NAME, "uname", 26, 255));
		$user_tray->addElement(new XoopsFormPassword(_MD_PASSWORD, "pass", 10, 32));
		$login_checkbox = new XoopsFormCheckBox('', 'login', 1);
		$login_checkbox->addOption(1, _MD_LOGIN);
		$user_tray->addElement($login_checkbox);
		$forum_form->addElement($user_tray, '');
	}

	$quickform = "textarea";
	$editor_configs = array();
	$editor_configs["caption"] = _MD_MESSAGEC;
	$editor_configs["name"] ="message";
	$editor_configs["rows"] = 10;
	$editor_configs["cols"] = 60;
	if(!$editor_handler = xoops_gethandler("editor", true)){
		if(!@include_once XOOPS_ROOT_PATH."/class/xoopseditor/xoopseditor.php") {
			require_once XOOPS_ROOT_PATH."/Frameworks/xoops22/class/xoopseditor/xoopseditor.php";
		}
		$editor_handler = new XoopsEditorHandler();
	}
	$editor_object =  $editor_handler->get($quickform, $editor_configs,"",1);
	$forum_form->addElement($editor_object, true);

	$forum_form->addElement(new XoopsFormHidden('dohtml', 0));
	$forum_form->addElement(new XoopsFormHidden('dosmiley', 1));
	$forum_form->addElement(new XoopsFormHidden('doxcode', 1));
	$forum_form->addElement(new XoopsFormHidden('dobr', 1));
	$forum_form->addElement(new XoopsFormHidden('attachsig', 1));

	$forum_form->addElement(new XoopsFormHidden('isreply', 1));
	
	$forum_form->addElement(new XoopsFormHidden('subject', _MD_RE.': '.$GLOBALS['xforumtopic']->getVar('topic_title', 'e')));
	$forum_form->addElement(new XoopsFormHidden('pid', empty($post_id)?$topic_handler->getTopPostId($topic_id):$post_id));
	$forum_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
	$forum_form->addElement(new XoopsFormHidden('forum', $forum_id));
	$forum_form->addElement(new XoopsFormHidden('viewmode', $GLOBALS['viewmode']));
	$forum_form->addElement(new XoopsFormHidden('order', $GLOBALS['order']));
	$forum_form->addElement(new XoopsFormHidden('start', $GLOBALS['start']));

	// backward compatible
	if(!class_exists("XoopsSecurity")){
		$post_valid = 1;
		$_SESSION['submit_token'] = $post_valid;
		$forum_form->addElement(new XoopsFormHidden('post_valid', $post_valid));
	}

	$forum_form->addElement(new XoopsFormHidden('notify', -1));
	$forum_form->addElement(new XoopsFormHidden('contents_submit', 1));
	$submit_button = new XoopsFormButton('', 'quick_submit', _SUBMIT, "submit");
	$submit_button->setExtra('onclick="if(document.forms.quick_reply.message.value == \'RE\' || document.forms.quick_reply.message.value == \'\'){ alert(\''._MD_QUICKREPLY_EMPTY.'\'); return false;}else{ return true;}"');
	$button_tray = new XoopsFormElementTray('');
	$button_tray->addElement($submit_button);
	$forum_form->addElement($button_tray);

	$toggles = forum_getcookie('G', true);
	$display = (in_array('qr', $toggles)) ? 'none;' : 'block;';
    $GLOBALS['xoopsTpl']->assign('quickreply', array( 'show' => 1, 'display'=>$display, 'icon'=>forum_displayImage($GLOBALS['xforumImage']['t_qr']), 'form' => $forum_form->render()));
	unset($forum_form);
}else{
	$GLOBALS['xoopsTpl']->assign('quickreply', array( 'show' => 0));
}

include XOOPS_ROOT_PATH.'/footer.php';
?>