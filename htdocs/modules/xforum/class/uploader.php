<?php

// $Id: uploader.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $

 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}
include_once XOOPS_ROOT_PATH . "/class/uploader.php";
class forum_uploader extends XoopsMediaUploader {
    var $ext = "";
    var $ImageSizeCheck = false;
    var $FileSizeCheck = true;
    var $CheckMediaTypeByExt = true;

    /**
     * No admin check for uploads
     */
    /**
     * Constructor
     *
     * @param string 	$uploadDir
     * @param array 	$allowedMimeTypes
     * @param int 		$maxFileSize
     * @param int 		$maxWidth
     * @param int 		$maxHeight
     */
    function forum_uploader($uploadDir, $allowedMimeTypes = 0, $maxFileSize = 0, $maxWidth = 0, $maxHeight = 0)
    {
        if (!is_array($allowedMimeTypes)) {
	        if(empty($allowedMimeTypes) || $allowedMimeTypes == "*"){
                $allowedMimeTypes = array();
	        }else{
	            $allowedMimeTypes = explode("|", strtolower($allowedMimeTypes));
            }
        }
	    $allowedMimeTypes = array_filter(array_map("trim", $allowedMimeTypes));
        $this->XoopsMediaUploader($uploadDir, $allowedMimeTypes, $maxFileSize, $maxWidth, $maxHeight);
    }

    /**
     * Set the CheckMediaTypeByExt
     *
     * @param string $value
     */
    function setCheckMediaTypeByExt($value = true)
    {
        $this->CheckMediaTypeByExt = $value;
    }

    /**
     * Set the imageSizeCheck
     *
     * @param string $value
     */
    function setImageSizeCheck($value)
    {
        $this->ImageSizeCheck = $value;
    }

    /**
     * Set the fileSizeCheck
     *
     * @param string $value
     */
    function setFileSizeCheck($value)
    {
        $this->FileSizeCheck = $value;
    }

    /**
     * Get the file extension
     *
     * @return string
     */
    function getExt()
    {
        $this->ext = strtolower(ltrim(strrchr($this->getMediaName(), '.'), '.'));
        return $this->ext;
    }

    /**
     * Is the file the right size?
     *
     * @return bool
     */
    function checkMaxFileSize()
    {
        if (!$this->FileSizeCheck) {
            return true;
        }
        if ($this->mediaSize > $this->maxFileSize) {
            return false;
        }
        return true;
    }

    /**
     * Is the picture the right width?
     *
     * @return bool
     */
    function checkMaxWidth()
    {
        if (!$this->ImageSizeCheck) {
            return true;
        }
        if (false !== $dimension = getimagesize($this->mediaTmpName)) {
            if ($dimension[0] > $this->maxWidth) {
                return false;
            }
        } else {
            trigger_error(sprintf('Failed fetching image size of %s, skipping max width check..', $this->mediaTmpName), E_USER_WARNING);
        }
        return true;
    }

    /**
     * Is the picture the right height?
     *
     * @return bool
     */
    function checkMaxHeight()
    {
        if (!$this->ImageSizeCheck) {
            return true;
        }
        if (false !== $dimension = getimagesize($this->mediaTmpName)) {
            if ($dimension[1] > $this->maxHeight) {
                return false;
            }
        } else {
            trigger_error(sprintf('Failed fetching image size of %s, skipping max height check..', $this->mediaTmpName), E_USER_WARNING);
        }
        return true;
    }

    /**
     * Is the file the right Mime type
     *
     * (is there a right type of mime? ;-)
     *
     * @return bool
     */
    function checkMimeType()
    {
        if ($this->CheckMediaTypeByExt) $type = $this->getExt();
        else $type = $this->mediaType;
        if (count($this->allowedMimeTypes) > 0 && !in_array($type, $this->allowedMimeTypes)) {
            return false;
        }
        return true;
    }
}

?>