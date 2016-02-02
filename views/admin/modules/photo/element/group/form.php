<?php defined('SYSPATH') or die('No direct access allowed.'); ?>

	<tfoot>
		<tr>
			<td colspan="5">
				<div id="group-error" class="alert alert-error" style="display: none;"></div>
				<form action="#" class="form-inline" autocomplete="off" style="margin-bottom: 0;">
					<label for="action_with_selected_items"><?php echo __('Action with selected items'); ?></label>
<?php
					echo '&nbsp;', Form::select('action_with_selected_items', array(
						'' => '',
						'first' => ' &uArr; '.__('Move first'),
						'up' => ' &uarr; '.__('Move up'),
						'down' => ' &darr; '.__('Move down'),
						'last' => ' &dArr; '.__('Move last'),
					), NULL, array(
						'id' => 'action_with_selected_items', 
						'class' => 'js-group-action'
					));
					echo '&nbsp;&nbsp;&nbsp;', Form::button('group_submit', __('Execute'), array(
						'class' => 'btn js-group-submit'
					));
?>
				</form>
			</td>
		</tr>
	</tfoot>