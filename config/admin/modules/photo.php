<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
	'a2' => array(
		'resources' => array(
			'photo_album_controller' => 'module_controller',
			'photo_element_controller' => 'module_controller',
			'photo_group_controller' => 'module_controller',
			'photo_multiupload_controller' => 'module_controller',
			'photo_album' => 'module',
			'photo' => 'module',
		),
		'rules' => array(
			'allow' => array(
				'controller_access_1' => array(
					'role' => 'base',
					'resource' => 'photo_album_controller',
					'privilege' => 'access',
				),
				'controller_access_2' => array(
					'role' => 'base',
					'resource' => 'photo_element_controller',
					'privilege' => 'access',
				),
				'controller_access_3' => array(
					'role' => 'base',
					'resource' => 'photo_group_controller',
					'privilege' => 'access',
				),
				'controller_access_4' => array(
					'role' => 'base',
					'resource' => 'photo_multiupload_controller',
					'privilege' => 'access',
				),
			
			
				'photo_album_add' => array(
					'role' => 'base',
					'resource' => 'photo_album',
					'privilege' => 'add',
				),
				'photo_album_edit_1' => array(
					'role' => 'base',
					'resource' => 'photo_album',
					'privilege' => 'edit',
					'assertion' => array('Acl_Assert_Edit', array(
						'site_id' => SITE_ID,
					)),
				),
				'photo_album_hide' => array(
					'role' => 'base',
					'resource' => 'photo_album',
					'privilege' => 'hide',
					'assertion'	=> array('Acl_Assert_Hide', array(
						'site_id' => SITE_ID,
						'site_id_master' => SITE_ID_MASTER
					)),
				),
				
			
				'photo_edit_1' => array(
					'role' => 'base',
					'resource' => 'photo',
					'privilege' => 'edit',
					'assertion' => array('Acl_Assert_Photo', array(
						'site_id' => SITE_ID,
					)),
				),
				'photo_fix_all' => array(
					'role' => 'super',
					'resource' => 'photo',
					'privilege' => 'fix_all',
				),
			),
			'deny' => array()
		)
	),
);