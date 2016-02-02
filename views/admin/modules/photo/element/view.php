<?php defined('SYSPATH') or die('No direct access allowed.');

	echo View_Admin::factory('layout/breadcrumbs', array(
		'breadcrumbs' => $breadcrumbs
	));

	$orm = $helper_orm->orm();
	$labels = $orm->labels();
	$required = $orm->required_fields();
?>
	<form method="post" action="#" class="form-horizontal" >	
<?php

/**** owner_id ****/

		echo View_Admin::factory('form/control', array(
			'field' => 'owner_id',
			'labels' => $labels,
			'required' => $required,
			'controls' => Form::input('owner_id', $album['title'], array(
				'id' => 'owner_id_field',
				'class' => 'input-xxlarge',
				'readonly' => 'readonly',
			)),
		));
		
/**** title ****/
		
		echo View_Admin::factory('form/control', array(
			'field' => 'title',
			'labels' => $labels,
			'required' => $required,
			'controls' => Form::input('title', $orm->title, array(
				'id' => 'title_field',
				'class' => 'input-xxlarge',
				'readonly' => 'readonly',
			)),
		));
		
/**** image ****/

		echo View_Admin::factory('form/image', array(
			'field' => 'image',
			'value' => $orm->image,
			'orm_helper' => $helper_orm,
			'labels' => $labels,
			'required' => $required,
			'image_only' =>	TRUE,
// 			'help_text'      => '360x240px',
		));
		
/**** text ****/

		$text = empty($orm->text) 
			? '<span class="plaintext">'.__('no text').'<span>' 
			: $orm->text;
		echo View_Admin::factory('form/control', array(
			'field' => 'text',
			'labels' => $labels,
			'required' => $required,
			'controls' => $text,
		));
		
		echo View_Admin::factory('form/back_button', array(
			'link' => $BACK_URL
		));
?>
	</form>
