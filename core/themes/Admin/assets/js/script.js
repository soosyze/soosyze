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
});