<?php

// $Id: post.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $


include 'header.php';
include_once XOOPS_ROOT_PATH . '/modules/' . $GLOBALS['xforumModule']->dirname() . '/class/uploader.php';

foreach (array(
			'forum',
			'topic_id',
			'post_id',
			'order',
			'pid',
			'start',
			'isreply',
			'isedit'
			) as $getint) {
    ${$getint} = isset($_POST[$getint]) ? intval($_POST[$getint]) : 0 ;
}
$op = isset($_POST['op']) ? $_POST['op'] : '';
$GLOBALS['viewmode'] = (isset($_POST['viewmode']) && $_POST['viewmode'] != 'flat') ? 'thread' : 'flat';
if ( empty($forum) ) {
    redirect_header(XOOPS_URL."/index.php", 2, _MD_ERRORFORUM);
    exit();
}

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$post_handler = xoops_getmodulehandler('post', 'xforum');

if ( !empty($isedit) && $post_id>0 ) {
    $forumpost = $post_handler->get($post_id);
    $topic_id = $forumpost->getVar("topic_id");
}else{
	$forumpost = $post_handler->create();
}

xoops_load("captcha");
$xoopsCaptcha = XoopsCaptcha::getInstance();
if (!$xoopsCaptcha->verify()) {
	redirect_header(XOOPS_URL.'/modules/xforum/', 10, $xoopsCaptcha->getMessage());
	exit(0);
}

$topic = $topic_handler->get($topic_id);
$forum_id = ($topic_id)?$topic->getVar("forum_id"):$forum;
$forum_obj = $GLOBALS['forum_handler']->get($forum_id);
if (!$forum_handler->getPermission($forum_obj)){
    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
    exit();
}

if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = xoops_getmodulehandler('online', 'xforum');
	$online_handler->init($forum_obj);
}

include XOOPS_ROOT_PATH."/header.php";

if ( !empty($_POST['contents_submit']) ) {
	$token_valid = false;
	if(class_exists("XoopsSecurity")){
		$token_valid = $GLOBALS['xoopsSecurity']->check();
	}else{ // backward compatible
		if( !empty($_SESSION['submit_token']) && !empty($_POST['post_valid']) && $_POST['post_valid']==$_SESSION['submit_token'] ) $token_valid = true;
		$_SESSION['submit_token'] = null;
	}
	
	$token_valid = true;

	if(!is_object($GLOBALS['xoopsUser'])){
		$uname = !isset($_POST['uname']) ? '' : trim($_POST['uname']);
		$pass = !isset($_POST['pass']) ? '' : trim($_POST['pass']);
		$member_handler = xoops_gethandler('member');
		$user = $member_handler->loginUser(addslashes($GLOBALS['myts']->stripSlashesGPC($uname)), addslashes($GLOBALS['myts']->stripSlashesGPC($pass)));
		if(is_object($user) && 0 < $user->getVar('level')){
			if(!empty($_POST["login"])){
				$user->setVar('last_login', time());
				if (!$member_handler->insertUser($user)) {
				}
				$_SESSION = array();
				$_SESSION['xoopsUserId'] = $user->getVar('uid');
				$_SESSION['xoopsUserGroups'] = $user->getGroups();
				if ($GLOBALS['xoopsConfig']['use_mysession'] && $GLOBALS['xoopsConfig']['session_name'] != '') {
					setcookie($GLOBALS['xoopsConfig']['session_name'], session_id(), time()+(60 * $GLOBALS['xoopsConfig']['session_expire']), '/',  '', 0);
				}
				$user_theme = $user->getVar('theme');
				if (in_array($user_theme, $GLOBALS['xoopsConfig']['theme_set_allowed'])) {
					$_SESSION['xoopsUserTheme'] = $user_theme;
				}
			}
			$GLOBALS['xoopsUser'] = $user;
		}
	}

	$GLOBALS['isadmin'] = forum_isAdmin($forum_obj);

	$time_valid = true;
	if( !$isadmin && !empty($GLOBALS['xforumModuleConfig']['post_timelimit']) ){
    	$last_post = forum_getsession('LP'); // using session might be more secure ...
		if(time()-$last_post < $GLOBALS['xforumModuleConfig']['post_timelimit']){
			$time_valid = false;
		}
	}

	if(!$token_valid || !$time_valid){
		$_POST['contents_preview'] = 1;
		$_POST['contents_submit'] = null;
		$_POST['contents_upload'] = null;
		if(!$token_valid) echo "<div class=\"errorMsg\">"._MD_INVALID_SUBMIT."</div>";
		if(!$time_valid) echo "<div class=\"errorMsg\">".sprintf(_MD_POSTING_LIMITED,$GLOBALS['xforumModuleConfig']['post_timelimit'])."</div>";
		echo "<br clear=\"both\" />";
	}
}

if ( !empty($_POST['contents_submit']) ) {
    $message =  $_POST['message'];
	if(empty($message)){
	    redirect_header("javascript:history.go(-1);", 1);
	    exit();
	}
    if ( !empty($isedit) && $post_id>0) {

		$uid = is_object($GLOBALS['xoopsUser'])? $GLOBALS['xoopsUser']->getVar('uid'):0;

		$GLOBALS['topic_status'] = $topic_handler->get($topic_id,'topic_status');
		if ( $topic_handler->getPermission($forum_obj, $GLOBALS['topic_status'], 'edit')
			&& ( $GLOBALS['isadmin'] || ( $forumpost->checkTimelimit('edit_timelimit') && $forumpost->checkIdentity() ))
			) {}
		else{
		    redirect_header(XOOPS_URL."/modules/xforum/viewtopic.php?forum=$forum_id&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid",2,_MD_NORIGHTTOEDIT);
		    exit();
		}

	    $delete_attach = isset($_POST['delete_attach']) ? $_POST['delete_attach'] : array();
	    if (is_array($delete_attach) && count($delete_attach)>0) $forumpost->deleteAttachment($delete_attach);
    }
    else {
		if($topic_id){
			$GLOBALS['topic_status'] = $topic_handler->get($topic_id,'topic_status');
			if (!$topic_handler->getPermission($forum_obj, $GLOBALS['topic_status'], 'reply')) {
			    redirect_header(XOOPS_URL."/modules/xforum/viewtopic.php?forum=$forum_id&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid",2,_MD_NORIGHTTOREPLY);
			    exit();
			}
		}else{
			$GLOBALS['topic_status'] = 0;
			if (!$topic_handler->getPermission($forum_obj, $GLOBALS['topic_status'], 'post')) {
			    redirect_header(XOOPS_URL."/modules/xforum/viewtopic.php?forum=$forum_id",2,_MD_NORIGHTTOPOST);
			    exit();
			}
		}

        $isreply = 0;
        $isnew = 1;
        if ( !is_object($GLOBALS['xoopsUser']) || ( !empty($_POST['noname']) && !empty($GLOBALS['xforumModuleConfig']['allow_user_anonymous']) ) ) {
            $uid = 0;
        }
        else {
            $uid = $GLOBALS['xoopsUser']->getVar("uid");
        }
        if (isset($pid) && $pid != "") {
            $forumpost->setVar('pid', $pid);
        }
        if (!empty($topic_id)) {
            $forumpost->setVar('topic_id', $topic_id);
            $isreply = 1;
        }
        $forumpost->setVar('poster_ip', forum_getIP());
        $forumpost->setVar('uid', $uid);
        $forumpost->setVar('post_time', time());
    }

	if($topic_handler->getPermission($forum_obj, $GLOBALS['topic_status'], 'noapprove')) $approved = 1;
	else $approved = 0;
	$forumpost->setVar('approved', $approved);

    $forumpost->setVar('forum_id', $forum_obj->getVar('forum_id'));

    $subject = xoops_trim($_POST['subject']);
    $subject = ($subject == '') ? _NOTITLE : $subject;
    $poster_name = !empty($_POST['poster_name'])?xoops_trim($_POST['poster_name']):'';
    $dohtml = !empty($_POST['dohtml']) ? 1 : 0;
    $dosmiley = !empty($_POST['dosmiley']) ? 1 : 0;
    $doxcode = !empty($_POST['doxcode']) ? 1 : 0;
    $dobr = !empty($_POST['dobr']) ? 1 : 0;
    $icon = (!empty($_POST['icon']) && is_file(XOOPS_ROOT_PATH . "/images/subject/" . $_POST['icon']) ) ? $_POST['icon'] : '';
    $attachsig = !empty($_POST['attachsig']) ? 1 : 0;
    $view_require = !empty($_POST['view_require']) ? $_POST['view_require'] : '';
    $post_karma = (($view_require == 'require_karma')&&isset($_POST['post_karma']))?intval($_POST['post_karma']):0;
    $require_reply = ($view_require == 'require_reply')?1:0;
	$tags = empty($_POST['tags']) ? "" : $_POST['tags'];
    $forumpost->setVar('subject', $subject);


	$objects_handler = xoops_getmodulehandler('post');
	$profile_handler = xoops_getmodulehandler('extras');
	
	$fields = $profile_handler->loadFields();
	$userfields = $profile_handler->getObjectVars();
	
	if ($post_id == 0) {
		$profile = $profile_handler->create();
		if (count($fields) > 0) {
			foreach (array_keys($fields) as $i) {
				$fieldname = $fields[$i]->getVar('field_name');
				if (in_array($fieldname, $userfields)) {
					$default = $fields[$i]->getVar('field_default');
					if ($default === '' || $default === null) {
						continue;
					}
					$forumpost->setVar($fieldname, $default);
				}
			}
		}
	} else {
		$profile = $profile_handler->get($post_id);
	}
	
	// Lets merge current $_POST  with $_SESSION['object_post'] so we can have access to info submited in previous steps
	// Get all fields that we can expect from a $_POST inlcuding our private '_message_'
	$fieldnames = array();
	foreach (array_keys($fields) as $i ) {
		$fieldnames[] = $fields[$i]->getVar('field_name');
	}
	$fieldnames = array_merge($fieldnames, $userfields);
	
	// Set vars from $_POST/$_SESSION['object_post']
	foreach (array_keys($fields) as $field) {
		if (!isset($_POST[$field])) {
			continue;
		}
	
		$value = $fields[$field]->getValueForSave($_POST[$field]);
		$profile->setVar($field, $value);
	}
	
	$stop = '';
		
	$isNew = $forumpost->isNew();
	//Did created an user already? If not then let us set some extra info
	if ($isNew) {
		$forumpost->setVar('tags', $tags);
		$forumpost->setVar('post_text', $message);
		$forumpost->setVar('post_karma', $post_karma);
		$forumpost->setVar('require_reply', $require_reply);
		$forumpost->setVar('poster_name', $poster_name);
		$forumpost->setVar('dohtml', $dohtml);
		$forumpost->setVar('dosmiley', $dosmiley);
		$forumpost->setVar('doxcode', $doxcode);
		$forumpost->setVar('dobr', $dobr);
		$forumpost->setVar('icon', $icon);
		$forumpost->setVar('attachsig', $attachsig);
		$forumpost->setAttachment();
	} else {
		$forumpost->setVar('tags', $tags);
		$forumpost->setVar('post_text', $message);
		$forumpost->setVar('post_karma', $post_karma);
		$forumpost->setVar('require_reply', $require_reply);
		$forumpost->setVar('poster_name', $poster_name);
		$forumpost->setVar('dohtml', $dohtml);
		$forumpost->setVar('dosmiley', $dosmiley);
		$forumpost->setVar('doxcode', $doxcode);
		$forumpost->setVar('dobr', $dobr);
		$forumpost->setVar('icon', $icon);
		$forumpost->setVar('attachsig', $attachsig);
		$forumpost->setAttachment();
	}



	if ( !empty($post_id) ) $forumpost->setPostEdit($poster_name); // is reply

	$attachments_tmp = array();
	if (!empty($_POST["attachments_tmp"])){
		$attachments_tmp=unserialize(base64_decode($_POST["attachments_tmp"]));
		if (isset($_POST["delete_tmp"]) && count($_POST["delete_tmp"])){
			foreach($_POST["delete_tmp"] as $key){
				unlink(XOOPS_CACHE_PATH . "/" . $attachments_tmp[$key][0]);
				unset($attachments_tmp[$key]);
			}
		}
    }
	if (count($attachments_tmp)){
		foreach($attachments_tmp as $key=>$attach){
			if(rename(XOOPS_CACHE_PATH . "/" . $attachments_tmp[$key][0],
				XOOPS_ROOT_PATH . "/".$GLOBALS['xforumModuleConfig']['dir_attachments']."/".$attachments_tmp[$key][0]
			)){
	            $forumpost->setAttachment($attach[0], $attach[1], $attach[2]);
	        }
        }
    }

    $error_upload = '';

    if (isset($_FILES['userfile']['name']) && $_FILES['userfile']['name']!=''
    	&& $topic_handler->getPermission($forum_obj, $GLOBALS['topic_status'], 'attach')
    )
    {
        $maxfilesize = $forum_obj->getVar('attach_maxkb')*1024;
        $uploaddir = XOOPS_ROOT_PATH . "/".$GLOBALS['xforumModuleConfig']['dir_attachments'];

        $uploader = new forum_uploader(
        	$uploaddir,
        	$forum_obj->getVar('attach_ext'),
        	$maxfilesize
        );

        $uploader->setCheckMediaTypeByExt();

        if ( $uploader->fetchMedia( $_POST['xoops_upload_file'][0]) )
        {
	        $prefix = is_object($GLOBALS['xoopsUser'])?strval($GLOBALS['xoopsUser']->uid()).'_':'forum_';
	        $uploader->setPrefix($prefix);
            if ( !$uploader->upload() )
                $error_upload = $uploader->getErrors();
            else{
                if ( is_file( $uploader->getSavedDestination() )){
                    $forumpost->setAttachment($uploader->getSavedFileName(), $uploader->getMediaName(), $uploader->getMediaType());
                }
            }
        }
        else
        {
            $error_upload = $uploader->getErrors();
        }
    }

	$isnew = $forumpost->isNew();
	
    if ($postid = $post_handler->insert($forumpost, true, __FILE__)) {
		$profile->setVar('post_id', $postid );
		if (!$isnew)
			$profile->unsetNew();
		else
			$profile->setNew();
		if (!$profile_handler->insert($profile)) {
			include_once(XOOPS_ROOT_PATH.'/header.php');
			xoops_error('Could not insert forum post extras');
			xoops_error($profile->getErrors());
			include_once(XOOPS_ROOT_PATH.'/footer.php');
			exit();
		}
	} else {
        include_once(XOOPS_ROOT_PATH.'/header.php');
        xoops_error('Could not insert forum post');
	    xoops_error($forumpost->getErrors());
        include_once(XOOPS_ROOT_PATH.'/footer.php');
        exit();
    }

	if ($GLOBALS['xforumModuleConfig']['tag']&&file_exists(XOOPS_ROOT_PATH . '/modules/tag/class/tag.php')) {
		$tag_handler = xoops_getmodulehandler('tag', 'tag');
		$tag_handler->updateByItem($_POST['tags'], $postid, $GLOBALS['xforumModule']->getVar("dirname"));
	}
	
	forum_setsession("LP", time()); // Recording last post time

    if(forum_checkSubjectPrefixPermission($forum_obj) && !empty($_POST['subject_pre'])){
		$subject_pre = intval($_POST['subject_pre']);
		$sbj_res = $post_handler->insertnewsubject($forumpost->getVar('topic_id'), $subject_pre);
    }

    // RMV-NOTIFY
    // Define tags for notification message
    if($approved && !empty($GLOBALS['xforumModuleConfig']['notification_enabled']) && !empty($isnew)){
	    $tags = array();
	    $tags['THREAD_NAME'] = $_POST['subject'];
	    $tags['THREAD_URL'] = $forumpost->getURL();
	    $tags['POST_URL'] = $tags['THREAD_URL'];
	    include_once 'include/notification.inc.php';
	    $forum_info = forum_notify_iteminfo ('forum', $forum_obj->getVar('forum_id'));
	    $tags['FORUM_NAME'] = $forum_info['name'];
	    $tags['FORUM_URL'] = $forum_info['url'];
	    $notification_handler = xoops_gethandler('notification');
        if (empty($isreply)) {
            // Notify of new thread
            $notification_handler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_thread', $tags);
        } else {
            // Notify of new post
            $notification_handler->triggerEvent('thread', $topic_id, 'new_post', $tags);
        }
        $notification_handler->triggerEvent('global', 0, 'new_post', $tags);
        $notification_handler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_post', $tags);
        $GLOBALS['myts'] = MyTextSanitizer::getInstance();
        $tags['POST_CONTENT'] = $GLOBALS['myts']->stripSlashesGPC($_POST['message']);
        $tags['POST_NAME'] = $GLOBALS['myts']->stripSlashesGPC($_POST['subject']);
        $notification_handler->triggerEvent('global', 0, 'new_fullpost', $tags);
        $notification_handler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_fullpost', $tags);
    }

    // If user checked notification box, subscribe them to the
    // appropriate event; if unchecked, then unsubscribe
    if (!empty($GLOBALS['xoopsUser']) && !empty($GLOBALS['xforumModuleConfig']['notification_enabled'])) {
	    $notification_handler = xoops_gethandler('notification');
        if (empty($_POST['notify'])) {
            $notification_handler->unsubscribe('thread', $forumpost->getVar('topic_id'), 'new_post');
        } elseif ($_POST['notify'] > 0) {
            $notification_handler->subscribe('thread', $forumpost->getVar('topic_id'), 'new_post');
        }
        // elseif($_POST['notify']<0) keep it as it is
    }

    if($approved){
		if(!empty($GLOBALS['xforumModuleConfig']['cache_enabled'])){
			forum_setsession("t".$forumpost->getVar("topic_id"), null);
		}
		$redirect = $forumpost->getURL();
	    $message = _MD_THANKSSUBMIT."<br />".$error_upload;
    }else{
	    $redirect = "viewforum.php?forum=".$forumpost->getVar('forum_id');
	    $message = _MD_THANKSSUBMIT."<br />"._MD_WAITFORAPPROVAL."<br />".$error_upload;
	}
	if ( $op == "add" ) {
		redirect_header(XOOPS_URL."/modules/xforum/polls.php?op=add&amp;forum=".$forumpost->getVar('forum_id')."&amp;topic_id=".$forumpost->getVar('topic_id')."",1,_MD_ADDPOLL);
		exit();
    }else{
	    redirect_header($redirect,2,$message);
        exit();
    }
}


if ( !empty($_POST['contents_upload']) ) {
	$attachments_tmp=array();
	if (!empty($_POST["attachments_tmp"])){
		$attachments_tmp=unserialize(base64_decode($_POST["attachments_tmp"]));
		if (isset($_POST["delete_tmp"]) && count($_POST["delete_tmp"])){
			foreach($_POST["delete_tmp"] as $key){
				unlink(XOOPS_CACHE_PATH . $attachments_tmp[$key][0]);
				unset($attachments_tmp[$key]);
			}
		}

    }

    $error_upload = '';
    if (isset($_FILES['userfile']['name']) && $_FILES['userfile']['name']!=''
    )
    {
        $maxfilesize = $forum_obj->getVar('attach_maxkb')*1024;
        $uploaddir = XOOPS_CACHE_PATH;

        $uploader = new forum_uploader(
        	$uploaddir,
        	$forum_obj->getVar('attach_ext'),
        	$maxfilesize
        );

        $uploader->setCheckMediaTypeByExt();

        if ( $uploader->fetchMedia( $_POST['xoops_upload_file'][0]) )
        {
	        $prefix = is_object($GLOBALS['xoopsUser'])?strval($GLOBALS['xoopsUser']->uid()).'_':'forum_';
	        $uploader->setPrefix($prefix);
            if ( !$uploader->upload() )
                $error_upload = $uploader->getErrors();
            else{
                if ( is_file( $uploader->getSavedDestination() )){
					$attachments_tmp[strval(time())]=array(
                    	$uploader->getSavedFileName(),
                    	$uploader->getMediaName(),
                    	$uploader->getMediaType()
                    	);
                }
            }
        }
        else
        {
            $error_upload = $uploader->getErrors();
        }
   }
}

if ( !empty($_POST['contents_preview']) || !empty($_GET['contents_preview']) ) {
	if (!empty($_POST["attachments_tmp"])){
		$attachments_tmp=unserialize(base64_decode($_POST["attachments_tmp"]));
	}

    $GLOBALS['myts'] = MyTextSanitizer::getInstance();
    $p_subject = $GLOBALS['myts']->htmlSpecialChars($GLOBALS['myts']->stripSlashesGPC($_POST['subject']));
    $dosmiley = empty($_POST['dosmiley']) ? 0 : 1;
    $dohtml = empty($_POST['dohtml']) ? 0 : 1;
    $doxcode = empty($_POST['doxcode']) ? 0 : 1;
    $dobr = empty($_POST['dobr']) ? 0 : 1;
    $p_message = $_POST['message'];
    $p_message = $GLOBALS['myts']->previewTarea($p_message, $dohtml, $dosmiley, $doxcode, 1, $dobr);
	if($dohtml && !forum_isAdmin($forum_obj) ) {
		//$p_message = forum_textFilter($p_message);
	}

    echo "<table cellpadding='4' cellspacing='1' width='98%' class='outer'>";
    echo "<tr><td class='head'>".$p_subject."</td></tr>";
    if(isset($_POST['poster_name'])){
		$p_poster_name = $GLOBALS['myts']->htmlSpecialChars($GLOBALS['myts']->stripSlashesGPC($_POST['poster_name']));
		echo "<tr><td>".$p_poster_name."</td></tr>";
	}
    echo "<tr><td><br />".$p_message."<br /></td></tr></table>";
}

if ( !empty($_POST['contents_upload']) || !empty($_POST['contents_preview']) || !empty($_GET['contents_preview']) || !empty($_POST['editor'])) 
{

    echo "<br />";
	$tags  = empty($_POST['tags']) ? "" : $_POST['tags'];
    $editor = empty($_POST['editor']) ? "" : $_POST['editor'];
    $dosmiley = empty($_POST['dosmiley']) ? 0 : 1;
    $dohtml = empty($_POST['dohtml']) ? 0 : 1;
    $doxcode = empty($_POST['doxcode']) ? 0 : 1;
    $dobr = empty($_POST['dobr']) ? 0 : 1;
    $subject_pre = (isset($_POST['subject_pre']))?$_POST['subject_pre']:'';
    $subject = $GLOBALS['myts']->htmlSpecialChars($GLOBALS['myts']->stripSlashesGPC($_POST['subject']));
	$message = $GLOBALS['myts']->htmlSpecialChars($GLOBALS['myts']->stripSlashesGPC($_POST['message']));
    $poster_name = isset($_POST['poster_name'])?$GLOBALS['myts']->htmlSpecialChars($GLOBALS['myts']->stripSlashesGPC($_POST['poster_name'])):'';
    $hidden = isset($_POST['hidden'])? $GLOBALS['myts']->htmlSpecialChars($GLOBALS['myts']->stripSlashesGPC($_POST['hidden'])):'';
    $notify = @intval($_POST['notify']);
    $attachsig = !empty($_POST['attachsig']) ? 1 : 0;
    $isreply = !empty($_POST['isreply']) ? 1 : 0;
    $isedit = !empty($_POST['isedit']) ? 1 : 0;
    $icon = (!empty($_POST['icon']) && is_file(XOOPS_ROOT_PATH . "/images/subject/" . $_POST['icon']) ) ? $_POST['icon'] : '';
    $view_require = isset($_POST['view_require']) ? $_POST['view_require'] : '';
    $post_karma = ( ($view_require == 'require_karma') && isset($_POST['post_karma']) )? intval($_POST['post_karma']) : 0 ;
    $require_reply = ($view_require == 'require_reply')?1:0;

    if(empty($_POST['contents_upload'])) $contents_preview = 1;
    $attachments=$forumpost->getAttachment();
    include 'include/forumform.inc.php';
}

include XOOPS_ROOT_PATH.'/footer.php';
?>