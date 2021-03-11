
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
    if (data.messages != null && data.messages.success != null) {
        $.each(data.messages.success, function (key, val) {
            $(selector).append(`<div class="alert alert-success" role="alert"><p>${val}</p></div>`);
        });
    } else if (data.messages !== undefined && data.messages.errors !== undefined) {
        $.each(data.messages.errors, function (key, val) {
            $(selector).append(`<div class="alert alert-danger" role="alert"><p>${val}</p></div>`);
        });
        $.each(data.errors_keys, function (key, val) {
            $(`.modal #${val}`).css('border-color', '#f00');
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

(function() {
    /**
     * Délare les éléments Traînable comme étant triable et leur options.
     */
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

    /**
     * Délare les listes de sélection dynamique
     */
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