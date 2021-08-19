function search()
{
    const search = $('#search').val();
    const active = $('#active').prop('checked');
    const disabled = $('#disabled').prop('checked');
    const reg = new RegExp(search, 'i');
    let number = 0;

    $('.package').each(function () {
        /* Si le package doit être affiché. */
        var package_hide = 'none';
        $(this).find('.module').each(function () {
            var checked = $(this).find('input[type=checkbox]').prop('checked');
            $(this).css('display', '');
            /* Si l'expression régulière est correcte. */
            if (reg.test($(this).data('title'))) {
                /* Si les 2 checkboxs ne sont pas cochées et que la condition ne correspond pas à l'état du module. */
                if (!(active && disabled) && (checked !== active || checked === disabled)) {
                    $(this).css('display', 'none');
                    return;
                }
                const str = strHighlight(search, $(this).data('title'));
                $(this).find('.str-search').html(`<span class="ui"></span> ${str}`);

                number++;
                package_hide = '';
            } else {
                $(this).css('display', 'none');
            }
        });
        $(this).css('display', package_hide);
        /* Pour l'affichage de la navigation. */
        $(`#nav-${this.id}`).css('display', package_hide);
    });

    if (number === 0) {
        $('#form-package').css('display', 'none');
        $('#package-nothing').css('display', '');
    } else {
        $('#form-package').css('display', '');
        $('#package-nothing').css('display', 'none');
    }

    $('#result-search').text(
        number <= 1
            ? `${number} module`
            : `${number} modules`
    );
}

$('#nav_config li a').click(function () {
    const elemId = $(this).attr('href');
    highlight(elemId);
});

$('#theme_admin_dark').click(function () {
    $('body').toggleClass('dark');
});

function highlight(elemId) {
    var elem = $(elemId);
    elem.toggleClass('highlight');
    setTimeout(function () {
        elem.toggleClass('highlight');
    }, 500);
}

$(function () {
    /* Cache selectors */
    const topMenu = $("#top-menu");
    const topMenuHeight = topMenu.outerHeight();
    /* All list items */
    const menuItems = topMenu.find("a");
    /* Anchors corresponding to menu items */
    const scrollItems = menuItems.map(function () {
        var item = $($(this).attr("href"));
        if (item.length) {
            return item;
        }
    });

    /* Bind to scroll */
    $(window).scroll(function () {
        /* Get container scroll position */
        const fromTop = $(this).scrollTop() + topMenuHeight;
        /* Get id of current scroll item */
        let cur = scrollItems.map(function () {
            if ($(this).offset().top < fromTop)
                return this;
        });
        /* Get the id of the current element */
        cur = cur[cur.length - 1];
        const id = cur && cur.length ? cur[0].id : "";
        /* Set/remove active class */
        menuItems.parent()
                .removeClass("active")
                .end()
                .filter(`[href='#${id}']`)
                .parent()
                .addClass("active");
    });

    new LazyLoad({});
});

$(document).delegate('.form-api input[type="submit"]', 'click', function (evt) {
    evt.preventDefault();
    const $this         = $(this);
    const $form         = $this.closest('form');
    const $classTabPane = $form.attr('data-tab-pane');

    let data = new FormData($form[0]);

    method = $($form).find('input[name="__method"]').attr('value');

    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        dataType: 'json',
        processData: false,
        contentType: false,
        headers: {"X-HTTP-Method-Override": method},
        success: function (data) {
            window.location.replace(data.redirect);
        },
        error: function (data) {
            renderMessage('.messages', data.responseJSON);
            fieldsetErrorFormApi($form, $classTabPane);
        }
    });
});

function fieldsetErrorFormApi(form, classTabPane) {
    $(form).find('.tab-pane').each(function () {
        let idPane = $(this).attr("id");

        $(this).find('input, textarea, select').each(function () {

            if (this.checkValidity() === false || $(this).hasClass('is-invalid')) {
                const tabPaneError = `
                    <span class="tab-pane-error" title="Error">
                        <i class='fa fa-exclamation-triangle' aria-hidden="true"></i>
                    <span>`;

                $(`${classTabPane} ul a[href="#${idPane}"]`).css("color", "red");
                $(`${classTabPane} ul a .tab-pane-error`).remove();
                $(`${classTabPane} ul a[href="#${idPane}"]`).append(tabPaneError);

                return false;
            }
        });
        $(this).find('.trumbowyg-textarea').each(function () {
            if (this.checkValidity() === false || $(this).hasClass('is-invalid')) {
                $(this).closest(`.trumbowyg-box`).css("border-color", "red");
            }
        });
    });
}