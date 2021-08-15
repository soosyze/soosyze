
var debounce = function (func, wait, immediate) {
    var timeout;

    return function () {
        var context = this;
        var args = arguments;

        var later = function () {
            timeout = null;
            if (!immediate) {
                func.apply(context, args);
            }
        };

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);

        if (immediate && !timeout) {
            func.apply(context, args);
        }
    };
};

var renderMessage = function (selector, data) {
    $(selector).html('');
    if (data != undefined && data.messages != undefined && data.messages.success != undefined) {
        $.each(data.messages.success, function (key, val) {
            $(selector).append(`<div class="alert alert-success" role="alert"><p>${val}</p></div>`);
        });
    } else if (data != undefined && data.messages != undefined && data.messages.errors != undefined) {
        $.each(data.messages.errors, function (key, val) {
            $(selector).append(`<div class="alert alert-danger" role="alert"><p>${val}</p></div>`);
        });
        $.each(data.errors_keys, function (key, val) {
            $(`#${val}`).addClass('is-invalid');
        });
    }
}

/* Rempli une liste de sélection dynamiquement à partir du choix d' une première liste. */
$(document).delegate('select.ajax-control', 'change', function () {
    const $selectCurrent = $(this)[0];
    const $selectTarget = $($(this).data('target'));
    const $optionSelected = $selectCurrent.options[$selectCurrent.selectedIndex];
    const $link = $($optionSelected).data('link');

    $.ajax({
        url: $link,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            let html = '';
            data.forEach(function (value) {
                html += `<option value="${value.value}">${value.label}</option>`;
            });
            $selectTarget.html(html);
        }
    });
});

(function () {
    /* --------------------------- */
    /* -------- SORTABLE --------- */
    /* --------------------------- */
    let nestedSortables = [].slice.call($('[data-draggable="sortable"]'));

    for (let i = 0; i < nestedSortables.length; i++) {
        let el = nestedSortables[i];
        let options = {
            animation: 150,
            dragoverBubble: true,
            fallbackOnBody: true,
            ghostClass: "placeholder",
            swapThreshold: 0.2
        };

        if (el.getAttribute("data-ghostClass") !== null) {
            options.ghostClass = el.getAttribute("data-ghostClass");
        }
        if (el.getAttribute("data-group") !== null) {
            options.group = el.getAttribute("data-group");
        }
        if (el.getAttribute("data-handle") !== null) {
            options.handle = el.getAttribute("data-handle");
        }
        if (el.getAttribute("data-onEnd") !== null) {
            options.onEnd = function (evt) {
                let stringFunction = el.getAttribute("data-onEnd");

                window[stringFunction](evt);
            };
        }

        new Sortable(el, options);
    }

    /* --------------------------- */
    /* -------- ICON RENDER ------ */
    /* --------------------------- */
    $('.text_icon').keyup(function () {
        $(this).parent().find('.render_icon i').attr('class', this.value);
    });

    /* --------------------------- */
    /* -------- API ROUTE -------- */
    /* --------------------------- */

    /* Place la liste sous tous les champs nécessitant une route. */
    const groupsApiRoute = document.querySelectorAll("div.api_route");

    if (groupsApiRoute) {
        for (var i = 0; i < groupsApiRoute.length; i++) {
            const target = $(groupsApiRoute[i]).find('input').attr('id');
            $(groupsApiRoute[i]).after(`<ul class="api_route-list hidden" data-target="#${target}"></ul>`);
        }
    }

    /* Place la liste sous le champ de champs nécessitant une route. */
    const inputsApiRoute = document.querySelectorAll("input.api_route");

    if (inputsApiRoute) {
        for (var i = 0; i < inputsApiRoute.length; i++) {
            const target = $(inputsApiRoute[i]).attr('id');
            $(inputsApiRoute[i]).after(`<ul class="api_route-list hidden" data-target="#${target}"></ul>`);
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
            data: $.param({title: searchField, exclude: exclude}),
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

    /* --------------------------- */
    /* -------- SELECT 2 --------- */
    /* --------------------------- */
    $('.select-ajax').select2({
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
        allowClear: true,
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
})();