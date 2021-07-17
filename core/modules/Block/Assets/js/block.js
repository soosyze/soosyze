$(function () {
    $(document).delegate('#modal_block input[type="submit"].submit-block-form', 'click', function (evt) {
        evt.preventDefault();
        const $this = $(this);
        const $form = $this.closest('form');

        let data = $form.serialize();
        const activeEl = document.activeElement;

        if (activeEl && activeEl.name && (activeEl.type === "submit" || activeEl.type === "image")) {
            if (data) {
                data += "&";
            }
            data += activeEl.name;
            if (activeEl.value) {
                data += "=" + activeEl.value;
            }
        }

        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            dataType: 'json',
            success: function (data) {
                closeModal.call(evt.target, evt);
                showBlock(data.inputs.section, data.link_show);
            },
            error: function (data) {
                renderMessage('#modal_block .modal-messages', data.responseJSON);
                fieldsetError(data.responseJSON.errors_keys);
            }
        });
    });

    /**
     * Menu de navigation pour les blocs.
     */
    $(document).delegate('.block-actions a.mod', 'click', function (evt) {
        evt.preventDefault();
        evt.stopPropagation();
        const link = evt.currentTarget.href;

        $.ajax({
            url: link,
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('#modal_block .modal-content').html(data);
                addEditor();
            }
        });
    });
});

function sortSection(evt, target)
{
    let weight = 0;

    $(evt.to).find('.block').each(function () {
        $.ajax({
            url: $(this).find('.fa-arrows-alt').data('link_update'),
            type: 'POST',
            data: `weight=${weight}&section=${$(evt.to).data('id')}`
        });
        weight++;
    });
}

function searchBlocks() {
    const search = document.getElementById('search').value;
    const reg = new RegExp(search, 'i');
    const elements = document.querySelectorAll('.search_item');

    Array.prototype.forEach.call(elements, function (el) {
        el.style.display = '';
        const searchEl = el.querySelector('.search_text');

        if (reg.test(searchEl.textContent)) {
            const str = strHighlight(search, searchEl.textContent);
            searchEl.innerHTML = str;
        } else {
            el.style.display = 'none';
        }
    });
}

/**
 * Affiche un bloc lorsque l'on clique sur la radiobox
 */
$(document).delegate('#modal_block .block-card label', 'click', function (evt) {
    const $this = $(this);
    const link = $this.data('link');

    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            $('#modal_block .block-preview').html(data);
        }
    });
});

/**
 * Affiche le formulaire de création de bloc après sa selection
 */
$(document).delegate('#modal_block input[type="submit"].block-create-list', 'click', function (evt) {
    evt.preventDefault();
    const $this = $(this);
    const $form = $this.closest('form');

    let data = $form.serialize();

    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        dataType: 'html',
        success: function (data) {
            $('#modal_block .modal-content').html(data);
            addEditor();
        },
        error: function (data) {
            renderMessage('#modal_block .modal-messages', data.responseJSON);
        }
    });
});

function showBlock(id, link) {
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            $('#section-' + id).replaceWith(data);
        }
    });
}

function fieldsetError(dataError) {
    $('#modal_block .tab-pane').each(function () {
        let idPane = $(this).attr("id");

        $(this).find('input, textarea, select').each(function () {
            if (dataError.indexOf(this.id) != -1 || this.checkValidity() === false || $(this).hasClass('is-invalid')) {
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
}