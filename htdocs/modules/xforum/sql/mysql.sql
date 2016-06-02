CREATE TABLE `xf_archive` (
  `topic_id` int(8) unsigned NOT NULL DEFAULT '0',
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `post_text` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_attachments` (
  `attach_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name_saved` varchar(255) NOT NULL DEFAULT '',
  `name_disp` varchar(255) NOT NULL DEFAULT '',
  `mimetype` varchar(255) NOT NULL DEFAULT '',
  `online` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `attach_time` int(10) unsigned NOT NULL DEFAULT '0',
  `download` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`attach_id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_categories` (
  `cat_id` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `cat_image` varchar(50) NOT NULL DEFAULT '',
  `cat_title` varchar(100) NOT NULL DEFAULT '',
  `cat_description` text,
  `cat_order` smallint(3) unsigned NOT NULL DEFAULT '0',
  `cat_url` varchar(255) NOT NULL DEFAULT '',
  `cat_domain` varchar(255) NOT NULL DEFAULT '',
  `cat_domains` mediumtext,
  `cat_languages` mediumtext,
  PRIMARY KEY (`cat_id`),
  KEY `cat_order` (`cat_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_digest` (
  `digest_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `digest_time` int(10) unsigned NOT NULL DEFAULT '0',
  `digest_content` text,
  PRIMARY KEY (`digest_id`),
  KEY `digest_time` (`digest_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_extras` (
  `post_id` int(13) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_field` (
  `field_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` mediumtext,
  `field_type` varchar(30) NOT NULL DEFAULT '',
  `field_valuetype` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `field_name` varchar(255) NOT NULL DEFAULT '',
  `field_title` varchar(255) NOT NULL DEFAULT '',
  `field_description` text,
  `field_required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_maxlength` smallint(6) unsigned NOT NULL DEFAULT '0',
  `field_weight` smallint(6) unsigned NOT NULL DEFAULT '0',
  `field_default` text,
  `field_notnull` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_edit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_show` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_config` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_options` text,
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_forums` (
  `forum_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `forum_name` varchar(150) NOT NULL DEFAULT '',
  `forum_desc` text,
  `parent_forum` smallint(4) unsigned NOT NULL DEFAULT '0',
  `forum_moderator` varchar(255) NOT NULL DEFAULT '',
  `forum_topics` int(8) unsigned NOT NULL DEFAULT '0',
  `forum_posts` int(10) unsigned NOT NULL DEFAULT '0',
  `forum_last_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `cat_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `forum_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `allow_html` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `allow_sig` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `allow_subject_prefix` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hot_threshold` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `forum_order` smallint(4) unsigned NOT NULL DEFAULT '0',
  `attach_maxkb` smallint(3) unsigned NOT NULL DEFAULT '1000',
  `attach_ext` varchar(255) NOT NULL DEFAULT '',
  `allow_polls` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `domain` varchar(255) NOT NULL DEFAULT '',
  `domains` mediumtext,
  `languages` mediumtext,
  PRIMARY KEY (`forum_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`),
  KEY `cat_forum` (`cat_id`,`forum_order`),
  KEY `forum_order` (`forum_order`),
  KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_moderates` (
  `mod_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mod_start` int(10) unsigned NOT NULL DEFAULT '0',
  `mod_end` int(10) unsigned NOT NULL DEFAULT '0',
  `mod_desc` varchar(255) NOT NULL DEFAULT '',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(32) NOT NULL DEFAULT '',
  `forum_id` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mod_id`),
  KEY `uid` (`uid`),
  KEY `mod_end` (`mod_end`),
  KEY `forum_id` (`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_online` (
  `online_forum` int(10) unsigned NOT NULL DEFAULT '0',
  `online_topic` int(8) unsigned NOT NULL DEFAULT '0',
  `online_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `online_uname` varchar(255) NOT NULL DEFAULT '',
  `online_ip` varchar(32) NOT NULL DEFAULT '',
  `online_updated` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `online_forum` (`online_forum`),
  KEY `online_topic` (`online_topic`),
  KEY `online_updated` (`online_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_posts` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(4) unsigned NOT NULL DEFAULT '0',
  `post_time` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  `poster_ip` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `dohtml` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dosmiley` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `doxcode` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `dobr` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `doimage` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `icon` varchar(25) NOT NULL DEFAULT '',
  `attachsig` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `approved` smallint(2) NOT NULL DEFAULT '1',
  `post_karma` int(10) unsigned NOT NULL DEFAULT '0',
  `attachment` text,
  `require_reply` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`post_id`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_id` (`topic_id`),
  KEY `forumid_uid` (`forum_id`,`uid`),
  KEY `topicid_uid` (`topic_id`,`uid`),
  KEY `post_time` (`post_time`),
  KEY `topicid_postid_pid` (`topic_id`,`post_id`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_posts_text` (
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `post_text` text,
  `post_edit` text,
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_reads_forum` (
  `read_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `read_time` int(10) unsigned NOT NULL DEFAULT '0',
  `read_item` smallint(4) unsigned NOT NULL DEFAULT '0',
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`read_id`),
  KEY `uid` (`uid`),
  KEY `read_item` (`read_item`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_reads_topic` (
  `read_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `read_time` int(10) unsigned NOT NULL DEFAULT '0',
  `read_item` int(8) unsigned NOT NULL DEFAULT '0',
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`read_id`),
  KEY `uid` (`uid`),
  KEY `read_item` (`read_item`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_report` (
  `report_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `reporter_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `reporter_ip` int(11) NOT NULL DEFAULT '0',
  `report_time` int(10) unsigned NOT NULL DEFAULT '0',
  `report_text` varchar(255) NOT NULL DEFAULT '',
  `report_result` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `report_memo` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`report_id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_topics` (
  `topic_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `topic_title` varchar(255) NOT NULL DEFAULT '',
  `topic_poster` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_time` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_views` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_replies` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `topic_last_post_id` int(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(4) unsigned NOT NULL DEFAULT '0',
  `topic_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `topic_subject` smallint(3) unsigned NOT NULL DEFAULT '0',
  `topic_sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `topic_digest` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `digest_time` int(10) unsigned NOT NULL DEFAULT '0',
  `approved` tinyint(2) NOT NULL DEFAULT '1',
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  `rating` double(6,4) NOT NULL DEFAULT '0.0000',
  `votes` int(11) unsigned NOT NULL DEFAULT '0',
  `topic_haspoll` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `poll_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_last_post_id` (`topic_last_post_id`),
  KEY `topic_poster` (`topic_poster`),
  KEY `topic_forum` (`topic_id`,`forum_id`),
  KEY `topic_sticky` (`topic_sticky`),
  KEY `topic_digest` (`topic_digest`),
  KEY `digest_time` (`digest_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_visibility` (
  `field_id` int(12) unsigned NOT NULL DEFAULT '0',
  `user_group` smallint(5) unsigned NOT NULL DEFAULT '0',
  `profile_group` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`user_group`,`profile_group`),
  KEY `visible` (`user_group`,`profile_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xf_votedata` (
  `ratingid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(8) unsigned NOT NULL DEFAULT '0',
  `ratinguser` int(10) unsigned NOT NULL DEFAULT '0',
  `rating` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ratinghostname` varchar(60) NOT NULL DEFAULT '',
  `ratingtimestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ratingid`),
  KEY `ratinguser` (`ratinguser`),
  KEY `ratinghostname` (`ratinghostname`),
  KEY `topic_id` (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;