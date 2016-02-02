<?php defined('SYSPATH') or die('No direct script access.');

class Model_Photo extends ORM_Base {

	protected $_sorting = array('position' => 'asc');
	protected $_deleted_column = 'delete_bit';
	protected $_active_column = 'active';

	protected $_belongs_to = array(
		'album' => array(
			'model' => 'photo_Album',
			'foreign_key' => 'owner_id',
		),
	);

	public function labels()
	{
		return array(
			'owner_id' => 'Album',
			'title' => 'Title',
			'image' => 'Image',
			'text' => 'Text',
			'active' => 'Active',
			'position' => 'Position',
		);
	}

	public function rules()
	{
		return array(
			'id' => array(
				array('digit'),
			),
			'owner' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'owner_id' => array(
				array('not_empty'),
				array('digit'),
			),
			'title' => array(
				array('max_length', array(':value', 255)),
			),
			'image' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'text' => array(
				array('max_length', array(':value', 500)),
			),
			'position' => array(
				array('digit'),
			),
		);
	}

	public function filters()
	{
		return array(
			TRUE => array(
				array('trim'),
			),
			'title' => array(
				array('strip_tags'),
			),
			'active' => array(
				array(array($this, 'checkbox'))
			),
		);
	}

}
