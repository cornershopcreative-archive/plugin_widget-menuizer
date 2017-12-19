// JavaScript Document
(function ($) {

	$(document).ready(function () {
		// Add box to create new sidebars.
		$('.widget-liquid-right').append('<div id="cshp-wm-widget-area-create"><h2>Add A New Sidebar<span class="spinner"></span></h2><div class="sidebar-description"><p class="description">Here you can create new widget areas for use in the Sidebar module.</p></div><p><label for="cshp-wm-new-widget-area-name">Sidebar Name</label><input id="cshp-wm-new-widget-area-name" class="widefat" type="text" value="" /></p><p><label for="cshp-wm-new-widget-area-description">Sidebar Description</label><textarea id="cshp-wm-new-widget-area-description" class="widefat"></textarea></p><p class="cshp-wm-widget-area-result"></p><button class="button button-primary cshp-wm-create-widget-area right">Create</button><button class="button button-secondary cshp-wm-refresh-widget-area right" style="display: none;">Refresh</button><div class="clear"></div></div>');

		//gather the box, input, all the created sidebars, and refresh button.
		var $create_box = $('#cshp-wm-widget-area-create'),
			$widget_name_input = $create_box.find('#cshp-wm-new-widget-area-name'),
			$widget_desc_input = $create_box.find('#cshp-wm-new-widget-area-description'),
			$spinner = $create_box.find('h2 .spinner'),
			$cshp_sidebars = $('div[id^=cshp-wm-widget-area-]'),
			$refresh_button = $('#cshp-wm-widget-area-create .cshp-wm-refresh-widget-area');

		// create button click event.
		$('#cshp-wm-widget-area-create .cshp-wm-create-widget-area').on( 'click', function (event) {
			// save button element on variable.
			var $this_el = $(this);
			
			// prevent default functionality.
			event.preventDefault();
			event.stopPropagation();
			
			// exit if the input is empty.
			if ($widget_name_input.val() === '') return;
			
			// show spinner
			$spinner.css("visibility", "visible");;
			
			// AJAX call to create new widget area
			$.ajax({
				type: "POST",
				url: cshp_wm_sidebars_options.ajaxurl,
				data: {
					action: 'cshp_wm_add_widget_area',
					cshp_wm_sidebars_nonce: cshp_wm_sidebars_options.cshp_wm_sidebars_nonce,
					cshp_wm_widget_area_name: $widget_name_input.val(),
					cshp_wm_widget_area_desc: $widget_desc_input.val()
				},
				success: function ( data ) {
					var response = jQuery.parseJSON( data );
					// hide spinner.
					$spinner.hide();
					// output the message from the AJAX call.
					$this_el.siblings('.cshp-wm-widget-area-result').hide().html(response.message).slideToggle();
					if ( 'success' == response.status ){
						// show refresh button
						$refresh_button.css("visibility", "hidden");;
						// reload page
						document.location.reload(true);
					}
				}
			});
		});

		// refresh button click event.
		$refresh_button.on( 'click', function (event) {
			// prevent default functionality.
			event.preventDefault();
			event.stopPropagation();
			
			// reload page.
			document.location.reload(true);
		});

		$cshp_sidebars.each(function () {
			// check that is not the creation box or an inactive sidebar.
			if ($(this).is('#cshp-wm-widget-area-create') || $(this).closest('.inactive-sidebar').length) return true;
			// add the remove button.
			$(this).closest('.widgets-holder-wrap').find('.sidebar-name h2').before('<a href="#" class="button cshp-wm-widget-area-remove left"><span class="dashicons dashicons-trash"></span></a>');
			// remove button click event.
			$('.cshp-wm-widget-area-remove').on( 'click', function (event) {
				// save button element and spinner on variable.
				var $this_el = $(this),
					$spinner = $this_el.parent().find('.spinner');
				// prevent default functionality.
				event.preventDefault();
				event.stopPropagation();
				// show spinner
				$spinner.css("visibility", "visible");
				// AJAX call to remove widget area
				$.ajax({
					type: "POST",
					url: cshp_wm_sidebars_options.ajaxurl,
					data: {
						action: 'cshp_wm_remove_widget_area',
						cshp_wm_sidebars_nonce: cshp_wm_sidebars_options.cshp_wm_sidebars_nonce,
						cshp_wm_widget_area_name: $this_el.closest('.widgets-holder-wrap').find('div[id^=cshp-wm-widget-area-]').attr('id')
					},
					success: function ( data ) {
						var response = jQuery.parseJSON( data );
						// hide spinner.
						if ( 'success' == response.status && response.sidebarId ){
							$spinner.css("visibility", "hidden");
							// remove the deleted widget area from the DOM.
							$( '#' + response.sidebarId ).closest('.widgets-holder-wrap').remove();
						}
					}
				});
			});
		});
	});

})(jQuery);