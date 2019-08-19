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
    /* MODAL */
    var target = '';
    $('[data-toogle="modal"]').click(function (evt) {
        evt.preventDefault();
        target = $(this).data('target');
        $(target).show();
        $('body').toggleClass('modal-open');
    });
    $(window).click(function (evt) {
        if (evt.target.className === 'modal' || evt.target.className === 'close') {
            $(target).hide();
            $('body').toggleClass('modal-open');
        }
    });
});