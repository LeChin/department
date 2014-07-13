jQuery(document).ready(function($) {
	
	$('a.jpibfi_selector_option').click(function(e) {
		$('#image_selector').val($(this).text());
		e.preventDefault();
	});

	$("#refresh_custom_button_preview").click( function(e) {
		var customWidth = $('#custom_image_width').val();
		var customHeight = $('#custom_image_height').val();
		var customUrl = $('#custom_image_url').val();

		$('#custom_button_preview')
				.css(
					{
						width: customWidth,
						height: customHeight,
						"background-image": "url('" +  customUrl + "')"
					}
				);
		return false;
	});

});