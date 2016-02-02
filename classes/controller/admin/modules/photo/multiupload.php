<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Photo_Multiupload extends Controller_Admin_Modules_Photo {

	private $max_file_age = 3600;
	
	public function before()
	{
		parent::before();
		
		if ( ! $this->request->current()->is_ajax()) {
// 			throw new HTTP_Exception_404();
		}
	}
	
	public function action_index()
	{
		Session::instance()
			->delete('multiupload_position');
		
		$albums = ORM::factory('photo_Album')
			->where('site_id', '=', SITE_ID)
			->find_all()
			->as_array('id', 'title');
		
		$this->template
			->set_filename('modules/photo/multiupload/list')
			->set('albums', $albums);
	}
	
	public function action_upload()
	{
		$this->auto_render = FALSE;
		
		$request = $this->request->current();
		$post = $request->post();
		$album_id = (int) Arr::get($post, 'album');
		$to_head = (Arr::get($post, 'to_head') === 'true');
		
		$album_orm = ORM::factory('photo_Album')
			->where('id', '=', $album_id)
			->find();
		
		if ( ! $album_orm->loaded() OR ! $this->acl->is_allowed($this->user, $album_orm, 'edit')) {
			throw new HTTP_Exception_404();
		}
		
		$response = array(
			'jsonrpc' => '2.0',
			'id' => 'id'
		);
		
	/* $target_dir */
		$target_dir = str_replace('/', DIRECTORY_SEPARATOR, DOCROOT.Kohana::$config->load('_photo.multiupload_dir'));
		if ( ! is_dir($target_dir)) {
			Ku_Dir::make($target_dir, 0755);
		}
		if (is_dir($target_dir) && ($dir = opendir($target_dir))) {
			while (($file = readdir($dir)) !== false) {
				$tmp_file_path = $target_dir.DIRECTORY_SEPARATOR.$file;
		
	/* Remove temp file if it is older than the max age and is not the current file */
				if (preg_match('/\.part$/', $file) AND (filemtime($tmp_file_path) < time() - $this->max_file_age) AND ($tmp_file_path != "{$file_path}.part")) {
					@unlink($tmp_file_path);
				}
			}
			closedir($dir);
		} else {
			$response['error'] = array(
				'code' => 100,
				'message' => 'Failed to open temp directory.',
			);
			$this->json_send($response);
			return;
		}
		
	/* $chunk, $chunks */
		$chunk = Arr::get($post, 'chunk', 0);
		$chunks = Arr::get($post, 'chunks', 0);
		
	/* $file_name */
		$file_name = Arr::get($post, 'name', '');
		$file_name = preg_replace('/[^\w\._]+/', '_', $file_name);
		$ext = UTF8::strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		if ( ! preg_match('/^jpe?g$/s', $ext)) {
			$response['error'] = array(
				'code' => 105,
				'message' => 'Invalid file type.',
			);
			$this->json_send($response);
			return;
		}
		if ($chunks < 2 AND file_exists($target_dir.DIRECTORY_SEPARATOR.$file_name)) {
			$ext = strrpos($file_name, '.');
			$file_name_a = substr($file_name, 0, $ext);
			$file_name_b = substr($file_name, $ext);
		
			$count = 1;
			while (file_exists($target_dir.DIRECTORY_SEPARATOR.$file_name_a.'_'.$count.$file_name_b)) {
				$count++;
			}
			$file_name = $file_name_a.'_'.$count.$file_name_b;
		}
		
	/* $file_path */
		$file_path = $target_dir.DIRECTORY_SEPARATOR.$file_name;
		
		$_h = $request->headers('http-content-type');
		$content_type = empty($_h) ? '' : $_h;
		$_h = $request->headers('content-type');
		$content_type = empty($_h) ? $content_type : $_h;
		
	/* Handle non multipart uploads older WebKit versions didn't support multipart in HTML5 */
		if (strpos($content_type, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				if ($out = fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab")) {
					if ($in = fopen($_FILES['file']['tmp_name'], "rb")) {
						while ($buff = fread($in, 4096)) {
							fwrite($out, $buff);
						}
					} else {
						$response['error'] = array(
							'code' => 101,
							'message' => 'Failed to open input stream.',
						);
						$this->json_send($response);
						return;
					}
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else {
					$response['error'] = array(
						'code' => 102,
						'message' => 'Failed to open output stream.',
					);
					$this->json_send($response);
					return;
				}
			} else {
				$response['error'] = array(
					'code' => 103,
					'message' => 'Failed to move uploaded file.',
				);
				$this->json_send($response);
				return;
			}
			
		} else {
			
			if ($out = fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab")) {
				if ($in = fopen("php://input", "rb")) {
					while ($buff = fread($in, 4096)) {
						fwrite($out, $buff);
					}
				} else {
					$response['error'] = array(
						'code' => 101,
						'message' => 'Failed to open input stream.',
					);
					$this->json_send($response);
					return;
				}
				fclose($in);
				fclose($out);
			} else {
				$response['error'] = array(
					'code' => 102,
					'message' => 'Failed to open output stream.',
				);
				$this->json_send($response);
				return;
			}
			
		}
		
	/* Check if file has been uploaded */
		if ( ! $chunks OR $chunk == ($chunks - 1)) {
			
	/* Strip the temp .part suffix off */
			rename("{$file_path}.part", $file_path);
			
			$save_result = $this->save_file($file_path, $album_orm, $to_head);
			if ($save_result !== TRUE) {
				$response['error'] = array(
					'code' => 104,
					'message' => $save_result,
				);
				$this->json_send($response);
				return;
			}
		}

	/* Return JSON-RPC response */
		$response['result'] = NULL;
		$this->json_send($response);
	}
	
	private function save_file($file_path, ORM $album_orm, $to_head)
	{
		$orm_helper = ORM_Helper::factory('photo');
	
		try {
			$orm_helper->save(array(
				'owner' => $album_orm->object_name(),
				'owner_id' => $album_orm->id,
				'title' => Arr::path($_FILES, 'file.name', ''),
				'image' => $file_path,
				'active' => 1,
				'creator_id' => $this->user->id,
			));
	
			if ($to_head) {
				$config = Arr::get($orm_helper->position_fields(), 'position');
				$position = Session::instance()
					->get('multiupload_position', $config['step']);
				
				$orm_helper->position_set('position', $position);
				
				Session::instance()
					->set('multiupload_position', $position + $config['step']);
			}
		} catch (ORM_Validation_Exception $e) {
			return implode( '. ', $e->errors('') );
		} catch (Exception $e) {
			Kohana::$log->add(Log::ERROR, 'Multiupload error. Exception occurred: :exception', array(
				':exception' => $e->getMessage()
			));
			return 'Save error';
		}
	
		return TRUE;
	}
	
	protected function _get_breadcrumbs()
	{
		$breadcrumbs = parent::_get_breadcrumbs();
	
		$breadcrumbs[] = array(
			'title' => 'Multiupload',
		);
	
		return $breadcrumbs;
	}
} 
