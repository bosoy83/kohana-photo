<?php defined('SYSPATH') or die('No direct script access.');

class Acl_Assert_Photo implements Acl_Assert_Interface {

	protected $_site_id;

	public function __construct($arguments)
	{
		$this->_site_id = $arguments['site_id'];
	}

	public function assert(Acl $acl, $role = null, $resource = null, $privilege = null)
	{
		$album_site_id = (int) $resource->album->site_id;
		
		return $album_site_id == $this->_site_id;
	}
}