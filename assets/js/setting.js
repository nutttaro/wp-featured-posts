(function($) {

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

})(jQuery);
