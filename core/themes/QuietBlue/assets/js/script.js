$(function () {
    /* SCROLL TOP */
    $('#btn_up').click(function () {
        $('html,body').animate({scrollTop: 0}, 'slow');
    });
    $(window).scroll(function () {
        if ($(this).scrollTop() < 300) {
            $('#btn_up').fadeOut();
        } else {
            $('#btn_up').fadeIn();
        }
    });
    /* MENU */
    $(window).resize(function () {
        if ($(this).innerWidth() > 768) {
            $('#menu').css('display', '');
        }
    });
    $('#toogle_menu').click(function () {
        $('#menu').slideToggle();
    });
});