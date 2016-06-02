<?php

// $Id: admin.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $
//%%%%%%	File Name  index.php   	%%%%%
define("_AM_XFORUM_FORUMCONF","Forum Configuration");
define("_AM_XFORUM_ADDAFORUM","Add a Forum");
define("_AM_XFORUM_SYNCFORUM","Sync forum");
define("_AM_XFORUM_REORDERFORUM","Reorder");
define("_AM_XFORUM_FORUM_MANAGER","Forums");
define("_AM_XFORUM_PRUNE_TITLE","Prune");
define("_AM_XFORUM_CATADMIN","Categories");
define("_AM_XFORUM_GENERALSET","Module Settings");
define("_AM_XFORUM_MODULEADMIN","Module Admin:");
define("_AM_XFORUM_HELP","Help");
define("_AM_XFORUM_ABOUT","About");
define("_AM_XFORUM_BOARDSUMMARY","Board Statistic");
define("_AM_XFORUM_PENDING_POSTS_FOR_AUTH","Posts pending authorization");
define("_AM_XFORUM_POSTID","Post ID");
define("_AM_XFORUM_POSTDATE","Post Date");
define("_AM_XFORUM_POSTER","Poster");
define("_AM_XFORUM_TOPICS","Topics");
define("_AM_XFORUM_SHORTSUMMARY","Board Summary");
define("_AM_XFORUM_TOTALPOSTS","Total Posts: %s");  // Changed 5.80
define("_AM_XFORUM_TOTALTOPICS","Total Topics: %s");  // Changed 5.80
define("_AM_XFORUM_TOTALVIEWS","Total Views: %s");  // Changed 5.80
define("_AM_XFORUM_BLOCKS","Blocks");
define("_AM_XFORUM_SUBJECT","Subject");
define("_AM_XFORUM_APPROVE","Approve Post");
define("_AM_XFORUM_APPROVETEXT","Content of this Posting");
define("_AM_XFORUM_POSTAPPROVED","Post has been approved");
define("_AM_XFORUM_POSTNOTAPPROVED","Post has NOT been approved");
define("_AM_XFORUM_POSTSAVED","Post has been saved");
define("_AM_XFORUM_POSTNOTSAVED","Post has NOT been saved");

define("_AM_XFORUM_TOPICAPPROVED","Topic has been approved");
define("_AM_XFORUM_TOPICNOTAPPROVED","Topic has been NOT approved");
define("_AM_XFORUM_TOPICID","Topic ID");
define("_AM_XFORUM_ORPHAN_TOPICS_FOR_AUTH","Unapproved topics authorization");

define('_AM_XFORUM_DEFAULTDOMAINSDESC','Default Multisite Domain for Display');
define('_AM_XFORUM_DOMAINSDESC','Domains to display on');
define('_AM_XFORUM_LANGUAGESDESC','Languages to display on');

define('_AM_XFORUM_DEL_ONE','Delete only this message');
define('_AM_XFORUM_POSTSDELETED','Selected post deleted.');
define('_AM_XFORUM_NOAPPROVEPOST','There are presently no posts waiting approval.');
define('_AM_XFORUM_SUBJECTC','Subject:');
define('_AM_XFORUM_MESSAGEICON','Message Icon:');
define('_AM_XFORUM_MESSAGEC','Message:');
define('_AM_XFORUM_CANCELPOST','Cancel Post');
define('_AM_XFORUM_GOTOMOD','Go to module');

define('_AM_XFORUM_PREFERENCES','Module preferences');
define('_AM_XFORUM_POLLMODULE','Xoops Poll Module: %s');  // Changed 5.80
define('_AM_XFORUM_POLL_OK','Ready for use');
define('_AM_XFORUM_GDLIB1','GD1 library: %s');  // Changed 5.80
define('_AM_XFORUM_GDLIB2','GD2 library: %s');  // Changed 5.80
define('_AM_XFORUM_AUTODETECTED','Autodetected: ');
define('_AM_XFORUM_AVAILABLE','Available');
define('_AM_XFORUM_NOTAVAILABLE','<font color="red">Not available</font>');
define('_AM_XFORUM_NOTWRITABLE','<font color="red">Not writable</font>');
define('_AM_XFORUM_IMAGEMAGICK','ImageMagicK: %s');  // Changed 5.80
define('_AM_XFORUM_IMAGEMAGICK_NOTSET','Not set');
define('_AM_XFORUM_ATTACHPATH','Path for attachment storing: %s');  // Changed 5.80
define('_AM_XFORUM_THUMBPATH','Path for attached image thumbs: %s');  // Changed 5.80
define('_AM_XFORUM_RSSPATH','Path for RSS feed');
define('_AM_XFORUM_REPORT','Reported posts');  // Changed 5.80
define('_AM_XFORUM_REPORT_PENDING','Pending report: %s');  // Changed 5.80
define('_AM_XFORUM_REPORT_PROCESSED','processed report: %s');  // Changed 5.80

define('_AM_XFORUM_CREATETHEDIR','Create it');
define('_AM_XFORUM_SETMPERM','Set the permission');
define('_AM_XFORUM_DIRCREATED','The directory has been created');
define('_AM_XFORUM_DIRNOTCREATED','The directory can not be created');
define('_AM_XFORUM_PERMSET','The permission has been set');
define('_AM_XFORUM_PERMNOTSET','The permission can not be set');

define('_AM_XFORUM_DIGEST','Digest notification');
define('_AM_XFORUM_DIGEST_PAST','<font color="red">Should be sent out %d minutes ago</font>');
define('_AM_XFORUM_DIGEST_NEXT','Need to send out in %d minutes');
define('_AM_XFORUM_DIGEST_ARCHIVE','Digest archives: %s');
define('_AM_XFORUM_DIGEST_SENT','Digest processed');
define('_AM_XFORUM_DIGEST_FAILED','Digest NOT processed');

// admin_forum_manager.php
define("_AM_XFORUM_NAME","Name");
define("_AM_XFORUM_CREATEFORUM","Create Forum");
define("_AM_XFORUM_EDIT","Edit");
define("_AM_XFORUM_CLEAR","Clear");
define("_AM_XFORUM_DELETE","Delete");
define("_AM_XFORUM_ADD","Add");
define("_AM_XFORUM_MOVE","Move");
define("_AM_XFORUM_ORDER","Order");
define("_AM_XFORUM_TWDAFAP","Note: This will remove the forum and all posts in it.<br /><br />WARNING: Are you sure you want to delete this Forum?");
define("_AM_XFORUM_FORUMREMOVED","Forum Removed.");
define("_AM_XFORUM_CREATENEWFORUM","Create a New Forum");
define("_AM_XFORUM_EDITTHISFORUM","Editing Forum:");
define("_AM_XFORUM_SET_FORUMORDER","Set Forum Position:");
define("_AM_XFORUM_ALLOWPOLLS","Allow Polls:");
define("_AM_XFORUM_ATTACHMENT_SIZE" ,"Max Size in kb`s:");
define("_AM_XFORUM_ALLOWED_EXTENSIONS","Allowed Extensions:<span style='font-size: xx-small; font-weight: normal; display: block;'>'*' indicates no limititations.<br /> Extensions delimited by '|'</span>");
//define("_AM_XFORUM_ALLOW_ATTACHMENTS","Allow Attachments:");
define("_AM_XFORUM_ALLOWHTML","Allow HTML:");
define("_AM_XFORUM_YES","Yes");
define("_AM_XFORUM_NO","No");
define("_AM_XFORUM_ALLOWSIGNATURES","Allow Signatures:");
define("_AM_XFORUM_HOTTOPICTHRESHOLD","Hot Topic Threshold:");
//define("_AM_XFORUM_POSTPERPAGE","Posts per Page:<span style='font-size: xx-small; font-weight: normal; display: block;'>(This is the number of posts<br /> per topic that will be<br /> displayed per page.)</span>");
//define("_AM_XFORUM_TOPICPERFORUM","Topics per Forum:<span style='font-size: xx-small; font-weight: normal; display: block;'>(This is the number of topics<br /> per forum that will be<br /> displayed per page.)</span>");
//define("_AM_XFORUM_SHOWNAME","Replace user's name with real name:");
//define("_AM_XFORUM_SHOWICONSPANEL","Show icons panel:");
//define("_AM_XFORUM_SHOWSMILIESPANEL","Show smilies panel:");
define("_AM_XFORUM_MODERATOR_REMOVE","Remove current moderators");
define("_AM_XFORUM_MODERATOR_ADD","Add moderators");
define("_AM_XFORUM_ALLOW_SUBJECT_PREFIX","Allow Subject Prefix for the Topics");
define("_AM_XFORUM_ALLOW_SUBJECT_PREFIX_DESC","This allows a Prefix, which will be added to the Topic Subject");


// admin_cat_manager.php

define("_AM_XFORUM_SETCATEGORYORDER","Set Category Position:");
define("_AM_XFORUM_ACTIVE","Active");
define("_AM_XFORUM_INACTIVE","Inactive");
define("_AM_XFORUM_STATE","Status:");
define("_AM_XFORUM_CATEGORYDESC","Category Description:");
define("_AM_XFORUM_SHOWDESC","Show Description?");
define("_AM_XFORUM_IMAGE","Image:");
//define("_AM_XFORUM_SPONSORIMAGE","Sponsor Image:");
define("_AM_XFORUM_SPONSORLINK","Sponsor Link:");
define("_AM_XFORUM_DELCAT","Delete Category");
define("_AM_XFORUM_WAYSYWTDTTAL","Note: This will NOT remove the forums under the category, you must do that via the Edit Forum section.<br /><br />WARNING: Are you sure you want to delete this Category?");



//%%%%%%        File Name  admin_forums.php           %%%%%
define("_AM_XFORUM_FORUMNAME","Forum Name:");
define("_AM_XFORUM_FORUMDESCRIPTION","Forum Description:");
define("_AM_XFORUM_MODERATOR","Moderator(s):");
define("_AM_XFORUM_REMOVE","Remove");
define("_AM_XFORUM_CATEGORY","Category:");
define("_AM_XFORUM_DATABASEERROR","Database Error");
define("_AM_XFORUM_CATEGORYUPDATED","Category Updated.");
define("_AM_XFORUM_EDITCATEGORY","Editing Category:");
define("_AM_XFORUM_CATEGORYTITLE","Category Title:");
define("_AM_XFORUM_CATEGORYCREATED","Category Created.");
define("_AM_XFORUM_CREATENEWCATEGORY","Create a New Category");
define("_AM_XFORUM_FORUMCREATED","Forum Created.");
define("_AM_XFORUM_ACCESSLEVEL","Global Access Level:");
define("_AM_XFORUM_CATEGORY1","Category");
define("_AM_XFORUM_FORUMUPDATE","Forum Settings Updated");
define("_AM_XFORUM_FORUM_ERROR","ERROR: Forum Setting Error");
define("_AM_XFORUM_CLICKBELOWSYNC","Clicking the button below will sync up your forums and topics pages with the correct data from the database. Use this section whenever you notice flaws in the topics and forums lists.");
define("_AM_XFORUM_SYNCHING","Synchronizing forum index and topics (This may take a while)");
define("_AM_XFORUM_CATEGORYDELETED","Category deleted.");
define("_AM_XFORUM_MOVE2CAT","Move to category:");
define("_AM_XFORUM_MAKE_SUBFORUM_OF","Make a sub forum of:");
define("_AM_XFORUM_MSG_FORUM_MOVED","Forum moved!");
define("_AM_XFORUM_MSG_ERR_FORUM_MOVED","Failed to move forum.");
define("_AM_XFORUM_SELECT","< Select >");
define("_AM_XFORUM_MOVETHISFORUM","Move this Forum");
define("_AM_XFORUM_MERGE","Merge");
define("_AM_XFORUM_MERGETHISFORUM","Merge this Forum");
define("_AM_XFORUM_MERGETO_FORUM","Merge this forum to:");
define("_AM_XFORUM_MSG_FORUM_MERGED","Forum merged!");
define("_AM_XFORUM_MSG_ERR_FORUM_MERGED","Failed to merge forum.");

//%%%%%%        File Name  admin_forum_reorder.php           %%%%%
define("_AM_XFORUM_REORDERID","ID");
define("_AM_XFORUM_REORDERTITLE","Title");
define("_AM_XFORUM_REORDERWEIGHT","Position");
define("_AM_XFORUM_SETFORUMORDER","Set Board Ordering");
define("_AM_XFORUM_BOARDREORDER","The Board has reordered to your specification");

// admin_permission.php
define("_AM_XFORUM_PERMISSIONS_TO_THIS_FORUM","Topic permissions for this Forum");
define("_AM_XFORUM_CAT_ACCESS","Category access");
define("_AM_XFORUM_CAN_ACCESS","Can access");
define("_AM_XFORUM_CAN_VIEW","Can View");
define("_AM_XFORUM_CAN_POST","Can start new topics");
define("_AM_XFORUM_CAN_REPLY","Can Reply");
define("_AM_XFORUM_CAN_EDIT","Can Edit");
define("_AM_XFORUM_CAN_DELETE","Can Delete");
define("_AM_XFORUM_CAN_ADDPOLL","Can Add Poll");
define("_AM_XFORUM_CAN_VOTE","Can Vote");
define("_AM_XFORUM_CAN_ATTACH","Can Attach");
define("_AM_XFORUM_CAN_NOAPPROVE","Can Post without Approval");
define("_AM_XFORUM_ACTION","Action");

define("_AM_XFORUM_PERM_TEMPLATE","Set default permission template");
define("_AM_XFORUM_PERM_TEMPLATE_DESC","Edit the following permission template so that it can be applied to a forum or a couple of forums");
define("_AM_XFORUM_PERM_FORUMS","Select forums");
define("_AM_XFORUM_PERM_TEMPLATE_CREATED","Permission template has been created");
define("_AM_XFORUM_PERM_TEMPLATE_ERROR","Error occurs during permission template creation");
define("_AM_XFORUM_PERM_TEMPLATEAPP","Apply default permission");
define("_AM_XFORUM_PERM_TEMPLATE_APPLIED","Default permissions have been applied to forums");
define("_AM_XFORUM_PERM_ACTION","Permission management tools");
define("_AM_XFORUM_PERM_SETBYGROUP","Set permissions directly by group");

// admin_forum_prune.php

define("_AM_XFORUM_PRUNE_RESULTS_TITLE","Prune Results");
define("_AM_XFORUM_PRUNE_RESULTS_TOPICS","Pruned Topics");
define("_AM_XFORUM_PRUNE_RESULTS_POSTS","Pruned Posts");
define("_AM_XFORUM_PRUNE_RESULTS_FORUMS","Pruned Forums");
define("_AM_XFORUM_PRUNE_STORE","Store posts in this forum instead of deleting them");
define("_AM_XFORUM_PRUNE_ARCHIVE","Make a copy of posts into Archive");
define("_AM_XFORUM_PRUNE_FORUMSELERROR","You forgot to select forum(s) to prune");

define("_AM_XFORUM_PRUNE_DAYS","Remove topics without replies in:");
define("_AM_XFORUM_PRUNE_FORUMS","Forums to be pruned");
define("_AM_XFORUM_PRUNE_STICKY","Keep Sticky topics");
define("_AM_XFORUM_PRUNE_DIGEST","Keep Digest topics");
define("_AM_XFORUM_PRUNE_LOCK","Keep Locked topics");
define("_AM_XFORUM_PRUNE_HOT","Keep topics with more than this number of replies");
define("_AM_XFORUM_PRUNE_SUBMIT","Ok");
define("_AM_XFORUM_PRUNE_RESET","Reset");
define("_AM_XFORUM_PRUNE_YES","Yes");
define("_AM_XFORUM_PRUNE_NO","No");
define("_AM_XFORUM_PRUNE_WEEK","A Week");
define("_AM_XFORUM_PRUNE_2WEEKS","Two Weeks");
define("_AM_XFORUM_PRUNE_MONTH","A Month");
define("_AM_XFORUM_PRUNE_2MONTH","Two Months");
define("_AM_XFORUM_PRUNE_4MONTH","Four Months");
define("_AM_XFORUM_PRUNE_YEAR","A Year");
define("_AM_XFORUM_PRUNE_2YEARS","2 Years");

// About.php constants
define('_AM_XFORUM_AUTHOR_INFO',"Author Informations");
define('_AM_XFORUM_AUTHOR_NAME',"Author");
define('_AM_XFORUM_AUTHOR_WEBSITE',"Author's website");
define('_AM_XFORUM_AUTHOR_EMAIL',"Author's email");
define('_AM_XFORUM_AUTHOR_CREDITS',"Credits");
define('_AM_XFORUM_MODULE_INFO',"Module Development Information");
define('_AM_XFORUM_MODULE_STATUS',"Status");
define('_AM_XFORUM_MODULE_DEMO',"Demo Site");
define('_AM_XFORUM_MODULE_SUPPORT',"Official support site");
define('_AM_XFORUM_MODULE_BUG',"Report a bug for this module");
define('_AM_XFORUM_MODULE_FEATURE',"Suggest a new feature for this module");
define('_AM_XFORUM_MODULE_DISCLAIMER',"Disclaimer");
define('_AM_XFORUM_AUTHOR_WORD',"The Author's Word");
define('_AM_XFORUM_BY','By');
define('_AM_XFORUM_AUTHOR_WORD_EXTRA',"
");

// admin_report.php
define("_AM_XFORUM_REPORTADMIN","Reported posts manager");
define("_AM_XFORUM_PROCESSEDREPORT","View processed reports");
define("_AM_XFORUM_PROCESSREPORT","Process reports");
define("_AM_XFORUM_REPORTTITLE","Report title");
define("_AM_XFORUM_REPORTEXTRA","Extra");
define("_AM_XFORUM_REPORTPOST","Reported post");
define("_AM_XFORUM_REPORTTEXT","Report text");
define("_AM_XFORUM_REPORTMEMO","Process memo");

// admin_report.php
define("_AM_XFORUM_DIGESTADMIN","Digest manager");
define("_AM_XFORUM_DIGESTCONTENT","Digest content");

// admin_votedata.php
define("_AM_XFORUM_VOTE_RATINGINFOMATION","Voting Information");
define("_AM_XFORUM_VOTE_TOTALVOTES","Total votes: ");
define("_AM_XFORUM_VOTE_REGUSERVOTES","Registered User Votes: %s");
define("_AM_XFORUM_VOTE_ANONUSERVOTES","Anonymous User Votes: %s");
define("_AM_XFORUM_VOTE_USER","User");
define("_AM_XFORUM_VOTE_IP","IP Address");
define("_AM_XFORUM_VOTE_USERAVG","Average User Rating");
define("_AM_XFORUM_VOTE_TOTALRATE","Total Ratings");
define("_AM_XFORUM_VOTE_DATE","Submitted");
define("_AM_XFORUM_VOTE_RATING","Rating");
define("_AM_XFORUM_VOTE_NOREGVOTES","No Registered User Votes");
define("_AM_XFORUM_VOTE_NOUNREGVOTES","No Unregistered User Votes");
define("_AM_XFORUM_VOTEDELETED","Vote data deleted.");
define("_AM_XFORUM_VOTE_ID","ID");
define("_AM_XFORUM_VOTE_FILETITLE","Thread Title");
define("_AM_XFORUM_VOTE_DISPLAYVOTES","Voting Data Information");
define("_AM_XFORUM_VOTE_NOVOTES","No User Votes to display");
define("_AM_XFORUM_VOTE_DELETE","No User Votes to display");
define("_AM_XFORUM_VOTE_DELETEDSC","<strong>Deletes</strong> the chosen vote information from the database.");

define("_AM_XFORUM_TYPE","Field Type");			
define("_AM_XFORUM_VALUETYPE","Value Type");		
define("_AM_XFORUM_TITLE","Title");			
define("_AM_XFORUM_DESCRIPTION","Description");		
define("_AM_XFORUM_REQUIRED","Required?");		
define("_AM_XFORUM_MAXLENGTH","Maximum Length");			
define("_AM_XFORUM_WEIGHT","Weight");			
define("_AM_XFORUM_DEFAULT","Default");			
define("_AM_XFORUM_NOTNULL","Not Null?");	
define("_AM_XFORUM_ARRAY","Array");			
define("_AM_XFORUM_EMAIL","Email");
define("_AM_XFORUM_INT","Integer");
define("_AM_XFORUM_TXTAREA","Text Area");
define("_AM_XFORUM_TXTBOX","Text field");
define("_AM_XFORUM_URL","URL");
define("_AM_XFORUM_OTHER","Other");
define("_AM_XFORUM_FLOAT","Floating Point");		
define("_AM_XFORUM_DECIMAL","Decimal Number");
define("_AM_XFORUM_UNICODE_ARRAY","Unicode Array");
define("_AM_XFORUM_UNICODE_EMAIL","Unicode Email");
define("_AM_XFORUM_UNICODE_TXTAREA","Unicode Text Area");
define("_AM_XFORUM_UNICODE_TXTBOX","Unicode Text field");
define("_AM_XFORUM_UNICODE_TEXTAREA","Unicode Text Area");
define("_AM_XFORUM_UNICODE_TEXTBOX","Unicode Text field");
define("_AM_XFORUM_UNICODE_URL","Unicode URL");
define('_AM_XFORUM_CHECKBOX','Checkbox');	
define('_AM_XFORUM_GROUP','Group');
define('_AM_XFORUM_GROUPMULTI','Multiselect Group');	
define('_AM_XFORUM_LANGUAGE','Language');
define('_AM_XFORUM_RADIO','Radio');	
define('_AM_XFORUM_SELECTMULTI','Multiselect');	
define('_AM_XFORUM_DHTMLTEXTAREA','DHTML Textarea');	
define('_AM_XFORUM_TIMEZONE','Timezone');
define('_AM_XFORUM_YESNO','Yes/No');	
define('_AM_XFORUM_DATETIME','Date Time');	
define('_AM_XFORUM_LONGDATE','Long Date');
define('_AM_XFORUM_THEME','Theme');	
define('_AM_XFORUM_AUTOTEXT','Autotext');
define('_AM_XFORUM_RANK','Rank');	
define('_AM_XFORUM_FIELDNOTCONFIGURABLE','Field not configurable');
define('_AM_XFORUM_SAVEDSUCCESS','saving of %s was succcessful!');	
define('_AM_XFORUM_FIELDS','Fields');
define('_AM_XFORUM_RUSUREDEL','Are you sure you want to delete %s?');
define('_AM_XFORUM_PARENTCAT','Parent Category');	

define('_AM_XFORUM_STEPNAME','Step Name');
define('_AM_XFORUM_STEPORDER','Step Order');	
define('_AM_XFORUM_STEPSAVE','Step Saves');	
define('_AM_XFORUM_STEP','Step');
define('_AM_XFORUM_STEPINTRO','Step Introduction');
define('_AM_XFORUM_FIELD','Field');
define('_AM_XFORUM_ADDOPTION','Options');
define('_AM_XFORUM_KEY','Key');
define('_AM_XFORUM_VALUE','Name');
define('_AM_XFORUM_REMOVEOPTIONS','Remove options');

define('_AM_XFORUM_FORUMS','Visible in Forums');
define('_AM_XFORUM_PROF_VISIBLE','Visible on Profile');
define('_AM_XFORUM_PROF_EDITABLE','Editable on Object');	
define('_AM_XFORUM_PROF_SEARCH','Search on Object');
define('_AM_XFORUM_PROF_ACCESS','Accessable on Profile');
define('_AM_XFORUM_PROF_POST','Display in Post');	
define('_AM_XFORUM_DELETEDSUCCESS','Deletion of %s');
define('_AM_XFORUM_FIELDVISIBLE','Field Visible');
define('_AM_XFORUM_FIELDVISIBLEFOR','Visible for:');	
define('_AM_XFORUM_FIELDVISIBLEON','Visible on:');
define('_AM_XFORUM_FIELDNOTVISIBLE','Not visible!');
define('_AM_XFORUM_FIELDVISIBLETOALL','Field visible to all');	

//Version 5.79
define('_AM_XFORUM_DATE','Date');
define('_AM_XFORUM_TEXTAREA','Textarea');
define('_AM_XFORUM_TEXTBOX','Textbox');
define('_AM_XFORUM_FORUM','Forum');

// Version 5.80
define('_AM_XFORUM_DIGEST_SEND','Disgest send (<a href="admin_digest.php">Click Here to send</a>): %s');
define('_AM_XFORUM_POST_APPROVALS','Posts Waiting for Approval!');
define('_AM_XFORUM_APPROVAL','Approval?');
define('_AM_XFORUM_CONTENT','Post Content');
define('_AM_XFORUM_POSTSWAITINGAPPROVAL','Posts waiting for approval (<a href="admin_dashboard.php?op=postapprovals">Approve Click Here</a>): %s');
define('_AM_XFORUM_TOPICWAITINGAPPROVAL','Topics waiting for approval (<a href="admin_dashboard.php?op=postapprovals">Approve Click Here</a>): %s');
define('_AM_XFORUM_NETPDM','NetPDM: %s');
define('_AM_XFORUM_PATHS','File Upload Paths');
define('_AM_XFORUM_DASHBOARD','Dashboard');
define('_AM_XFORUM_EDITOR','Editor');
define('_AM_XFORUM_ABOUT_MAKEDONATE','Make Donation for the Use of XForum');
if (!defined('_AM_XFORUM_TOPIC'))
	define('_AM_XFORUM_TOPIC','Topic');
if (!defined('_AM_XFORUM_USER'))
	define('_AM_XFORUM_USER','User');
?>
