<?php defined('SYSPATH') or die('No direct access allowed.'); ?>

	<th class="align-center">
<?php 
		echo Form::checkbox('group_item', $id, FALSE, array(
			'class' => 'js-group-item', 
			'title' => __('Select item'), 
			'autocomplete' => 'off'
		));
?>
	</th>