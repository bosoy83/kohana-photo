<?php defined('SYSPATH') or die('No direct script access.');

class Model_Photo_Album extends ORM_Base {

	protected $_table_name = 'photos_albums';
	protected $_sorting = array('public_date' => 'desc');
	protected $_deleted_column = 'delete_bit';
	protected $_has_many = array(
		'photos' => array(
			'model' => 'photo',
			'foreign_key' => 'owner_id',
		),
	);

	public function labels()
	{
		return array(
			'uri' => 'URI',
			'group' => 'Group',
			'title' => 'Title',
			'image' => 'Cover',
			'status' => 'Status',
			'text' => 'Text',
			'public_date' => 'Public date',
			'title_tag' => 'Title tag',
			'keywords_tag' => 'Keywords tag',
			'description_tag' => 'Desription tag',
			'for_all' => 'For all sites',
		);
	}

	public function rules()
	{
		return array(
			'id' => array(
				array('digit'),
			),
			'site_id' => array(
				array('not_empty'),
				array('digit'),
			),
			'uri' => array(
				array('min_length', array(':value', 2)),
				array('max_length', array(':value', 255)),
				array('alpha_dash'),
			),
			'group' => array(
				array('not_empty'),
			),
			'status' => array(
				array('not_empty'),
				array('digit'),
				array('range', array(':value', 0, 2)),
			),
			'title' => array(
				array('not_empty'),
				array('min_length', array(':value', 2)),
				array('max_length', array(':value', 255)),
			),
			'image' => array(
				array('max_length', array(':value', 255)),
			),
			'title_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'keywords_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'description_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'public_date' => array(
				array('date'),
			),
		);
	}

	public function filters()
	{
		return array(
			TRUE => array(
				array('UTF8::trim'),
			),
			'title' => array(
				array('strip_tags'),
			),
			'title_tag' => array(
				array('strip_tags'),
			),
			'keywords_tag' => array(
				array('strip_tags'),
			),
			'description_tag' => array(
				array('strip_tags'),
			),
			'for_all' => array(
				array(array($this, 'checkbox'))
			),
		);
	}

	public function apply_mode_filter()
	{
		parent::apply_mode_filter();

		if($this->_filter_mode == ORM_Base::FILTER_FRONTEND) {
			$this->where($this->_object_name.'.public_date', '<=', date('Y-m-d H:i:00'));
		}
	}

}
