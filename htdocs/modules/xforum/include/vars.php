<?php

// $Id: vars.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
include_once $GLOBALS['xoops']->path('/modules/xforum/include/functions.ini.php');

$ori_error_level = ini_get('error_reporting');
error_reporting(E_ALL ^ E_NOTICE);

/**#@+
 * xforum constant
 *
 **/
define('FORUM_CONSTANTS',1);
define('FORUM_READ', 1);
define('FORUM_UNREAD', 2);
define('FORUM_UNREPLIED', 3);
define('FORUM_DIGEST', 4);
define('FORUM_DELETEONE', 1);
define('FORUM_DELETEALL', 2);
if (!defined('FORUM_PERM_ITEMS')) define('FORUM_PERM_ITEMS', 'access,view,post,reply,edit,delete,addpoll,vote,attach,noapprove');

/* some static xoopsModuleConfig */
$GLOBALS['xforumModuleConfig']["require_name"] = true; // "name" field is required for anonymous users in edit form

// MENU handler
/* You could remove anyone by commenting out in order to disable it */
$valid_menumodes = array(
	0 => _MD_MENU_SELECT,	// for selectbox
	1 => _MD_MENU_CLICK,	// for "click to expand"
	2 => _MD_MENU_HOVER		// for "mouse hover to expand"
	);

include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';

// You shouldn't have to change any of these
$GLOBALS['xforumUrl']['root'] = XOOPS_URL."/modules/" . $GLOBALS['xforumModule']->dirname();
$GLOBALS['xforumUrl']['images_root'] = XOOPS_URL."/modules/".$GLOBALS['xforumModule']->dirname()."/"."images";

//$handle = opendir(XOOPS_ROOT_PATH.'/modules/' . $GLOBALS['xforumModule']->dirname() . '/images/imagesets/');
$setdir = $GLOBALS['xforumModuleConfig']['image_set'];
//if (empty($setdir) || !is_dir(XOOPS_ROOT_PATH.'/modules/'. $GLOBALS['xforumModule']->dirname() .'/images/imagesets/'.$setdir.'/')) {
if (empty($setdir)) {
	$setdir = "default";
}

$GLOBALS['xforumUrl']['images_set']= $GLOBALS['xforumUrl']['images_root']."/imagesets/".$setdir;
if (is_dir(XOOPS_ROOT_PATH.'/modules/'. $GLOBALS['xforumModule']->dirname() .'/images/imagesets/'.$setdir.'/'.$GLOBALS['xoopsConfig']['language'])) {
	$GLOBALS['xforumUrl']['images_lang']=$GLOBALS['xforumUrl']['images_set']."/".$GLOBALS['xoopsConfig']['language'];
}else{
	$GLOBALS['xforumUrl']['images_lang']=$GLOBALS['xforumUrl']['images_set']."/english";
}

/* -- You shouldn't have to change anything after this point */
/* -- Images -- */

$GLOBALS['xforumImage']['attachment'] = $GLOBALS['xforumUrl']['images_set']."/attachment-a";
$GLOBALS['xforumImage']['clip'] = $GLOBALS['xforumUrl']['images_set']."/clip-a";
$GLOBALS['xforumImage']['whosonline'] = $GLOBALS['xforumUrl']['images_set']."/whosonline-a";

$GLOBALS['xforumImage']['folder_sticky'] = $GLOBALS['xforumUrl']['images_set']."/folder_sticky-a";
$GLOBALS['xforumImage']['folder_digest'] = $GLOBALS['xforumUrl']['images_set']."/folder_digest-a";
$GLOBALS['xforumImage']['locked_topic'] = $GLOBALS['xforumUrl']['images_set']."/lock-a";
$GLOBALS['xforumImage']['poll'] = $GLOBALS['xforumUrl']['images_set']."/poll-a";

$GLOBALS['xforumImage']['newposts_forum'] = $GLOBALS['xforumUrl']['images_set']."/folder_new_big-a";
$GLOBALS['xforumImage']['folder_forum'] = $GLOBALS['xforumUrl']['images_set']."/folder_big-a";
$GLOBALS['xforumImage']['locked_forum'] = $GLOBALS['xforumUrl']['images_set']."/folder_locked_big-a";
$GLOBALS['xforumImage']['locked_forum_newposts'] = $GLOBALS['xforumUrl']['images_set']."/folder_locked_big_newposts-a";
$GLOBALS['xforumImage']['folder_topic'] = $GLOBALS['xforumUrl']['images_set']."/folder-a";
$GLOBALS['xforumImage']['hot_folder_topic'] = $GLOBALS['xforumUrl']['images_set']."/hot_folder-a";
$GLOBALS['xforumImage']['newposts_topic'] = $GLOBALS['xforumUrl']['images_set']."/red_folder-a";
$GLOBALS['xforumImage']['hot_newposts_topic'] = $GLOBALS['xforumUrl']['images_set']."/hot_red_folder-a";
$GLOBALS['xforumImage']['hot_user_folder_topic'] = $GLOBALS['xforumUrl']['images_set']."/hot_folder_user-a";
$GLOBALS['xforumImage']['newposts_user_topic'] = $GLOBALS['xforumUrl']['images_set']."/red_folder_user-a";
$GLOBALS['xforumImage']['folder_user_topic'] = $GLOBALS['xforumUrl']['images_set']."/folder_user-a";

$GLOBALS['xforumImage']['rss'] = $GLOBALS['xforumUrl']['images_root']."/rss-a";
$GLOBALS['xforumImage']['subforum'] = $GLOBALS['xforumUrl']['images_root']."/arrow-a";
$GLOBALS['xforumImage']['blank'] = $GLOBALS['xforumUrl']['images_root']."/blank";
$GLOBALS['xforumImage']['move_topic'] = $GLOBALS['xforumUrl']['images_root']."/move_topic-a";
$GLOBALS['xforumImage']['del_topic'] = $GLOBALS['xforumUrl']['images_root']."/del_topic-a";
$GLOBALS['xforumImage']['lock_topic'] = $GLOBALS['xforumUrl']['images_root']."/lock_topic-a";
$GLOBALS['xforumImage']['unlock_topic'] = $GLOBALS['xforumUrl']['images_root']."/unlock_topic-a";
$GLOBALS['xforumImage']['sticky'] = $GLOBALS['xforumUrl']['images_root']."/sticky-a";
$GLOBALS['xforumImage']['unsticky'] = $GLOBALS['xforumUrl']['images_root']."/unsticky-a";
$GLOBALS['xforumImage']['digest'] = $GLOBALS['xforumUrl']['images_root']."/digest-a";
$GLOBALS['xforumImage']['undigest'] = $GLOBALS['xforumUrl']['images_root']."/undigest-a";

$GLOBALS['xforumImage']['edit'] = $GLOBALS['xforumUrl']['images_root']."/edit-a";
$GLOBALS['xforumImage']['delete'] = $GLOBALS['xforumUrl']['images_root']."/delete-a";
$GLOBALS['xforumImage']['restart'] = $GLOBALS['xforumUrl']['images_root']."/approve-a";
$GLOBALS['xforumImage']['approve'] = $GLOBALS['xforumUrl']['images_root']."/approve-a";

$GLOBALS['xforumImage']['personal'] = $GLOBALS['xforumUrl']['images_root']."/personal-a";
$GLOBALS['xforumImage']['pm'] = $GLOBALS['xforumUrl']['images_root'] . "/pm-a";
$GLOBALS['xforumImage']['icq'] = $GLOBALS['xforumUrl']['images_root'] . "/icq-a";
$GLOBALS['xforumImage']['email'] = $GLOBALS['xforumUrl']['images_root'] . "/email-a";
$GLOBALS['xforumImage']['aim'] = $GLOBALS['xforumUrl']['images_root'] . "/aim-a";
$GLOBALS['xforumImage']['home'] = $GLOBALS['xforumUrl']['images_root'] . "/home-a";
$GLOBALS['xforumImage']['yahoo'] = $GLOBALS['xforumUrl']['images_root'] . "/yahoo-a";
$GLOBALS['xforumImage']['msnm'] = $GLOBALS['xforumUrl']['images_root'] . "/msnm-a";
$GLOBALS['xforumImage']['pdf'] = $GLOBALS['xforumUrl']['images_root']."/pdf-a";
$GLOBALS['xforumImage']['spacer'] = $GLOBALS['xforumUrl']['images_root']."/spacer-a";
$GLOBALS['xforumImage']['news'] = $GLOBALS['xforumUrl']['images_root']."/news-a";
$GLOBALS['xforumImage']['docicon'] = $GLOBALS['xforumUrl']['images_root']."/document-a";

$GLOBALS['xforumImage']['p_delete'] = $GLOBALS['xforumUrl']['images_lang']."/p_delete-a";
$GLOBALS['xforumImage']['p_reply'] = $GLOBALS['xforumUrl']['images_lang']."/p_reply-a";
$GLOBALS['xforumImage']['p_quote'] = $GLOBALS['xforumUrl']['images_lang']."/p_quote-a";
$GLOBALS['xforumImage']['p_edit'] = $GLOBALS['xforumUrl']['images_lang']."/p_edit-a";
$GLOBALS['xforumImage']['p_report'] = $GLOBALS['xforumUrl']['images_lang']."/p_report-a";
$GLOBALS['xforumImage']['p_up'] = $GLOBALS['xforumUrl']['images_lang']."/p_up-a";
$GLOBALS['xforumImage']['t_new'] = $GLOBALS['xforumUrl']['images_lang']."/t_new-a";
$GLOBALS['xforumImage']['t_poll'] = $GLOBALS['xforumUrl']['images_lang']."/t_poll-a";
$GLOBALS['xforumImage']['t_qr'] = $GLOBALS['xforumUrl']['images_lang']."/t_qr-a";
$GLOBALS['xforumImage']['t_reply'] = $GLOBALS['xforumUrl']['images_lang']."/t_reply-a";

$GLOBALS['xforumImage']['online'] = $GLOBALS['xforumUrl']['images_lang']."/online-a";
$GLOBALS['xforumImage']['offline'] = $GLOBALS['xforumUrl']['images_lang']."/offline-a";
$GLOBALS['xforumImage']['new_forum']    = $GLOBALS['xforumUrl']['images_lang']."/new_forum-a";
$GLOBALS['xforumImage']['new_subforum'] = $GLOBALS['xforumUrl']['images_lang']."/new_subforum-a";

$GLOBALS['xforumImage']['post_content'] = $GLOBALS['xforumUrl']['images_set']."/post_content-a";

$GLOBALS['xforumImage']['threaded'] = $GLOBALS['xforumUrl']['images_set']."/threaded-a";
$GLOBALS['xforumImage']['flat'] = $GLOBALS['xforumUrl']['images_set']."/flat-a";
$GLOBALS['xforumImage']['left'] = $GLOBALS['xforumUrl']['images_set']."/left-a";
$GLOBALS['xforumImage']['right'] = $GLOBALS['xforumUrl']['images_set']."/right-a";
$GLOBALS['xforumImage']['doubledown'] = $GLOBALS['xforumUrl']['images_set']."/doubledown-a";
$GLOBALS['xforumImage']['down'] = $GLOBALS['xforumUrl']['images_set']."/down-a";
$GLOBALS['xforumImage']['up'] = $GLOBALS['xforumUrl']['images_set']."/up-a";
$GLOBALS['xforumImage']['printer'] = $GLOBALS['xforumUrl']['images_set']."/printer-a";

$GLOBALS['xforumImage']['pm'] = XOOPS_URL."/images/icons/pm_small.gif";

$GLOBALS['xforumImage']['rate1'] = $GLOBALS['xforumUrl']['images_set'].'/rate1-a';
$GLOBALS['xforumImage']['rate2'] = $GLOBALS['xforumUrl']['images_set'].'/rate2-a';
$GLOBALS['xforumImage']['rate3'] = $GLOBALS['xforumUrl']['images_set'].'/rate3-a';
$GLOBALS['xforumImage']['rate4'] = $GLOBALS['xforumUrl']['images_set'].'/rate4-a';
$GLOBALS['xforumImage']['rate5'] = $GLOBALS['xforumUrl']['images_set'].'/rate5-a';

// xforum cookie structure
/* -- Cookie settings -- */
$forumCookie['domain'] = "";
$forumCookie['path'] = "/";
$forumCookie['secure'] = false;
$forumCookie['expire'] = time() + 3600 * 24 * 30; // one month
$forumCookie['prefix'] = '';

// set cookie name to avoid subsites confusion such as: domain.com, sub1.domain.com, sub2.domain.com, domain.com/xoopss, domain.com/xoops2
if(empty($forumCookie['prefix'])){
	$cookie_prefix = preg_replace("/[^a-z_0-9]+/i", "_", preg_replace("/(http(s)?:\/\/)?(www.)?/i","",XOOPS_URL));
	$cookie_userid = (is_object($GLOBALS['xoopsUser']))?$GLOBALS['xoopsUser']->getVar('uid'):0;
	$forumCookie['prefix'] = $cookie_prefix."_".$GLOBALS['xforumModule']->dirname().'_'.$cookie_userid."_";
}

// set LastVisitTemp cookie, which only gets the time from the LastVisit cookie if it does not exist yet
// otherwise, it gets the time from the LastVisitTemp cookie
//$last_visit = forum_getcookie("LVT");
$last_visit = forum_getsession("LV");
$last_visit = ($last_visit)?$last_visit:forum_getcookie("LV");
$last_visit = ($last_visit)?$last_visit:time();


// update LastVisit cookie.
forum_setcookie("LV", time(), $forumCookie['expire']); // set cookie life time to one month
//forum_setcookie("LVT", $last_visit);
forum_setsession("LV", $last_visit);

/* xforum cookie storage
	Long term cookie: (configurable, generally one month)
		LV - Last Visit
		M - Menu mode
		V - View mode
		G - Toggle
	Short term cookie: (same as session life time)
		ST - Stored Topic IDs for mark
		LP - Last Post
		LF - Forum Last view
		LT - Topic Last read
		LVT - Last Visit Temp
*/

// include customized variables
if( is_object($GLOBALS['xforumModule']) && "xforum" == $GLOBALS['xforumModule']->getVar("dirname", "n") ) {
	$GLOBALS['xforumModuleConfig'] = forum_load_config();
}


error_reporting($ori_error_level);
?>