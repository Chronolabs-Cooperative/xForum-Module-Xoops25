<?php



defined('XOOPS_ROOT_PATH') or die("XOOPS root path not defined");

class XForumExtras extends XoopsObject
{
    function __construct($fields)
    {
		$this->initVar('post_id', XOBJ_DTYPE_INT, null, true);			
        $this->init($fields);	
    }


	
    function XForumExtras($fields)
    {
        $this->__construct($fields);
    }

    /**
    * Initiate variables
    * @param array $fields field information array of {@link XoopsObjectsField} objects
    */
    function init($fields)
    {
        if (is_array($fields) && count($fields) > 0) {
            foreach (array_keys($fields) as $key ) {
                $this->initVar($key, $fields[$key]->getVar('field_valuetype'), $fields[$key]->getVar('field_default', 'n'), $fields[$key]->getVar('field_required'), $fields[$key]->getVar('field_maxlength'));
            }
        }
    }
}
/**
 * @package kernel
 * @copyright copyright &copy; 2000 XOOPS.org
 */
class XForumExtrasHandler extends XoopsPersistableObjectHandler
{
    /**
    * holds reference to {@link ObjectsFieldHandler} object
    */
    var $_fHandler;

    /**
    * Array of {@link XoopsObjectsField} objects
    * @var array
    */
    var $_fields = array();

    function __construct($db)
    {
        parent::__construct($db, "xf_extras", 'XForumExtras', "post_id");
        $this->_fHandler = xoops_getmodulehandler('field', 'xforum');
    }

    /**
     * create a new {@link XForumExtras}
     *
     * @param bool $isNew Flag the new objects as "new"?
     *
     * @return object {@link XForumExtras}
     */
    function &create($isNew = true)
    {
        $obj = new $this->className($this->loadFields() );
        $obj->handler = $this;
        $obj->setNew();
        return $obj;
    }

    /**
     * Get a {@link XForumExtras}
     *
     * @param   boolean $createOnFailure create a new {@link XForumExtras} if none is feteched
     *
     * @return  object {@link XForumExtras}
     */
    function &get($uid, $createOnFailure = true)
    {
        $obj = parent::get($uid);
        if (!is_object($obj) && $createOnFailure) {
            $obj = $this->create();
        }
        return $obj;
    }

    /**
    * Create new {@link ObjectsField} object
    *
    * @param bool $isNew
    *
    * @return object
    */
    function &createField($isNew = true)
    {
        $return = $this->_fHandler->create($isNew);
        return $return;
    }

    /**
    * Load field information
    *
    * @return array
    */
    function loadFields()
    {
        if (count($this->_fields) == 0) {
            $this->_fields = $this->_fHandler->loadFields();
        }
        return $this->_fields;
    }
	
	function getPostVars()
	{
		return array('post_id', 'topic_id', 'forum_id', 'post_time', 'poster_ip', 'poster_name', 'subject', 'pid', 'dohtml', 'dosmiley', 'doxcode', 'doimage',
 					 'dobr', 'uid', 'icon', 'attachsig', 'approved', 'post_karma', 'require_reply', 'attachment', 'post_text', 'post_edit', 'tags');
	}


    function getFields($criteria, $id_as_key = true, $as_object = true)
    {
        return $this->_fHandler->getObjects($criteria, $id_as_key, $as_object);
    }

    /**
    * Insert a field in the database
    *
    * @param object $field
    * @param bool $force
    *
    * @return bool
    */
    function insertField($field, $force = false)
    {
        return $this->_fHandler->insert($field, $force);
    }

    /**
    * Delete a field from the database
    *
    * @param object $field
    * @param bool $force
    *
    * @return bool
    */
    function deleteField($field, $force = false)
    {
        return $this->_fHandler->delete($field, $force);
    }

    /**
    * Save a new field in the database
    *
    * @param array $vars array of variables, taken from $module->loadInfo('Extras')['field']
    * @param int $categoryid ID of the category to add it to
    * @param int $type valuetype of the field
    * @param int $moduleid ID of the module, this field belongs to
    * @param int $weight
    *
    * @return string
    **/
    function saveField($vars, $weight = 0)
    {
        $field = $this->createField();
        $field->setVar('field_name', $vars['name']);
        $field->setVar('field_valuetype', $vars['valuetype']);
        $field->setVar('field_type', $vars['type']);
        $field->setVar('field_weight', $weight);
        if (isset($vars['title'])) {
            $field->setVar('field_title', $vars['title']);
        }
        if (isset($vars['description'])) {
            $field->setVar('field_description', $vars['description']);
        }
        if (isset($vars['required'])) {
            $field->setVar('field_required', $vars['required']); //0 = no, 1 = yes
        }
        if (isset($vars['maxlength'])) {
            $field->setVar('field_maxlength', $vars['maxlength']);
        }
        if (isset($vars['default'])) {
            $field->setVar('field_default', $vars['default']);
        }
        if (isset($vars['notnull'])) {
            $field->setVar('field_notnull', $vars['notnull']);
        }
        if (isset($vars['show'])) {
            $field->setVar('field_show', $vars['show']);
        }
        if (isset($vars['edit'])) {
            $field->setVar('field_edit', $vars['edit']);
        }
        if (isset($vars['config'])) {
            $field->setVar('field_config', $vars['config']);
        }
        if (isset($vars['options'])) {
            $field->setVar('field_options', $vars['options']);
        } else {
            $field->setVar('field_options', array() );
        }
        if ($this->insertField($field)) {
            $msg = '&nbsp;&nbsp;Field <b>' . $vars['name'] . '</b> added to the database';
        } else {
            $msg = '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Could not insert field <b>' . $vars['name'] . '</b> into the database. '.implode(' ', $field->getErrors()) . $this->db->error() . '</span>';
        }
        unset($field);
        return $msg;
    }

    /**
     * insert a new object in the database
     *
     * @param object $obj reference to the object
     * @param bool $force whether to force the query execution despite security settings
     * @param bool $checkObject check if the object is dirty and clean the attributes
     *
     * @return bool FALSE if failed, TRUE if already present and unchanged or successful
     */

    function insert($obj, $force = false, $checkObject = true)
    {
        $uservars = $this->getPostVars();
        foreach ($uservars as $var) {
			if ($var!='post_id')
	            unset($obj->vars[$var]);
        }
        if (count($obj->vars) == 0) {
            return true;
        }
        return parent::insert($obj, $force, $checkObject);
    }

}
?>