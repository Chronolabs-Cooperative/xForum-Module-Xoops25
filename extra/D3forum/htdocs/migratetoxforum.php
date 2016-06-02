<?php
	// RUN ONCE FROM THE XOOPS_ROOT_PATH
	
	// Stable: 20/12/2010 4:06 AM

	include ('mainfile.php');
	
	$sql = array();
	$sql[] = 'TRUNCATE '.$GLOBALS['xoopsDB']->prefix('xf_categories'); 
	$sql[] = 'INSERT INTO '.$GLOBALS['xoopsDB']->prefix('xf_categories').' (cat_id, cat_title, cat_description, cat_order) SELECT cat_id, cat_title, cat_desc, cat_weight FROM '.$GLOBALS['xoopsDB']->prefix('d3forum_categories'); 
	$sql[] = 'TRUNCATE '.$GLOBALS['xoopsDB']->prefix('xf_forums'); 
	$sql[] = 'INSERT INTO '.$GLOBALS['xoopsDB']->prefix('xf_forums').' (forum_id, cat_id, forum_name, forum_desc, forum_last_post_id, forum_topics, forum_posts, forum_order) SELECT forum_id, cat_id, forum_title, forum_desc, forum_last_post_id, forum_topics_count, forum_posts_count, forum_weight FROM '.$GLOBALS['xoopsDB']->prefix('d3forum_forums'); 
	$sql[] = 'TRUNCATE '.$GLOBALS['xoopsDB']->prefix('xf_posts');
	$sql[] = 'INSERT INTO '.$GLOBALS['xoopsDB']->prefix('xf_posts').' (post_id, pid, topic_id, post_time, uid, poster_name, `subject`) SELECT post_id, pid, topic_id, post_time, uid, guest_name, `subject` FROM '.$GLOBALS['xoopsDB']->prefix('d3forum_posts'); 
	$sql[] = 'TRUNCATE '.$GLOBALS['xoopsDB']->prefix('xf_posts_text'); 
	$sql[] = 'INSERT INTO '.$GLOBALS['xoopsDB']->prefix('xf_posts_text').' (post_id, post_text)  SELECT post_id, post_text FROM '.$GLOBALS['xoopsDB']->prefix('d3forum_posts'); 
	$sql[] = 'TRUNCATE '.$GLOBALS['xoopsDB']->prefix('xf_topics'); 
	$sql[] = 'INSERT INTO '.$GLOBALS['xoopsDB']->prefix('xf_topics').' (topic_id, forum_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, topic_last_post_id)  SELECT topic_id, forum_id, topic_title, topic_first_uid, topic_first_post_time, topic_views, topic_posts_count, topic_last_post_id FROM '.$GLOBALS['xoopsDB']->prefix('d3forum_topics');
	
	foreach($sql as $id=>$question)
		$GLOBALS['xoopsDB']->queryF($question);
		
	$topic_handler =& xoops_getmodulehandler('topic', 'xforum');
	$post_handler =& xoops_getmodulehandler('post', 'xforum');
	$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
	
	$posts = $post_handler->getObjects(NULL, true);
	$topics = $topic_handler->getObjects(NULL, true);
	$forums = $forum_handler->getObjects(NULL, true);
		
	foreach($posts as $post_id => $post) {
		$post->setVar('forum_id', $topics[$post->getVar('topic_id')]->getVar('forum_id'));
		$forums[$topics[$post->getVar('topic_id')]->getVar('forum_id')]->setVar('forum_last_post_id', $post->getVar('post_id'));
		$forums[$topics[$post->getVar('topic_id')]->getVar('forum_id')]->setVar('forum_posts', $forums[$topics[$post->getVar('topic_id')]->getVar('forum_id')]->getVar('forum_posts')+1); 
		$post_handler->insert($post, true);
		$topics[$post->getVar('topic_id')]->setVar('topic_last_post_id', $post->getVar('post_id'));
		$topics[$post->getVar('topic_id')]->setVar('topic_time', time());
		
		if (!in_array($post->getVar('topic_id'), $topicsid)) {
			$topicsid[$post->getVar('topic_id')] = $post->getVar('topic_id');
			$forums[$topics[$post->getVar('topic_id')]->getVar('forum_id')]->setVar('forum_topics', $forums[$topics[$post->getVar('topic_id')]->getVar('forum_id')]->getVar('forum_topics')+1);			
		} else {
			$topics[$post->getVar('topic_id')]->setVar('topic_replies', $topics[$post->getVar('topic_id')]->getVar('topic_replies')+1);
		}
		$topic_handler->insert($topics[$post->getVar('topic_id')]);
		$forum_handler->insert($forums[$topics[$post->getVar('topic_id')]->getVar('forum_id')], true);
	}
	
	echo 'done!'
?>
