<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Photo_Album extends Controller_Admin_Modules_Photo {

	private $filter_type_options;
	private $not_deleted_albums = array();

	public function before()
	{
		parent::before();
		
		$this->filter_type_options = array(
			'all' => __('all'),
			'own' => __('own'),
		);
	}
	
	public function action_index()
	{
		$orm = ORM::factory('photo_Album')
			->where('group', '=', $this->group_key);
		
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
			->set_filename('modules/photo/album/list')
			->set('list', $list)
			->set('hided_list', $this->get_hided_list($orm->object_name()))
			->set('not_deleted_albums', $this->not_deleted_albums)
			->set('paginator', $paginator)
			->set('filter_type_options', $this->filter_type_options);
			
		$this->left_menu_album_add($orm);
		
		$this->title = __('Photo albums');
		$this->sub_title = __('List'); 
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
		$request = $this->request->current();
		$this->category_id = $id = (int) $this->request->current()->param('id');
		$helper_orm = ORM_Helper::factory('photo_Album');
		$orm = $helper_orm->orm();
		if ( (bool) $id) {
			$orm
				->and_where('id', '=', $id)
				->find();
			if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
				throw new HTTP_Exception_404();
			}
			$this->title = __('Edit album');
		} else {
			$this->title = __('Add album');
		}
		
		if (empty($this->back_url)) {
			$query_array = array(
				'group' => $this->group_key,
			);
			$query_array = Paginator::query($request, $query_array);
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['album'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		if ($this->is_cancel) {
			$request->redirect($this->back_url);
		}

		$errors = array();
		$submit = Request::$current->post('submit');
		if ($submit) {
			try {
				if ($orm->loaded()) {
					$orm->updater_id = $this->user->id;
					$orm->updated = date('Y-m-d H:i:s');
					$reload = FALSE;
				} else {
					$orm->group = $this->group_key;
					$orm->site_id = SITE_ID;
					$orm->creator_id = $this->user->id;
					$reload = TRUE;
				}

				$values = $this->meta_seo_reset(
					$this->request->current()->post(),
					'meta_tags'
				);
				
				$values['public_date'] = $this->value_multiple_date($values, 'public_date');
				if (empty($values['uri']) OR row_exist($orm, 'uri', $values['uri'])) {
					$values['uri'] = transliterate_unique($values['title'], $orm, 'uri');
				}
				
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
			
			$status_list = Kohana::$config->load('_photo.status');
			
			$this->template
				->set_filename('modules/photo/album/edit')
				->set('errors', $errors)
				->set('helper_orm', $helper_orm)
				->set('status_list', $status_list);
			
			$this->left_menu_album_add($orm);
		} else {
			$request
				->redirect($this->back_url);
		}
	}

	public function action_delete()
	{
		$request = $this->request->current();
		$id = (int) $request->param('id');
		
		$helper_orm = ORM_Helper::factory('photo_Album');
		$orm = $helper_orm->orm();
		$orm
			->and_where('id', '=', $id)
			->find();
		
		if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
			throw new HTTP_Exception_404();
		}
		if (in_array($orm->code, $this->not_deleted_albums)) {
			throw new HTTP_Exception_404();
		}
		
		if ($this->element_delete($helper_orm)) {
			if (empty($this->back_url)) {
				$query_array = array(
					'group' => $this->group_key,
				);
				$query_array = Paginator::query($request, $query_array);
				$this->back_url = Route::url('modules', array(
					'controller' => $this->controller_name['album'],
					'query' => Helper_Page::make_query_string($query_array),
				));
			}
				
			$request
				->redirect($this->back_url);
		}
	}

	public function action_visibility()
	{
		$request = $this->request->current();
		$id = (int) $request->param('id');
		$mode = $request->query('mode');
		
		$orm = ORM::factory('photo_Album')
			->and_where('id', '=', $id)
			->find();
		
		if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'hide')) {
			throw new HTTP_Exception_404();
		}
		
		if (in_array($orm->code, $this->not_deleted_albums)) {
			throw new HTTP_Exception_404();
		}
		
		if ($mode == 'hide') {
			$this->element_hide($orm->object_name(), $orm->id);
		} elseif ($mode == 'show') {
			$this->element_show($orm->object_name(), $orm->id);
		}
		
		if (empty($this->back_url)) {
			$query_array = array(
				'group' => $this->group_key,
			);
			$query_array = Paginator::query($request, $query_array);
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['album'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		$request
			->redirect($this->back_url);
	}

	protected function _get_breadcrumbs()
	{
		$breadcrumbs = parent::_get_breadcrumbs();
	
		$query_array = array(
			'group' => $this->group_key,
		);
	
		$request = $this->request->current();
		if (in_array($request->action(), array('edit'))) {
			$id = (int) $this->request->current()->param('id');
			$album_orm = ORM::factory('photo_Album')
				->where('id', '=', $id)
				->find();
			if ($album_orm->loaded()) {
				$breadcrumbs[] = array(
					'title' => $album_orm->title.' ['.__('edition').']',
				);
			} else {
				$breadcrumbs[] = array(
					'title' => ' ['.__('new album').']',
				);
			}
		}
	
		return $breadcrumbs;
	}

} 
