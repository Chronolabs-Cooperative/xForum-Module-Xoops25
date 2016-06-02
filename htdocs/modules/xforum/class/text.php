<?php

// $Id: report.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

class Ntext extends XoopsObject {
    function __construct()
    {
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('post_text', XOBJ_DTYPE_TXTAREA);
        $this->initVar('post_edit', XOBJ_DTYPE_TXTAREA);
    }
}

class xforumTextHandler extends XoopsPersistableObjectHandler 
{
    function __construct($db) {
        parent::__construct($db, 'xf_posts_text', 'Ntext', 'post_id');
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    return parent::cleanOrphan($this->db->prefix("xf_posts"), "post_id");
    }
}

?>