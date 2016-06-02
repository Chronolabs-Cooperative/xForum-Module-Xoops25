<?php

// $Id: viewpost.php,v 4.04 2008/06/05 16:23:59 wishcraft Exp $

include 'header.php';
// To enable image auto-resize by js
$xoops_module_header .= '<script src="'.XOOPS_URL.'/Frameworks/textsanitizer/xoops.js" type="text/javascript"></script>';

$GLOBALS['start'] = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$forum_id = !empty($_GET['forum']) ? intval($_GET['forum']) : 0;
$GLOBALS['order'] = isset($_GET['order'])?$_GET['order']:"DESC";

$uid = !empty($_GET['uid']) ? intval($_GET['uid']) : 0;
$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "new")))? $_GET['type'] : "active";
$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : 0;
$mode = (!empty($type) && in_array($type, array("active", "pending", "deleted")) )?2:$mode;

if ($GLOBALS['xforumModuleConfig']['htaccess']) {
	$url = XOOPS_URL.'/'.$GLOBALS['xforumModuleConfig']['baseurl']."/viewpost,$forum_id,$start,$order,$uid,$mode,$type".$GLOBALS['xforumModuleConfig']['endofurl'];
	if (strpos($url, $_SERVER['REQUEST_URI'])==0) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$post_handler = xoops_getmodulehandler('post', 'xforum');

$GLOBALS['isadmin'] = forum_isAdmin($forum_id);
/* Only admin has access to admin mode */
if(!$isadmin){
	$type = in_array($type, array("active", "pending", "deleted"))?"":$type;
	$mode = 0;
}
if($mode){
	$_GET['viewmode'] = "flat";
}

if(empty($forum_id)){
	$xforums = $GLOBALS['forum_handler']->getForums(0, "view");
	$access_forums = array_keys($xforums);
}else{
	$forum_obj = $GLOBALS['forum_handler']->get($forum_id);
	$xforums[$forum_id] = $forum_obj;
	$access_forums = array($forum_id);
}

$post_perpage = $GLOBALS['xforumModuleConfig']['posts_per_page'];

$criteria_count = new CriteriaCompo(new Criteria("forum_id", "(".implode(",",$access_forums).")", "IN"));
$criteria_post = new CriteriaCompo(new Criteria("p.forum_id", "(".implode(",",$access_forums).")", "IN"));
$criteria_post->setSort("p.post_time");
$criteria_post->setOrder($order);

if(!empty($uid)){
	$criteria_count->add(new Criteria("uid", $uid));
	$criteria_post->add(new Criteria("p.uid", $uid));
}

$join = null;
switch($type){
	case "pending":
		$criteria_type_count = new Criteria("approved", 0);
		$criteria_type_post = new Criteria("p.approved", 0);
		break;
	case "deleted":
		$criteria_type_count = new Criteria("approved", -1);
		$criteria_type_post = new Criteria("p.approved", -1);
		break;
	case "new":
		$criteria_type_count = new CriteriaCompo(new Criteria("post_time", intval($last_visit), ">"));
		$criteria_type_post = new CriteriaCompo(new Criteria("p.post_time", intval($last_visit), ">"));
		$criteria_type_count->add(new Criteria("approved", 1));
		$criteria_type_post->add(new Criteria("p.approved", 1));
		// following is for "unread" -- not finished
		/*
        if(empty($GLOBALS['xforumModuleConfig']["read_mode"])){
        }elseif($GLOBALS['xforumModuleConfig']["read_mode"] ==2){
    		$join = ' LEFT JOIN ' . $this->db->prefix('xf_reads_topic') . ' r ON r.read_item = p.topic_id';
			$criteria_type_post = new CriteriaCompo(new Criteria("p.post_id", "r.post_id", ">"));
			$criteria_type_post->add(new Criteria("r.read_id", "NULL", "IS"), "OR");
			$criteria_type_post->add(new Criteria("p.approved", 1));
			$criteria_type_count = $criteria_type_post;
        }elseif($GLOBALS['xforumModuleConfig']["read_mode"] == 1){
			$criteria_type_count = new CriteriaCompo(new Criteria("post_time", intval($last_visit), ">"));
			$criteria_type_post = new CriteriaCompo(new Criteria("p.post_time", intval($last_visit), ">"));
			$criteria_type_count->add(new Criteria("approved", 1));
			$criteria_type_post->add(new Criteria("p.approved", 1));
        }
        */
		break;
	default:
		$criteria_type_count = new Criteria("approved", 1);
		$criteria_type_post = new Criteria("p.approved", 1);
		break;
}
$criteria_count->add($criteria_type_count);
$criteria_post->add($criteria_type_post);

$karma_handler = xoops_getmodulehandler('karma', 'xforum');
$GLOBALS['user_karma'] = $karma_handler->getUserKarma();

$valid_modes = array("flat", "compact");
$viewmode_cookie = forum_getcookie("V");
if(isset($_GET['viewmode'])&&$_GET['viewmode']=="compact") forum_setcookie("V", "compact", $forumCookie['expire']);
$GLOBALS['viewmode'] = isset($_GET['viewmode'])?
			$_GET['viewmode']:
			(
				!empty($viewmode_cookie)?
				$viewmode_cookie:
				(
				/*
					is_object($GLOBALS['xoopsUser'])?
					$GLOBALS['xoopsUser']->getVar('umode'):
				*/
					@$valid_modes[$GLOBALS['xforumModuleConfig']['view_mode']-1]
				)
			);
$GLOBALS['viewmode'] = in_array($viewmode, $valid_modes)?$viewmode:"flat";

$postCount = $post_handler->getPostCount($criteria_count);
$posts = $post_handler->getPostsByLimit($criteria_post, $post_perpage, $GLOBALS['start']/*, $join*/);

$poster_array = array();
if(count($posts)>0) foreach (array_keys($posts) as $id) {
	$poster_array[$posts[$id]->getVar('uid')] = 1;
}

$xoops_pagetitle = $GLOBALS['xforumModule']->getVar('name'). ' - ' ._MD_VIEWALLPOSTS;
$GLOBALS['xoopsOption']['xoops_pagetitle']= $xoops_pagetitle;
$GLOBALS['xoopsOption']['xoops_module_header']= $xoops_module_header;
$GLOBALS['xoopsOption']['template_main'] = 'xforum_viewpost.html';
include XOOPS_ROOT_PATH."/header.php";
if($GLOBALS['xoopsTpl']->xoops_canUpdateFromFile() && is_dir(XOOPS_THEME_PATH."/".$GLOBALS['xoopsConfig']['theme_set']."/templates/".$GLOBALS['xforumModule']->getVar("dirname"))){
	$GLOBALS['xoopsTpl']->assign('forum_template_path', XOOPS_THEME_PATH."/".$GLOBALS['xoopsConfig']['theme_set']."/templates/".$GLOBALS['xforumModule']->getVar("dirname"));
}else{
	$GLOBALS['xoopsTpl']->assign('forum_template_path', XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/templates");
}

if(!empty($forum_id)){
	if (!$forum_handler->getPermission($forum_obj, "view")){
	    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
	    exit();
	}
	if($forum_obj->getVar('parent_forum')){
		$parent_forum_obj = $GLOBALS['forum_handler']->get($forum_obj->getVar('parent_forum'), array("forum_name"));
		$parentforum = array("id"=>$forum_obj->getVar('parent_forum'), "name"=>$parent_forum_obj->getVar("forum_name"));
		unset($parent_forum_obj);
		$GLOBALS['xoopsTpl']->assign_by_ref("parentforum", $parentforum);
	}
	$GLOBALS['xoopsTpl']->assign('forum_name', $forum_obj->getVar('forum_name'));
	$GLOBALS['xoopsTpl']->assign('forum_moderators', $forum_obj->disp_forumModerators());

	$xoops_pagetitle = $forum_obj->getVar('forum_name'). ' - ' ._MD_VIEWALLPOSTS. ' [' . $GLOBALS['xforumModule']->getVar('name'). ']';
	$GLOBALS['xoopsTpl']->assign("forum_id", $forum_obj->getVar('forum_id'));

	if(!empty($GLOBALS['xforumModuleConfig']['rss_enable'])){
		$xoops_module_header .= '<link rel="alternate" type="application/xml+rss" title="'.$GLOBALS['xforumModule']->getVar('name').'-'.$forum_obj->getVar('forum_name').'" href="'.XOOPS_URL.'/modules/'.$GLOBALS['xforumModule']->getVar('dirname').'/rss.php?f='.$forum_id.'" />';
	}
}elseif(!empty($GLOBALS['xforumModuleConfig']['rss_enable'])){
	$xoops_module_header .= '<link rel="alternate" type="application/xml+rss" title="'.$GLOBALS['xforumModule']->getVar('name').'" href="'.XOOPS_URL.'/modules/'.$GLOBALS['xforumModule']->getVar('dirname').'/rss.php" />';
}
$GLOBALS['xoopsTpl']->assign('xoops_module_header', $xoops_module_header);
$GLOBALS['xoopsTpl']->assign('xoops_pagetitle', $xoops_pagetitle);

$userid_array=array();
if(count($poster_array)>0){
	$member_handler = xoops_gethandler('member');
	$userid_array = array_keys($poster_array);
	$user_criteria = "(".implode(",",$userid_array).")";
	$users = $member_handler->getUsers( new Criteria('uid', $user_criteria, 'IN'), true);
}else{
	$user_criteria = '';
	$users = null;
}

if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$GLOBALS['online'] = array();
	if(!empty($user_criteria)){
		$online_handler = xoops_getmodulehandler('online', 'xforum');
		$online_handler->init($forum_id);
		$online_full = $online_handler->getAll(new Criteria('online_uid', $user_criteria, 'IN'));
		if(is_array($online_full)&&count($online_full)>0){
			foreach ($online_full as $thisonline) {
			    if ($thisonline['online_uid'] > 0) $GLOBALS['online'][$thisonline['online_uid']] = 1;
			}
		}
	}
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
	}
}
unset($users);
unset($groups_disp);

$pn =0;
$topic_handler = xoops_getmodulehandler('topic', 'xforum');
static $suspension = array();
foreach(array_keys($posts) as $id){
	$pn++;

	$post = $posts[$id];
    $post_title = $post->getVar('subject');

    if ( $posticon = $post->getVar('icon') ){
        $post_image = '<a name="' . $post->getVar('post_id') . '"><img src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($posticon) . '" alt="" /></a>';
    }else{
        $post_image = '<a name="' . $post->getVar('post_id') . '"><img src="' . XOOPS_URL . '/images/icons/no_posticon.gif" alt="" /></a>';
    }
    if($post->getVar('uid')>0 && isset($GLOBALS['viewtopic_users'][$post->getVar('uid')])) {
	    $poster = $GLOBALS['viewtopic_users'][$post->getVar('uid')];
    }
    else $poster= array(
    	'uid' => 0,
        'name' => $post->getVar('poster_name')?$post->getVar('poster_name'):$GLOBALS['myts']->HtmlSpecialChars($GLOBALS['xoopsConfig']['anonymous']),
        'link' => $post->getVar('poster_name')?$post->getVar('poster_name'):$GLOBALS['myts']->HtmlSpecialChars($GLOBALS['xoopsConfig']['anonymous'])
  	);
    if ($isadmin || $post->checkIdentity()) {
        $post_text = $post->getVar('post_text');
        $post_attachment = $post->displayAttachment();
    } elseif ($GLOBALS['xforumModuleConfig']['enable_karma'] && $post->getVar('post_karma') > $GLOBALS['user_karma']) {
        $post_text = "<div class='karma'>" . sprintf(_MD_KARMA_REQUIREMENT, $GLOBALS['user_karma'], $post->getVar('post_karma')) . "</div>";
        $post_attachment = '';
    } elseif (
        	$GLOBALS['xforumModuleConfig']['allow_require_reply']
        	&& $post->getVar('require_reply')
    	) {
        $post_text = "<div class='karma'>" . _MD_REPLY_REQUIREMENT . "</div>";
        $post_attachment = '';
    } else {
        $post_text = $post->getVar('post_text');
        $post_attachment = $post->displayAttachment();
    }

    $thread_buttons = array();
    
	if($GLOBALS['xforumModuleConfig']['enable_permcheck']){
	
		if(!isset($suspension[$post->getVar('forum_id')])){
			$moderate_handler = xoops_getmodulehandler('moderate', 'xforum');
			$suspension[$post->getVar('forum_id')] = $moderate_handler->verifyUser(-1,"",$post->getVar('forum_id'));
		}
		
	    if (!$suspension[$post->getVar('forum_id')] && $post->checkIdentity() && $post->checkTimelimit('edit_timelimit')
	    	|| $GLOBALS['isadmin']) 
	    {
	        $thread_buttons['edit']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_edit'], _EDIT);
	        $thread_buttons['edit']['link'] = XOOPS_URL."/modules/xforum/edit.php?forum=" .$post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id');
	        $thread_buttons['edit']['name'] = _EDIT;
	    }
	
	    if ( (!$suspension[$post->getVar('forum_id')] && $post->checkIdentity() && $post->checkTimelimit('delete_timelimit')) 
	    	|| $GLOBALS['isadmin'] )
	    {
	        $thread_buttons['delete']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_delete'], _DELETE);
	        $thread_buttons['delete']['link'] = XOOPS_URL."/modules/xforum/delete.php?forum=" . $post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id');
	        $thread_buttons['delete']['name'] = _DELETE;
	    }
	    if (!$suspension[$post->getVar('forum_id')] && is_object($GLOBALS['xoopsUser'])) {
	        $thread_buttons['reply']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_reply'], _MD_REPLY);
	        $thread_buttons['reply']['link'] = XOOPS_URL."/modules/xforum/reply.php?forum=" . $post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id');
	        $thread_buttons['reply']['name'] = _MD_REPLY;
	        /*
	        $thread_buttons['quote']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_quote'], _MD_QUOTE);
	        $thread_buttons['quote']['link'] = "reply.php?forum=" . $post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id') . "&amp;quotedac=1";
	        $thread_buttons['quote']['name'] = _MD_QUOTE;
	        */
	    }
    
	}else{
        $thread_buttons['edit']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_edit'], _EDIT);
        $thread_buttons['edit']['link'] = XOOPS_URL."/modules/xforum/edit.php?forum=" .$post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id');
        $thread_buttons['edit']['name'] = _EDIT;
        $thread_buttons['delete']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_delete'], _DELETE);
        $thread_buttons['delete']['link'] = XOOPS_URL."/modules/xforum/delete.php?forum=" . $post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id');
        $thread_buttons['delete']['name'] = _DELETE;
        $thread_buttons['reply']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_reply'], _MD_REPLY);
        $thread_buttons['reply']['link'] = XOOPS_URL."/modules/xforum/reply.php?forum=" . $post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id');
        $thread_buttons['reply']['name'] = _MD_REPLY;
	}

    if (!$isadmin && $GLOBALS['xforumModuleConfig']['reportmod_enabled']) {
        $thread_buttons['report']['image'] = forum_displayImage($GLOBALS['xforumImage']['p_report'], _MD_REPORT);
        $thread_buttons['report']['link'] = XOOPS_URL."/modules/xforum/report.php?forum=" . $post->getVar('forum_id') . "&amp;topic_id=" . $post->getVar('topic_id');
        $thread_buttons['report']['name'] = _MD_REPORT;
    }
    $thread_action = array();

    $GLOBALS['xoopsTpl']->append('posts',
    		array(
    			'post_id' 		=> $post->getVar('post_id'),
    			'topic_id' 		=> $post->getVar('topic_id'),
    			'forum_id' 		=> $post->getVar('forum_id'),
                'post_date' 	=> forum_formatTimestamp($post->getVar('post_time')),
                'post_image' 	=> $post_image,
                'post_title' 	=> $post_title,
                'post_text' 	=> $post_text,
                'post_attachment'	=> $post_attachment,
                'post_edit'			=> $post->displayPostEdit(),
                'post_no' 			=> $GLOBALS['start']+$pn,
                'post_signature'	=> ($post->getVar('attachsig'))?@$poster["signature"]:"",
	            'poster_ip' 		=> ($isadmin && $GLOBALS['xforumModuleConfig']['show_ip'])?long2ip($post->getVar('poster_ip')):"",
		    	'thread_action' 	=> $thread_action,
                'thread_buttons' 	=> $thread_buttons,
                'poster' 			=> $poster
       		)
  	);

    unset($thread_buttons);
	unset($poster);
}
unset($GLOBALS['viewtopic_users']);
unset($xforums);

if(!empty($GLOBALS['xforumModuleConfig']['show_jump'])){
	$GLOBALS['xoopsTpl']->assign('forum_jumpbox', forum_make_jumpbox($forum_id));
}

if ( $postCount > $post_perpage ) {
    $nav = new XoopsPageNav($postCount, $post_perpage, $GLOBALS['start'], "start", 'forum='.$forum_id.'&amp;viewmode='.$viewmode.'&amp;type='.$type.'&amp;uid='.$uid.'&amp;order='.$order."&amp;mode=".$mode);
    $GLOBALS['xoopsTpl']->assign('pagenav', $nav->renderNav(4));
} else {
    $GLOBALS['xoopsTpl']->assign('pagenav', '');
}

$GLOBALS['xoopsTpl']->assign('lang_forum_index', sprintf(_MD_XFORUMINDEX,htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));
$GLOBALS['xoopsTpl']->assign('folder_topic', forum_displayImage($GLOBALS['xforumImage']['folder_topic']));

switch($type){
	case 'active':
		$lang_title = _MD_VIEWALLPOSTS. ' ['._MD_TYPE_ADMIN.']';
		break;
	case 'pending':
		$lang_title = _MD_VIEWALLPOSTS. ' ['._MD_TYPE_PENDING.']';
		break;
	case 'deleted':
		$lang_title = _MD_VIEWALLPOSTS. ' ['._MD_TYPE_DELETED.']';
		break;
	case 'new':
		$lang_title = _MD_NEWPOSTS;
		break;
	default:
		$lang_title = _MD_VIEWALLPOSTS;
		break;
	}
if($uid>0){
	$lang_title .= ' ('.XoopsUser::getUnameFromId($uid).')';
}	
$GLOBALS['xoopsTpl']->assign('lang_title',$lang_title);
$GLOBALS['xoopsTpl']->assign('p_up',forum_displayImage($GLOBALS['xforumImage']['p_up'],_MD_TOP));
$GLOBALS['xoopsTpl']->assign('groupbar_enable', $GLOBALS['xforumModuleConfig']['groupbar_enabled']);
$GLOBALS['xoopsTpl']->assign('anonymous_prefix', $GLOBALS['xforumModuleConfig']['anonymous_prefix']);
$GLOBALS['xoopsTpl']->assign('down',forum_displayImage($GLOBALS['xforumImage']['doubledown']));
$GLOBALS['xoopsTpl']->assign('down2',forum_displayImage($GLOBALS['xforumImage']['down']));
$GLOBALS['xoopsTpl']->assign('up',forum_displayImage($GLOBALS['xforumImage']['up']));
$GLOBALS['xoopsTpl']->assign('printer',forum_displayImage($GLOBALS['xforumImage']['printer']));
$GLOBALS['xoopsTpl']->assign('personal',forum_displayImage($GLOBALS['xforumImage']['personal']));
$GLOBALS['xoopsTpl']->assign('post_content',forum_displayImage($GLOBALS['xforumImage']['post_content']));

$all_link = XOOPS_URL."/modules/xforum/viewall.php?forum=".$forum_id."&amp;start=$start";
$post_link = XOOPS_URL."/modules/xforum/viewpost.php?forum=".$forum_id;
$newpost_link = XOOPS_URL."/modules/xforum/viewpost.php?forum=".$forum_id."&amp;new=1";
$digest_link = XOOPS_URL."/modules/xforum/viewall.php?forum=".$forum_id."&amp;start=$start&amp;type=digest";
$unreplied_link = XOOPS_URL."/modules/xforum/viewall.php?forum=".$forum_id."&amp;start=$start&amp;type=unreplied";
$unread_link = XOOPS_URL."/modules/xforum/viewall.php?forum=".$forum_id."&amp;start=$start&amp;type=unread";

$GLOBALS['xoopsTpl']->assign('all_link', $all_link);
$GLOBALS['xoopsTpl']->assign('post_link', $post_link);
$GLOBALS['xoopsTpl']->assign('newpost_link', $newpost_link);
$GLOBALS['xoopsTpl']->assign('digest_link', $digest_link);
$GLOBALS['xoopsTpl']->assign('unreplied_link', $unreplied_link);
$GLOBALS['xoopsTpl']->assign('unread_link', $unread_link);

$viewmode_options = array();
if($viewmode=="compact"){
	$viewmode_options[]= array("link"=>XOOPS_URL."/modules/xforum/viewpost.php?viewmode=flat&amp;order=".$order."&amp;forum=".$forum_id,	"title"=>_FLAT);
	if ($order == 'DESC') {
		$viewmode_options[]= array("link"=>XOOPS_URL."/modules/xforum/viewpost.php?viewmode=compact&amp;order=ASC&amp;forum=".$forum_id,"title"=>_OLDESTFIRST);
	} else {
		$viewmode_options[]= array("link"=>XOOPS_URL."/modules/xforum/viewpost.php?viewmode=compact&amp;order=DESC&amp;forum=".$forum_id,"title"=>_NEWESTFIRST);
	}
}else{
	$viewmode_options[]= array("link"=>XOOPS_URL."/modules/xforum/viewpost.php?viewmode=compact&amp;order=".$order."&amp;forum=".$forum_id,	"title"=>_MD_COMPACT);
	if ($order == 'DESC') {
		$viewmode_options[]= array("link"=>XOOPS_URL."/modules/xforum/viewpost.php?viewmode=flat&amp;order=ASC&amp;forum=".$forum_id,"title"=>_OLDESTFIRST);
	} else {
		$viewmode_options[]= array("link"=>XOOPS_URL."/modules/xforum/viewpost.php?viewmode=flat&amp;order=DESC&amp;forum=".$forum_id,"title"=>_NEWESTFIRST);
	}
}

$GLOBALS['xoopsTpl']->assign('viewmode_compact', ($viewmode=="compact")?1:0);
$GLOBALS['xoopsTpl']->assign_by_ref('viewmode_options', $viewmode_options);
$GLOBALS['xoopsTpl']->assign('menumode',$menumode);
$GLOBALS['xoopsTpl']->assign('menumode_other',$menumode_other);

$GLOBALS['xoopsTpl']->assign('viewer_level', ($isadmin)?2:(is_object($GLOBALS['xoopsUser'])?1:0) );
$GLOBALS['xoopsTpl']->assign('uid', $uid);
$GLOBALS['xoopsTpl']->assign('mode', $mode);
$GLOBALS['xoopsTpl']->assign('type', $type);

include XOOPS_ROOT_PATH.'/footer.php';
?>