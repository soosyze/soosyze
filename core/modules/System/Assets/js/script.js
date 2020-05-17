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
    $('#result-search').text(`${number} module(s)`);
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
    elem.css("backgroundColor", "#fff"); // hack for Safari
    elem.animate({backgroundColor: '#e1e4e8'}, 0);
    setTimeout(function () {
        $(elemId).animate({backgroundColor: "transparent"}, 500)
    }, 300);
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
});