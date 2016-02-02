<?php defined('SYSPATH') or die('No direct access allowed.'); 

	$action_link = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['group'],
	));
?>

	<script>
		$(document).ready(function(){
			var $holder = $("#photo-table");
			var $chekboxList = $holder.find(".js-group-item");
			var $selectAction = $holder.find(".js-group-action");
			var $submitBtn = $holder.find(".js-group-submit");
			var $errorHolder = $("#group-error");

			function check() {
				if ($chekboxList.filter(':checked').length && $selectAction.val()) {
					$submitBtn.addClass("btn-primary")
						.prop("disabled", false);
				} else {
					$submitBtn.removeClass("btn-primary")
						.prop("disabled", true);
				}
			}

			check();
			$chekboxList.on('change', function(){
				check();
			});
			$selectAction.on('change', function(){
				check();
			});

			var running = false;
			$submitBtn.on("click", function(e){
				e.preventDefault();

				if (running) {
					return;
				}

				$errorHolder.empty()
					.hide();
				
				var action = $selectAction.val();
				if ( ! action) {
					return;
				}

				var items = [];
				$chekboxList.each(function(){
					if (this.checked) {
						items.push(this.value);
					}
				});
				if ( ! items.length) {
					return;
				}

				running = true;
				$.ajax({
					url: "<?php echo $action_link; ?>",
					type: "POST",
					async: true,
					cache: false,
					dataType: "html",
					data: {operation: action, items: items, group: "<?php echo $GROUP_KEY; ?>", album: "<?php echo $ALBUM_ID; ?>"}
				}).done(function(){
					window.location.reload();
				}).fail(function(jqXHR, textStatus, errorThrown) {
					if (textStatus !== "abort" && jqXHR.readyState > 0) {
						var message = "<p>Не удалось выполнить запрос</p>";
		
						if (jqXHR.status == 404) {
							message = "<p>Запрашиваемая страница не найдена</p>";
						}
						if (textStatus === "error") {
							message += "<p>[ <b>" + jqXHR.status + "</b> ] "+jqXHR.responseText+"</p>";
						}

						$errorHolder.html(message)
							.show();
					}
				}).always(function(){
					running = false;
				});
			});
		});
	</script>