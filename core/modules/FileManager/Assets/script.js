/* COPY CLIPBOARD */
document.querySelectorAll('.copy-clipboard').forEach(function (el) {
    el.addEventListener('click', function () {
        var tmp = document.createElement("textarea");
        tmp.value = this.dataset.link;
        tmp.style.height = "0";
        tmp.style.overflow = "hidden";
        tmp.style.position = "fixed";
        document.body.appendChild(tmp);
        tmp.focus();
        tmp.select();
        document.execCommand("copy");
        document.body.removeChild(tmp);
    });
});
/**
 * Evenements de la page des profils de fichier.
 */
if (all = document.getElementById('file_extensions_all')) {
    all.addEventListener('click', function () {
        var extensions = document.querySelectorAll('.ext');
        var checked = this.checked;
        extensions.forEach(function (el) {
            el.checked = checked;
        });
    });
}
document.querySelectorAll('.ext').forEach(function (el) {
    el.addEventListener('click', function () {
        var all = document.getElementById('file_extensions_all');
        if (all.checked) {
            all.checked = false;
        }
    });
});
/**
 * Ajoute les événements des action (voir, modifier, supprimer) de fichiers.
 */
$(document).delegate('#modal_folder input[name="submit"]', 'click', function (evt) {
    evt.preventDefault();
    var $formModal = $(this).parent('form');
    var $modal = $(this).closest('.modal');
    $.ajax({
        url: $formModal.attr('action'),
        type: $formModal.attr('method'),
        data: $formModal.serialize(),
        dataType: 'json',
        success: function () {
            $modal.hide();
            $('body').toggleClass('modal-open');
            var action = $('#table-file').data('link_show');
            updateManager(action);
        },
        error: function (data) {
            renderMessage('.modal-messages', data.responseJSON);
        }
    });
});
/**
 * Evenement des bouton d'actions.
 */
$(document).delegate('.actions-file .mod', 'click', function (evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var link = $(this).data('link');
    var target = $(this).data('target');
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            $('.modal-content').html(data);
            renderMessage('.modal-messages', data);
        }
    });
})
/**
 * Evenement pour l'affichage des sous dossiers (icone).
 */
$(document).delegate('.dir-link_show', 'click', function (evt) {
    evt.preventDefault();
    updateManager($(this).data('link_show'));
});
/**
 * Evenement pour l'affichage des fichier (icone).
 */
$(document).delegate('.file-link_show', 'click', function (evt) {
    evt.preventDefault();
    var link = $(this).data('link_show');
    var target = $(this).data('target');
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
/**
 * Evenement de création de dossier.
 */
$(document).delegate('#folder_create', 'click', function (evt) {
    evt.preventDefault();
    var link = $(this).data('link');
    var target = $(this).data('target');
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


dropFile();

/**
 * Ajoute les événements à la création de fichier.
 */
function dropFile() {
    var $form = $('.dropfile');
    $form.on('dragover dragleave drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
    }).on('dragover', function () {
        $form.css('outline-offset', '-10px');
    }).on('dragleave', function () {
        $form.css('outline-offset', '0px');
    }).on('drop', function (evt) {
        var droppedFiles = evt.originalEvent.dataTransfer.files;
        $form.find('input[type="file"]').prop('files', droppedFiles);
        evt.preventDefault();
        var data = new FormData($form.get(0));
        var action = $form.attr("action");
        $.ajax({
            url: action,
            type: 'POST',
            data: data,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            complete: function () {
                $form.css('outline-offset', '0px');
            },
            success: function () {
                $form.css('outline', '2px dashed green');
                var action = $('#table-file').data('link_show');
                updateManager(action);
            },
            error: function (data) {
                $form.css('outline', '2px dashed red');
                console.log(data.responseJSON);
                renderMessage('.dropfile-messages', data.responseJSON);
            }
        });
    });
}


function renderMessage(selector, data) {
    $(selector).html('');
    if (data.messages !== undefined && data.messages.success !== undefined) {
        $.each(data.messages.success, function (key, val) {
            $(selector).append('<div class="alert alert-success" role="alert"><p>' + val + '</p></div>');
        });
    } else if (data.messages !== undefined && data.messages.errors !== undefined) {
        $.each(data.messages.errors, function (key, val) {
            $(selector).append('<div class="alert alert-danger" role="alert"><p>' + val + '</p></div>');
        });
        $.each(data.errors_keys, function (key, val) {
            $('.modal #' + val).css('border-color', '#f00');
        });
    }
}

/**
 * 
 * @param {type} action
 * @returns {undefined}
 */
function updateManager(action) {
    $.ajax({
        url: action,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            $('#filemanager').html(data);
            dropFile();
        }
    });
}