<?php

// $Id: functions.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $


if(!defined("forum_FUNCTIONS")):
define("forum_FUNCTIONS", true);

include_once dirname(__FILE__)."/functions.ini.php";

if (!function_exists('xoops_sef'))
{
	function xoops_sef($datab, $char ='-')
	{
		$replacement_chars = array();
		$accepted = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","m","o","p","q",
				 "r","s","t","u","v","w","x","y","z","0","9","8","7","6","5","4","3","2","1");
		for($i=0;$i<256;$i++){
			if (!in_array(strtolower(chr($i)),$accepted))
				$replacement_chars[] = chr($i);
		}
		$return_data = (str_replace($replacement_chars,$char,$datab));
		#print $return_data . "<BR><BR>";
		return($return_data);
	
	}
}
function &forum_getUnameFromIds( $userid, $usereal = 0, $linked = false )
{
	$users = XoopsUserUtility::getUnameFromIds($userid, $usereal, $linked);
	return $users;
}

function forum_getUnameFromId( $userid, $usereal = 0, $linked = false)
{
	return XoopsUserUtility::getUnameFromId($userid, $usereal, $linked);
}

function forum_is_dir($dir){
    $openBasedir = ini_get('open_basedir');
    if (empty($openBasedir)) {
	    return @is_dir($dir);
    }

    return in_array($dir, explode(':', $openBasedir));
}

/*
 * Sorry, we have to use the stupid solution unless there is an option in MyTextSanitizer:: htmlspecialchars();
 */
function forum_htmlSpecialChars($text)
{
	return preg_replace(array("/&amp;/i", "/&nbsp;/i"), array('&', '&amp;nbsp;'), htmlspecialchars($text));
}

function &forum_displayTarea(&$text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1)
{
	if ($html != 1) {
		// html not allowed
		$text = forum_htmlSpecialChars($text);
	}
	$text = $GLOBALS['myts']->codePreConv($text, $xcode); // Ryuji_edit(2003-11-18)
	$text = $GLOBALS['myts']->makeClickable($text);
	if ($smiley != 0) {
		// process smiley
		$text = $GLOBALS['myts']->smiley($text);
	}
	if ($xcode != 0) {
		// decode xcode
		if ($image != 0) {
			// image allowed
			$text = $GLOBALS['myts']->xoopsCodeDecode($text);
		} else {
    		// image not allowed
    		$text = $GLOBALS['myts']->xoopsCodeDecode($text, 0);
		}
	}
	if ($br != 0) {
		$text = $GLOBALS['myts']->nl2Br($text);
	}
	$text = $GLOBALS['myts']->codeConv($text, $xcode, $image);	// Ryuji_edit(2003-11-18)
	return $text;
}

/* 
 * Filter out possible malicious text
 * kses project at SF could be a good solution to check
 *
 * package: Article
 *
 * @param string	$text 	text to filter
 * @param bool		$force 	flag indicating to force filtering
 * @return string 	filtered text
 */
function &forum_textFilter($text, $force = false)
{
	
	
	if(empty($force) && is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->isAdmin()){
		return $text;
	}
	// For future applications
	$tags=empty($GLOBALS['xoopsConfig']["filter_tags"])?array():explode(",", $GLOBALS['xoopsConfig']["filter_tags"]);
	$tags = array_map("trim", $tags);
	
	// Set embedded tags
	$tags[] = "SCRIPT";
	$tags[] = "VBSCRIPT";
	$tags[] = "JAVASCRIPT";
	foreach($tags as $tag){
		$search[] = "/<".$tag."[^>]*?>.*?<\/".$tag.">/si";
		$replace[] = " [!".strtoupper($tag)." FILTERED!] ";
	}
	// Set iframe tag
	$search[]= "/<IFRAME[^>\/]*SRC=(['\"])?([^>\/]*)(\\1)[^>\/]*?\/>/si";
	$replace[]=" [!IFRAME FILTERED!] \\2 ";
	$search[]= "/<IFRAME[^>]*?>([^<]*)<\/IFRAME>/si";
	$replace[]=" [!IFRAME FILTERED!] \\1 ";
	// action
	$text = preg_replace($search, $replace, $text);
	return $text;
}

function forum_html2text($document)
{
	$text = strip_tags($document);
	return $text;
}

/*
 * Currently the xforum session/cookie handlers are limited to:
 * -- one dimension
 * -- "," and "|" are preserved
 *
 */

function forum_setsession($name, $string = '')
{
	if(is_array($string)) {
		$value = array();
		foreach ($string as $key => $val){
			$value[]=$key."|".$val;
		}
		$string = implode(",", $value);
	}
	$_SESSION['xforum_'.$name] = $string;
}

function forum_getsession($name, $isArray = false)
{
	$value = !empty($_SESSION['xforum_'.$name]) ? $_SESSION['xforum_'.$name] : false;
	if($isArray) {
		$_value = ($value)?explode(",", $value):array();
		$value = array();
		if(count($_value)>0) foreach($_value as $string){
			$key = substr($string, 0, strpos($string,"|"));
			$val = substr($string, (strpos($string,"|")+1));
			$value[$key] = $val;
		}
		unset($_value);
	}
	return $value;
}

function forum_setcookie($name, $string = '', $expire = 0)
{
	global $forumCookie;
	if(is_array($string)) {
		$value = array();
		foreach ($string as $key => $val){
			$value[]=$key."|".$val;
		}
		$string = implode(",", $value);
	}
	setcookie($forumCookie['prefix'].$name, $string, intval($expire), $forumCookie['path'], $forumCookie['domain'], $forumCookie['secure']);
}

function forum_getcookie($name, $isArray = false)
{
	global $forumCookie;
	$value = !empty($_COOKIE[$forumCookie['prefix'].$name]) ? $_COOKIE[$forumCookie['prefix'].$name] : null;
	if($isArray) {
		$_value = ($value)?explode(",", $value):array();
		$value = array();
		if(count($_value)>0) foreach($_value as $string){
			$sep = strpos($string,"|");
			if($sep===false){
				$value[]=$string;
			}else{
				$key = substr($string, 0, $sep);
				$val = substr($string, ($sep+1));
				$value[$key] = $val;
			}
		}
		unset($_value);
	}
	return $value;
}

function forum_checkTimelimit($action_last, $action_tag, $inMinute = true)
{
	
	if(!isset($GLOBALS['xforumModuleConfig'][$action_tag]) or $GLOBALS['xforumModuleConfig'][$action_tag]==0) return true;
	$timelimit = ($inMinute)?$GLOBALS['xforumModuleConfig'][$action_tag]*60:$GLOBALS['xforumModuleConfig'][$action_tag];
	return ($action_last > time()-$timelimit)?true:false;
}


function &getModuleAdministrators($mid=0)
{
	static $module_administrators=array();
	if(isset($module_administrators[$mid])) return $module_administrators[$mid];

    $moduleperm_handler = xoops_gethandler('groupperm');
    $groupsIds = $moduleperm_handler->getGroupIds('module_admin', $mid);

    $administrators = array();
    $member_handler = xoops_gethandler('member');
    foreach($groupsIds as $groupid){
    	$userIds = $member_handler->getUsersByGroup($groupid);
    	foreach($userIds as $userid){
        	$administrators[$userid] = 1;
    	}
    }
    $module_administrators[$mid] =array_keys($administrators);
    unset($administrators);
    return $module_administrators[$mid];
}

/* use hardcoded DB query to save queries */
function forum_isModuleAdministrator($uid = 0, $mid = 0)
{
	static $module_administrators=array();
	if(isset($module_administrators[$mid][$uid])) return $module_administrators[$mid][$uid];

    $sql = "SELECT COUNT(l.groupid) FROM ".$GLOBALS['xoopsDB']->prefix('groups_users_link')." AS l".
    		" LEFT JOIN ".$GLOBALS['xoopsDB']->prefix('group_permission')." AS p ON p.gperm_groupid=l.groupid".
    		" WHERE l.uid=".intval($uid).
    		"	AND p.gperm_modid = '1' AND p.gperm_name = 'module_admin' AND p.gperm_itemid = '".intval($mid)."'";
    if(!$result = $GLOBALS['xoopsDB']->query($sql)){
	    $module_administrators[$mid][$uid] = null;
    }else{
    	list($count) = $GLOBALS['xoopsDB']->fetchRow($result);
	    $module_administrators[$mid][$uid] = intval($count);    	
    }
    return $module_administrators[$mid][$uid];
}

/* use hardcoded DB query to save queries */
function forum_isModuleAdministrators($uid = array(), $mid = 0)
{
	$module_administrators=array();

	if(empty($uid)) return $module_administrators;
    $sql = "SELECT COUNT(l.groupid) AS count, l.uid FROM ".$GLOBALS['xoopsDB']->prefix('groups_users_link')." AS l".
    		" LEFT JOIN ".$GLOBALS['xoopsDB']->prefix('group_permission')." AS p ON p.gperm_groupid=l.groupid".
    		" WHERE l.uid IN (".implode(", ", array_map("intval", $uid)).")".
    		"	AND p.gperm_modid = '1' AND p.gperm_name = 'module_admin' AND p.gperm_itemid = '".intval($mid)."'".
    		" GROUP BY l.uid";
    if($result = $GLOBALS['xoopsDB']->query($sql)){
	    while($myrow = $GLOBALS['xoopsDB']->fetchArray($result)){
	    	$module_administrators[$myrow["uid"]] = intval($myrow["count"]);
    	}
    }
    return $module_administrators;
}

function forum_isAdministrator($user=-1, $mid=0)
{
	
	static $administrators, $forum_mid;

	if(is_numeric($user) && $user == -1) $user = $GLOBALS['xoopsUser'];
	if(!is_object($user) && intval($user)<1) return false;
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);

	if(!$mid){
		if (!isset($forum_mid)) {
		    if(is_object($GLOBALS['xforumModule'])&& 'xforum' == $GLOBALS['xforumModule']->dirname()){
		    	
		    	$forum_mid = $GLOBALS['xforumModule']->getVar('mid');
		    }else{
		    	
		        $modhandler = xoops_gethandler('module');
		        
		        $GLOBALS['xforumModule'] = $modhandler->getByDirname('xforum');
			    $forum_mid = $GLOBALS['xforumModule']->getVar('mid');
			    unset($xforum);
		    }
		}
		$mid = $forum_mid;
	}
	
	return forum_isModuleAdministrator($uid, $mid);
}

function forum_isAdmin($xforum = 0, $user=-1)
{
	
	static $_cachedModerators;

	if(is_numeric($user) && $user == -1) $user = $GLOBALS['xoopsUser'];
	if(!is_object($user) && intval($user)<1) return false;
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);
	if(forum_isAdministrator($uid)) return true;

	$cache_id = (is_object($xforum))?$xforum->getVar('forum_id'):intval($xforum);
	if(!isset($_cachedModerators[$cache_id])){
		$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
		if(!is_object($xforum)) $xforum = $GLOBALS['forum_handler']->get(intval($xforum));
		$_cachedModerators[$cache_id] = $GLOBALS['forum_handler']->getModerators($xforum);
	}
	return in_array($uid,$_cachedModerators[$cache_id]);
}

function forum_isModerator($xforum = 0, $user=-1)
{
	
	static $_cachedModerators;

	if(is_numeric($user) && $user == -1) $user = $GLOBALS['xoopsUser'];
	if(!is_object($user) && intval($user)<1) {
		return false;
	}
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);

	$cache_id = (is_object($xforum))?$xforum->getVar('forum_id'):intval($xforum);
	if(!isset($_cachedModerators[$cache_id])){
		$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
		if(!is_object($xforum)) $xforum = $GLOBALS['forum_handler']->get(intval($xforum));
		$_cachedModerators[$cache_id] = $GLOBALS['forum_handler']->getModerators($xforum);
	}
	return in_array($uid,$_cachedModerators[$cache_id]);
}

function forum_checkSubjectPrefixPermission($xforum = 0, $user=-1)
{
	

	if($GLOBALS['xforumModuleConfig']['subject_prefix_level']<1){
		return false;
	}
	if($GLOBALS['xforumModuleConfig']['subject_prefix_level']==1){
		return true;
	}
	if(is_numeric($user) && $user == -1) $user = $GLOBALS['xoopsUser'];
	if(!is_object($user) && intval($user)<1) return false;
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);
	if($GLOBALS['xforumModuleConfig']['subject_prefix_level']==2){
		return true;
	}
	if($GLOBALS['xforumModuleConfig']['subject_prefix_level']==3){
		if(forum_isAdmin($xforum, $user)) return true;
		else return false;
	}
	if($GLOBALS['xforumModuleConfig']['subject_prefix_level']==4){
		if(forum_isAdministrator($user)) return true;
	}
	return false;
}
/*
* Gets the total number of topics in a form
*/
function get_total_topics($forum_id="")
{
	$topic_handler = xoops_getmodulehandler('topic', 'xforum');
	$criteria = new CriteriaCompo(new Criteria("approved", 0, ">"));
    if ( $forum_id ) {
	    $criteria->add(new Criteria("forum_id", intval($forum_id)));
    }
    return $topic_handler->getCount($criteria);
}

/*
* Returns the total number of posts in the whole system, a forum, or a topic
* Also can return the number of users on the system.
*/
function get_total_posts($id = 0, $type = "all")
{
	$post_handler = xoops_getmodulehandler('post', 'xforum');
	$criteria = new CriteriaCompo(new Criteria("approved", 0, ">"));
    switch ( $type ) {
    case 'forum':
        if($id>0) $criteria->add(new Criteria("forum_id", intval($id)));
        break;
    case 'topic':
        if($id>0) $criteria->add(new Criteria("topic_id", intval($id)));
        break;
    case 'all':
    default:
        break;
    }
    return $post_handler->getCount($criteria);
}

function get_total_views()
{
    $sql = "SELECT sum(topic_views) FROM ".$GLOBALS['xoopsDB']->prefix("xf_topics")."";
    if ( !$result = $GLOBALS['xoopsDB']->query($sql) ) {
        return null;
    }
    list ($total) = $GLOBALS['xoopsDB']->fetchRow($result);
    return $total;
}

function forum_forumSelectBox($value = null, $permission = "access", $delimitor_category = true)
{
	$category_handler = xoops_getmodulehandler('category', 'xforum');
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
    $categories = $category_handler->getAllCats($permission, true);
    $xforums = $GLOBALS['forum_handler']->getForumsByCategory(array_keys($categories), $permission, false);

    if(!defined("_MD_SELFORUM")) {
		if ( !( $ret = @include_once( XOOPS_ROOT_PATH."/modules/xforum/language/".$GLOBALS['xoopsConfig']['language']."/main.php" ) ) ) {
			include_once( XOOPS_ROOT_PATH."/modules/xforum/language/english/main.php" );
		}
    }
    $value = is_array($value)?$value:array($value);
    $box ='<option value="-1">-- '._MD_SELFORUM.' --</option>';
	if(count($categories)>0 && count($xforums)>0){
		foreach(array_keys($xforums) as $key){
			if($delimitor_category) {
	            $box .= "<option value='-1'>&nbsp;</option>";
			}
            $box .= "<option value='-1'>[".$categories[$key]->getVar('cat_title')."]</option>";
            foreach ($xforums[$key] as $f=>$xforum) {
                $box .= "<option value='".$f."' ".( (in_array($f, $value))?" selected":"" ).">-- ".$xforum['title']."</option>";
				if( !isset($xforum["sub"]) || count($xforum["sub"]) ==0 ) continue; 
	            foreach (array_keys($xforum["sub"]) as $s) {
	                $box .= "<option value='".$s."' ".( (in_array($s, $value))?" selected":"" ).">---- ".$xforum["sub"][$s]['title']."</option>";
                }
            }
		}
    } else {
        $box .= "<option value='-1'>"._MD_NOFORUMINDB."</option>";
    }
    unset($xforums, $categories);
 
    return $box;   
}
	
function forum_make_jumpbox($forum_id = 0)
{
	$box = '<form name="forum_jumpbox" method="get" action="'.XOOPS_URL.'/modules/xforum/viewforum.php" onsubmit="javascript: if(document.forum_jumpbox.forum.value &lt; 1){return false;}">';
	$box .= '<select class="select" name="forum" onchange="javascript: if(this.options[this.selectedIndex].value >0 ){ document.forms.forum_jumpbox.submit();}">';
    $box .= forum_forumSelectBox($forum_id);
    $box .= "</select> <input type='submit' class='button' value='"._GO."' /></form>";
    unset($xforums, $categories);
    return $box;
}

function forum_isIE5()
{
	static $user_agent_is_IE5;

	if(isset($user_agent_is_IE5)) return $user_agent_is_IE5;;
    $msie='/msie\s(5\.[5-9]|[6-9]\.[0-9]*).*(win)/i';
    if( !isset($_SERVER['HTTP_USER_AGENT']) ||
        !preg_match($msie,$_SERVER['HTTP_USER_AGENT']) ||
        preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])){
	    $user_agent_is_IE5 = false;
    }else{
	    $user_agent_is_IE5 = true;
    }
    return $user_agent_is_IE5;
}

function forum_displayImage($image, $alt = "", $width = 0, $height =0, $style ="margin: 0px;", $sizeMeth='scale')
{
	
	static $image_type;

	$user_agent_is_IE5 = forum_isIE5();
	if(!isset($image_type)) $image_type = ($GLOBALS['xforumModuleConfig']['image_type'] == 'auto')?(($user_agent_is_IE5)?'gif':'png'):$GLOBALS['xforumModuleConfig']['image_type'];
	$image .= '.'.$image_type;
	$imageuri=preg_replace("/^".preg_quote(XOOPS_URL,"/")."/",XOOPS_ROOT_PATH,$image);
	if(!preg_match("/^".preg_quote(XOOPS_ROOT_PATH,"/")."/",$imageuri)){
		$imageuri = XOOPS_ROOT_PATH."/".$image;
	}
	if(file_exists($imageuri)){
	    $size=@getimagesize($imageuri);
	    if(is_array($size)){
		    $width=$size[0];
		    $height=$size[1];
	    }
    }else{
		$image=$GLOBALS['xforumImage']['blank'].'.gif';
    }
    $width .='px';
    $height .='px';

    $img_style = "width: $width; height:$height; $style";
    $image_url = "<img src=\"".$image."\" style=\"".$img_style."\" alt=\"".$alt."\" align=\"middle\" />";

    return $image_url;
}

/**
 * forum_updaterating()
 *
 * @param $sel_id
 * @return updates rating data in itemtable for a given item
 **/
function forum_updaterating($sel_id)
{
    $query = "select rating FROM " . $GLOBALS['xoopsDB'] -> prefix('xf_votedata') . " WHERE topic_id = " . $sel_id . "";
    $voteresult = $GLOBALS['xoopsDB'] -> query($query);
    $votesDB = $GLOBALS['xoopsDB'] -> getRowsNum($voteresult);
    $totalrating = 0;
    while (list($rating) = $GLOBALS['xoopsDB'] -> fetchRow($voteresult))
    {
        $totalrating += $rating;
    }
    $finalrating = $totalrating / $votesDB;
    $finalrating = number_format($finalrating, 4);
    $sql = sprintf("UPDATE %s SET rating = %u, votes = %u WHERE topic_id = %u", $GLOBALS['xoopsDB'] -> prefix('xf_topics'), $finalrating, $votesDB, $sel_id);
    $GLOBALS['xoopsDB'] -> queryF($sql);
}

function forum_sinceSelectBox($selected = 100)
{
	

	$select_array = explode(',',$GLOBALS['xforumModuleConfig']['since_options']);
	$select_array = array_map('trim',$select_array);

	$forum_selection_since = '<select name="since">';
	foreach ($select_array as $since) {
		$forum_selection_since .= '<option value="'.$since.'"'.(($selected == $since) ? ' selected="selected"' : '').'>';
		if($since>0){
			$forum_selection_since .= sprintf(_MD_FROMLASTDAYS, $since);
		}else{
			$forum_selection_since .= sprintf(_MD_FROMLASTHOURS, abs($since));
		}
		$forum_selection_since .= '</option>';
	}
	$forum_selection_since .= '<option value="365"'.(($selected == 365) ? ' selected="selected"' : '').'>'._MD_THELASTYEAR.'</option>';
	$forum_selection_since .= '<option value="0"'.(($selected == 0) ? ' selected="selected"' : '').'>'._MD_BEGINNING.'</option>';
	$forum_selection_since .= '</select>';

	return $forum_selection_since;
}

function forum_getSinceTime($since = 100)
{
	if($since==1000) return 0;
	if($since>0) return intval($since) * 24 * 3600;
	else return intval(abs($since)) * 3600;
}

function forum_welcome( $user = -1 )
{
	

	if(empty($GLOBALS['xforumModuleConfig']["welcome_forum"])) return null;
	if(is_numeric($user) && $user == -1) $user = $GLOBALS['xoopsUser'];
	if(!is_object($user) || $user->getVar('posts')){
		return false;
	}

	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$xforum = $GLOBALS['forum_handler']->get($GLOBALS['xforumModuleConfig']["welcome_forum"]);
	if (!$forum_handler->getPermission($xforum)){
		unset($xforum);
		return false;
	}
	unset($xforum);
	include_once dirname(__FILE__)."/functions.welcome.php";
	return forum_welcome_create($user, $GLOBALS['xforumModuleConfig']["welcome_forum"]);
}

function forum_synchronization($type = "")
{
	switch($type){
	case "rate":
	case "report":
	case "post":
	case "topic":
	case "forum":
	case "category":
	case "moderate":
	case "read":
		$type = array($type);
		$clean = $type;
		break;
	default:
		$type = null;
		$clean = array("category", "forum", "topic", "post", "report", "rate", "moderate", "readtopic", "readforum");
		break;
	}
	foreach($clean as $item){
		$handler = xoops_getmodulehandler($item, "xforum");
		$handler->cleanOrphan();
		unset($handler);
	}
    $xforumConfig = forum_load_config();
	if(empty($type) || in_array("post", $type)):
		$post_handler = xoops_getmodulehandler("post", "xforum");
        $expires = isset($xforumConfig["pending_expire"])?intval($xforumConfig["pending_expire"]):7;
		$post_handler->cleanExpires($expires*24*3600);
	endif;
	if(empty($type) || in_array("topic", $type)):
		$topic_handler = xoops_getmodulehandler("topic", "xforum");
        $expires = isset($xforumConfig["pending_expire"])?intval($xforumConfig["pending_expire"]):7;
		$topic_handler->cleanExpires($expires*24*3600);
		$topic_handler->synchronization();
	endif;
	if(empty($type) || in_array("forum", $type)):
		$GLOBALS['forum_handler'] = xoops_getmodulehandler("forum", "xforum");
		$GLOBALS['forum_handler']->synchronization();
	endif;
	if(empty($type) || in_array("moderate", $type)):
		$moderate_handler = xoops_getmodulehandler("moderate", "xforum");
		$moderate_handler->clearGarbage();
	endif;
	if(empty($type) || in_array("read", $type)):
		$read_handler = xoops_getmodulehandler("readforum", "xforum");
		$read_handler->clearGarbage();
		$read_handler->synchronization();
		$read_handler = xoops_getmodulehandler("readtopic", "xforum");
		$read_handler->clearGarbage();
		$read_handler->synchronization();
	endif;
	return true;
}

function forum_setRead($type, $item_id, $post_id, $uid = null)
{
	$read_handler = xoops_getmodulehandler("read".$type, "xforum");
	return $read_handler->setRead($item_id, $post_id, $uid);
}

function forum_getRead($type, $item_id, $uid = null)
{
	$read_handler = xoops_getmodulehandler("read".$type, "xforum");
	return $read_handler->getRead($item_id, $uid);
}

function forum_setRead_forum($status = 0, $uid = null)
{
	$read_handler = xoops_getmodulehandler("readforum", "xforum");
	return $read_handler->setRead_items($status, $uid);
}

function forum_setRead_topic($status = 0, $forum_id = 0, $uid = null)
{
	$read_handler = xoops_getmodulehandler("readtopic", "xforum");
	return $read_handler->setRead_items($status, $forum_id, $uid);
}

function forum_isRead($type, &$items, $uid = null)
{
	$read_handler = xoops_getmodulehandler("read".$type, "xforum");
	return $read_handler->isRead_items($items, $uid);
}

endif;

if (!function_exists('chronolabs_inline')) {
	function chronolabs_inline($flash = false)
	{
	
		/*$ret = '<div style="clear:both; height 10px;">&nbsp;</div>
	<div style="clear:both; height 10px;"><center><img src="http://www.chronolabs.org.au/images/banners/loader/supportimage.php?flash=false" /></center></div>
	<div style="clear:both;">Chronolabs offer limited free support should you want some development work done please contact us <a href="http://www.chronolabs.org.au/liaise/">on the question for a quote form.</a> We offer a wide range of XOOPS Professional Solution and have options for Basic SEO and marketing of your site as well as Search Engine Optimization for <a href="http://www.xoops.org/">XOOPS</a>. If you are looking for work done with this module/application or are looking for development on your site please contact us.</div>';
		return $ret;*/
	}
}
//trabis
xoops_loadLanguage('user');

/**
 * Get {@link XoopsThemeForm} for adding/editing fields
 *
 * @param object $field {@link ProfileField} object to get edit form for
 * @param mixed $action URL to submit to - or false for $_SERVER['PHP_SELF']
 *
 * @return object
 */
function xforum_getFieldForm(&$field, $action = false)
{
	if ( $action === false ) {
		$action = $_SERVER['PHP_SELF'];
	}
	$title = $field->isNew() ? sprintf(_AM_XFORUM_ADD, _AM_XFORUM_FIELD) : sprintf(_AM_XFORUM_EDIT, _AM_XFORUM_FIELD);

	xoops_load('XoopsFormLoader');

	$form = new XoopsThemeForm($title, 'form', $action, 'post', true);

	$form->addElement(new XoopsFormText(_AM_XFORUM_TITLE, 'field_title', 35, 255, $field->getVar('field_title', 'e')));
	$form->addElement(new XoopsFormTextArea(_AM_XFORUM_DESCRIPTION, 'field_description', $field->getVar('field_description', 'e')));

	if (!$field->isNew()) {
		$fieldforum_id = $field->getVar('forum_id');
	} else {
		$fieldforum_id = array(1=>0);
	}
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum');
	$cat_select = new XoopsFormSelect(_AM_XFORUM_FORUM, 'forum_id', $fieldforum_id, 7, true);
	$cat_select->addOption(0, _AM_XFORUM_DEFAULT);
	foreach($forum_handler->getObjects(NULL, true) as $forum_id => $forum)
		$cat_select->addOption($forum_id, $forum->getVar('forum_name'));
	$form->addElement($cat_select);
	$form->addElement(new XoopsFormText(_AM_XFORUM_WEIGHT, 'field_weight', 10, 10, $field->getVar('field_weight', 'e')));
	if ($field->getVar('field_config') || $field->isNew()) {
		if (!$field->isNew()) {
			$form->addElement(new XoopsFormLabel(_AM_Xforum_name, $field->getVar('field_name')));
			$form->addElement(new XoopsFormHidden('id', $field->getVar('field_id')));
		} else {
			$form->addElement(new XoopsFormText(_AM_Xforum_name, 'field_name', 35, 255, $field->getVar('field_name', 'e')));
		}

		//autotext and theme left out of this one as fields of that type should never be changed (valid assumption, I think)
		$fieldtypes = array(
			'checkbox' => _AM_XFORUM_CHECKBOX,
			'date' => _AM_XFORUM_DATE,
			'datetime' => _AM_XFORUM_DATETIME,
			'longdate' => _AM_XFORUM_LONGDATE,
			'group' => _AM_XFORUM_GROUP,
			'group_multi' => _AM_XFORUM_GROUPMULTI,
			'language' => _AM_XFORUM_LANGUAGE,
			'radio' => _AM_XFORUM_RADIO,
			'select' => _AM_XFORUM_SELECT,
			'select_multi' => _AM_XFORUM_SELECTMULTI,
			'textarea' => _AM_XFORUM_TEXTAREA,
			'dhtml' => _AM_XFORUM_DHTMLTEXTAREA,
			'textbox' => _AM_XFORUM_TEXTBOX,
			'timezone' => _AM_XFORUM_TIMEZONE,
			'yesno' => _AM_XFORUM_YESNO);

		$element_select = new XoopsFormSelect(_AM_XFORUM_TYPE, 'field_type', $field->getVar('field_type', 'e'));
		$element_select->addOptionArray($fieldtypes);

		$form->addElement($element_select);

		switch ($field->getVar('field_type')) {
			case "textbox":
				$valuetypes = array(
					XOBJ_DTYPE_ARRAY            => _AM_XFORUM_ARRAY,
					XOBJ_DTYPE_EMAIL            => _AM_XFORUM_EMAIL,
					XOBJ_DTYPE_INT              => _AM_XFORUM_INT,
					XOBJ_DTYPE_FLOAT            => _AM_XFORUM_FLOAT,
					XOBJ_DTYPE_DECIMAL          => _AM_XFORUM_DECIMAL,
					XOBJ_DTYPE_TXTAREA          => _AM_XFORUM_TXTAREA,
					XOBJ_DTYPE_TXTBOX           => _AM_XFORUM_TXTBOX,
					XOBJ_DTYPE_URL              => _AM_XFORUM_URL,
					XOBJ_DTYPE_OTHER    		=> _AM_XFORUM_OTHER,
					XOBJ_DTYPE_UNICODE_ARRAY 	=> _AM_XFORUM_UNICODE_ARRAY,
					XOBJ_DTYPE_UNICODE_TXTBOX 	=> _AM_XFORUM_UNICODE_TXTBOX,
					XOBJ_DTYPE_UNICODE_TXTAREA 	=> _AM_XFORUM_UNICODE_TXTAREA,
					XOBJ_DTYPE_UNICODE_EMAIL 	=> _AM_XFORUM_UNICODE_EMAIL,
					XOBJ_DTYPE_UNICODE_URL 		=> _AM_XFORUM_UNICODE_URL);

				$type_select = new XoopsFormSelect(_AM_XFORUM_VALUETYPE, 'field_valuetype', $field->getVar('field_valuetype', 'e'));
				$type_select->addOptionArray($valuetypes);
				$form->addElement($type_select);
				break;

			case "select":
			case "radio":
				$valuetypes = array(
					XOBJ_DTYPE_ARRAY            => _AM_XFORUM_ARRAY,
					XOBJ_DTYPE_EMAIL            => _AM_XFORUM_EMAIL,
					XOBJ_DTYPE_INT              => _AM_XFORUM_INT,
					XOBJ_DTYPE_FLOAT            => _AM_XFORUM_FLOAT,
					XOBJ_DTYPE_DECIMAL          => _AM_XFORUM_DECIMAL,
					XOBJ_DTYPE_TXTAREA          => _AM_XFORUM_TXTAREA,
					XOBJ_DTYPE_TXTBOX           => _AM_XFORUM_TXTBOX,
					XOBJ_DTYPE_URL              => _AM_XFORUM_URL,
					XOBJ_DTYPE_OTHER            => _AM_XFORUM_OTHER,
					XOBJ_DTYPE_UNICODE_ARRAY    => _AM_XFORUM_UNICODE_ARRAY,
					XOBJ_DTYPE_UNICODE_TXTBOX   => _AM_XFORUM_UNICODE_TXTBOX,
					XOBJ_DTYPE_UNICODE_TXTAREA  => _AM_XFORUM_UNICODE_TXTAREA,
					XOBJ_DTYPE_UNICODE_EMAIL    => _AM_XFORUM_UNICODE_EMAIL,
					XOBJ_DTYPE_UNICODE_URL      => _AM_XFORUM_UNICODE_URL);

				$type_select = new XoopsFormSelect(_AM_XFORUM_VALUETYPE, 'field_valuetype', $field->getVar('field_valuetype', 'e'));
				$type_select->addOptionArray($valuetypes);
				$form->addElement($type_select);
				break;
		}

		//$form->addElement(new XoopsFormRadioYN(_AM_XFORUM_NOTNULL, 'field_notnull', $field->getVar('field_notnull', 'e') ));

		if ($field->getVar('field_type') == "select" || $field->getVar('field_type') == "select_multi" || $field->getVar('field_type') == "radio" || $field->getVar('field_type') == "checkbox") {
			$options = $field->getVar('field_options');
			if (count($options) > 0) {
				$remove_options = new XoopsFormCheckBox(_AM_XFORUM_REMOVEOPTIONS, 'removeOptions');
				$remove_options->columns = 3;
				asort($options);
				foreach (array_keys($options) as $key) {
					$options[$key] .= "[{$key}]";
				}
				$remove_options->addOptionArray($options);
				$form->addElement($remove_options);
			}

			$option_text = "<table  cellspacing='1'><tr><td width='20%'>" . _AM_XFORUM_KEY . "</td><td>" . _AM_XFORUM_VALUE . "</td></tr>";
			for ($i = 0; $i < 3; $i++) {
				$option_text .= "<tr><td><input type='text' name='addOption[{$i}][key]' id='addOption[{$i}][key]' size='15' /></td><td><input type='text' name='addOption[{$i}][value]' id='addOption[{$i}][value]' size='35' /></td></tr>";
				$option_text .= "<tr height='3px'><td colspan='2'> </td></tr>";
			}
			$option_text .= "</table>";
			$form->addElement(new XoopsFormLabel(_AM_XFORUM_ADDOPTION, $option_text) );
		}
	}

	if ($field->getVar('field_edit')) {
		switch ($field->getVar('field_type')) {
			case "textbox":
			case "textarea":
			case "dhtml":
				$form->addElement(new XoopsFormText(_AM_XFORUM_MAXLENGTH, 'field_maxlength', 35, 35, $field->getVar('field_maxlength', 'e')));
				$form->addElement(new XoopsFormTextArea(_AM_XFORUM_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
				break;

			case "checkbox":
			case "select_multi":
				$def_value = $field->getVar('field_default', 'e') != null ? unserialize($field->getVar('field_default', 'n')) : null;
				$element = new XoopsFormSelect(_AM_XFORUM_DEFAULT, 'field_default', $def_value, 8, true);
				$options = $field->getVar('field_options');
				asort($options);
				// If options do not include an empty element, then add a blank option to prevent any default selection
				if (!in_array('', array_keys($options))) {
					$element->addOption('', _NONE);
				}
				$element->addOptionArray($options);
				$form->addElement($element);
				break;

			case "select":
			case "radio":
				$def_value = $field->getVar('field_default', 'e') != null ? $field->getVar('field_default') : null;
				$element = new XoopsFormSelect(_AM_XFORUM_DEFAULT, 'field_default', $def_value);
				$options = $field->getVar('field_options');
				asort($options);
				// If options do not include an empty element, then add a blank option to prevent any default selection
				if (!in_array('', array_keys($options))) {
					$element->addOption('', _NONE);
				}
				$element->addOptionArray($options);
				$form->addElement($element);
				break;

			case "date":
				$form->addElement(new XoopsFormTextDateSelect(_AM_XFORUM_DEFAULT, 'field_default', 15, $field->getVar('field_default', 'e')));
				break;

			case "longdate":
				$form->addElement(new XoopsFormTextDateSelect(_AM_XFORUM_DEFAULT, 'field_default', 15, strtotime($field->getVar('field_default', 'e'))));
				break;

			case "datetime":
				$form->addElement(new XoopsFormDateTime(_AM_XFORUM_DEFAULT, 'field_default', 15, $field->getVar('field_default', 'e')));
				break;

			case "yesno":
				$form->addElement(new XoopsFormRadioYN(_AM_XFORUM_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
				break;

			case "timezone":
				$form->addElement(new XoopsFormSelectTimezone(_AM_XFORUM_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
				break;

			case "language":
				$form->addElement(new XoopsFormSelectLang(_AM_XFORUM_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
				break;

			case "group":
				$form->addElement(new XoopsFormSelectGroup(_AM_XFORUM_DEFAULT, 'field_default', true, $field->getVar('field_default', 'e')));
				break;

			case "group_multi":
				$form->addElement(new XoopsFormSelectGroup(_AM_XFORUM_DEFAULT, 'field_default', true, $field->getVar('field_default', 'e'), 5, true));
				break;

			case "theme":
				$form->addElement(new XoopsFormSelectTheme(_AM_XFORUM_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
				break;

			case "autotext":
				$form->addElement(new XoopsFormTextArea(_AM_XFORUM_DEFAULT, 'field_default', $field->getVar('field_default', 'e')));
				break;
		}
	}

	$groupperm_handler = xoops_gethandler('groupperm');
	$searchable_types = array(
		'textbox',
		'select',
		'radio',
		'yesno',
		'date',
		'datetime',
		'timezone',
		'language');
	if (in_array($field->getVar('field_type'), $searchable_types)) {
		$search_groups = $groupperm_handler->getGroupIds('xforum_search', $field->getVar('field_id'), $GLOBALS['xforumModule']->getVar('mid'));
		$form->addElement(new XoopsFormSelectGroup(_AM_XFORUM_PROF_SEARCH, 'xforum_search', true, $search_groups, 5, true) );
	}
	if ($field->getVar('field_edit') || $field->isNew()) {
		if (!$field->isNew()) {
			//Load groups
			$editable_groups = $groupperm_handler->getGroupIds('xforum_edit', $field->getVar('field_id'), $GLOBALS['xforumModule']->getVar('mid'));
		} else {
			$editable_groups = array();
		}
		$form->addElement(new XoopsFormSelectGroup(_AM_XFORUM_PROF_EDITABLE, 'xforum_edit', false, $editable_groups, 5, true));
		$form->addElement($steps_select);
	}
	$form->addElement(new XoopsFormHidden('op', 'save') );
	$form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

	return $form;
}
/**
* Get {@link XoopsThemeForm} for registering new users
*
* @param object $user {@link XoopsUser} to register
f* //@param int $step Which step we are at
* @param profileRegstep $next_step
*
* @return object
*/
function xforum_getPostsForm(&$user, $profile, $step = null)
{



	$action = $_SERVER['PHP_SELF'];

	return $reg_form;
}

/**
* Get {@link XoopsThemeForm} for editing a user
*
* @param object $user {@link XoopsUser} to edit
*
* @return object
*/
function xforum_getUserSearchForm($action = false)
{
	if ($action === false) {
		$action = $_SERVER['PHP_SELF'];
	}
	if (empty($GLOBALS['xoopsConfigUser'])) {
		$config_handler = xoops_gethandler('config');
		$GLOBALS['xoopsConfigUser'] = $config_handler->getConfigsByCat(XOOPS_CONF_USER);
	}

	include_once($GLOBALS['xoops']->path('/modules/xforum/include/formselectforum.php'));
	$title = _AM_XFORUM_SEARCH;

	$form = new XoopsThemeForm($title, 'search', $action, 'post', true);

	$xforum_handler = xoops_getmodulehandler('profile', 'objects');
	// Get fields
	$fields = $xforum_handler->loadFields();

	$gperm_handler = xoops_gethandler('groupperm');
	$config_handler = xoops_gethandler('config');
	$groups = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
	$module_handler = xoops_gethandler('module');
	$xoModule = $module_handler->getByDirname('objects');
	$modid = $xoModule->getVar('mid');

	// Get ids of fields that can be edited
	$gperm_handler = xoops_gethandler('groupperm');

	$editable_fields = $gperm_handler->getItemIds('xforum_search', $groups, $modid );

	$cat_handler = xoops_getmodulehandler('forum');

	$selcat = new XoopsFormSelectForum('Forum', 'forum_id', (!empty($_REQUEST['forum_id']))?intval($_REQUEST['forum_id']):0, 1, false, false, false, true );
	$selcat->setExtra(' onChange="window.location=\''.XOOPS_URL.'/modules/objects/search.php?op=search&fct=form&forum_id=\'+document.search.forum_id.options[document.search.forum_id.selectedIndex].value"');

	$form->addElement($selcat, true);

	$categories = array();

	$criteria = new CriteriaCompo(new Criteria('forum_id', (!empty($_REQUEST['forum_id']))?intval($_REQUEST['forum_id']):'0'), "OR");
	$all_categories = $cat_handler->getObjects($criteria, true, false);
	$count_fields = count($fields);

	foreach (array_keys($fields) as $i ) {
		if ( in_array($fields[$i]->getVar('field_id'), $editable_fields)  ) {
			// Set default value for user fields if available
			$fieldinfo['element'] = $fields[$i]->getSearchElement();
			$fieldinfo['required'] = false;

			foreach($fields[$i]->getVar('forum_id') as $catidid => $forum_id) {
				if (in_array($forum_id, array_keys($all_categories))) {
					$key = $all_categories[$forum_id]['cat_weight'] * $count_fields + $forum_id;
					$elements[$key][] = $fieldinfo;
					$weights[$key][] = $fields[$i]->getVar('field_weight');
					$categories[$key] = $all_categories[$forum_id];
				} elseif (in_array(0, $fields[$i]->getVar('forum_id'))) {
					$key = $all_categories[$forum_id]['cat_weight'] * $count_fields + $forum_id;
					$elements[$key][] = $fieldinfo;
					$weights[$key][] = $fields[$i]->getVar('field_weight');
					$categories[$key] = $all_categories[$forum_id];
				}
			}
		}
	}

	ksort($elements);
	foreach (array_keys($elements) as $k) {
		array_multisort($weights[$k], SORT_ASC, array_keys($elements[$k]), SORT_ASC, $elements[$k]);
		$title = isset($categories[$k]) ? $categories[$k]['cat_title'] : _OBJS_MF_DEFAULT;
		$desc = isset($categories[$k]) ? $categories[$k]['cat_description'] : "";
		$form->addElement(new XoopsFormLabel("<h3>{$title}</h3>", $desc), false);
		foreach (array_keys($elements[$k]) as $i) {
			$form->addElement($elements[$k][$i]['element'], $elements[$k][$i]['required']);
		}
	}

	$form->addElement(new XoopsFormHidden('fct', 'objects' ));
	$form->addElement(new XoopsFormHidden('op', 'search' ));
	$form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
	return $form;
}


/**
* Get {@link XoopsThemeForm} for editing a user
*
* @param object $user {@link XoopsUser} to edit
*
* @return object
*/
function xforum_getUserForm(&$user, $profile = null, $action = false)
{
	if ($action === false) {
		$action = $_SERVER['PHP_SELF'];
	}
	if (empty($GLOBALS['xoopsConfigUser'])) {
		$config_handler = xoops_gethandler('config');
		$GLOBALS['xoopsConfigUser'] = $config_handler->getConfigsByCat(XOOPS_CONF_USER);
	}

	$title = $user->isNew() ? _AM_XFORUM_ADDUSER : _US_EDITPROFILE;

	$form = new XoopsThemeForm($title, 'object', $action, 'post', true);


	$xforum_handler = xoops_getmodulehandler('profile', 'objects');
	// Dynamic fields
	if (!$profile) {
		$profile = $xforum_handler->get($user->getVar('oid') );
	}
	// Get fields
	$fields = $xforum_handler->loadFields();

	$desc = '<div style="display:block;"><strong>'._OBJS_CREATED.':</strong>'.date(_SHORTDATESTRING, $user->getVar('created')).'</div>';
	$desc .= '<div style="display:block;"><strong>'._OBJS_UPDATED.':</strong>'.date(_SHORTDATESTRING, $user->getVar('updated')).'</div>';

	$form->addElement(new XoopsFormLabel("<h3>".$user->getVar('pux')."</h3>", $desc), false);
	if ($GLOBALS['xforumModuleConfig']['support_tag']&&class_exists('XoopsFormTag'))
		$form->addElement(new XoopsFormTag('tags', 45, 255), true);

	if ($GLOBALS['xforumModuleConfig']['support_multisite']&&class_exists('XoopsFormCheckBoxDomains'))
		$form->addElement(new XoopsFormCheckBoxDomains(_OBJS_MN_DOMAINS, 'domains', $user->getVar('domains')), true);
	else
		if (count($user->getVar('domains'))>0) {
			foreach($user->getVar('domains') as $did => $domain)
				$form->addElement(new XoopsFormHidden('domains['.$did.']', $domain), false);
		} else
			$form->addElement(new XoopsFormHidden('domains[0]', urlencode(XOOPS_URL)), false);

	$form->addElement(new XoopsFormRadioYN(_OBJS_MN_ACTIVE, 'active', $user->getVar('active')));
	$form->addElement(new XoopsFormRadioYN(_OBJS_MN_ACTIONABLE, 'actionable', $user->getVar('actionable')));

	// Get ids of fields that can be edited
	$gperm_handler = xoops_gethandler('groupperm');
	$editable_fields = $gperm_handler->getItemIds('xforum_edit', $GLOBALS['xoopsUser']->getGroups(), $GLOBALS['xforumModule']->getVar('mid') );

	$cat_handler = xoops_getmodulehandler('forum', 'xforum');

	$selcat = new XoopsFormSelectForum(_OBJS_MF_CATEGORY, 'forum_id', (!empty($_REQUEST['forum_id']))?intval($_REQUEST['forum_id']):$user->getVar('forum_id'), 1, false );
	$selcat->setExtra(' onChange="window.location=\''.$_SERVER['PHP_SELF'].'?op=object&fct=edit&oid='.$user->getVar('oid').'&forum_id=\'+document.object.forum_id.options[document.object.forum_id.selectedIndex].value"');

	$form->addElement($selcat, true);

	$form->addElement(new XoopsFormSelectUser(_OBJS_OWNER, 'uid', false, $user->getVar('uid')), true);

	if ($GLOBALS['xoopsUser']->isAdmin())
		$form->addElement(new XoopsFormSelectBroker(_OBJS_BROKER, 'broker_uid', $user->getVar('broker_uid')), true);

	$form->addElement(new XoopsFormSelectStatus(_OBJS_STATUS, 'status', $user->getVar('status')));

	$categories = array();
	$criteria = new CriteriaCompo(new Criteria('forum_id', (!empty($_REQUEST['forum_id']))?intval($_REQUEST['forum_id']):$user->getVar('forum_id')), "OR");
	$all_categories = $cat_handler->getObjects($criteria, true, false);
	$count_fields = count($fields);
	foreach (array_keys($fields) as $i ) {
		if ( in_array($fields[$i]->getVar('field_id'), $editable_fields)  ) {
			// Set default value for user fields if available
			if ($user->isNew()) {
				$default = $fields[$i]->getVar('field_default');
				if ($default !== '' && $default !== null) {
					$user->setVar($fields[$i]->getVar('field_name'), $default);
				}
			}

			$fieldinfo['element'] = $fields[$i]->getEditElement($user, $profile);
			$fieldinfo['required'] = $fields[$i]->getVar('field_required');

			foreach($fields[$i]->getVar('forum_id') as $catidid => $forum_id) {
				if (in_array($forum_id, array_keys($all_categories))||(!empty($_REQUEST['forum_id']))?intval($_REQUEST['forum_id']):$user->getVar('forum_id')==$forum_id) {
					$key = $all_categories[$forum_id]['cat_weight'] * $count_fields + $forum_id;
					$elements[$key][] = $fieldinfo;
					$weights[$key][] = $fields[$i]->getVar('field_weight');
					$categories[$key] = $all_categories[$forum_id];
				} elseif (in_array(0, $fields[$i]->getVar('forum_id'))) {
					$key = $all_categories[0]['cat_weight'] * $count_fields + 0;
					$elements[$key][] = $fieldinfo;
					$weights[$key][] = $fields[$i]->getVar('field_weight');
					$categories[$key] = $all_categories[0];
				}
			}
		}
	}

	ksort($elements);
	foreach (array_keys($elements) as $k) {
		array_multisort($weights[$k], SORT_ASC, array_keys($elements[$k]), SORT_ASC, $elements[$k]);
		$title = isset($categories[$k]) ? $categories[$k]['cat_title'] : _OBJS_MF_DEFAULT;
		$desc = isset($categories[$k]) ? $categories[$k]['cat_description'] : "";
		$form->addElement(new XoopsFormLabel("<h3>{$title}</h3>", $desc), false);
		foreach (array_keys($elements[$k]) as $i) {
			$form->addElement($elements[$k][$i]['element'], $elements[$k][$i]['required']);
		}
	}

	$form->addElement(new XoopsFormHidden('oid', $user->getVar('oid') ));
	$form->addElement(new XoopsFormHidden('fct', 'objects' ));
	$form->addElement(new XoopsFormHidden('op', 'save' ));
	$form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
	return $form;
}


?>