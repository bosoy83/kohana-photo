<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Photo extends Controller_Admin_Front {

	protected $module_config = 'photo';
	protected $menu_active_item = 'modules';
	protected $title = 'Photo';
	protected $sub_title = 'Photo';
	
	protected $album_id;
	protected $group_options;
	protected $group_key;
	protected $controller_name = array(
		'album' => 'photo_album',
		'element' => 'photo_element',
		'group' => 'photo_group',
		'multiupload' => 'photo_multiupload',
	);
	
	public function before()
	{
		parent::before();
		$this->album_id = (int) Request::current()->query('album');
		$this->template
			->bind_global('ALBUM_ID', $this->album_id);
		
		$this->group_options = Kohana::$config->load('_photo.groups');
		$this->_sort_group_options($this->group_options);
		$this->template
			->bind_global('GROUP_OPTIONS', $this->group_options);
		
		$this->group_key = Request::current()->query('group');
		if (empty($this->group_key)) {
			$this->group_key = 'common';
		}
		$this->template
			->bind_global('GROUP_KEY', $this->group_key);
	
		$query_controller = $this->request->query('controller');
		if ( ! empty($query_controller) AND is_array($query_controller)) {
			$this->controller_name = $this->request->query('controller');
		}
		$this->template
			->bind_global('CONTROLLER_NAME', $this->controller_name);
		
		$this->title = __($this->title);
		$this->sub_title = __($this->sub_title);
	}
	
	private function _sort_group_options( & $options)
	{
		$array = array();
		if (isset($options['common'])) {
			$array['common'] = $options['common'];
			unset($options['common']);
		}
		asort($options);
		$options = array_merge($array, $options);
	}
	
	protected function layout_aside()
	{
		$menu_items = array_merge_recursive(
			Kohana::$config->load('admin/aside/photo')->as_array(),
			$this->menu_left_ext
		);
		
		return parent::layout_aside()
			->set('menu_items', $menu_items)
			->set('replace', array(
				'{GROUP_KEY}' => $this->group_key,
				'{ALBUM_ID}' =>	$this->album_id,
			));
	}

	protected function left_menu_album_add($orm)
	{
		if ($this->acl->is_allowed($this->user, $orm, 'add') ) {
			$this->menu_left_add(array(
				'photo_albums' => array(
					'sub' => array(
						'add' => array(
							'title' => __('Add album'),
							'link' => Route::url('modules', array(
								'controller' => $this->controller_name['album'],
								'action' => 'edit',
								'query' => 'group={GROUP_KEY}'
							)),
						),
					),
				),
			));
		}
	}
	
	protected function left_menu_element_list()
	{
		if (empty($this->back_url)) {
			$link = Route::url('modules', array(
				'controller' => $this->controller_name['element'],
				'query' => 'group={GROUP_KEY}&album={ALBUM_ID}'
			));
		} else {
			$link = $this->back_url;
		}
		
		$this->menu_left_add(array(
			'photo_elements' => array(
				'title' => __('Photos list'),
				'link' => $link,
				'sub' => array(),
			),
		));
	}
	
	protected function left_menu_element_add(Model_Photo_Album $album_orm)
	{
		
		$link = Route::url('modules', array(
			'controller' => $this->controller_name['element'],
			'action' => 'edit',
			'query' => 'group={GROUP_KEY}&album={ALBUM_ID}'
		));
		
		if ( ! empty($this->back_url)) {
			$link .= '&back_url='.urlencode($this->back_url);
		}
		
		
		if ($this->acl->is_allowed($this->user, $album_orm, 'edit') ) {
			$this->menu_left_add(array(
				'photo_elements' => array(
					'sub' => array(
						'add' => array(
							'title' => __('Add photo'),
							'link' => $link,
						),
					),
				),
			));
		}
	}
	
	protected function left_menu_element_fix($orm)
	{
		$can_fix_all = $this->acl->is_allowed($this->user, $orm, 'fix_all');
		
		if ($can_fix_all) {
			$this->menu_left_add(array(
				'photo_elements' => array(
					'sub' => array(
						'fix' => array(
							'title' => __('Fix positions'),
							'link'  => Route::url('modules', array(
								'controller' => $this->controller_name['element'],
								'action' => 'position',
								'query' => 'group={GROUP_KEY}&album={ALBUM_ID}&mode=fix'
							)),
						),
					),
				),
			));
		}
	}
	
	protected function _get_breadcrumbs()
	{
		$query_array = array(
			'group' => $this->group_key,
		);
	
		return array(
			array(
				'title' => __('Photo albums'),
				'link' => Route::url('modules', array(
					'controller' => $this->controller_name['album'],
					'query' => Helper_Page::make_query_string($query_array),
				)),
			)
		);
	}
}

