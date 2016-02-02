<?php defined('SYSPATH') or die('No direct access allowed.'); 

	$is_initial_request = Request::current()->is_initial();

	if ($is_initial_request) {
		echo View_Admin::factory('layout/breadcrumbs', array(
			'breadcrumbs' => $breadcrumbs
		));
	
		echo View_Admin::factory('modules/photo/element/list/filter', array(
			'filter_type_options' => $filter_type_options
		));
	}
	
	if ($list->count() <= 0) {
		return;
	}

	$query_array = array(
		'group' => $GROUP_KEY,
		'album' => $ALBUM_ID,
	);
	
	if ( ! empty($BACK_URL)) {
		$query_array['back_url'] = $BACK_URL;
	}

	$query_array = Paginator::query(Request::current(), $query_array);
	$edit_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'edit',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$delete_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'delete',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$view_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'view',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));

	$query_array['mode'] = 'first';
	$first_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'position',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$query_array['mode'] = 'up';
	$up_tpl	= Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'position',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$query_array['mode'] = 'down';
	$down_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'position',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$query_array['mode'] = 'last';
	$last_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'position',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
?>

	<table id="photo-table" class="table table-bordered table-striped">
		<colgroup>
<?php
		if ($acl_album_can_edit AND $is_initial_request):
?>		
			<col class="span1">
			<col class="span1">
			<col class="span3">
			<col class="span2">
			<col class="span2">
<?php
		else:
?>
			<col class="span1">
			<col class="span4">
			<col class="span2">
			<col class="span2">
<?php
		endif;
?>
		</colgroup>
		<thead>
			<tr>
<?php
				if ($acl_album_can_edit AND $is_initial_request) {
					echo '<th></th>';
				}
?>	
				<th><?php echo __('ID'); ?></th>
				<th><?php echo __('Title'); ?></th>
				<th><?php echo __('Image'); ?></th>
				<th><?php echo __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php 			
		$orm_helper = ORM_Helper::factory('photo');
		foreach ($list as $_orm):
?>
			<tr>
<?php
				if ($acl_album_can_edit AND $is_initial_request) {
					echo View_Admin::factory('modules/photo/element/group/checkbox', array(
						'id' => $_orm->id
					));
				}
?>	
				<td><?php echo $_orm->id; ?></td>
				<td>
<?php
				
					if ( (bool) $_orm->active) {
						echo '<i class="icon-eye-open"></i>&nbsp;';
					} else {
						echo '<i class="icon-eye-open" style="background: none;"></i>&nbsp;';
					}
					
					$_title = empty($_orm->title) ? __('Unnamed') : $_orm->title;
					echo HTML::chars($_title);
?>
				</td>
				<td>
<?php
					echo View_Admin::factory('layout/list/image', array(
						'field' => 'image',
						'orm_helper' => $orm_helper,
						'value' => $_orm->image,
					));
?>				
				</td>
				<td>
<?php 
					echo '<div class="btn-group">';
					
						if ($ACL->is_allowed($USER, $_orm, 'edit')) {
							echo HTML::anchor(str_replace('{id}', $_orm->id, $edit_tpl), '<i class="icon-edit"></i> '.__('Edit'), array(
								'class' => 'btn',
								'title' => __('Edit'),
							));
							echo '<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>';
							echo '<ul class="dropdown-menu">';
							
								echo View_Admin::factory('layout/controls/position', array(
									'orm' => $_orm,
									'first_tpl' => $first_tpl,
									'up_tpl' => $up_tpl,
									'down_tpl' => $down_tpl,
									'last_tpl' => $last_tpl,
								));
							
								echo '<li>', HTML::anchor(str_replace('{id}', $_orm->id, $delete_tpl), '<i class="icon-remove"></i> '.__('Delete'), array(
									'class' => 'delete_button',
									'title' => __('Delete'),
								)), '</li>';
							echo '</ul>';
						} else {
							echo HTML::anchor(str_replace('{id}', $_orm->id, $view_tpl), '<i class="icon-file"></i> '.__('View'), array(
								'class' => 'btn',
								'title' => __('View'),
							));
						}
					echo '</div>';
?>				
				</td>
			</tr>
<?php 
		endforeach;
?>
		</tbody>
<?php
		if ($acl_album_can_edit AND $is_initial_request) {
			echo View_Admin::factory('modules/photo/element/group/form');
		}
?>
	</table>
<?php
	if ($acl_album_can_edit AND $is_initial_request) {
		echo View_Admin::factory('modules/photo/element/group/script');
	}
		
	if (empty($BACK_URL)) {
		$query_array = array(
			'group' => $GROUP_KEY,
			'album' => $ALBUM_ID,
		);
		$filter_query = Request::current()->query('filter');
		if ( ! empty($filter_query)) {
			$query_array['filter'] = $filter_query;
		}
		if ( ! empty($BACK_URL)) {
			$query_array['back_url'] = $BACK_URL;
		}
		$link = Route::url('modules', array(
			'controller' => $CONTROLLER_NAME['element'],
			'query' => Helper_Page::make_query_string($query_array),
		));
	} else {
		$link = $BACK_URL;
	}
	
	echo $paginator->render($link);
