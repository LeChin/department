jQuery(document).ready(function($) {

	//Uploading media using old media uploader

	$('#upload-image').click(function() {

		tb_show('Choose an image and click "Insert into post"', 'media-upload.php?referer=jpibfi&type=image&TB_iframe=true&post_id=0', false);

		// Re-define the global function 'send_to_editor'
		// Define where the new value will be sent to
		window.send_to_editor = function(html) {

			// Get the URL of new image
			var domElement = new DOMParser().parseFromString("<p>" + html + "</p>", "text/xml");
			var image = $('img', domElement);
			$("#custom_image_url").val(image.attr('src'));
			$("#custom_image_width").val(image.attr('width'));
			$("#custom_image_height").val(image.attr('height'));

			tb_remove(); // Then close the popup window
		}
		return false;
	});

});