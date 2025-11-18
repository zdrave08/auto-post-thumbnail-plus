/**
 * Admin JavaScript for Auto Post Thumbnail Plus
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        var mediaUploader;
        
        // Select image button click
        $('#aptp_select_image_button').on('click', function(e) {
            e.preventDefault();
            
            // If the uploader object has already been created, reopen the dialog
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            // Create the media uploader
            mediaUploader = wp.media({
                title: 'Select Default Featured Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // When an image is selected, run a callback
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Set the image ID
                $('#aptp_default_image_id').val(attachment.id);
                
                // Display the image preview
                var imageUrl = attachment.sizes && attachment.sizes.medium 
                    ? attachment.sizes.medium.url 
                    : attachment.url;
                    
                $('#aptp-default-image-preview')
                    .attr('src', imageUrl)
                    .show();
                
                // Show remove button
                $('#aptp_remove_image_button').show();
            });
            
            // Open the uploader dialog
            mediaUploader.open();
        });
        
        // Remove image button click
        $('#aptp_remove_image_button').on('click', function(e) {
            e.preventDefault();
            
            // Clear the image ID
            $('#aptp_default_image_id').val('');
            
            // Hide the image preview
            $('#aptp-default-image-preview')
                .attr('src', '')
                .hide();
            
            // Hide remove button
            $(this).hide();
        });
        
        // Bulk apply button click
        $('#aptp_bulk_apply_button').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $status = $('#aptp_bulk_apply_status');
            
            // Confirm action
            if (!confirm('This will process all existing posts without featured images. This may take a while. Continue?')) {
                return;
            }
            
            // Disable button and show processing
            $button.prop('disabled', true);
            $status.html('<span style="color: #0073aa;">' + aptpAjax.processing + '</span>');
            
            // Make AJAX request
            $.ajax({
                url: aptpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aptp_bulk_apply',
                    nonce: aptpAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
                    } else {
                        $status.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
                    }
                    
                    // Re-enable button after 3 seconds
                    setTimeout(function() {
                        $button.prop('disabled', false);
                    }, 3000);
                },
                error: function() {
                    $status.html('<span style="color: #dc3232;">✗ ' + aptpAjax.error + '</span>');
                    $button.prop('disabled', false);
                }
            });
        });
    });
    
})(jQuery);
