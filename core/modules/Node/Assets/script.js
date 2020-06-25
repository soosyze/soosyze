$(function () {
    $('#form_filter_node').on('keyup change', debounce(function () {
        const $this = $(this);

        $.ajax({
            url: $this.attr('action'),
            type: $this.attr('method'),
            data: $this.serialize(),
            dataType: 'html',
            success: function (data) {
                $('tbody').replaceWith(data);
            }
        });
    }, 250));
});