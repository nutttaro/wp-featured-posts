(function($) {

    $(document).ready(function() {

        $('.wpfp-toggle-expand').on('click', function (e) {
            e.preventDefault();

            var expand = $(this).data('expand');
            if ($(expand).length) {
                $(expand).toggle();
            }

            if ($(this).hasClass('show')) {
                $(this).addClass('hide');
                $(this).find('.hide').hide();
                $(this).find('.show').show();
            } else {
                $(this).addClass('show');
                $(this).find('.hide').show();
                $(this).find('.show').hide();
            }
        });

        // Media Uploader for Pin Image
        var wpfpMediaUploader;

        $(document).on('click', '.wpfp-upload-pin-image', function(e) {
            e.preventDefault();

            // If the uploader already exists, reopen it
            if (wpfpMediaUploader) {
                wpfpMediaUploader.open();
                return;
            }

            // Create a new media uploader
            wpfpMediaUploader = wp.media({
                title: 'Select Pin Icon Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            // When an image is selected in the media uploader
            wpfpMediaUploader.on('select', function() {
                var attachment = wpfpMediaUploader.state().get('selection').first().toJSON();
                $('#wp-featured-posts-pin-image').val(attachment.url);
                $('.wpfp-pin-preview img').attr('src', attachment.url);
                $('.wpfp-pin-preview').show();
                $('.wpfp-remove-pin-image').show();
            });

            // Open the uploader
            wpfpMediaUploader.open();
        });

        // Remove pin image
        $(document).on('click', '.wpfp-remove-pin-image', function(e) {
            e.preventDefault();
            $('#wp-featured-posts-pin-image').val('');
            $('.wpfp-pin-preview').hide();
            $(this).hide();
        });


})(jQuery);
