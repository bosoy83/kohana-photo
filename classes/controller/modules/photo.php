<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Modules_Photo extends Controller_Front {

	public function action_preview()
	{
		$this->ttl = 0;
		
		$id = (int) $this->request->query('id');
		$token = $this->request->query('token');
		if ( ! Route::check_preview_token($id, $token)){
			throw new HTTP_Exception_404();
		}
		
		echo 'preview page for photo here';
	}

} 