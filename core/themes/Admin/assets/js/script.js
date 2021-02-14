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

    /* Place la liste sous tous les champs nécessitant une route. */
    const groupsApiRoute = document.querySelectorAll("div.api_route, input.api_route");

    if (groupsApiRoute) {
        for (var i = 0; i < groupsApiRoute.length; i++) {
            const target = $(groupsApiRoute[i]).find('input').attr('id');
            $(groupsApiRoute[i]).after(`<ul class="api_route-list hidden" data-target="#${target}"></ul>`);
        }
    }

    /* Déclanche la recherce des route en fonction du terme. */
    $(document).delegate('input.api_route, .api_route input', 'keyup', debounce(function () {
        /* Récupère la liste avec l'id de l'input. */
        const list = $(`ul[data-target="#${$(this).attr('id')}"]`);
        const searchField = $(this).val();
        const link = $(this).data('link');
        const exclude = $(this).data('exclude');

        if (searchField === '') {
            list.addClass('hidden');
            list.html('');
        }

        $.ajax({
            url: link,
            type: 'GET',
            data: $.param({"title": searchField, "exclude": exclude}),
            dataType: 'json',
            success: function (data) {
                list.html('');
                data.forEach(function (value) {
                    list.append(`<li title="${value.route}" data-value="${value.route}"><b>${value.title}</b> <i>${value.route}</i></li>`);
                });
                list.removeClass('hidden');
            }
        });
    }, 250));

    /* Selection de la route */
    $(document).delegate('.api_route-list li', 'click', function (evt) {
        const value = $(this).data('value');
        const list = $(this).parent();
        const target = list.data('target');

        if (value !== '') {
            $(target).val(value);
            list.addClass('hidden');
            list.html('');
        }
    });
    /* Ferme la liste au clique dans la fenêtre. */
    window.addEventListener('click', (evt) => {
        const listApiRoute = document.querySelectorAll(".api_route-list");

        if (listApiRoute) {
            for (var i = 0; i < listApiRoute.length; i++) {
                if (!listApiRoute[i].classList.contains('hidden')) {
                    listApiRoute[i].classList.add('hidden');
                }
            }
        }
    });
});

