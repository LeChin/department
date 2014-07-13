jQuery(document).ready(function($) {

	//Uploading media using new media uploader (Wordpress 3.5+)

	var file_frame;

	$('#upload-image').click(function(e) {
		e.preventDefault();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select your custom "Pin It" button',
			button: {
				text: 'Use as "Pin It" button'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			var image = file_frame.state().get('selection').first().toJSON();

			$("#custom_image_url").val(image.url);
			$("#custom_image_width").val(image.width);
			$("#custom_image_height").val(image.height);
		});

		file_frame.open();
	});

});
