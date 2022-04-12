
function sortMenu(evt) {
    let weight = 1;
    let id = $(evt.to).parent("li").children('input[name^="link_id"]').val();

    if (id === undefined) {
        id = -1;
    }

    $(evt.to).children("li").each(function () {
        $(this).children('input[name^="weight"]').val(weight);
        $(this).children('input[name^="parent"]').val(id);
        weight++;
    });
}