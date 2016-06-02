<?php


include 'admin_header.php';
xoops_cp_header();


$op = (!empty($_GET['op']) ? $_GET['op'] : (!empty($_POST['op']) ? $_POST['op'] : (!empty($_REQUEST['id']) ? "edit" : 'list')));
$profilefield_handler = xoops_getmodulehandler('field');
switch( $op ) {
default:
case "list":
	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation(basename(__FILE__));

	$fields = $profilefield_handler->getObjects(NULL, false, false);

	$module_handler = xoops_gethandler('module');
	$modules = $module_handler->getObjects(null, true);

	$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum');
	$criteria = new CriteriaCompo();
	$criteria->setSort('forum_order');
	$forums = $GLOBALS['forum_handler']->getObjects($criteria, true);
	unset($criteria);

	$categories[0] = array('forum_id' => 0, 'forum_title' => _AM_XFORUM_DEFAULT);
	if ( count($forums) > 0 ) {
		foreach (array_keys($forums) as $i ) {
			$categories[$forums[$i]->getVar('forum_id')] = array('forum_id' => $forums[$i]->getVar('forum_id'), 'forum_title' => $forums[$i]->getVar('forum_name'));
		}
	}
	$GLOBALS['xoopsTpl']->assign('forums', $categories);
	unset($categories);
	$valuetypes = array(XOBJ_DTYPE_ARRAY => _AM_XFORUM_ARRAY,
						XOBJ_DTYPE_EMAIL => _AM_XFORUM_EMAIL,
						XOBJ_DTYPE_INT => _AM_XFORUM_INT,
						XOBJ_DTYPE_TXTAREA => _AM_XFORUM_TXTAREA,
						XOBJ_DTYPE_TXTBOX => _AM_XFORUM_TXTBOX,
						XOBJ_DTYPE_URL => _AM_XFORUM_URL,
						XOBJ_DTYPE_OTHER => _AM_XFORUM_OTHER,
						XOBJ_DTYPE_MTIME => _AM_XFORUM_DATE);

	$fieldtypes = array('checkbox' => _AM_XFORUM_CHECKBOX,
							'group' => _AM_XFORUM_GROUP,
							'group_multi' => _AM_XFORUM_GROUPMULTI,
							'language' => _AM_XFORUM_LANGUAGE,
							'radio' => _AM_XFORUM_RADIO,
							'select' => _AM_XFORUM_SELECT,
							'select_multi' => _AM_XFORUM_SELECTMULTI,
							'textarea' => _AM_XFORUM_TEXTAREA,
							'dhtml' => _AM_XFORUM_DHTMLTEXTAREA,
							'editor' => _AM_XFORUM_EDITOR,
							'textbox' => _AM_XFORUM_TEXTBOX,
							'timezone' => _AM_XFORUM_TIMEZONE,
							'yesno' => _AM_XFORUM_YESNO,
							'date' => _AM_XFORUM_DATE,
							'datetime' => _AM_XFORUM_DATETIME,
							'longdate' => _AM_XFORUM_LONGDATE,
							'theme' => _AM_XFORUM_THEME,
							'autotext' => _AM_XFORUM_AUTOTEXT,
							'rank' => _AM_XFORUM_RANK);

	foreach (array_keys($fields) as $i ) {
		$fields[$i]['canEdit'] = $fields[$i]['field_config'] || $fields[$i]['field_show'] || $fields[$i]['field_edit'];
		$fields[$i]['canDelete'] = $fields[$i]['field_config'];
		$fields[$i]['fieldtype'] = $fieldtypes[$fields[$i]['field_type']];
		$fields[$i]['valuetype'] = $valuetypes[$fields[$i]['field_valuetype']];
		$categories[$i][] = $fields[$i];
		$weights[$i] = $fields[$i]['field_weight'];
	}
	//sort fields order in categories
	foreach (array_keys($fields) as $i ) {
		array_multisort($weights[$i], SORT_ASC, array_keys($categories[$i]), SORT_ASC, $categories[$i]);
	}
	ksort($categories);
	$GLOBALS['xoopsTpl']->assign('fieldcategories', $categories);
	$GLOBALS['xoopsTpl']->assign('token', $GLOBALS['xoopsSecurity']->getTokenHTML() );
	$template_main = "xforum_admin_fieldlist.html";
	break;

case "new":

	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation(basename(__FILE__));
	$obj = $profilefield_handler->create();
	include_once('../include/functions.php');
	$form = xforum_getFieldForm($obj);
	$form->display();
	break;

case "edit":

	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation(basename(__FILE__));
	$obj = $profilefield_handler->get($_REQUEST['id']);
	if ( !$obj->getVar('field_config') && !$obj->getVar('field_show') && !$obj->getVar('field_edit')  ) { //If no configs exist
		redirect_header('admin_field.php', 2, _AM_XFORUM_FIELDNOTCONFIGURABLE);
	}
	include_once('../include/functions.php');	
	$form = xforum_getFieldForm($obj);
	$form->display();
	break;

case "reorder":
	if ( !$GLOBALS['xoopsSecurity']->check()  ) {
		redirect_header('admin_field.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors() ));
	}
	if ( isset($_POST['field_ids']) && count($_POST['field_ids']) > 0 ) {
		$oldweight = $_POST['oldweight'];
		$oldcat = $_POST['oldcat'];
		$oldforums = $_POST['oldforums'];
		$forums = $_POST['forums'];
		$weight = $_POST['weight'];
		$ids = array();
		foreach ($_POST['field_ids'] as $field_id ) {
			if ( $oldweight[$field_id] != $weight[$field_id] || $oldcat[$field_id] != $category[$field_id]  || count($oldforums[$field_id]) != count(array_unique(array_merge($forums[$field_id], $oldforums[$field_id]))) ) {
				//if field has changed
				$ids[] = intval($field_id);
			}
		}
		if ( count($ids) > 0 ) {
			$errors = array();
			//if there are changed fields, fetch the fieldcategory objects
			$field_handler = xoops_getmodulehandler('field');
			$fields = $field_handler->getObjects(new Criteria('field_id', "(" . implode(',', $ids) . ")", "IN"), true);
			foreach ($ids as $i ) {
				$fields[$i]->setVar('field_weight', intval($weight[$i]) );
				$fields[$i]->setVar('forum_id', $forums[$i] );
				if ( !$field_handler->insert($fields[$i])  ) {
					$errors = array_merge($errors, $fields[$i]->getErrors() );
				}
			}
			if ( count($errors) == 0 ) {
				//no errors
				redirect_header('admin_field.php', 2, sprintf(_AM_XFORUM_SAVEDSUCCESS, _AM_XFORUM_FIELDS) );
			} else {
				redirect_header('admin_field.php', 3, implode('<br />', $errors) );
			}
		}
	}
	redirect_header('admin_field.php', 2, sprintf(_AM_XFORUM_SAVEDSUCCESS, _AM_XFORUM_FIELDS) );
	break;

case "save":
	if ( !$GLOBALS['xoopsSecurity']->check()  ) {
		redirect_header('admin_field.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors() ));
	}
	$redirect_to_edit = false;
	if ( isset($_REQUEST['id'])  ) {
		$obj = $profilefield_handler->get($_REQUEST['id']);
		if ( !$obj->getVar('field_config') && !$obj->getVar('field_show') && !$obj->getVar('field_edit')  ) { //If no configs exist
			redirect_header('admin_fields.php', 2, _AM_XFORUM_FIELDNOTCONFIGURABLE);
		}
	} else {
		$obj = $profilefield_handler->create();
		$obj->setVar('field_name', $_REQUEST['field_name']);
		$obj->setVar('field_moduleid', $GLOBALS['xforumModule']->getVar('mid') );
		$obj->setVar('field_show', 1);
		$obj->setVar('field_edit', 1);
		$obj->setVar('field_config', 1);
		$redirect_to_edit = true;
	}
	$obj->setVar('field_title', $_REQUEST['field_title']);
	$obj->setVar('field_description', $_REQUEST['field_description']);
	if ( $obj->getVar('field_config')  ) {
		$obj->setVar('field_type', $_REQUEST['field_type']);
		if ( isset($_REQUEST['field_valuetype'])  ) {
			$obj->setVar('field_valuetype', $_REQUEST['field_valuetype']);
		}
		$options = $obj->getVar('field_options');
		
		if ( isset($_REQUEST['removeOptions']) && is_array($_REQUEST['removeOptions'])  ) {
			foreach ($_REQUEST['removeOptions'] as $index ) {
				unset($options[$index]);
			}
			$redirect_to_edit = true;
		}
		
		if ( !empty($_REQUEST['addOption'])  ) {
			foreach ($_REQUEST['addOption'] as $option ) {
				if ( empty($option['value']) ) continue;
				$options[$option['key']] = $option['value'];
				$redirect_to_edit = true;
			}
		}
		$obj->setVar('field_options', $options);
	}
	if ( $obj->getVar('field_edit')  ) {
		$required = isset($_REQUEST['field_required']) ? $_REQUEST['field_required'] : 0;
		$obj->setVar('field_required', $required); //0 = no, 1 = yes
		if ( isset($_REQUEST['field_maxlength'])  ) {
			$obj->setVar('field_maxlength', $_REQUEST['field_maxlength']);
		}
		if ( isset($_REQUEST['field_default'])  ) {
			$field_default = $obj->getValueForSave($_REQUEST['field_default']);
			//Check for multiple selections
			if ( is_array($field_default)  ) {
				$obj->setVar('field_default', serialize($field_default) );
			} else {
				$obj->setVar('field_default', $field_default);
			}
		}
	}

	$obj->setVar('field_weight', $_REQUEST['field_weight']);
	$obj->setVar('forum_id', $_REQUEST['forum_id']);

	if ( $profilefield_handler->insert($obj)  ) {
		$groupperm_handler = xoops_gethandler('groupperm');

		$perm_arr = array();
		if ( $obj->getVar('field_show')  ) {
			$perm_arr[] = 'xforum_show';
			$perm_arr[] = 'xforum_visible';
		}
		if ( $obj->getVar('field_edit')  ) {
			$perm_arr[] = 'xforum_edit';
		}
		if ( $obj->getVar('field_edit') || $obj->getVar('field_show')  ) {
			$perm_arr[] = 'xforum_search';
		}
		if ( count($perm_arr) > 0 ) {
			foreach ($perm_arr as $perm ) {
				$criteria = new CriteriaCompo(new Criteria('gperm_name', $perm) );
				$criteria->add(new Criteria('gperm_itemid', intval($obj->getVar('field_id') )) );
				$criteria->add(new Criteria('gperm_modid', intval($GLOBALS['xforumModule']->getVar('mid') )) );
				if ( isset($_REQUEST[$perm]) && is_array($_REQUEST[$perm])  ) {
					$perms = $groupperm_handler->getObjects($criteria);
					if ( count($perms) > 0 ) {
						foreach (array_keys($perms) as $i ) {
							$groups[$perms[$i]->getVar('gperm_groupid')] = $perms[$i];
						}
					} else {
						$groups = array();
					}
					foreach ($_REQUEST[$perm] as $grouoid ) {
						$grouoid = intval($grouoid);
						if ( !isset($groups[$grouoid])  ) {
							$perm_obj = $groupperm_handler->create();
							$perm_obj->setVar('gperm_name', $perm);
							$perm_obj->setVar('gperm_itemid', intval($obj->getVar('field_id') ));
							$perm_obj->setVar('gperm_modid', $GLOBALS['xforumModule']->getVar('mid') );
							$perm_obj->setVar('gperm_groupid', $grouoid);
							$groupperm_handler->insert($perm_obj);
							unset($perm_obj);
						}
					}
					$removed_groups = array_diff(array_keys($groups), $_REQUEST[$perm]);
					if ( count($removed_groups) > 0 ) {
						$criteria->add(new Criteria('gperm_groupid', "(".implode(',', $removed_groups).")", "IN") );
						$groupperm_handler->deleteAll($criteria);
					}
					unset($groups);

				} else {
					$groupperm_handler->deleteAll($criteria);
				}
				unset($criteria);
			}
		}
		$url = $redirect_to_edit ? 'admin_field.php?op=edit&amp;id=' . $obj->getVar('field_id') : 'admin_field.php';
		redirect_header($url, 3, sprintf(_AM_XFORUM_SAVEDSUCCESS, _AM_XFORUM_FIELD) );
	}
	echo $obj->getHtmlErrors();
	$form = xforum_getFieldForm($obj);
	$form->display();
	break;

case "delete":
	$obj = $profilefield_handler->get($_REQUEST['id']);
	if ( !$obj->getVar('field_config')  ) {
		redirect_header('index.php', 2, _AM_XFORUM_FIELDNOTCONFIGURABLE);
	}
	if ( isset($_REQUEST['ok']) && $_REQUEST['ok'] == 1 ) {
		if ( !$GLOBALS['xoopsSecurity']->check()  ) {
			redirect_header('admin_field.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors() ));
		}
		if ( $profilefield_handler->delete($obj)  ) {
			redirect_header('admin_field.php', 3, sprintf(_AM_XFORUM_DELETEDSUCCESS, _AM_XFORUM_FIELD) );
		} else {
			echo $obj->getHtmlErrors();
		}
	} else {
		xoops_confirm(array('ok' => 1, 'id' => $_REQUEST['id'], 'op' => 'delete'), $_SERVER['REQUEST_URI'], sprintf(_AM_XFORUM_RUSUREDEL, $obj->getVar('field_title') ));
	}
	break;
}

if ( isset($template_main)  ) {
	$GLOBALS['xoopsTpl']->display("db:{$template_main}");
}

xoops_cp_footer();
?>