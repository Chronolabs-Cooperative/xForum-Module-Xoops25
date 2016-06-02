<?php

/**
 * newCategory()
 *
 * @return
 */
function newCategory()
{
    editCategory();
}

/**
 * editCategory()
 *
 * @param integer $catid
 * @return
 */
function editCategory($cat_id = 0)
{
    $category_handler = xoops_getmodulehandler('category', 'xforum');
    if ($cat_id > 0) {
    	$fc = $category_handler->get($cat_id);
    } else {
        $fc = $category_handler->create();
    }
    $groups_cat_access = null;
	include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/class/xoopsformloader.php";
	
    if ($cat_id) {
        $sform = new XoopsThemeForm(_AM_XFORUM_EDITCATEGORY . " " . $fc->getVar('cat_title'), "op", xoops_getenv('PHP_SELF'));
    } else {
        $sform = new XoopsThemeForm(_AM_XFORUM_CREATENEWCATEGORY, "op", xoops_getenv('PHP_SELF'));
        $fc->setVar('cat_title', '');
        $fc->setVar('cat_image', 'blank.gif');
        $fc->setVar('cat_description', '');
        $fc->setVar('cat_order', 0);
        $fc->setVar('cat_domain', urlencode(XOOPS_URL));
        $fc->setVar('cat_domains', array(urlencode(XOOPS_URL)));
        $fc->setVar('cat_languages', array($GLOBALS['xoopsConfig']['language']));
        //$fc->setVar('cat_state', 0);
        //$fc->setVar('cat_showdescript', 1);
        $fc->setVar('cat_url', 'http://www.chronolabs.org XOOPS');
    }

    $sform->addElement(new XoopsFormText(_AM_XFORUM_SETCATEGORYORDER, 'cat_order', 5, 10, $fc->getVar('cat_order')), false);
    $sform->addElement(new XoopsFormText(_AM_XFORUM_CATEGORY, 'title', 50, 80, $fc->getVar('cat_title', 'E')), true);
    $sform->addElement(new XoopsFormDhtmlTextArea(_AM_XFORUM_CATEGORYDESC, 'catdescript', $fc->getVar('cat_description', 'E'), 10, 60), false);

    //$displaydescription_radio = new XoopsFormRadioYN(_AM_XFORUM_SHOWDESC, 'show', $fc->getVar('cat_showdescript'), '' . _YES . '', ' ' . _NO . '');
    //$sform->addElement($displaydescription_radio);
	
    if ($GLOBALS['xforumModuleConfig']['multisite']) {
    	
    	$sform->addElement(new XoopsFormSelectDomains(_AM_XFORUM_DEFAULTDOMAINSDESC, 'cat_domain', $fc->getVar('cat_domain')));
		
    	if (count($fc->getVar('cat_domains')))
    		$sform->addElement(new XoopsFormCheckBoxDomains(_AM_XFORUM_DOMAINSDESC, 'cat_domains', $fc->getVar('cat_domains')), '&nbsp;', true);
    	else 
    		$sform->addElement(new XoopsFormCheckBoxDomains(_AM_XFORUM_DOMAINSDESC, 'cat_domains', array(urlencode(XOOPS_URL))), '&nbsp;', true);
    	
    } else {
    	if (count($fc->getVar('cat_domains')))
	    	foreach($fc->getVar('cat_domains') as $did => $domain)
	    		$sform->addElement(new XoopsFormHidden('cat_domains['.$did.']', $domain));
	    else 
	    	$sform->addElement(new XoopsFormHidden('cat_domains[0]', urlencode(XOOPS_URL)));
	    	
	    if (strlen($fc->getVar('cat_domain'))>0) {
	    	$sform->addElement(new XoopsFormHidden('cat_domain', $fc->getVar('cat_domain')));
	    } else {
	    	$sform->addElement(new XoopsFormHidden('cat_domain', urlencode(XOOPS_URL)));
	    }
    }
    
    if ($GLOBALS['xforumModuleConfig']['multilingual']) {
    	if (count($fc->getVar('cat_languages')))
    		$language_select = new XoopsFormSelect(_AM_XFORUM_LANGUAGESDESC, 'cat_languages', $fc->getVar('cat_languages'), 5, true);
    	else 
    		$language_select = new XoopsFormSelect(_AM_XFORUM_LANGUAGESDESC, 'cat_languages', array($GLOBALS['xoopsConfig']['language']), 5, true);
    	$xlanguage_handler = xoops_getmodulehandler('xlanguage_ext', 'xforum');
    	$language_select->addOptionArray($xlanguage_handler->getLanguages());
		$sform->addElement($language_select);
    } else {
    	if (count($fc->getVar('cat_languages')))
	    	foreach($fc->getVar('cat_languages') as $lid => $language)
	    		$sform->addElement(new XoopsFormHidden('cat_languages['.$lid.']', $language));
	    else 
	    	$sform->addElement(new XoopsFormHidden('cat_languages[0]', $GLOBALS['xoopsConfig']['language']));
    }
    
    $imgdir = "/modules/" . $GLOBALS['xforumModule']->dirname() . "/images/category";
    if (!$fc->getVar("cat_image")) $fc->setVar('cat_image', 'blank.gif');
    $graph_array = XoopsLists::getImgListAsArray(XOOPS_ROOT_PATH . $imgdir."/");
	array_unshift($graph_array, _NONE);
    $indeximage_select = new XoopsFormSelect('', 'indeximage', $fc->getVar('cat_image'));
    $indeximage_select->addOptionArray($graph_array);
	$indeximage_select->setExtra("onchange=\"showImgSelected('img', 'indeximage', '/".$imgdir."/', '', '" . XOOPS_URL . "')\"");
    $indeximage_tray = new XoopsFormElementTray(_AM_XFORUM_IMAGE, '&nbsp;');
    $indeximage_tray->addElement($indeximage_select);
    $indeximage_tray->addElement(new XoopsFormLabel('', "<br /><img src='" . XOOPS_URL . $imgdir . "/" . $fc->getVar('cat_image') . " 'name='img' id='img' alt='' />"));
    $sform->addElement($indeximage_tray);

    $sform->addElement(new XoopsFormText(_AM_XFORUM_SPONSORLINK, 'sponurl', 50, 80, $fc->getVar('cat_url', 'E')), false);
    $sform->addElement(new XoopsFormHidden('cat_id', $cat_id));

    $button_tray = new XoopsFormElementTray('', '');
    $button_tray->addElement(new XoopsFormHidden('op', 'save'));

    $butt_save = new XoopsFormButton('', '', _SUBMIT, 'submit');
    $butt_save->setExtra('onclick="this.form.elements.op.value=\'save\'"');
    $button_tray->addElement($butt_save);
    if ($cat_id) {
        $butt_delete = new XoopsFormButton('', '', _CANCEL, 'submit');
        $butt_delete->setExtra('onclick="this.form.elements.op.value=\'default\'"');
        $button_tray->addElement($butt_delete);
    }
    $sform->addElement($button_tray);
    $sform->display();
}

/**
 * newForum()
 *
 * @param integer $catid
 * @return
 */
function newForum($parent_forum = 0)
{
    editForum(null, $parent_forum);
}

/**
 * editForum()
 *
 * @param integer $catid
 * @return
 */
function editForum($ff, $parent_forum = 0)
{
    $GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
    if (!is_object($ff)) {
        $ff = $GLOBALS['forum_handler']->create();
        $new = true;
        $xforum = 0;
    } else {
        $xforum = $ff->getVar('forum_id');
        $new = false;
    }
    if ($parent_forum > 0) {
        $pf = $GLOBALS['forum_handler']->get($parent_forum);
    }

    $mytree = new XoopsTree($GLOBALS['xoopsDB']->prefix("xf_categories"), "cat_id", "0");

	require_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/class/xoopsformloader.php";
    if ($xforum) {
        $sform = new XoopsThemeForm(_AM_XFORUM_EDITTHISFORUM . " " . $ff->getVar('forum_name'), "op", xoops_getenv('PHP_SELF'));
    } else {
        $sform = new XoopsThemeForm(_AM_XFORUM_CREATENEWFORUM, "op", xoops_getenv('PHP_SELF'));

        $ff->setVar('parent_forum', $parent_forum);
        $ff->setVar('forum_order', 0);
        $ff->setVar('forum_name', '');
        $ff->setVar('forum_desc', '');
        $ff->setVar('forum_moderator', array(1));
        $ff->setVar('domain', urlencode(XOOPS_URL));
        $ff->setVar('domains', array(urlencode(XOOPS_URL)));
        $ff->setVar('languages', array($GLOBALS['xoopsConfig']['language']));
        $ff->setVar('forum_type', 0);
        $ff->setVar('allow_html', 1);
        $ff->setVar('allow_sig', 1);
        $ff->setVar('allow_polls', 1);
        $ff->setVar('allow_subject_prefix', 1);
        $ff->setVar('hot_threshold', 10);
        //$ff->setVar('allow_attachments', 1);
        $ff->setVar('attach_maxkb', 1000);
        $ff->setVar('attach_ext', 'zip|gif|jpg');
    }

    $sform->addElement(new XoopsFormText(_AM_XFORUM_FORUMNAME, 'forum_name', 50, 80, $ff->getVar('forum_name', 'E')), true);
    $sform->addElement(new XoopsFormDhtmlTextArea(_AM_XFORUM_FORUMDESCRIPTION, 'forum_desc', $ff->getVar('forum_desc', 'E'), 10, 60), false);

    $sform->addElement(new XoopsFormHidden('parent_forum', $ff->getVar('parent_forum')));
    if ($parent_forum == 0) {
        ob_start();
        if ($new) {
        	$mytree->makeMySelBox("cat_title", "cat_id", @$_GET['cat_id']);
        } else {
        	$mytree->makeMySelBox("cat_title", "cat_id", $ff->getVar('cat_id'));
        }
        $sform->addElement(new XoopsFormLabel(_AM_XFORUM_CATEGORY, ob_get_contents()));
        ob_end_clean();
    } else {
        $sform->addElement(new XoopsFormHidden('cat_id', $pf->getVar('cat_id')));
    }
    
	if ($GLOBALS['xforumModuleConfig']['multisite']) {
    
    	$sform->addElement(new XoopsFormSelectDomains(_AM_XFORUM_DEFAULTDOMAINSDESC, 'domain', $ff->getVar('domain')));
    	if (count($ff->getVar('domains')))
    		$sform->addElement(new XoopsFormCheckBoxDomains(_AM_XFORUM_DOMAINSDESC, 'domains', $ff->getVar('domains')));
    	else 
    		$sform->addElement(new XoopsFormCheckBoxDomains(_AM_XFORUM_DOMAINSDESC, 'domains', array(urlencode(XOOPS_URL))));
    } else {
    	if (count($ff->getVar('domains')))
	    	foreach($ff->getVar('domains') as $did => $domain)
	    		$sform->addElement(new XoopsFormHidden('domains['.$did.']', $domain));
	    else 
	    	$sform->addElement(new XoopsFormHidden('domains[0]', urlencode(XOOPS_URL)));
	    	
	    if (strlen($ff->getVar('domain'))>0) {
	    	$sform->addElement(new XoopsFormHidden('domain', $ff->getVar('domain')));
	    } else {
	    	$sform->addElement(new XoopsFormHidden('domain', urlencode(XOOPS_URL)));
	    }
    }
    
    if ($GLOBALS['xforumModuleConfig']['multilingual']) {
    	if (count($ff->getVar('languages')))
    		$language_select = new XoopsFormSelect(_AM_XFORUM_LANGUAGESDESC, 'languages', $ff->getVar('languages'), 5, true);
    	else 
    		$language_select = new XoopsFormSelect(_AM_XFORUM_LANGUAGESDESC, 'languages', array($GLOBALS['xoopsConfig']['language']), 5, true);
    	$xlanguage_handler = xoops_getmodulehandler('xlanguage_ext', 'xforum');
    	$language_select->addOptionArray($xlanguage_handler->getLanguages());
		$sform->addElement($language_select);
    } else {
    	if (count($ff->getVar('languages')))
	    	foreach($ff->getVar('languages') as $lid => $language)
	    		$sform->addElement(new XoopsFormHidden('languages['.$lid.']', $language));
	    else 
	    	$sform->addElement(new XoopsFormHidden('languages[0]', $GLOBALS['xoopsConfig']['language']));
    }
    

    $sform->addElement(new XoopsFormText(_AM_XFORUM_SET_FORUMORDER, 'forum_order', 5, 10, $ff->getVar('forum_order')), false);
    $status_select = new XoopsFormSelect(_AM_XFORUM_STATE, "forum_type", $ff->getVar('forum_type'));
    $status_select->addOptionArray(array('0' => _AM_XFORUM_ACTIVE, '1' => _AM_XFORUM_INACTIVE));
    $sform->addElement($status_select);

    $allowhtml_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOWHTML, 'allow_html', $ff->getVar('allow_html'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowhtml_radio);

    $allowsig_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOWSIGNATURES, 'allow_sig', $ff->getVar('allow_sig'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowsig_radio);

    $allowpolls_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOWPOLLS, 'allow_polls', $ff->getVar('allow_polls'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowpolls_radio);

    $allowprefix_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOW_SUBJECT_PREFIX, 'allow_subject_prefix', $ff->getVar('allow_subject_prefix'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowprefix_radio);

    $sform->addElement(new XoopsFormText(_AM_XFORUM_HOTTOPICTHRESHOLD, 'hot_threshold', 5, 10, $ff->getVar('hot_threshold')), true);

    /*
    $allowattach_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOW_ATTACHMENTS, 'allow_attachments', $ff->getVar('allow_attachments'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowattach_radio);
    */
    $sform->addElement(new XoopsFormText(_AM_XFORUM_ATTACHMENT_SIZE, 'attach_maxkb', 5, 10, $ff->getVar('attach_maxkb')), true);
    //$sform->addElement(new XoopsFormText(_AM_XFORUM_ALLOWED_EXTENSIONS, 'attach_ext', 50, 255, $ff->getVar('attach_ext')), true);
    $ext = $ff->getVar('attach_ext');
    $sform->addElement(new XoopsFormText(_AM_XFORUM_ALLOWED_EXTENSIONS, 'attach_ext', 50, 255, $ext), true);
   	$sform->addElement(new XoopsFormSelectUser(_AM_XFORUM_MODERATOR, 'forum_moderator', false, $ff->getVar("forum_moderator"), 5, true));

    $perm_tray = new XoopsFormElementTray(_AM_XFORUM_PERMISSIONS_TO_THIS_FORUM, '');
	$perm_checkbox = new XoopsFormCheckBox('', 'perm_template', $ff->isNew());
	$perm_checkbox->addOption(1, _AM_XFORUM_PERM_TEMPLATEAPP);
	$perm_tray->addElement($perm_checkbox);
	$perm_tray->addElement(new XoopsFormLabel('', '<a href="admin_permissions.php?action=template" target="_blank">'._AM_XFORUM_PERM_TEMPLATE.'</a>'));
    $sform->addElement($perm_tray);
   	
    $sform->addElement(new XoopsFormHidden('forum', $xforum));
    $sform->addElement(new XoopsFormHidden('op', "save"));

    $button_tray = new XoopsFormElementTray('', '');
    $button_tray->addElement(new XoopsFormButton('', '', _SUBMIT, 'submit'));

    $button_tray->addElement(new XoopsFormButton('', '', _AM_XFORUM_CLEAR, 'reset'));

    $butt_cancel = new XoopsFormButton('', '', _CANCEL, 'button');
    $butt_cancel->setExtra('onclick="history.go(-1)"');
    $button_tray->addElement($butt_cancel);

    $sform->addElement($button_tray);
    $sform->display();
}

function forum_admin_getPathStatus($path)
{
	if(empty($path)) return false;
	if(@is_writable($path)){
		$path_status = _AM_XFORUM_AVAILABLE;
	}elseif(!@is_dir($path)){
		$path_status = _AM_XFORUM_NOTAVAILABLE." <a href='admin_dashboard.php?op=createdir&amp;path=".urlencode($path)."'>"._AM_XFORUM_CREATETHEDIR.'</a>';
	}else{
		$path_status = _AM_XFORUM_NOTWRITABLE." <a href='admin_dashboard.php?op=setperm&amp;path=".urlencode($path)."'>"._AM_XFORUM_SETMPERM.'</a>';
	}
	return $path_status;
}

function forum_admin_mkdir($target, $mode=0777)
{
	// http://www.php.net/manual/en/function.mkdir.php
	return is_dir($target) or ( forum_admin_mkdir(dirname($target), $mode) and mkdir($target, $mode) );
}

function forum_admin_chmod($target, $mode = 0777)
{
	return @chmod($target, $mode);
}

function forum_getImageLibs()
{

	$imageLibs= array();
	unset($output, $status);
	if ( $GLOBALS['xforumModuleConfig']['image_lib'] == 1 or $GLOBALS['xforumModuleConfig']['image_lib'] == 0 ){
		$path = empty($GLOBALS['xforumModuleConfig']['path_magick'])?"":$GLOBALS['xforumModuleConfig']['path_magick']."/";
		@exec($path.'convert -version', $output, $status);
		if(empty($status)&&!empty($output)){
			if(preg_match("/imagemagick[ \t]+([0-9\.]+)/i",$output[0],$matches))
			   $imageLibs['imagemagick'] = $matches[0];
		}
		unset($output, $status);
	}
	 if ( $GLOBALS['xforumModuleConfig']['image_lib'] == 2 or $GLOBALS['xforumModuleConfig']['image_lib'] == 0 ){
		$path = empty($GLOBALS['xforumModuleConfig']['path_netpbm'])?"":$GLOBALS['xforumModuleConfig']['path_netpbm']."/";
		@exec($path.'jpegtopnm -version 2>&1',  $output, $status);
		if(empty($status)&&!empty($output)){
			if(preg_match("/netpbm[ \t]+([0-9\.]+)/i",$output[0],$matches))
			   $imageLibs['netpbm'] = $matches[0];
		}
		unset($output, $status);
	}

	$GDfuncList = get_extension_funcs('gd');
	ob_start();
	@phpinfo(INFO_MODULES);
	$output=ob_get_contents();
	ob_end_clean();
	$matches[1]='';
	if(preg_match("/GD Version[ \t]*(<[^>]+>[ \t]*)+([^<>]+)/s",$output,$matches)){
		$gdversion = $matches[2];
	}
	if( $GDfuncList ){
	 if( in_array('imagegd2',$GDfuncList) )
		$imageLibs['gd2'] = $gdversion;
	 else
		$imageLibs['gd1'] = $gdversion;
	}
	return $imageLibs;
}