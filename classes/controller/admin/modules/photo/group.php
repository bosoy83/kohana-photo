<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Photo_Group extends Controller_Admin_Front {

	protected $module_config = 'photo';
	
	public $auto_render = FALSE;
	
	public function before()
	{
		parent::before();
		
		if ( ! $this->request->current()->is_ajax()) {
			throw new HTTP_Exception_404();
		}
	}
	
	public function action_index()
	{
		$post = $this->request->current()->post();
		
		$group = Arr::get($post, 'group');
		$album_id = (int) Arr::get($post, 'album');
		$items = Arr::get($post, 'items');
		$operation = Arr::get($post, 'operation');
		
		$album_orm = ORM::factory('photo_Album')
			->where('group', '=', $group)
			->and_where('id', '=', $album_id)
			->find();
		
		if ( ! $album_orm->loaded() OR ! $this->acl->is_allowed($this->user, $album_orm, 'edit')) {
			throw new HTTP_Exception_404();
		}
		
		if (empty($items) OR ! is_array($items) OR empty($operation)) {
			throw new HTTP_Exception_404();
		}
		
		try {
			$this->_group_operation_move($operation, $items, $album_orm);
		} catch (Exception $e) {
			$code = $e->getCode();
			$this->response
				->status(ctype_digit($code) ? $code : 500)
				->body(Kohana_Exception::text($e));
			return;
		}
		
		$this->response
			->body('OK');
	}
	
	private function _group_operation_move($operation, array $items, Model_Photo_Album $album_orm)
	{
		$directions = array(
			'up' => ORM_Position::MOVE_PREV,
			'down' => ORM_Position::MOVE_NEXT,
			'first' => ORM_Position::MOVE_FIRST,
			'last' => ORM_Position::MOVE_LAST,
		);
	
		if ( ! isset($directions[$operation])) {
			throw new HTTP_Exception_404();
		}
	
		$direction = $directions[$operation];

		if ($operation === 'first' OR $operation === 'down') {
			$items = array_reverse($items);
		}

		$orm_helper = ORM_Helper::factory('photo');
		foreach ($items as $_id) {
			$_orm = ORM::factory('photo')
				->where('owner', '=', $album_orm->object_name())
				->and_where('owner_id', '=', $album_orm->id)
				->and_where('id', '=', $_id)
				->find();

			if ( ! $_orm->loaded()) {
				continue;
			}

			$orm_helper->orm($_orm);
			$orm_helper->position_move('position', $direction);
		}
	}
} 
