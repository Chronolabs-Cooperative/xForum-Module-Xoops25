<?php

// $Id: forumform.inc.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $


if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/functions.php";
include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/class/xoopsformloader.php";
error_reporting(E_ALL);

if(empty($forum_obj)){
	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
	$forum = isset($_GET['forum']) ? intval($_GET['forum']) : (isset($forum) ? intval($forum) : 0);
	$forum_obj = $GLOBALS['forum_handler']->get($forum);
}

foreach (array(
		'start',
		'topic_id',
		'post_id',
		'pid',
		'isreply',
		'isedit',
		'contents_preview'
		) as $getint) {
	${$getint} = isset($_GET[$getint]) ? intval($_GET[$getint]) : ( (!empty(${$getint}))?${$getint}:0 );
}
foreach (array(
		'order',
		'viewmode',
		'hidden',
		'forum_form',
		'icon',
		'op'
		) as $getstr) {
	${$getstr} = isset($_GET[$getstr]) ? $_GET[$getstr] : ( (!empty(${$getstr}))? ${$getstr} : '' );
}


$topic_handler = xoops_getmodulehandler('topic', 'xforum');
$GLOBALS['topic_status'] = $topic_handler->get(@$topic_id,'topic_status');

$forum_form_action = (empty($admin_form_action))?XOOPS_URL."/modules/xforum/post.php":$admin_form_action; // admin/index.php also uses this form
$forum_form = new XoopsThemeForm('', 'forumform', $forum_form_action, 'post', true);
$forum_form->setExtra('enctype="multipart/form-data"');

if (forum_checkSubjectPrefixPermission($forum)) {
	if ($forum_obj->getVar('allow_subject_prefix')) {
		$subject_add = new XoopsFormElementTray(_MD_TOPIC_SUBJECTC,'');
		$subjectpres = explode(',',$GLOBALS['xforumModuleConfig']['subject_prefix']);
		$subjectpres = array_map('trim',$subjectpres);
		if(count($subjectpres)>1) {
			foreach($subjectpres as $subjectpre){
				$subject_array[]=trim($subjectpre);
			}
			$subject_select = new XoopsFormSelect('', 'subject_pre', $subject_pre);
			$subject_select->addOptionArray($subject_array);
			$subject_add->addElement(new XoopsFormLabel($subject_select->render()));
		}
		$forum_form->addElement($subject_add);
	}
}
$subject_form = new XoopsFormText(_MD_SUBJECTC, 'subject', 60, 100, $subject);
$subject_form->setExtra("tabindex='1'");
$forum_form->addElement($subject_form,true);

if (!is_object($GLOBALS['xoopsUser']) && empty($admin_form_action)) {
	$required = !empty($GLOBALS['xforumModuleConfig']["require_name"]);
	$forum_form->addElement(new XoopsFormText(_MD_NAMEMAIL, 'poster_name', 60, 255, ( !empty($isedit) && !empty($poster_name))?$poster_name:''), $required);
}

$icons_radio = new XoopsFormRadio(_MD_MESSAGEICON, 'icon', $icon);
$subject_icons = XoopsLists::getSubjectsList();
foreach ($subject_icons as $iconfile) {
	$icons_radio->addOption($iconfile, '<img src="'.XOOPS_URL.'/images/subject/'.$iconfile.'" alt="" />');
}
$forum_form->addElement($icons_radio);

$nohtml = ($forum_obj->getVar('allow_html'))?false:true;

$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum');

$criteria = new CriteriaCompo(new Criteria('forum_id', (!empty($_REQUEST['forum_id']))?intval($_REQUEST['forum_id']):$forum_obj->getVar('forum_id')));
$all_categories = $GLOBALS['forum_handler']->getObjects($criteria, true, false);

// Dynamic fields
$extras_handler = xoops_getmodulehandler('extras');
$post_handler = xoops_getmodulehandler('post');
$gperm_handler = xoops_gethandler('groupperm');
$module_handler = xoops_gethandler('module');
$xoModule = $module_handler->getByDirname('xforum');
$modid = $xoModule->getVar('mid');

if (is_object($GLOBALS['xoopsUser']))
	$groups = $GLOBALS['xoopsUser']->getGroups();
else
	$groups = array(XOOPS_GROUP_ANONYMOUS=>XOOPS_GROUP_ANONYMOUS);

$count_fields = 0;
$fields = $extras_handler->loadFields();
$post = $post_handler->get($post_id);
if ($isreply!=true)
	$extra = $extras_handler->get($post_id);
else
	$extra = $extras_handler->create();
$allnames = array();
foreach (array_keys($fields) as $i) {
	if (($post_id<>0&&$gperm_handler->checkRight('xforum_edit',$fields[$i]->getVar('field_id'),$groups, $modid)) ||
		($post_id==0&&$gperm_handler->checkRight('xforum_post',$fields[$i]->getVar('field_id'),$groups, $modid))) {
		$fieldinfo['element'] = $fields[$i]->getEditElement($post, $extra);
		$fieldinfo['required'] = $fields[$i]->getVar('field_required');
		foreach($fields[$i]->getVar('forum_id') as $catidid => $forum_id) {
			if (!in_array($fields[$i]->getVar('field_name'), $allnames)) {
				$allnames[] = $fields[$i]->getVar('field_name');
				if (in_array($forum_id, array_keys($all_categories))||$forum_id==((!empty($_REQUEST['forum_id']))?intval($_REQUEST['forum_id']):$forum_obj->getVar('forum_id'))) {
					$key = $all_categories[$forum_id]['forum_order'] * $count_fields + $forum_obj->getVar('forum_id');
					$elements[$key][] = $fieldinfo;
					$weights[$key][] = $fields[$i]->getVar('field_weight');
				} elseif (in_array(0, $fields[$i]->getVar('forum_id'))) {
					$key = $all_categories[$forum_id]['forum_order'] * $count_fields + $forum_obj->getVar('forum_id');
					$elements[$key][] = $fieldinfo;
					$weights[$key][] = $fields[$i]->getVar('field_weight');
				}
			}
		}
	}
}
ksort($elements);

foreach (array_keys($elements) as $k) {
	array_multisort($weights[$k], SORT_ASC, array_keys($elements[$k]), SORT_ASC, $elements[$k]);
	foreach (array_keys($elements[$k]) as $i) {
		$forum_form->addElement($elements[$k][$i]['element'], $elements[$k][$i]['required']);
	}
}

if(!empty($editor)){
	forum_setcookie("editor",$editor);
}elseif(!$editor = forum_getcookie("editor")){
	//$editor = forum_getcookie("editor");
	if(is_object($GLOBALS['xoopsUser'])){
		$editor =@ $GLOBALS['xoopsUser']->getVar("editor"); // Need set through user profile
	}
	if(empty($editor)){
		$editor =@ $GLOBALS['xforumModuleConfig']["editor_default"];
	}
}
$forum_form->addElement(new XoopsFormSelectEditor($forum_form, "editor", $editor, $nohtml));

$editor_configs = array();
$editor_configs["name"] ="message";
$editor_configs["value"] = $message;
$editor_configs["rows"] = empty($GLOBALS['xforumModuleConfig']["editor_rows"])? 35 : $GLOBALS['xforumModuleConfig']["editor_rows"];
$editor_configs["cols"] = empty($GLOBALS['xforumModuleConfig']["editor_cols"])? 60 : $GLOBALS['xforumModuleConfig']["editor_cols"];
$editor_configs["width"] = empty($GLOBALS['xforumModuleConfig']["editor_width"])? "100%" : $GLOBALS['xforumModuleConfig']["editor_width"];
$editor_configs["height"] = empty($GLOBALS['xforumModuleConfig']["editor_height"])? "400px" : $GLOBALS['xforumModuleConfig']["editor_height"];
$forum_form->addElement(new XoopsFormEditor(_MD_MESSAGEC, $editor, $editor_configs, $nohtml, $onfailure=null));

if (!class_exists('XoopsFormTag')) {
	if (!isset($tags))	
		$tags = '';
	$forum_form->addElement(new XoopsFormHidden('tags', $tags));
} else {
	if ($isreply!=true)
		$forum_form->addElement(new XoopsFormTag('tags', 60, 255, ($post_id==0)?$tags:$post_id));
	else
		$forum_form->addElement(new XoopsFormTag('tags', 60, 255, 0));
}

$options_tray = new XoopsFormElementTray(_MD_OPTIONS, '<br />');
if (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xforumModuleConfig']['allow_user_anonymous'] == 1) {
	$noname = (!empty($isedit) && is_object($forumpost) && $forumpost->getVar('uid') == 0) ? 1 : 0;
	$noname_checkbox = new XoopsFormCheckBox('', 'noname', $noname);
	$noname_checkbox->addOption(1, _MD_POSTANONLY);
	$options_tray->addElement($noname_checkbox);
}

if ($forum_obj->getVar('allow_html')) {
	$html_checkbox = new XoopsFormCheckBox('', 'dohtml', $dohtml);
	$html_checkbox->addOption(1, _MD_DOHTML);
	$options_tray->addElement($html_checkbox);
}else {
	$forum_form->addElement(new XoopsFormHidden('dohtml', 0));
}

$smiley_checkbox = new XoopsFormCheckBox('', 'dosmiley', $dosmiley);
$smiley_checkbox->addOption(1, _MD_DOSMILEY);
$options_tray->addElement($smiley_checkbox);

$xcode_checkbox = new XoopsFormCheckBox('', 'doxcode', $doxcode);
$xcode_checkbox->addOption(1, _MD_DOXCODE);
$options_tray->addElement($xcode_checkbox);

$br_checkbox = new XoopsFormCheckBox('', 'dobr', $dobr);
$br_checkbox->addOption(1, _MD_DOBR);
$options_tray->addElement($br_checkbox);

if ($forum_obj->getVar('allow_sig') && is_object($GLOBALS['xoopsUser'])) {
	$attachsig_checkbox = new XoopsFormCheckBox('', 'attachsig', $attachsig);
	$attachsig_checkbox->addOption(1, _MD_ATTACHSIG);
	$options_tray->addElement($attachsig_checkbox);
}

if ( empty($admin_form_action) && is_object($GLOBALS['xoopsUser']) && $GLOBALS['xforumModuleConfig']['notification_enabled']) {
	if (!empty($notify)) {
		// If 'notify' set, use that value (e.g. preview or upload)
		//$notify = 1;
	}
	else {
		// Otherwise, check previous subscribed status...
		$notification_handler = xoops_gethandler('notification');
		if (!empty($topic_id) && $notification_handler->isSubscribed('thread', $topic_id, 'new_post', $GLOBALS['xforumModule']->getVar('mid'), $GLOBALS['xoopsUser']->getVar('uid'))) {
			$notify = 1;
		}
		else {
			$notify = 0;
		}
	}

	$notify_checkbox = new XoopsFormCheckBox('', 'notify', $notify);
	$notify_checkbox->addOption(1, _MD_NEWPOSTNOTIFY);
	$options_tray->addElement($notify_checkbox);
}
$forum_form->addElement($options_tray);

if ($topic_handler->getPermission($forum_obj, $GLOBALS['topic_status'], 'attach')) {
	$upload_tray = new XoopsFormElementTray(_MD_ATTACHMENT);
	$upload_tray->addElement(new XoopsFormFile('', 'userfile',''));
	$upload_tray->addElement(new XoopsFormButton('', 'contents_upload', _MD_UPLOAD, "submit"));
	$upload_tray->addElement(new XoopsFormLabel("<BR /><BR />"._MD_MAX_FILESIZE.":", $forum_obj->getVar('attach_maxkb')."K; "));
	$extensions = trim(str_replace('|',' ',$forum_obj->getVar('attach_ext')));
	$extensions = (empty($extensions) || $extensions == "*")?_ALL:$extensions;
	$upload_tray->addElement(new XoopsFormLabel(_MD_ALLOWED_EXTENSIONS.":", $extensions));
	$forum_form->addElement($upload_tray);
}

if (!empty($attachments) && is_array($attachments) && count($attachments)){
	$delete_attach_checkbox = new XoopsFormCheckBox(_MD_THIS_FILE_WAS_ATTACHED_TO_THIS_POST, 'delete_attach[]');
	foreach($attachments as $key => $attachment){
		$attach = _DELETE.' <a href='.XOOPS_URL.'/'.$GLOBALS['xforumModuleConfig']['dir_attachments'].'/'.$attachment['name_saved'].' targe="_blank" >'.$attachment['name_display'].'</a>';
		$delete_attach_checkbox->addOption($key, $attach);
	}
	$forum_form->addElement($delete_attach_checkbox);
	unset($delete_attach_checkbox);
}

if (!empty($attachments_tmp) && is_array($attachments_tmp) && count($attachments_tmp)){
	$delete_attach_checkbox = new XoopsFormCheckBox(_MD_REMOVE, 'delete_tmp[]');
	$url_prefix = str_replace(XOOPS_ROOT_PATH, XOOPS_URL, XOOPS_CACHE_PATH);
	foreach($attachments_tmp as $key => $attachment){
		$attach = ' <a href="'.$url_prefix.'/'.$attachment[0].'" targe="_blank" >'.$attachment[1].'</a>';
		$delete_attach_checkbox->addOption($key, $attach);
	}
	$forum_form->addElement($delete_attach_checkbox);
	unset($delete_attach_checkbox);
	$attachments_tmp =  base64_encode(serialize($attachments_tmp));
	$forum_form->addElement(new XoopsFormHidden('attachments_tmp', $attachments_tmp));
}

if($GLOBALS['xforumModuleConfig']['enable_karma'] || $GLOBALS['xforumModuleConfig']['allow_require_reply']){
	$view_require = ($require_reply)?'require_reply':(($post_karma)?'require_karma':'require_null');
	$radiobox = new XoopsFormRadio( _MD_VIEW_REQUIRE, 'view_require', $view_require );
	if($GLOBALS['xforumModuleConfig']['allow_require_reply']){
		$radiobox->addOption( 'require_reply', _MD_REQUIRE_REPLY);
	}
	if($GLOBALS['xforumModuleConfig']['enable_karma']){
		$karmas = array_map("trim", explode(',', $GLOBALS['xforumModuleConfig']['karma_options']));
		if(count($karmas)>1) {
			foreach($karmas as $karma){
				$karma_array[strval($karma)] = intval($karma);
			}
			$karma_select = new XoopsFormSelect('', "post_karma", $post_karma);
			$karma_select->addOptionArray($karma_array);
			$radiobox->addOption( 'require_karma', _MD_REQUIRE_KARMA. ($karma_select->render()) );
		}
	}
	$radiobox->addOption( 'require_null', _MD_REQUIRE_NULL);
}
$forum_form->addElement( $radiobox );

if(!empty($admin_form_action)){
	$approved_radio = new XoopsFormRadioYN(_AM_forum_APPROVE, 'approved', 1, '' . _YES . '', ' ' . _NO . '');
	$forum_form->addElement($approved_radio);
}

// backward compatible
if(!class_exists("XoopsSecurity")){
	$post_valid = 1;
	$_SESSION['submit_token'] = $post_valid;
	$forum_form->addElement(new XoopsFormHidden('post_valid', $post_valid));
}

$forum_form->addElement(new XoopsFormCaptcha(), true);
$forum_form->addElement(new XoopsFormHidden('pid', $pid));
$forum_form->addElement(new XoopsFormHidden('post_id', $post_id));
$forum_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
$forum_form->addElement(new XoopsFormHidden('forum', $forum_obj->getVar('forum_id')));
$forum_form->addElement(new XoopsFormHidden('viewmode', $GLOBALS['viewmode']));
$forum_form->addElement(new XoopsFormHidden('order', $GLOBALS['order']));
$forum_form->addElement(new XoopsFormHidden('start', $GLOBALS['start']));
$forum_form->addElement(new XoopsFormHidden('isreply', $isreply));
$forum_form->addElement(new XoopsFormHidden('isedit', $isedit));
$forum_form->addElement(new XoopsFormHidden('op', $op));

$button_tray = new XoopsFormElementTray('');

$submit_button = new XoopsFormButton('', 'contents_submit', _SUBMIT, "submit");
$submit_button->setExtra("tabindex='3'");

$cancel_button = new XoopsFormButton('', 'cancel', _CANCEL, 'button');
if ( isset($topic_id) && $topic_id != "" )
	$extra = XOOPS_URL.'/modules/xforum/'."viewtopic.php?topic_id=".intval($topic_id);
else
	$extra = XOOPS_URL.'/modules/xforum/'."viewforum.php?forum=".$forum_obj->getVar('forum_id');
$cancel_button->setExtra("onclick='location=\"".$extra."\"'");
$cancel_button->setExtra("tabindex='6'");

if ( !empty($isreply) && !empty($hidden) ) {
	$forum_form->addElement(new XoopsFormHidden('hidden', $hidden));

	$quote_button = new XoopsFormButton('', 'quote', _MD_QUOTE, 'button');
	$quote_button->setExtra("onclick='xoopsGetElementById(\"message\").value=xoopsGetElementById(\"message\").value+ xoopsGetElementById(\"hidden\").value;xoopsGetElementById(\"hidden\").value=\"\";'");
	$quote_button->setExtra("tabindex='4'");
	$button_tray->addElement($quote_button);
}

$preview_button = new XoopsFormButton('', 'btn_preview', _PREVIEW, "button");
$preview_button->setExtra("tabindex='5'");
$preview_button->setExtra('onclick="window.document.forms.forumform.contents_preview.value=1;
		window.document.forms.forumform.submit();"');
$forum_form->addElement(new XoopsFormHidden('contents_preview', 0));


$button_tray->addElement($preview_button);
$button_tray->addElement($submit_button);
$button_tray->addElement($cancel_button);
$forum_form->addElement($button_tray);

$forum_form->display();
?>
