<?php

// $Id: search.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $

include 'header.php';

if ($GLOBALS['xforumModuleConfig']['htaccess']) {
	$url = XOOPS_URL.'/'.$GLOBALS['xforumModuleConfig']['baseurl'].'/search'.$GLOBALS['xforumModuleConfig']['endofurl'].'?'.http_build_query(array_merge($_POST, $_GET));
	if (strpos($url, $_SERVER['REQUEST_URI'])==0) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}

forum_load_lang_file("search");
$config_handler = xoops_gethandler('config');
$GLOBALS['xoopsConfigSearch'] = $config_handler->getConfigsByCat(XOOPS_CONF_SEARCH);
if ($GLOBALS['xoopsConfigSearch']['enable_search'] != 1) {
    header('Location: '.XOOPS_URL.'/modules/xforum/index.php');
    exit();
}

$GLOBALS['xoopsConfig']['module_cache'][$GLOBALS['xforumModule']->getVar('mid')] = 0;
$GLOBALS['xoopsOption']['template_main']= 'xforum_search.html';
include XOOPS_ROOT_PATH.'/header.php';

include_once XOOPS_ROOT_PATH.'/modules/xforum/include/search.inc.php';
$limit = $GLOBALS['xforumModuleConfig']['topics_per_page'];

$queries = array();
$andor = "";
$GLOBALS['start'] = 0;
$uid = 0;
$xforum = 0;
$sortby = 'p.post_time DESC';
$subquery = "";
$searchin = "both";
$sort = "";
$since = isset($_POST['since']) ? $_POST['since'] : (isset($_GET['since']) ? $_GET['since'] : null);
$next_search['since'] = $since;
$term = isset($_POST['term']) ? $_POST['term'] : (isset($_GET['term']) ? $_GET['term'] : null);
$uname = isset($_POST['uname']) ? $_POST['uname'] : (isset($_GET['uname']) ? $_GET['uname'] : null);

if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = xoops_getmodulehandler('online', 'xforum');
	$online_handler->init(0);
}

$GLOBALS['xoopsTpl']->assign("forumindex", sprintf(_MD_XFORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));
$GLOBALS['xoopsTpl']->assign("img_folder", forum_displayImage($GLOBALS['xforumImage']['folder_topic']));

if ( !empty($_POST['submit']) || !empty($_GET['submit']) || !empty($uname) || !empty($term)) {
    $GLOBALS['start'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $xforum = isset($_POST['forum']) ? $_POST['forum'] : (isset($_GET['forum']) ? $_GET['forum'] : null);
    if (empty($xforum) or $xforum == 'all' or (is_array($xforum) and in_array('all', $xforum))) {
       $xforum = array();
    } elseif(!is_array($xforum)){
       $xforum = array_map("intval",explode(",", $xforum));
    }
    $next_search['forum'] = implode(",", $xforum);

    $addterms = isset($_POST['andor']) ? $_POST['andor'] : (isset($_GET['andor']) ? $_GET['andor'] : "");
    $next_search['andor'] = $addterms;

	if ( !in_array(strtolower($addterms), array("or", "and", "exact"))){
	    $andor = "AND";
	}else{
	    $andor = strtoupper($addterms);
	}

    $uname_required = false;
    $search_username = $uname;
    $search_username = trim($search_username);
    $next_search['uname'] = $search_username;
    if ( !empty($search_username) ) {
	    $uname_required = true;
        $search_username = $GLOBALS['myts']->addSlashes($search_username);
        if ( !$result = $GLOBALS['xoopsDB']->query("SELECT uid FROM ".$GLOBALS['xoopsDB']->prefix("users")." WHERE uname LIKE '%$search_username%'") ) {
            redirect_header('search.php',1,_MD_ERROROCCURED);
            exit();
        }
        $uid = array();
        while ($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
            $uid[] = $row['uid'];
        }
    }
    else {
        $uid = 0;
    }

    $next_search['term'] = $term;
    $query = trim($term);

    if ( $andor != "EXACT" ) {
        $ignored_queries = array(); // holds kewords that are shorter than allowed minmum length
        $temp_queries = preg_split('/[\s,]+/', $query);
        foreach ($temp_queries as $q) {
            $q = trim($q);
            if (strlen($q) >= $GLOBALS['xoopsConfigSearch']['keyword_min']) {
                $queries[] = $GLOBALS['myts']->addSlashes($q);
            } else {
                $ignored_queries[] = $GLOBALS['myts']->addSlashes($q);
            }
        }
        if (!$uname_required && count($queries) == 0) {
            redirect_header('search.php', 2, sprintf(_SR_KEYTOOSHORT, $GLOBALS['xoopsConfigSearch']['keyword_min']));
            exit();
        }
    } else {
        //$query = trim($query);
        if (!$uname_required && (strlen($query) < $GLOBALS['xoopsConfigSearch']['keyword_min'])) {
            redirect_header('search.php', 2, sprintf(_SR_KEYTOOSHORT, $GLOBALS['xoopsConfigSearch']['keyword_min']));
            exit();
        }
        $queries = array($GLOBALS['myts']->addSlashes($query));
    }

    // entries must be lowercase
    $allowed = array('p.post_time desc', 't.topic_title', 't.topic_views', 't.topic_replies', 'f.forum_name', 'u.uname');

    $sortby = isset($_POST['sortby']) ? $_POST['sortby'] : (isset($_GET['sortby']) ? $_GET['sortby'] : null);
    $next_search['sortby'] = $sortby;
    $sortby = (in_array(strtolower($sortby), $allowed)) ? $sortby :  'p.post_time DESC';
    $searchin = isset($_POST['searchin']) ? $_POST['searchin'] : (isset($_GET['searchin']) ? $_GET['searchin'] : 'both');
    $next_search['searchin'] = $searchin;
	if (!empty($since)) {
		$subquery = ' AND p.post_time >= ' . (time() - forum_getSinceTime($since));
	}

	if($uname_required&&(!$uid||count($uid)<1)) $result = false;
    else $results = forum_search($queries, $andor, $limit, $GLOBALS['start'], $uid, $xforum, $sortby, $searchin, $subquery);

    if ( count($results) < 1 ) {
        $GLOBALS['xoopsTpl']->assign("lang_nomatch", _SR_NOMATCH);
    }
    else {
        foreach ($results as $row) {
            $GLOBALS['xoopsTpl']->append('results', array('forum_name' => $GLOBALS['myts']->htmlSpecialChars($row['forum_name']), 'forum_link' => $row['forum_link'], 'link' => $row['link'], 'title' => $row['title'], 'poster' => $row['poster'], 'post_time' => formatTimestamp($row['time'], "m")));
        }
        unset($results);

        if(count($next_search)>0){
	        $items = array();
	        foreach($next_search as $para => $val){
		        if(!empty($val)) $items[] = "$para=$val";
	        }
	        if(count($items)>0) $paras = implode("&",$items);
	        unset($next_search);
	        unset($items);
        }
      	$search_url = XOOPS_URL.'/modules/'.$GLOBALS['xforumModule']->getVar('dirname')."/search.php?".$paras;

       	$next_results = forum_search($queries, $andor, 1, $GLOBALS['start'] + $limit, $uid, $xforum, $sortby, $searchin, $subquery);
        $next_count = count($next_results);
        $has_next = false;
        if (is_array($next_results) && $next_count >0) {
            $has_next = true;
        }
        if (false != $has_next) {
            $next = $GLOBALS['start'] + $limit;
            $queries = implode(',',$queries);
            $search_url_next = $search_url."&start=$next";
            $search_next = '<a href="'.htmlspecialchars($search_url_next).'">'._SR_NEXT.'</a>';
			$GLOBALS['xoopsTpl']->assign("search_next", $search_next);
        }
        if ( $GLOBALS['start'] > 0 ) {
            $prev = $GLOBALS['start'] - $limit;
            $search_url_prev = $search_url."&start=$prev";
            $search_prev = '<a href="'.htmlspecialchars($search_url_prev).'">'._SR_PREVIOUS.'</a>';
			$GLOBALS['xoopsTpl']->assign("search_prev", $search_prev);
        }
    }

	$search_info = _SR_KEYWORDS.": ".$GLOBALS['myts']->htmlSpecialChars($term);
    if($uname_required){
	    if($search_info) $search_info .= "<br />";
	    $search_info .= _MD_USERNAME.": ".$GLOBALS['myts']->htmlSpecialChars($search_username);
	}
	$GLOBALS['xoopsTpl']->assign("search_info", $search_info);
}

$xforumperms = xoops_getmodulehandler('permission', 'xforum');
$allowed_forums = $xforumperms->getPermissions('forum');

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
$forum_array = $GLOBALS['forum_handler']->getForums();

$select_forum = '<select name="forum[]" size="5" multiple="multiple">';
$select_forum .= '<option value="all">'._MD_SEARCHALLFORUMS.'</option>';
foreach ($forum_array as $key => $xforum) {
    if (in_array($xforum->getVar('forum_id'), array_keys($allowed_forums))) {
        $select_forum .= '<option value="'.$xforum->getVar('forum_id').'">'.$xforum->getVar('forum_name').'</option>';
    }
}
$select_forum .= '</select>';
$GLOBALS['xoopsTpl']->assign_by_ref("forum_selection_box", $select_forum);
$select_since = forum_sinceSelectBox($GLOBALS['xforumModuleConfig']['since_default']);
$GLOBALS['xoopsTpl']->assign_by_ref("since_selection_box", $select_since);

if ($GLOBALS['xoopsConfigSearch']['keyword_min'] > 0) {
	$GLOBALS['xoopsTpl']->assign("search_rule", sprintf(_SR_KEYIGNORE, $GLOBALS['xoopsConfigSearch']['keyword_min']));
}

include XOOPS_ROOT_PATH.'/footer.php';
?>