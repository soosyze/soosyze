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
});

function sortEntity(evt, target) {
    let weight = 1;

    $(evt.from).children(".sort_weight").each(function () {
        $(this).children('input[name*="weight"]').val(weight);
        weight++;
    });
}