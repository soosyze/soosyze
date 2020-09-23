$(function () {
    /* SCROLL TOP */
    $('#btn_up').click(function () {
        $('html,body').animate({scrollTop: 0}, 'slow');
    });
    $(window).scroll(function () {
        if ($(window).scrollTop() < 300) {
            $('#btn_up').fadeOut();
        } else {
            $('#btn_up').fadeIn();
        }
    });

    /* NUMBER TEXTAREA */
    var textarea = document.querySelectorAll('textarea');
    textarea.forEach((el) => {
        if (el.maxLength > 0) {
            $('<div class="maxLength_show"><span class="maxLength_value">' + el.value.length + '</span>/' + el.maxLength + '<div>').appendTo(el.parentNode);
        }
    });
    $('textarea, .trumbowyg').keyup(function () {
        $this = $(this);
        const length = $this.val().length
                ? $this.val().length
                : $this.find('.trumbowyg-editor').html().length;

        $this.parent()
                .find('.maxLength_value')
                .html(length);
    });

    /* INPUT ICON RENDER */
    $('.text_icon').keyup(function () {
        $(this).parent().find('.render_icon i').attr('class', this.value);
    });

    $('.select-ajax-multiple').select2({
        width: "100%",
        ajax: {
            delay: 300,
            url: function (params) {
                return $(this).data('link');
            },
            data: function (params) {
                var query = {
                    search: params.term
                };

                return query;
            }
        },
        templateResult: function (repo) {
            if (repo.loading) {
                return repo.text;
            }

            if (repo.tpl) {
                var $container = $(repo.tpl);
            } else {
                return repo.text;
            }

            return $container;
        }
    });

});

