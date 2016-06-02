<?php

// $Id: report.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

class Nrate extends XoopsObject {
    function __construct()
    {
        $this->initVar('ratingid', XOBJ_DTYPE_INT);
        $this->initVar('topic_id', XOBJ_DTYPE_INT);
        $this->initVar('ratinguser', XOBJ_DTYPE_INT);
        $this->initVar('rating', XOBJ_DTYPE_INT);
        $this->initVar('ratingtimestamp', XOBJ_DTYPE_INT);
        $this->initVar('ratinghostname', XOBJ_DTYPE_TXTBOX);
    }
}

class xforumRateHandler extends XoopsPersistableObjectHandler 
{
    function __construct($db) {
        parent::__construct($db, 'xf_votedata', 'Nrate', 'ratingid');
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    return parent::cleanOrphan($this->db->prefix("xf_topics"), "topic_id");
    }
}

?>