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
    /* SCROLL TO */
    $('a').click(function () {
        var page = $(this).attr('href');
        if ($(page)[0] !== undefined) {
            var speed = 750;
            $('html, body').animate({scrollTop: $(page).offset().top - 80}, speed); // Go
        }
        return false;
    });
    /* INPUT ICON RENDER */
    $('.text_icon').keyup(function () {
        $(this).parent().find('.render_icon i').attr('class', this.value);
    });
});