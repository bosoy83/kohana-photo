<?php defined('SYSPATH') or die('No direct access allowed.');

	echo View_Admin::factory('layout/breadcrumbs', array(
		'breadcrumbs' => $breadcrumbs
	));

	$query_array = array(
		'group' => $GROUP_KEY,
		'album' => $ALBUM_ID,
	);
	
	$action = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['multiupload'],
		'action' => 'upload',
		'query' => Helper_Page::make_query_string($query_array),
	));
	
?>
	<div class="row">
		<div class="span4">
			<div class="form-inline">
				<label for="js-album"><?php echo __('Album'); ?>:</label>
<?php
					echo Form::select('album_id', array('' => '') + $albums, FALSE, array( 
						'id' => 'js-album' 
					));
?>
			</div>
		</div>
		<div class="span5">
			<div class="form-inline pull-right" style="padding-top: 5px;">
				<label for="js-to-head" class="checkbox-label"><?php echo __('Add to head'); ?></label>
<?php
				echo Form::checkbox('to_head', 1, FALSE, array(
					'id' => 'js-to-head',
					'style' => 'margin: -1px 0 0 5px'
				)); 
?>			
			</div>
		</div>
	</div>
	<br>
	<div id="js-multiupload">Loading, please wait..</div>
	<br>
	
	<script>
	$(function(){
		var $holder = $("#js-multiupload");
		var $albumSelect = $("#js-album");
		var $toHeadCheckbox = $("#js-to-head");


		$holder.plupload({
			runtimes: "html5",
			url: "<?php echo $action; ?>",
			chunk_size: "1mb",
			unique_names: true,
			filters: {
				max_file_size: "10mb",
				mime_types: [
					{title: "Изображения", extensions: "jpg,jpeg"}
				]
			},
			sortable: true,
			unique_names: true,
			views: {
				list: false,
				thumbs: true,
				'default': "thumbs",
				remember: false
			}
		}).on("selected", function(){
			$holder.find("#js-multiupload_dropbox .plupload_droptext")
				.hide();
		}).on("removed", function(e, params){
			if ( ! params.up.files.length) {
				$holder.find("#js-multiupload_dropbox .plupload_droptext")
					.show();
			}
		}).on("started", function(e, params){
			if ( ! $albumSelect.val()) {
				alert("Укажите в какой альбом производить загрузку");
				$holder.plupload('stop');
			}
		});
		
		function setQueryData() {
			$holder.plupload('getUploader')
				.setOption("multipart_params", {
					album: $albumSelect.val(),
					to_head: $toHeadCheckbox.is(":checked")
				});
		};
		setQueryData();
		
		$albumSelect.on("change", function(){
			setQueryData();
		});
		$toHeadCheckbox.on("change", function(){
			setQueryData();
		});
	});
	</script>
	
