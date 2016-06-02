<?php

	// 	migratetoxforum.php - For Migration between CBB and XForum
	
	include('mainfile.php');
	
	$sql = array();
	
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_archive") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_attachments") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_categories") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_digest") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_forums") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_moderates") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_online") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_posts") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_posts_text") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_reads_forum") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_reads_topic") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_report") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_topics") . "`";
	$sql[] = "TRUNCATE " . "`" . $GLOBALS['xoopsDB']->prefix("xf_votedata") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_archive") . "` (`topic_id`, `post_id`, `post_text`) SELECT * FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_archive") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_attachments") . "` (`attach_id`, `post_id`, `name_saved`, `name_disp`, `mimetype`, `online`, `attach_time`, `download`) SELECT * FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_attachments") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_categories") . "` (`cat_id`, `cat_image`, `cat_title`, `cat_description`, `cat_order`, `cat_url`, `cat_domain`, `cat_domains`, `cat_languages`) SELECT (`cat_id`, `cat_image`, `cat_title`, `cat_description`, `cat_order`, `cat_url`, '".$_SERVER['HTTP_HOST']."', '".serialize(array($_SERVER['HTTP_HOST']))."', '".serialize(array($GLOBALS['xoopsConfig']['language']))."') FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_attachments") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_digest") . "` (`digest_id`, `digest_time`, `digest_content`) SELECT * FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_digest") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_forums") . "` (`forum_id`, `forum_name`, `forum_desc`, `parent_forum`, `forum_moderator`, `forum_topics`, `forum_posts`, `forum_last_post_id`, `cat_id`, `hot_threshold`, `forum_order`, `attach_maxkb`, `attach_ext`, `allow_polls`, `domain`, `domains`, `languages`) SELECT (`forum_id`, `forum_name`, `forum_desc`, `parent_forum`, `forum_moderator`, `forum_topics`, `forum_posts`, `forum_last_post_id`, `cat_id`, `hot_threshold`, `forum_order`, `attach_maxkb`, `attach_ext`, `allow_polls`, '".$_SERVER['HTTP_HOST']."', '".serialize(array($_SERVER['HTTP_HOST']))."', '".serialize(array($GLOBALS['xoopsConfig']['language']))."') FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_forums") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_moderates") . "` (`mod_id`, `mod_start`, `mod_end`, `mod_desc`, `uid`, `ip`, `forum_id`) SELECT * FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_moderates") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_online") . "` (`online_forum`, `online_topic`, `online_uid`, `online_uname`, `online_ip`, `online_updated`) SELECT * FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_online") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_posts") . "` (`post_id`, `pid`, `topic_id`, `forum_id`, `post_time`, `uid`, `poster_name`, `poster_ip`, `subject`, `icon`, `attachsig`, `approved`, `post_karma`, `attachment`, `require_reply`) SELECT (`post_id`, `pid`, `topic_id`, `forum_id`, `post_time`, `uid`, `poster_name`, `poster_ip`, `subject`, `icon`, `attachsig`, `approved`, `post_karma`, `attachment`, `require_reply`) FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_posts") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_posts_text") . "` (`post_id`, `post_text`, `post_edit`) SELECT (`post_id`, `post_text`, `post_edit`) FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_posts_text") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_reads_forum") . "` (`read_id`, `uid`, `read_time`, `read_item`, `post_id`) SELECT (`read_id`, `uid`, `read_time`, `read_item`, `post_id`) FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_reads_forum") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_reads_topic") . "` (`read_id`, `uid`, `read_time`, `read_item`, `post_id`) SELECT (`read_id`, `uid`, `read_time`, `read_item`, `post_id`) FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_reads_topic") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_report") . "` (`report_id`, `post_id`, `reporter_uid`, `reporter_ip`, `report_text`, `report_result`, `report_memo`) SELECT (`report_id`, `post_id`, `reporter_uid`, `reporter_ip`, `report_text`, `report_result`, `report_memo`) FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_report") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_topics") . "` (`topic_id`, `topic_title`, `topic_poster`, `topic_time`, `topic_views`, `topic_replies`, `topic_last_post_id`, `forum_id`, `topic_status`, `topic_sticky`, `topic_digest`, `digest_time`, `approved`, `poster_name`, `rating`, `votes`, `topic_haspoll`, `poll_id`) SELECT (`topic_id`, `topic_title`, `topic_poster`, `topic_time`, `topic_views`, `topic_replies`, `topic_last_post_id`, `forum_id`, `topic_status`, `topic_sticky`, `topic_digest`, `digest_time`, `approved`, `poster_name`, `rating`, `votes`, `topic_haspoll`, `poll_id`) FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_topics") . "`";
	$sql[] = "INSERT INTO " . "`" . $GLOBALS['xoopsDB']->prefix("xf_votedata") . "` (`ratingid`, `topic_id`, `ratinguser`, `rating`, `ratinghostname`, `ratingtimestamp`) SELECT (`ratingid`, `topic_id`, `ratinguser`, `rating`, `ratinghostname`, `ratingtimestamp`) FROM " . "`" . $GLOBALS['xoopsDB']->prefix("bb_votedata") . "`";
	
	include('header.php');
	
	foreach($sql as $id => $question) {
		if ($GLOBALS['xoopsDB']->queryF($question)) {
			xoops_error($question, 'SQL Executed Successfully');
		}
	}
	
	include('footer.php');
	
?>