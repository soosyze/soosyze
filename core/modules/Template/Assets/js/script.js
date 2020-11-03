
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