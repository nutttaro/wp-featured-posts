(function($) {

    $('#form-featured-sorting').submit(function() {

        var $form = $(this),
            $btnSubmit = $form.find('[type="submit"]'),
            formData = $form.serialize();

        $('#added-featured-posts-message').remove();

        $.ajax({
            url: wtfp_admin_global.ajax_url,
            type: "post",
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $btnSubmit.prop('disabled', true).addClass('loading');
            },
            success: function(response) {

                if ( response.data.message ) {
                    var messageType = response.success ? 'updated' : 'error';
                    var messageHtml = '<div id="added-featured-posts-message" class="' + messageType + ' notice notice-success is-dismissible added-featured-posts-message"><p>' + response.data.message + '</p></div>';

                    $( messageHtml ).insertAfter( $btnSubmit );
                }

                if ( response.success ) {
                    setTimeout(function() {
                        window.location.href = response.data._redirect;
                    }, 1000);
                }

            },
            error: function (xhr, textStatus, errorThrown) {
                if ( xhr.status != 200 ) {
                    console.log( xhr.status + ' (' + xhr.statusText + ')' );
                }
            }
        });

        return false;
    });

    $(document).on('click', '#table-featured-sorting .column-action a', function(e) {
        e.preventDefault();

        var $this = $(this),
            nonce = $this.data('nonce'),
            id   = $this.data('id');

        $('#delete-featured-posts-message').remove();

        $.ajax({
            url: wtfp_admin_global.ajax_url,
            type: "post",
            data: {
                action: 'delete_featured_sorting',
                _ajax_nonce: nonce,
                post_id: id,
                post_type_title: $('.order-featured-sorting [name="post_type_title"]').val(),
                post_type: $('.order-featured-sorting [name="post_type"]').val(),
                featured_key: $('.order-featured-sorting [name="featured_key"]').val(),
                lang: $('.order-featured-sorting [name="lang"]').val()
            },
            dataType: 'json',
            beforeSend: function() {
                $this.addClass('disabled').text('Loading...');
            },
            success: function(response) {

                if ( response.data.message ) {
                    var messageType = response.success ? 'updated' : 'error';
                    var messageHtml = '<div id="delete-featured-posts-message" class="' + messageType + ' notice notice-success is-dismissible delete-featured-posts-message delete-featured-posts-message-' + id + '"><p>' + response.data.message + '</p></div>';

                    $( messageHtml ).insertAfter( $('#table-featured-sorting') );
                }


                if ( response.success ) {
                    setTimeout(function() {
                        window.location.href = response.data._redirect;
                    }, 1000);
                }


            },
            error: function (xhr, textStatus, errorThrown) {
                if ( xhr.status != 200 ) {
                    console.log( xhr.status + ' (' + xhr.statusText + ')' );
                }
            }
        });

        return false;
    });


    var $sortableElement = $('table#table-featured-sorting:not(.post-type-events) tbody');
    if ($sortableElement.length) {
        $sortableElement.sortable({
            placeholder: "ui-state-highlight",
            stop: function (event, ui) {
                $(event.target).find('tr.ui-sortable-handle').each(function (index, el) {
                    $(this).find('.column-order span').text(++index);
                });

                $('#order-featured-sorting').submit();

            }
        });
        $sortableElement.disableSelection();
    }

    $('#order-featured-sorting').submit(function() {

        var $form = $(this),
            formData = $form.serialize();

        $('#added-featured-posts-message').remove();

        $.ajax({
            url: wtfp_admin_global.ajax_url,
            type: "post",
            data: formData,
            dataType: 'json',
            success: function(response) {

                // if ( response.data.message ) {
                //     var messageType = response.success ? 'updated' : 'error';
                //     var messageHtml = '<div id="added-featured-posts-message" class="' + messageType + ' notice notice-success is-dismissible added-featured-posts-message"><p>' + response.data.message + '</p></div>';

                //     $( messageHtml ).insertAfter( $('#table-featured-sorting') );
                // }

            },
            error: function (xhr, textStatus, errorThrown) {
                if ( xhr.status != 200 ) {
                    console.log( xhr.status + ' (' + xhr.statusText + ')' );
                }
            }
        });

        return false;
    });

})(jQuery);
