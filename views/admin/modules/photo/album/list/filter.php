<?php defined('SYSPATH') or die('No direct access allowed.'); 

	$filter_query = Request::current()->query('filter');
	
	$query_array = array(
		'group' => $GROUP_KEY,
	);
	if ( ! empty($BACK_URL)) {
		$query_array['back_url'] = $BACK_URL;
	}
	$action = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['album'],
		'query' => Helper_Page::make_query_string($query_array),
	));
?>
	<div class="row">
		<div class="span9">
			<form class="form-inline" action="<?php echo $action; ?>" method="get">
<?php
				foreach ($query_array as $_n => $_v) {
					echo Form::hidden($_n, $_v);
				}
?>				
				<div class="input-prepend">
					<span class="add-on"><i class="icon-pencil"></i></span>
<?php
					echo Form::input('filter[title]', Arr::get($filter_query, 'title'), array(
						'placeholder' => __('Search by title'),
						'class' => 'span4'
					));
?>				
				</div>
<?php				
				if ( ! IS_MASTER_SITE) {
					echo '&nbsp;', Form::select('filter[type]', $filter_type_options, Arr::get($filter_query, 'type'), array( 
						'class' => 'span1'
					));
				}
?>
				<button class="btn" type="submit"><i class="icon-search">&nbsp;</i></button>
				<button class="btn btn-clear" type="submit"><i class="icon-trash">&nbsp;</i></button>
			</form>
		</div>
	</div>
	<script>
	$(function(){
		$('.btn-clear').click(function(e){
			$(this).closest('.form-inline')
				.find(':input').not(':button, :submit, :reset, :hidden')
					.val('')
					.removeAttr('checked')
					.removeAttr('selected');
		});
	});
	</script>
