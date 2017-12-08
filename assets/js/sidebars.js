// JavaScript Document
(function ($) {

	$(document).ready(function () {
		// Add box to create new sidebars.
		$('.widget-liquid-right').append('<div id="cshp-wm-widget-area-create"><h2>Add A New Sidebar</h2><div class="sidebar-description"><p class="description">Here you can create new widget areas for use in the Sidebar module.</p></div><p><label for="cshp-wm-new-widget-area-name">Sidebar Name</label><input id="cshp-wm-new-widget-area-name" class="widefat" type="text" value="" /></p><p class="cshp-wm-widget-area-result"></p><button class="button button-primary cshp-wm-create-widget-area right">Create</button><button class="button button-secondary cshp-wm-refresh-widget-area right" style="display: none;">Refresh</button><div class="clear"></div></div>');

		//gather the box, input, all the created sidebars, and refresh button.
		var $create_box = $('#cshp-wm-widget-area-create'),
			$widget_name_input = $create_box.find('#cshp-wm-new-widget-area-name'),
			$cshp_sidebars = $('div[id^=cshp-wm-widget-area-]'),
			$refresh_button = $('#cshp-wm-widget-area-create .cshp-wm-refresh-widget-area');

		// create button click event.
		$('#cshp-wm-widget-area-create .cshp-wm-create-widget-area').on( 'click', function (event) {
			// prevent default functionality.
			event.preventDefault();
			event.stopPropagation();

			// exit if the input is empty.
			if ($widget_name_input.val() === '') return;

			// AJAX call to create new widget area
			$.ajax({
				type: "POST",
				url: cshp_wm_sidebars_options.ajaxurl,
				data: {
					action: 'cshp_wm_add_widget_area',
					cshp_wm_sidebars_nonce: cshp_wm_sidebars_options.cshp_wm_sidebars_nonce,
					cshp_wm_widget_area_name: $widget_name_input.val()
				},
				success: function (data) {
					// output the message from the AJAX call.
					$this_el.siblings('.cshp-wm-widget-area-result').hide().html(data).slideToggle();
					$refresh_button.show();
				}
			});
		});

		// refresh button click event.
		$refresh_button.on( 'click', function (event) {
			// prevent default functionality.
			event.preventDefault();
			event.stopPropagation();
			
			document.location.reload(true);
		});

		$cshp_sidebars.each(function () {
			// check that is not the creation box or an inactive sidebar.
			if ($(this).is('#cshp-wm-widget-area-create') || $(this).closest('.inactive-sidebar').length) return true;
			// add the remove button.
			$(this).closest('.widgets-holder-wrap').find('.sidebar-name h2').before('<a href="#" class="button cshp-wm-widget-area-remove left"><span class="dashicons dashicons-trash"></span></a>');
			// remove button click event.
			$('.cshp-wm-widget-area-remove').on( 'click', function (event) {
				// save button element on variable.
				var $this_el = $(this);
				// prevent default functionality.
				event.preventDefault();
				event.stopPropagation();

				// AJAX call to remove widget area
				$.ajax({
					type: "POST",
					url: cshp_wm_sidebars_options.ajaxurl,
					data: {
						action: 'cshp_wm_remove_widget_area',
						cshp_wm_sidebars_nonce: cshp_wm_sidebars_options.cshp_wm_sidebars_nonce,
						cshp_wm_widget_area_name: $this_el.closest('.widgets-holder-wrap').find('div[id^=cshp-wm-widget-area-]').attr('id')
					},
					success: function (data) {
						// remove the deleted widget area from the DOM.
						$('#' + data).closest('.widgets-holder-wrap').remove();
					}
				});
			});
		});
	});

})(jQuery);