<?php

// $Id: category.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

class Category extends XoopsObject {

    function Category()
    {
	    $this->XoopsObject("xf_categories");
        $this->initVar('cat_id', XOBJ_DTYPE_INT);
        $this->initVar('pid', XOBJ_DTYPE_INT, 0);
        $this->initVar('cat_title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('cat_image', XOBJ_DTYPE_TXTBOX);
        $this->initVar('cat_description', XOBJ_DTYPE_TXTAREA);
        $this->initVar('cat_order', XOBJ_DTYPE_INT);
        $this->initVar('cat_url', XOBJ_DTYPE_URL);
		$this->initVar('cat_domain', XOBJ_DTYPE_TXTBOX);
        $this->initVar('cat_domains', XOBJ_DTYPE_ARRAY);
        $this->initVar('cat_languages', XOBJ_DTYPE_ARRAY);
    }
    
    function getURL()
    {
    	if ($GLOBALS['xforumModuleConfig']['htaccess']) {
    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : urldecode($this->getVar('cat_domain'))) . '/' . $GLOBALS['xforumModuleConfig']['baseurl'] . '/cat,'.$this->getVar('cat_id') . $GLOBALS['xforumModuleConfig']['endofurl'];
    	} else {
    		return (!$GLOBALS['xforumModuleConfig']['multisite'] ? XOOPS_URL : urldecode($this->getVar('cat_domain'))) . '/modules/xforum/index.php?cat='.$this->getVar('cat_id');
    	}
    }
}

class xforumCategoryHandler extends XoopsPersistableObjectHandler
{
    function xforumCategoryHandler($db) {
		parent::__construct($db, 'xf_categories', 'Category', 'cat_id', 'cat_title');
    }

    function &getAllCats($permission = false, $idAsKey = true, $tags = null, $admin = false)
    {
	    $perm_string = (empty($permission))?'all':'access';
        $_cachedCats[$perm_string]=array();
        
        $criteria = new CriteriaCompo(new Criteria('1','1'));

        if ($GLOBALS['xforumModuleConfig']['multisite']&$admin==false) {
        	$criteria->add(new Criteria('cat_domains', '%'.urlencode(XOOPS_URL).'%', 'LIKE'));
        	$criteria->add(new Criteria('cat_domains', '%"all"%', 'LIKE'), 'OR');
        }
        
        if ($GLOBALS['xforumModuleConfig']['multilingual']&$admin==false) 
        	$criteria->add(new Criteria('cat_languages', '%"'.$GLOBALS['xoopsConfig']['language'].'"%', 'LIKE'));
        	
        $criteria->setSort("cat_order");
        $categories = $this->getAll($criteria, $tags, $idAsKey);
        foreach(array_keys($categories) as $key){
            if ($permission && !$this->getPermission($categories[$key])) continue;
            if($idAsKey){
            	$_cachedCats[$perm_string][$key] = $categories[$key];
            }else{
            	$_cachedCats[$perm_string][] = $categories[$key];
        	}
        }
        return $_cachedCats[$perm_string];
    }

    function insert($category)
    {
        parent::insert($category, true);
        if ($category->isNew()) {
	        $this->applyPermissionTemplate($category);
        }

        return $category->getVar('cat_id');
    }

    function delete($category)
    {
        
		$GLOBALS['forum_handler'] = xoops_getmodulehandler('forum', 'xforum');
		$GLOBALS['forum_handler']->deleteAll(new Criteria("cat_id", $category->getVar('cat_id')), true, true);
        if ($result = parent::delete($category)) {
            // Delete group permissions
            return $this->deletePermission($category);
        } else {
	        $category->setErrors("delete category error: ".$sql);
            return false;
        }
    }

    /*
     * Check permission for a category
     *
     * TODO: get a list of categories per permission type
     *
     * @param	mixed (object or integer)	category object or ID
     * return	bool
     */
    function getPermission($category)
    {
        
        static $_cachedCategoryPerms;

        if (forum_isAdministrator()) return true;

        if(!isset($_cachedCategoryPerms)){
	        $getpermission = xoops_getmodulehandler('permission', 'xforum');
	        $_cachedCategoryPerms = $getpermission->getPermissions("category");
        }

        $cat_id = is_object($category)? $category->getVar('cat_id'):intval($category);
        $permission = (isset($_cachedCategoryPerms[$cat_id]['category_access'])) ? 1 : 0;

        return $permission;
    }
        
    function deletePermission($category)
    {
		$perm_handler = xoops_getmodulehandler('permission', 'xforum');
		return $perm_handler->deleteByCategory($category->getVar("cat_id"));
	}
    
    function applyPermissionTemplate($category)
    {
		$perm_handler = xoops_getmodulehandler('permission', 'xforum');
		return $perm_handler->setCategoryPermission($category->getVar("cat_id"));
	}
}

?>