<?php

// $Id: report.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

class Report extends XoopsObject {
    function __construct()
    {
        $this->initVar('report_id', XOBJ_DTYPE_INT);
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('reporter_uid', XOBJ_DTYPE_INT);
        $this->initVar('reporter_ip', XOBJ_DTYPE_INT);
        $this->initVar('report_time', XOBJ_DTYPE_INT);
        $this->initVar('report_text', XOBJ_DTYPE_TXTBOX);
        $this->initVar('report_result', XOBJ_DTYPE_INT);
        $this->initVar('report_memo', XOBJ_DTYPE_TXTBOX);
    }
}

class xforumReportHandler extends XoopsPersistableObjectHandler 
{
    function __construct($db) {
        parent::__construct($db, 'xf_report', 'Report', 'report_id');
    }
    function &getByPost($posts)
    {
	    $ret = array();
        if (!$posts) {
	        return $ret;
        }
        if (!is_array($posts)) $posts = array($posts);
        $post_criteria = new Criteria("post_id", "(" . implode(", ", $posts) . ")", "IN");
		$ret = $this->getAll($post_criteria);
        return $ret;
    }
    
    function &getAllReports($xforums = 0, $order = "ASC", $perpage = 0, $start, $report_result = 0, $report_id = 0)
    {
        if ($order == "DESC") {
            $operator_for_position = '>' ;
        } else {
            $GLOBALS['order'] = "ASC" ;
            $operator_for_position = '<' ;
        }
        $order_criteria = " ORDER BY r.report_id ".$order;

        if ($perpage <= 0) {
            $perpage = 10;
        }
        if (empty($start)) {
            $GLOBALS['start'] = 0;
        }
        $result_criteria = ' AND r.report_result = ' . $report_result;

        if (!$xforums) {
            $forum_criteria = '';
        } else if (!is_array($xforums)) {
            $xforums = array($xforums);
            $forum_criteria = ' AND p.forum_id IN (' . implode(',', $xforums) . ')';
        }
        $tables_criteria = ' FROM ' . $this->db->prefix('xf_report') . ' r, ' . $this->db->prefix('xf_posts') . ' p WHERE r.post_id= p.post_id';

        if ($report_id) {
            $result = $this->db->query("SELECT COUNT(*) as report_count" . $tables_criteria . $forum_criteria . $result_criteria . " AND report_id $operator_for_position $report_id" . $order_criteria);
            if ($result) $row = $this->db->fetchArray($result);
            $position = $row['report_count'];
            $GLOBALS['start'] = intval($position / $perpage) * $perpage;
        }

        $sql = "SELECT r.*, p.subject, p.topic_id, p.forum_id" . $tables_criteria . $forum_criteria . $result_criteria . $order_criteria;
        $result = $this->db->query($sql, $perpage, $GLOBALS['start']);
        $ret = array();
        //$report_handler = xoops_getmodulehandler('report', 'xforum');
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow; // return as array
        }
        return $ret;
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