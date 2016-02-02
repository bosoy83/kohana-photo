<?php defined('SYSPATH') or die('No direct script access.');

/*
 * FIXME создавать фотоальбом по требованию
 */

class Injector_Photo extends Injector_Base {
	
	private $controller_name = 'photo_element';
	private $tab_code = 'photo_album';
	private $album_group = 'common';
	
	protected function init() {
		$module_config = Helper_Module::load_config('photo');
		$helper_acl = new Helper_ACL($this->acl);
		$helper_acl->inject(Arr::get($module_config, 'a2'));
		
		$this->album_group = Arr::get($this->params, 'group');
	}
	
	public function get_hook($orm)
	{
		if ($orm->photo_album_id == 0) {
			$photo_album_orm = ORM::factory('photo_Album');
			$photo_album_orm
				->values(array(
					'group' => $this->album_group,
					'site_id' => $orm->site_id,
					'creator_id' => $this->user->id,
					'public_date' => date('Y-m-d H:i:s'),
					'status' => Kohana::$config->load('_photo.status_codes.hidden'),
					'title' => $orm->title,
					'uri' => transliterate_unique($orm->title, $photo_album_orm, 'uri'),
					'for_all' => $orm->for_all,
				))
				->save();

			$orm->photo_album_id = $photo_album_orm->id;
			$orm->save();
			unset($photo_album_orm);
		}

		return array(
			array($this, 'hook_callback'),
			array($orm)
		);
	}
	
	public function hook_callback($content, $orm)
	{
		$request = $this->request;
		$back_url = $request->url();
		$query_array = $request->query();
		if ( ! empty($query_array)) {
			$back_url .= '?'.http_build_query($query_array);
		}
		$back_url .= '#tab-'.$this->tab_code;
		unset($query_array);
	
		$query_array = array(
			'group' => $this->album_group,
			'album' => $orm->photo_album_id,
			'back_url' => $back_url,
			'content_only' => TRUE
		);
		$query_array = Paginator::query($request, $query_array);
		$link = Route::url('modules', array(
			'controller' => $this->controller_name,
			'query' => Helper_Page::make_query_string($query_array),
		));
		
		$html_photo_album = Request::factory($link)
			->execute()
			->body();
	
		$tab_nav_html = View_Admin::factory('layout/tab/nav', array(
			'code' => $this->tab_code,
			'title' => '<b>'.__('Photo album').'</b>',
		));
		$tab_pane_html = View_Admin::factory('layout/tab/pane', array(
			'code' => $this->tab_code,
			'content' => $html_photo_album
		));
	
		return str_replace(array(
			'<!-- #tab-nav-insert# -->', '<!-- #tab-pane-insert# -->'
		), array(
			$tab_nav_html.'<!-- #tab-nav-insert# -->', $tab_pane_html.'<!-- #tab-pane-insert# -->'
		), $content);
	}
	
	public function menu_list($orm, $tab_mode = TRUE)
	{
		if ($tab_mode) {
			$link = '#tab-'.$this->tab_code;
			$class = 'tab-control';
		} else {
			$link = Route::url('modules', array(
				'controller' => $this->controller_name,
				'query' => 'group='.$this->album_group.'&album='.$orm->id
			));
			$class = FALSE;
		}
	
		return array(
			'photo' => array(
				'title' => __('Photo album ('.$this->album_group.')'),
				'link' => $link,
				'class' => $class,
				'sub' => array(),
			),
		);
	}
	
	public function menu_add($orm)
	{
		if ($this->acl->is_allowed($this->user, $orm, 'edit') ) {
			$back_url = urlencode($_SERVER['REQUEST_URI'].'#tab-'.$this->tab_code);
	
			return array(
				'photo' => array(
					'sub' => array(
						'add' => array(
							'title' => __('Add photo'),
							'link' => Route::url('modules', array(
								'controller' => $this->controller_name,
								'action' => 'edit',
								'query' => 'group='.$this->album_group.'&album='.$orm->id.'&back_url='.$back_url
							)),
						),
					),
				),
			);
		}
	}
	
	public function menu_fix($orm)
	{
		$can_fix_all = $this->acl->is_allowed($this->user, $orm->photos, 'fix_all');
	
		if ($can_fix_all) {
			$back_url = urlencode($_SERVER['REQUEST_URI'].'#tab-'.$this->tab_code);
	
			return array(
				'photo' => array(
					'sub' => array(
						'fix' => array(
							'title' => __('Fix positions'),
							'link'  => Route::url('modules', array(
								'controller' => $this->controller_name,
								'action' => 'position',
								'query' => 'group='.$this->album_group.'&album='.$orm->id.'&mode=fix&back_url='.$back_url
							)),
						),
					),
				),
			);
		}
	}
	
}