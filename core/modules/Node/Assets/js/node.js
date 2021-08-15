$(function () {
    /* Requête à partir des filtres. */
    $('#form_filter_node').on('input', debounce(function () {
        const $this = $(this);

        $.ajax({
            url: $this.attr('action'),
            type: $this.attr('method'),
            data: $this.serialize(),
            dataType: 'html',
            success: function (data) {
                $('.node-table').html(data);
            }
        });
    }, 250));

    /* Requête à partir de la pagination ou du trie. */
    $(document).delegate('.node-table .pagination a, .node-table a.sort', 'click', function (evt) {
        evt.preventDefault();
        const $this = $(this);

        $.ajax({
            url: $this.attr('href'),
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('.node-table').html(data);
            }
        });
    });
    $(document).delegate('#modal_node input[name="submit"]', 'click', function (evt) {
        evt.preventDefault();
        const $formModal = $(this).parent('form');
        $.ajax({
            url: $formModal.attr('action'),
            type: $formModal.attr('method'),
            data: $formModal.serialize(),
            dataType: 'json',
            success: function () {
                closeModal.call(evt.target, evt);
            },
            error: function (data) {
                renderMessage('.modal-messages', data.responseJSON);
            }
        });
    });
    /**
     * Evenement des bouton d'actions.
     */
    $(document).delegate('.btn-action-remove', 'click', function (evt) {
        evt.preventDefault();
        evt.stopPropagation();

        const link = evt.currentTarget.href;
        $.ajax({
            url: link,
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('.modal-content').html(data);
                renderMessage('.modal-messages', data);
            }
        });
    });

    $('#form-node .tab-pane').each(function () {
        let idPane = $(this).attr("id");

        $(this).find('input, textarea, select').each(function () {

            if ($(this).hasClass('is-invalid')) {
                const error = `
                    <span class="fieldset-error" title="Error">
                        <i class='fa fa-exclamation-triangle' aria-hidden="true"></i>
                    <span>`;

                $(`ul a[href="#${idPane}"]`).css("color", "red");
                $(`ul a .fieldset-error`).remove();
                $(`ul a[href="#${idPane}"]`).append(error);

                return false;
            }
        });
    });
});

function sortEntity(evt, target) {
    let weight = 1;

    $(evt.from).children(".sort_weight").each(function () {
        $(this).children('input[name*="weight"]').val(weight);
        weight++;
    });
}