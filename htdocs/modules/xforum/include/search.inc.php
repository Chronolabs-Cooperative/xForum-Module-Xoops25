<?php

// $Id: search.inc.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
require_once(XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php');

function &forum_search($queryarray, $andor, $limit, $offset, $userid, $xforums = 0, $sortby = 0, $searchin = "both", $subquery = "")
{
	static $allowedForums, $xforumConfig;

	$uid = (is_object($GLOBALS['xoopsUser'])&&$GLOBALS['xoopsUser']->isactive())?$GLOBALS['xoopsUser']->getVar('uid'):0;

	if(!isset($allowedForums[$uid])){
		
		$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
		if (is_array($xforums) && count($xforums) > 0) {
			$xforums = array_map('intval', $xforums);
			foreach($xforums as $xforumid){
				
				$_forum = $GLOBALS['forum_handler']->get($xforumid);
				
				if($GLOBALS['forum_handler']->getPermission($_forum)) {
					$allowedForums[$uid][$xforumid] = $_forum;
				}
				
				unset($_forum);
			}
		}
		elseif (is_numeric($xforums) && $xforums > 0) {
			
			$xforumid = $xforums;
			$_forum = $GLOBALS['forum_handler']->get($xforumid);
			
			if($GLOBALS['forum_handler']->getPermission($_forum)) {
				$allowedForums[$uid][$xforumid] = $_forum;
			}
			
			unset($_forum);
		}
		else {
			
			$xforums = $GLOBALS['forum_handler']->getForums();
			
			foreach($xforums as $xforumid => $_forum){
				
				if($GLOBALS['forum_handler']->getPermission($_forum)) {
					$allowedForums[$uid][$xforumid] = $_forum;
				}
				
				unset($_forum);
			}
			unset($xforums);
		}
	}
	$xforum = implode(',',array_keys($allowedForums[$uid]));

 	$sql = 'SELECT p.uid,f.forum_id, p.topic_id, p.poster_name, p.post_time,';
    $sql .= ' f.forum_name, p.post_id, p.subject
            FROM '.$GLOBALS['xoopsDB']->prefix('xf_posts').' p,
            '.$GLOBALS['xoopsDB']->prefix('xf_posts_text').' pt,
    		'.$GLOBALS['xoopsDB']->prefix('xf_forums').' f';
    $sql .= ' WHERE p.post_id = pt.post_id';
    $sql .= ' AND p.approved = 1';
    $sql .= ' AND p.forum_id = f.forum_id';
//                AND p.uid = u.uid'; // In case exists a userid with which the associated user is removed, this line will block search results.  Can do nothing unless xoops changes its way dealing with user removal
    if (!empty($xforum)) {
        $sql .= ' AND f.forum_id IN ('.$xforum.')';
    }

    $isUser=false;
	if ( is_numeric($userid) && $userid != 0 ) {
		$sql .= " AND p.uid=".$userid." ";
    	$isUser=true;
	}else
    // TODO
    if (is_array($userid) && count($userid) > 0) {
		$userid = array_map('intval', $userid);
        $sql .= " AND p.uid IN (".implode(',', $userid).") ";
    	$isUser=true;
    }

	$count = count($queryarray);
	if ( is_array($queryarray) && $count > 0) {
	    switch ($searchin) {
	       case 'title':
	           $sql .= " AND ((p.subject LIKE '%$queryarray[0]%')";
	           for($i=1;$i<$count;$i++){
	               $sql .= " $andor ";
	               $sql .= "(p.subject LIKE '%$queryarray[$i]%')";
	           }
	           $sql .= ") ";
	           break;

	       case 'text':
	           $sql .= " AND ((pt.post_text LIKE '%$queryarray[0]%')";
	           for($i=1;$i<$count;$i++){
	               $sql .= " $andor ";
	               $sql .= "(pt.post_text LIKE '%$queryarray[$i]%')";
	           }
	           $sql .= ") ";
	           break;
	        case 'both' :
	        default;
	           $sql .= " AND ((p.subject LIKE '%$queryarray[0]%' OR pt.post_text LIKE '%$queryarray[0]%')";
	           for($i=1;$i<$count;$i++){
	               $sql .= " $andor ";
	               $sql .= "(p.subject LIKE '%$queryarray[$i]%' OR pt.post_text LIKE '%$queryarray[$i]%')";
	           }
	           $sql .= ") ";
	           break;
		}
	}

	if (!$sortby) {
	    $sortby = "p.post_time DESC";
	}
	$sql .= $subquery." ORDER BY ".$sortby;
	$result = $GLOBALS['xoopsDB']->query($sql,$limit,$offset);
	$ret = array();
	$users = array();
	$i = 0;
 	while($myrow = $GLOBALS['xoopsDB']->fetchArray($result)){
        $ret[$i]['link'] = XOOPS_URL.'/modules/xforum/'."viewtopic.php?topic_id=".$myrow['topic_id']."&amp;forum=".$myrow['forum_id']."&amp;post_id=".$myrow['post_id']."#forumpost".$myrow['post_id'];
		$ret[$i]['title'] = $myrow['subject'];
		$ret[$i]['time'] = $myrow['post_time'];
		$ret[$i]['uid'] = $myrow['uid'];
		$ret[$i]['forum_name'] = $GLOBALS['myts']->htmlSpecialChars($myrow['forum_name']);
		$ret[$i]['forum_link'] = "viewforum.php?forum=".$myrow['forum_id'];
		$users[$myrow['uid']] = 1;
		$ret[$i]['poster_name'] = $myrow['poster_name'];
		$i++;
	}

	if(count($users)>0){
		$member_handler = xoops_gethandler('member');
		$userid_array = array_keys($users);
		$user_criteria = "(".implode(",",$userid_array).")";
		$users = $member_handler->getUsers( new Criteria('uid', $user_criteria, 'IN'), true);
	}else{
		$users = null;
	}

	$module_handler = xoops_gethandler('module');
	$xforum = $module_handler->getByDirname('xforum');

	if(!isset($xforumConfig)){
		$config_handler = xoops_gethandler('config');
		$xforumConfig = $config_handler->getConfigsByCat(0, $xforum->getVar('mid'));
	}

	$count = count($ret);
	for($i =0; $i < $count; $i ++ ){
		if($ret[$i]['uid'] >0){
			if ( isset($users[$ret[$i]['uid']]) && (is_object($users[$ret[$i]['uid']])) && ($users[$ret[$i]['uid']]->isActive()) ){
				$poster = ($xforumConfig['show_realname']&&$users[$ret[$i]['uid']]->getVar('name'))?$users[$ret[$i]['uid']]->getVar('name'):$users[$ret[$i]['uid']]->getVar('uname');
				$poster = $GLOBALS['myts']->htmlSpecialChars($poster);
				$ret[$i]['poster'] = '<a href="'.XOOPS_URL.'/userinfo.php?uid='.$ret[$i]['uid'].'">'.$poster.'</a>';
		    	$title = $GLOBALS['myts']->htmlSpecialChars($ret[$i]['title']);
			}else{
				$ret[$i]['poster'] = $GLOBALS['xoopsConfig']['anonymous'];
				$ret[$i]['uid'] = 0; // Have to force this to fit xoops search.php
			}
		}else{
            $ret[$i]['poster'] = (empty($ret[$i]['poster_name']))?$GLOBALS['xoopsConfig']['anonymous']:$GLOBALS['myts']->htmlSpecialChars($ret[$i]['poster_name']);
			$ret[$i]['uid'] = 0;
		}
	}
	unset($users);

	return $ret;
}
?>