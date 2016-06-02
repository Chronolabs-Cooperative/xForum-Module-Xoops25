<?php

// $Id: forum_block.php,v 4.03 2008/06/05 16:23:31 wishcraft Exp $

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
require_once(XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php');
if(defined('FORUM_BLOCK_DEFINED')) return;
define('FORUM_BLOCK_DEFINED',true);

function b_xforum_array_filter($var){
	return $var > 0;
}

// options[0] - Citeria valid: time(by default)
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_xforum_show($options)
{
    
    global $access_forums;

    $GLOBALS['xoopsDB'] = Database::getInstance();
    $GLOBALS['myts'] = MyTextSanitizer::getInstance();
    $block = array();
    $i = 0;
    $GLOBALS['order'] = "";
    $extra_criteria = "";
	if(!empty($options[2])) {
		$time_criteria = time() - forum_getSinceTime($options[2]);
		$extra_criteria = " AND p.post_time>".$time_criteria;
	}
    $time_criteria = null;
    switch ($options[0]) {
        case 'time':
        default:
            $GLOBALS['order'] = 'p.post_time';
    		$extra_criteria .= " AND p.approved=1";
            break;
    }
    $xforumConfig = getConfigForBlock();
    			
    if(!isset($access_forums)){
	    $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
    	if(!$access_obj = $GLOBALS['forum_handler']->getForums(0, 'access', array('forum_id', 'cat_id', 'forum_type')) ){
	    	return null;
    	}
    	$access_forums = array_keys( $access_obj ); // get all accessible forums
    	unset($access_obj );
	}
    if (!empty($options[6])) {
        $allowedforums = array_filter(array_slice($options, 6), "b_xforum_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    }else{
        $allowed_forums = $access_forums;
    }

    $forum_criteria = ' AND t.forum_id IN (' . implode(',', $allowed_forums) . ')';
    $approve_criteria = ' AND t.approved = 1';

    $query = 'SELECT'.
    		'	DISTINCT t.topic_id, t.topic_replies, t.forum_id, t.topic_title, t.topic_views, t.topic_subject,'.
    		'	f.forum_name, f.allow_subject_prefix,'.
    		'	p.post_id, p.post_time, p.icon, p.uid, p.poster_name'.
    		'	FROM ' . $GLOBALS['xoopsDB']->prefix('xf_posts') . ' AS p '.
    		'	LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('xf_topics') . ' AS t ON t.topic_last_post_id=p.post_id'.
    		'	LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('xf_forums') . ' AS f ON f.forum_id=t.forum_id'.
    		'	WHERE 1=1 ' .
    			$forum_criteria .
    			$approve_criteria .
    			$extra_criteria .
    			' ORDER BY ' . $GLOBALS['order'] . ' DESC';

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);
    if (!$result) {
	    forum_message("xforum block query error: ".$query);
        return false;
    }
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows = array();
    $author = array();
    while ($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
        $rows[] = $row;
        $author[$row["uid"]] = 1;
    }
    if (count($rows) < 1) return false;
	$author_name = forum_getUnameFromIds(array_keys($author), $xforumConfig['show_realname'], true);

    foreach ($rows as $arr) {
        $topic_page_jump = '';
        if ($arr['allow_subject_prefix']) {
            $subjectpres = explode(',', $xforumConfig['subject_prefix']);
            if (count($subjectpres) > 1) {
                foreach($subjectpres as $subjectpre) {
                    $subject_array[] = $subjectpre;
                }
               	$subject_array[0] = null;
            }
            $topic['topic_subject'] = $subject_array[$arr['topic_subject']];
        } else {
            $topic['topic_subject'] = "";
        }
        $topic['post_id'] = $arr['post_id'];
        $topic['forum_id'] = $arr['forum_id'];
        $topic['forum_name'] = $GLOBALS['myts']->htmlSpecialChars($arr['forum_name']);
        $topic['id'] = $arr['topic_id'];

        $title = $GLOBALS['myts']->htmlSpecialChars($arr['topic_title']);
        if(!empty($options[5])){
        	$title = xoops_substr($title, 0, $options[5]);
    	}
        $topic['title'] = $title;
        $topic['replies'] = $arr['topic_replies'];
        $topic['views'] = $arr['topic_views'];
        $topic['time'] = forum_formatTimestamp($arr['post_time']);
        if (!empty($author_name[$arr['uid']])) {
        	$topic_poster = $author_name[$arr['uid']];
        } else {
            $topic_poster = $GLOBALS['myts']->htmlSpecialChars( ($arr['poster_name'])?$arr['poster_name']:$GLOBALS["xoopsConfig"]["anonymous"] );
        }
        $topic['topic_poster'] = $topic_poster;
        $topic['topic_page_jump'] = $topic_page_jump;
        $block['topics'][] = $topic;
        unset($topic);
    }
    $block['indexNav'] = intval($options[4]);

    return $block;
}

// options[0] - Citeria valid: time(by default), views, replies, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_xforum_topic_show($options)
{
    
    global $access_forums;

    $GLOBALS['xoopsDB'] = Database::getInstance();
    $GLOBALS['myts'] = MyTextSanitizer::getInstance();
    $block = array();
    $i = 0;
    $GLOBALS['order'] = "";
    $extra_criteria = "";
    $time_criteria = null;
	if(!empty($options[2])) {
		$time_criteria = time() - forum_getSinceTime($options[2]);
		$extra_criteria = " AND t.topic_time>".$time_criteria;
	}
    switch ($options[0]) {
        case 'views':
            $GLOBALS['order'] = 't.topic_views';
            break;
        case 'replies':
            $GLOBALS['order'] = 't.topic_replies';
            break;
        case 'digest':
            $GLOBALS['order'] = 't.digest_time';
    		$extra_criteria = " AND t.topic_digest=1";
    		if($time_criteria)
    		$extra_criteria .= " AND t.digest_time>".$time_criteria;
            break;
        case 'sticky':
            $GLOBALS['order'] = 't.topic_time';
    		$extra_criteria .= " AND t.topic_sticky=1";
            break;
        case 'time':
        default:
            $GLOBALS['order'] = 't.topic_time';
            break;
    }
	$xforumConfig = getConfigForBlock();

    if(!isset($access_forums)){
	    $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
    	if(!$access_obj = $GLOBALS['forum_handler']->getForums(0, 'access', array('forum_id', 'cat_id', 'forum_type')) ){
	    	return null;
    	}
    	$access_forums = array_keys( $access_obj ); // get all accessible forums
    	unset($access_obj );
	}

    if (!empty($options[6])) {
        $allowedforums = array_filter(array_slice($options, 6), "b_xforum_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    }else{
        $allowed_forums = $access_forums;
    }

    $forum_criteria = ' AND t.forum_id IN (' . implode(',', $allowed_forums) . ')';
    $approve_criteria = ' AND t.approved = 1';

    $query = 'SELECT'.
    		'	t.topic_id, t.topic_replies, t.forum_id, t.topic_title, t.topic_views, t.topic_subject, t.topic_time, t.topic_poster, t.poster_name,'.
    		'	f.forum_name, f.allow_subject_prefix'.
    		'	FROM ' . $GLOBALS['xoopsDB']->prefix('xf_topics') . ' AS t '.
    		'	LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('xf_forums') . ' AS f ON f.forum_id=t.forum_id'.
    		'	WHERE 1=1 ' .
    			$forum_criteria .
    			$approve_criteria .
    			$extra_criteria .
    			' ORDER BY ' . $GLOBALS['order'] . ' DESC';

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);
    if (!$result) {
	    forum_message("xforum block query error: ".$query);
        return false;
    }
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows = array();
    $author = array();
    while ($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
        $rows[] = $row;
        $author[$row["topic_poster"]] = 1;
    }
    if (count($rows) < 1) return false;
	$author_name = forum_getUnameFromIds(array_keys($author), $xforumConfig['show_realname'], true);

    foreach ($rows as $arr) {
        $topic_page_jump = '';
        if ($arr['allow_subject_prefix']) {
            $subjectpres = explode(',', $xforumConfig['subject_prefix']);
            if (count($subjectpres) > 1) {
                foreach($subjectpres as $subjectpre) {
                    $subject_array[] = $subjectpre;
                }
               	$subject_array[0] = null;
            }
            $topic['topic_subject'] = $subject_array[$arr['topic_subject']];
        } else {
            $topic['topic_subject'] = "";
        }
        $topic['forum_id'] = $arr['forum_id'];
        $topic['forum_name'] = $GLOBALS['myts']->htmlSpecialChars($arr['forum_name']);
        $topic['id'] = $arr['topic_id'];

        $title = $GLOBALS['myts']->htmlSpecialChars($arr['topic_title']);
        if(!empty($options[5])){
        	$title = xoops_substr($title, 0, $options[5]);
    	}
        $topic['title'] = $title;
        $topic['replies'] = $arr['topic_replies'];
        $topic['views'] = $arr['topic_views'];
        $topic['time'] = forum_formatTimestamp($arr['topic_time']);
        if (!empty($author_name[$arr['topic_poster']])) {
        	$topic_poster = $author_name[$arr['topic_poster']];
        } else {
            $topic_poster = $GLOBALS['myts']->htmlSpecialChars( ($arr['poster_name'])?$arr['poster_name']:$GLOBALS["xoopsConfig"]["anonymous"] );
        }
        $topic['topic_poster'] = $topic_poster;
        $topic['topic_page_jump'] = $topic_page_jump;
        $block['topics'][] = $topic;
        unset($topic);
    }
    $block['indexNav'] = intval($options[4]);

    return $block;
}

// options[0] - Citeria valid: title(by default), text
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view; Only valid for "time"
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title/Text Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_xforum_post_show($options)
{
    
    global $access_forums;

    $GLOBALS['xoopsDB'] = Database::getInstance();
    $GLOBALS['myts'] = MyTextSanitizer::getInstance();
    $block = array();
    $i = 0;
    $GLOBALS['order'] = "";
    $extra_criteria = "";
    $time_criteria = null;
	if(!empty($options[2])) {
		$time_criteria = time() - forum_getSinceTime($options[2]);
		$extra_criteria = " AND p.post_time>".$time_criteria;
	}
    
    switch ($options[0]) {
	    case "text":
		    if(!empty($xforumConfig['enable_karma']))
				$extra_criteria .= " AND p.post_karma = 0";
		    if(!empty($xforumConfig['allow_require_reply']))
				$extra_criteria .= " AND p.require_reply = 0";	    
        default:
            $GLOBALS['order'] = 'p.post_time';
            break;
    }
    $xforumConfig = getConfigForBlock();

    if(!isset($access_forums)){
	    $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
    	if(!$access_obj = $GLOBALS['forum_handler']->getForums(0, 'access', array('forum_id', 'cat_id', 'forum_type')) ){
	    	return null;
    	}
    	$access_forums = array_keys( $access_obj ); // get all accessible forums
    	unset($access_obj );
	}

    if (!empty($options[6])) {
        $allowedforums = array_filter(array_slice($options, 6), "b_xforum_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    }else{
        $allowed_forums = $access_forums;
    }

    $forum_criteria = ' AND p.forum_id IN (' . implode(',', $allowed_forums) . ')';
    $approve_criteria = ' AND p.approved = 1';

    $query = 'SELECT';
    $query .= '	p.post_id, p.subject, p.post_time, p.icon, p.uid, p.poster_name,';
    if($options[0]=="text"){
    	$query .= '	p.dohtml, p.dosmiley, p.doxcode, p.dobr, pt.post_text,';    
	}
    $query .= '	f.forum_id, f.forum_name, f.allow_subject_prefix'.
    		'	FROM ' . $GLOBALS['xoopsDB']->prefix('xf_posts') . ' AS p '.
    		'	LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('xf_forums') . ' AS f ON f.forum_id=p.forum_id';
    if($options[0]=="text"){
    	$query .= '	LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('xf_posts_text') . ' AS pt ON pt.post_id=p.post_id';
	}
    $query .= '	WHERE 1=1 ' .
    			$forum_criteria .
    			$approve_criteria .
    			$extra_criteria .
    			' ORDER BY ' . $GLOBALS['order'] . ' DESC';

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);
    if (!$result) {
	    forum_message("xforum block query error: ".$query);
        return false;
    }
    $block['disp_mode'] = ($options[0]=="text")?3:$options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows = array();
    $author = array();
    while ($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
        $rows[] = $row;
        $author[$row["uid"]] = 1;
    }
    if (count($rows) < 1) return false;
	$author_name = forum_getUnameFromIds(array_keys($author), $xforumConfig['show_realname'], true);

    foreach ($rows as $arr) {
		//if ($arr['icon'] && is_file(XOOPS_ROOT_PATH . "/images/subject/" . $arr['icon'])) {
		if (!empty($arr['icon'])) {
            $last_post_icon = '<img src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($arr['icon']) . '" alt="" />';
        } else {
            $last_post_icon = '<img src="' . XOOPS_URL . '/images/subject/icon1.gif" alt="" />';
        }
        //$topic['jump_post'] = "<a href='" . XOOPS_URL . "/modules/xforum/viewtopic.php?post_id=" . $arr['post_id'] ."#forumpost" . $arr['post_id'] . "'>" . $last_post_icon . "</a>";
        $topic['forum_id'] = $arr['forum_id'];
        $topic['forum_name'] = $GLOBALS['myts']->htmlSpecialChars($arr['forum_name']);
        //$topic['id'] = $arr['topic_id'];

        $title = $GLOBALS['myts']->htmlSpecialChars($arr['subject']);
        if($options[0]!="text" && !empty($options[5])) {
	        $title = xoops_substr($title, 0, $options[5]);
        }
        $topic['title'] = $title;
        $topic['post_id'] = $arr['post_id'];
        $topic['time'] = forum_formatTimestamp($arr['post_time']);
        if (!empty($author_name[$arr['uid']])) {
        	$topic_poster = $author_name[$arr['uid']];
        } else {
            $topic_poster = $GLOBALS['myts']->htmlSpecialChars( ($arr['poster_name'])?$arr['poster_name']:$GLOBALS["xoopsConfig"]["anonymous"] );
        }
        $topic['topic_poster'] = $topic_poster;
    	
        if($options[0]=="text"){
	        $post_text = $GLOBALS['myts']->displayTarea($arr['post_text'],$arr['dohtml'],$arr['dosmiley'],$arr['doxcode'],1,$arr['dobr']);
        	if(!empty($options[5])){
	    		$post_text = xoops_substr(forum_html2text($post_text), 0, $options[5]);
    		}
        	$topic['post_text'] = $post_text;
        }        
        
        $block['topics'][] = $topic;
        unset($topic);
    }
    $block['indexNav'] = intval($options[4]);
    return $block;
}

// options[0] - Citeria valid: post(by default), topic, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_xforum_author_show($options)
{
    
    global $access_forums;

    $GLOBALS['xoopsDB'] = Database::getInstance();
    $GLOBALS['myts'] = MyTextSanitizer::getInstance();
    $block = array();
    $i = 0;
    $type = "topic";
    $GLOBALS['order'] = "count";
    $extra_criteria = "";
    $time_criteria = null;
	if(!empty($options[2])) {
		$time_criteria = time() - forum_getSinceTime($options[2]);
		$extra_criteria = " AND topic_time>".$time_criteria;
	}
    switch ($options[0]) {
        case 'topic':
            break;
        case 'digest':
    		$extra_criteria = " AND topic_digest=1";
    		if($time_criteria)
    		$extra_criteria .= " AND digest_time>".$time_criteria;
            break;
        case 'sticky':
    		$extra_criteria .= " AND topic_sticky=1";
            break;
        case 'post':
        default:
        	$type = "post";
    		if($time_criteria)
			$extra_criteria = " AND post_time>".$time_criteria;
            break;
    }
    $xforumConfig = getConfigForBlock();

    if(!isset($access_forums)){
	    $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
    	if(!$access_obj = $GLOBALS['forum_handler']->getForums(0, 'access', array('forum_id', 'cat_id', 'forum_type')) ){
	    	return null;
    	}
    	$access_forums = array_keys( $access_obj ); // get all accessible forums
    	unset($access_obj );
	}

    if (!empty($options[5])) {
        $allowedforums = array_filter(array_slice($options, 5), "b_xforum_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    }else{
        $allowed_forums = $access_forums;
    }

    if($type=="topic"){
	    $forum_criteria = ' AND forum_id IN (' . implode(',', $allowed_forums) . ')';
	    $approve_criteria = ' AND approved = 1';
	    $query = 'SELECT DISTINCT topic_poster AS author, COUNT(*) AS count
	    			FROM ' . $GLOBALS['xoopsDB']->prefix('xf_topics') . '
	    			WHERE topic_poster>0 ' .
	    			$forum_criteria .
	    			$approve_criteria .
	    			$extra_criteria .
	    			' GROUP BY topic_poster ORDER BY ' . $GLOBALS['order'] . ' DESC';
	}else{
	    $forum_criteria = ' AND forum_id IN (' . implode(',', $allowed_forums) . ')';
	    $approve_criteria = ' AND approved = 1';
	    $query = 'SELECT DISTINCT uid AS author, COUNT(*) AS count
	    			FROM ' . $GLOBALS['xoopsDB']->prefix('xf_posts') . '
	    			WHERE uid>0 ' .
	    			$forum_criteria .
	    			$approve_criteria .
	    			$extra_criteria .
	    			' GROUP BY uid ORDER BY ' . $GLOBALS['order'] . ' DESC';
	}

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);
    if (!$result) {
	    forum_message("xforum block query error: ".$query);
        return false;
    }
    $author = array();
    while ($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
	    $author[$row["author"]]["count"] = $row["count"];
    }
    if (count($author) < 1) return false;
	$author_name = forum_getUnameFromIds(array_keys($author), $xforumConfig['show_realname']);
	foreach(array_keys($author) as $uid){
		$author[$uid]["name"] = $GLOBALS['myts']->htmlSpecialChars($author_name[$uid]);
	}
    $block['authors'] = $author;
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - lite view;
    $block['indexNav'] = intval($options[4]);
    return $block;
}

function b_xforum_edit($options)
{
    $form  = _MB_XFORUM_CRITERIA."<select name='options[0]'>";
    $form .= "<option value='time'";
    if($options[0]=="time") $form .= " selected='selected' ";
    $form .= ">"._MB_XFORUM_CRITERIA_TIME."</option>";
    $form .= "</select>";
    $form .= "<br />" . _MB_XFORUM_DISPLAY."<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_XFORUM_TIME."<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_XFORUM_TIME_DESC. "</small>";
    $form .= "<br />" . _MB_XFORUM_DISPLAYMODE. "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if ($options[3] == 2) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_XFORUM_INDEXNAV."<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />"._YES."<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />"._NO;

    $form .= "<br />" . _MB_XFORUM_TITLE_LENGTH."<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= "<br /><br />" . _MB_XFORUM_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 6), "b_xforum_array_filter"); // get allowed forums
    $isAll = (count($options_forum)==0||empty($options_forum[0]))?true:false;
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected=\"selected\"";
    $form .= ">"._ALL."</option>";
    $form .= forum_forumSelectBox($options_forum);
    /*
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$xforums = $GLOBALS['forum_handler']->getForumsByCategory(0, '', false);
	foreach (array_keys($xforums) as $c) {
		foreach(array_keys($xforums[$c]) as $f){
        	$sel = ($isAll || in_array($f, $options_forum))?" selected=\"selected\"":"";
        	$form .= "<option value=\"$f\" $sel>".$xforums[$c][$f]["title"]."</option>";
	        if(!isset($xforums[$c][$f]["sub"])) continue;
			foreach(array_keys($xforums[$c][$f]["sub"]) as $s){
        		$sel = ($isAll || in_array($s, $options_forum))?" selected=\"selected\"":"";
        		$form .= "<option value=\"$s\" $sel>-- ".$xforums[$c][$f]["sub"][$s]["title"]."</option>";
			}
		}
	}
    unset($xforums);
    */
    $form .= "</select><br />";

    return $form;
}

function b_xforum_topic_edit($options)
{
    $form  = _MB_XFORUM_CRITERIA."<select name='options[0]'>";
    $form .= "<option value='time'";
	    if($options[0]=="time") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_TIME."</option>";
    $form .= "<option value='views'";
	    if($options[0]=="views") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_VIEWS."</option>";
    $form .= "<option value='replies'";
	    if($options[0]=="replies") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_REPLIES."</option>";
    $form .= "<option value='digest'";
	    if($options[0]=="digest") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_DIGEST."</option>";
    $form .= "<option value='sticky'";
	    if($options[0]=="sticky") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_STICKY."</option>";
    $form .= "</select>";
    $form .= "<br />" . _MB_XFORUM_DISPLAY."<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_XFORUM_TIME."<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_XFORUM_TIME_DESC. "</small>";
    $form .= "<br />" . _MB_XFORUM_DISPLAYMODE. "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if ($options[3] == 2) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_XFORUM_INDEXNAV."<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />"._YES."<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />"._NO;

    $form .= "<br />" . _MB_XFORUM_TITLE_LENGTH."<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= "<br /><br />" . _MB_XFORUM_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 6), "b_xforum_array_filter"); // get allowed forums
    $isAll = (count($options_forum)==0||empty($options_forum[0]))?true:false;
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected=\"selected\"";
    $form .= ">"._ALL."</option>";
    $form .= forum_forumSelectBox($options_forum);
	/*    
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$xforums = $GLOBALS['forum_handler']->getForumsByCategory(0, '', false);
	foreach (array_keys($xforums) as $c) {
		foreach(array_keys($xforums[$c]) as $f){
        	$sel = ($isAll || in_array($f, $options_forum))?" selected=\"selected\"":"";
        	$form .= "<option value=\"$f\" $sel>".$xforums[$c][$f]["title"]."</option>";
	        if(!isset($xforums[$c][$f]["sub"])) continue;
			foreach(array_keys($xforums[$c][$f]["sub"]) as $s){
        		$sel = ($isAll || in_array($s, $options_forum))?" selected=\"selected\"":"";
        		$form .= "<option value=\"$s\" $sel>-- ".$xforums[$c][$f]["sub"][$s]["title"]."</option>";
			}
		}
	}
    unset($xforums);
    */
    $form .= "</select><br />";

    return $form;
}

function b_xforum_post_edit($options)
{
    $form  = _MB_XFORUM_CRITERIA."<select name='options[0]'>";
    $form .= "<option value='title'";
	    if($options[0]=="title") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_TITLE."</option>";
    $form .= "<option value='text'";
	    if($options[0]=="text") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_TEXT."</option>";
    $form  .= "</select>";
    $form .= "<br />" . _MB_XFORUM_DISPLAY."<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_XFORUM_TIME."<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_XFORUM_TIME_DESC. "</small>";
    $form .= "<br />" . _MB_XFORUM_DISPLAYMODE. "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if ($options[3] == 2) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_XFORUM_INDEXNAV."<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />"._YES."<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />"._NO;

    $form .= "<br />" . _MB_XFORUM_TITLE_LENGTH."<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= "<br /><br />" . _MB_XFORUM_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 6), "b_xforum_array_filter"); // get allowed forums
    $isAll = (count($options_forum)==0||empty($options_forum[0]))?true:false;
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected=\"selected\"";
    $form .= ">"._ALL."</option>";
    $form .= forum_forumSelectBox($options_forum);
    /*
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$xforums = $GLOBALS['forum_handler']->getForumsByCategory(0, '', false);
	foreach (array_keys($xforums) as $c) {
		foreach(array_keys($xforums[$c]) as $f){
        	$sel = ($isAll || in_array($f, $options_forum))?" selected=\"selected\"":"";
        	$form .= "<option value=\"$f\" $sel>".$xforums[$c][$f]["title"]."</option>";
	        if(!isset($xforums[$c][$f]["sub"])) continue;
			foreach(array_keys($xforums[$c][$f]["sub"]) as $s){
        		$sel = ($isAll || in_array($s, $options_forum))?" selected=\"selected\"":"";
        		$form .= "<option value=\"$s\" $sel>-- ".$xforums[$c][$f]["sub"][$s]["title"]."</option>";
			}
		}
	}
    unset($xforums);
    */
    $form .= "</select><br />";

    return $form;
}

function b_xforum_author_edit($options)
{
    $form  = _MB_XFORUM_CRITERIA."<select name='options[0]'>";
    $form .= "<option value='post'";
	    if($options[0]=="post") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_POST."</option>";
    $form .= "<option value='topic'";
	    if($options[0]=="topic") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_TOPIC."</option>";
    $form .= "<option value='digest'";
	    if($options[0]=="digest") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_DIGESTS."</option>";
    $form .= "<option value='sticky'";
	    if($options[0]=="sticky") $form .= " selected='selected' ";
	    $form .= ">"._MB_XFORUM_CRITERIA_STICKYS."</option>";
    $form .= "</select>";
    $form .= "<br />" . _MB_XFORUM_DISPLAY."<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_XFORUM_TIME."<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_XFORUM_TIME_DESC. "</small>";
    $form .= "<br />" . _MB_XFORUM_DISPLAYMODE. "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_XFORUM_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_XFORUM_INDEXNAV."<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />"._YES."<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />"._NO;

    $form .= "<br /><br />" . _MB_XFORUM_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 5), "b_xforum_array_filter"); // get allowed forums
    $isAll = (count($options_forum)==0||empty($options_forum[0]))?true:false;
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected=\"selected\"";
    $form .= ">"._ALL."</option>";
    $form .= forum_forumSelectBox($options_forum);
    /*
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$xforums = $GLOBALS['forum_handler']->getForumsByCategory(0, '', false);
	foreach (array_keys($xforums) as $c) {
		foreach(array_keys($xforums[$c]) as $f){
        	$sel = ($isAll || in_array($f, $options_forum))?" selected=\"selected\"":"";
        	$form .= "<option value=\"$f\" $sel>".$xforums[$c][$f]["title"]."</option>";
	        if(!isset($xforums[$c][$f]["sub"])) continue;
			foreach(array_keys($xforums[$c][$f]["sub"]) as $s){
        		$sel = ($isAll || in_array($s, $options_forum))?" selected=\"selected\"":"";
        		$form .= "<option value=\"$s\" $sel>-- ".$xforums[$c][$f]["sub"][$s]["title"]."</option>";
			}
		}
	}
    unset($xforums);
    */
    $form .= "</select><br />";

    return $form;
}

function b_xforum_tag_block_cloud_show($options) 
{
    include_once XOOPS_ROOT_PATH . "/modules/tag/blocks/block.php";
    return tag_block_cloud_show($options, 'xforum');
}
function b_xforum_tag_block_cloud_edit($options) 
{
    include_once XOOPS_ROOT_PATH . "/modules/tag/blocks/block.php";
    return tag_block_cloud_edit($options);
}
function b_xforum_tag_block_top_show($options) 
{
    include_once XOOPS_ROOT_PATH . "/modules/tag/blocks/block.php";
    return tag_block_top_show($options, 'xforum');
}
function b_xforum_tag_block_top_edit($options) 
{
    include_once XOOPS_ROOT_PATH . "/modules/tag/blocks/block.php";
    return tag_block_top_edit($options);
}
?>