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
    /* MODAL */
    var target = '';
    $(document).delegate('[data-toogle="modal"]', 'click', function (evt) {
        evt.preventDefault();
        target = $(this).data('target');
        $('body').toggleClass('modal-open');
        $(target).show();
    });
    $(window).click(function (evt) {
        if (evt.target.className === 'modal' || evt.target.className === 'close') {
            $(target).hide();
            $('body').toggleClass('modal-open');
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