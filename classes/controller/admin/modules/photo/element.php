<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Photo_Element extends Controller_Admin_Modules_Photo {

	private $filter_type_options;

	public function before()
	{
		parent::before();

		if (empty($this->album_id)) {
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['album'],
			));
			$this->request->current()
				->redirect($this->back_url);
		}
		
		$this->filter_type_options = array(
			'all' => __('all'),
			'own' => __('own'),
		);
	}

	public function action_index()
	{
		$album_orm = ORM::factory('photo_Album')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->album_id)
			->find();
		if ( ! $album_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$orm = ORM::factory('photo')
			->where('owner', '=', $album_orm->object_name())
			->where('owner_id', '=', $this->album_id);
		
		$this->_apply_filter($orm);
			
		$paginator_orm = clone $orm;
		$paginator = new Paginator('admin/layout/paginator');
		$paginator
			->per_page(20)
			->count($paginator_orm->count_all());
		unset($paginator_orm);
		
		$list = $orm
			->paginator($paginator)
			->find_all();
			
		$this->template
			->set_filename('modules/photo/element/list')
			->set('list', $list)
			->set('filter_type_options', $this->filter_type_options)
			->set('paginator', $paginator)
			->set('acl_album_can_edit', $this->acl->is_allowed($this->user, $album_orm, 'edit'));
			
		$this->left_menu_album_add($album_orm);
		$this->left_menu_element_list();
		$this->left_menu_element_add($album_orm);
		$this->left_menu_element_fix($orm);
		
		$this->title = __('List');;
	}

	private function _apply_filter($orm)
	{
		$filter_query = $this->request->query('filter');

		if ( ! empty($filter_query)) {
			$title = Arr::get($filter_query, 'title');
			if ( ! empty($title)) {
				$orm->where('title', 'like', '%'.$title.'%');
			}

			$type = Arr::get($filter_query, 'type');
			if ( ! empty($type) AND $type == 'own') {
				$orm->where('site_id', '=', SITE_ID);
			}
		}
	}

	public function action_edit()
	{
		$album_orm = ORM::factory('photo_Album')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->album_id)
			->find();
		if ( ! $album_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $this->request->current()->param('id');
		$helper_orm = ORM_Helper::factory('photo');
		$orm = $helper_orm->orm();
		if ( (bool) $id) {
			$orm
				->where('id', '=', $id)
				->where('owner', '=', $album_orm->object_name())
				->where('owner_id', '=', $this->album_id)
				->find();
		
			if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
				throw new HTTP_Exception_404();
			}
			$this->title = __('Edit photo');
		} else {
			$this->title = __('Add photo');
		}
		
		if (empty($this->back_url)) {
			$query_array = array(
				'group' => $this->group_key,
				'album' => $this->album_id,
			);
			$query_array = Paginator::query($request, $query_array);
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['element'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		if ($this->is_cancel) {
			$request
				->redirect($this->back_url);
		}

		$errors = array();
		$submit = $request->post('submit');
		if ($submit) {
			try {
				if ( (bool) $id) {
					$orm->updater_id = $this->user->id;
					$orm->updated = date('Y-m-d H:i:s');
					$reload = FALSE;
				} else {
					$orm->owner = $album_orm->object_name();
					$orm->owner_id = $this->album_id;
					$orm->creator_id = $this->user->id;
					$reload = TRUE;
				}
				
				$values = $request->post();
				
				$helper_orm->save($values + $_FILES);
				
				if ($reload) {
					if ($submit != 'save_and_exit') {
						$this->back_url = Route::url('modules', array(
							'controller' => $request->controller(),
							'action' => $request->action(),
							'id' => $orm->id,
							'query' => Helper_Page::make_query_string($request->query()),
						));
					}
						
					$request
						->redirect($this->back_url);
				}
			} catch (ORM_Validation_Exception $e) {
				$errors = $this->errors_extract($e);
			}
		}

		// If add action then $submit = NULL
		if ( ! empty($errors) OR $submit != 'save_and_exit') {
			
			$albums_list = ORM::factory('photo_Album')
				->find_all()
				->as_array('id', 'title');

			if ( ! $orm->loaded()) {
				$orm->owner_id = $this->album_id;
			}

			$this->template
				->set_filename('modules/photo/element/edit')
				->set('errors', $errors)
				->set('helper_orm', $helper_orm)
				->set('albums_list', $albums_list);
			
			$this->left_menu_album_add($album_orm);
			$this->left_menu_element_list();
			$this->left_menu_element_add($album_orm);
		} else {
			$request
				->redirect($this->back_url);
		}
	}

	public function action_view()
	{
		$album_orm = ORM::factory('photo_Album')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->album_id)
			->find();
		if ( ! $album_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $request->param('id');
		$helper_orm = ORM_Helper::factory('photo');
		$orm = $helper_orm->orm();
		$orm
			->where('owner', '=', $album_orm->object_name())
			->where('owner_id', '=', $this->album_id)
			->where('id', '=', $id)
			->find();
			
		if ( ! $orm->loaded()) {
			throw new HTTP_Exception_404();
		}
				
		if (empty($this->back_url)) {
			$query_array = array(
				'group' => $this->group_key,
				'album' => $this->album_id,
			);
			$query_array = Paginator::query($request, $query_array);
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['element'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		$this->template
			->set_filename('modules/photo/element/view')
			->set('helper_orm', $helper_orm)
			->set('album', $album_orm->as_array());
		
		$this->title = __('Viewing');
		
		$this->left_menu_album_add($album_orm);
		$this->left_menu_element_list();
		$this->left_menu_element_add($album_orm);
		$this->left_menu_element_fix($orm);
	}

	public function action_delete()
	{
		$album_orm = ORM::factory('photo_Album')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->album_id)
			->find();
		if ( ! $album_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $request->param('id');
		
		$helper_orm = ORM_Helper::factory('photo');
		$orm = $helper_orm->orm();
		$orm
			->where('owner', '=', $album_orm->object_name())
			->and_where('owner_id', '=', $this->album_id)
			->and_where('id', '=', $id)
			->find();
		
		if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
			throw new HTTP_Exception_404();
		}
		
		if ($this->element_delete($helper_orm)) {
			if (empty($this->back_url)) {
				$query_array = array(
					'group' => $this->group_key,
					'album' => $this->album_id,
				);
				$query_array = Paginator::query($request, $query_array);
				$this->back_url = Route::url('modules', array(
					'controller' => $this->controller_name['element'],
					'query' => Helper_Page::make_query_string($query_array),
				));
			}
		
			$request
				->redirect($this->back_url);
		}
	}

	public function action_position()
	{
		$album_orm = ORM::factory('photo_Album')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->album_id)
			->find();
		if ( ! $album_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $request->param('id');
		$mode = $request->query('mode');
		$errors = array();
		$helper_orm = ORM_Helper::factory('photo');
	
		try {
			$this->element_position($helper_orm, $id, $mode);
		} catch (ORM_Validation_Exception $e) {
			$errors = $this->errors_extract($e);
		}
	
		if (empty($errors)) {
			if (empty($this->back_url)) {
				$query_array = array(
					'group' => $this->group_key,
					'album' => $this->album_id,
				);
				if ($mode != 'fix') {
					$query_array = Paginator::query($request, $query_array);
				}
	
				$this->back_url = Route::url('modules', array(
					'controller' => $this->controller_name['element'],
					'query' => Helper_Page::make_query_string($query_array),
				));
			}
	
			$request
				->redirect($this->back_url);
		}
	}
	
	protected function _get_breadcrumbs()
	{
		$breadcrumbs = parent::_get_breadcrumbs();
		
		$query_array = array(
			'group' => $this->group_key,
			'album' => $this->album_id,
		);
		
		$album_orm = ORM::factory('photo_Album')
			->and_where('id', '=', $this->album_id)
			->find();
		if ($album_orm->loaded()) {
			$breadcrumbs[] = array(
				'title' => $album_orm->title,
				'link' => Route::url('modules', array(
					'controller' => $this->controller_name['element'],
					'query' => Helper_Page::make_query_string($query_array),
				)),
			);
		}
		
		$action = $this->request->current()
			->action();
		if (in_array($action, array('edit', 'view'))) {
			$id = (int) $this->request->current()->param('id');
			$element_orm = ORM::factory('photo')
				->where('id', '=', $id)
				->find();
			if ($element_orm->loaded()) {
				switch ($action) {
					case 'edit':
						$_str = ' ['.__('edition').']';
						break;
					case 'view':
						$_str = ' ['.__('viewing').']';
						break;
					default:
						$_str = '';
				}
				
				$breadcrumbs[] = array(
					'title' => $element_orm->title.$_str,
				);
			} else {
				$breadcrumbs[] = array(
					'title' => ' ['.__('new photo').']',
				);
			}
		}
		
		return $breadcrumbs;
	}
} 
