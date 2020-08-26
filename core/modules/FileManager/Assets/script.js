$(function () {
    var nestedSortables = [].slice.call($('.nested-sortable-file_permission'));

    for (var i = 0; i < nestedSortables.length; i++) {
        new Sortable(nestedSortables[i], {
            animation: 150,
            handle: '.draggable',
            onEnd: function (evt) {
                sortRole("#main_sortable");
            }
        });
    }

    function sortRole(idMenu) {
        var weight = 1;

        $(idMenu).find('input[name^="profil_weight"]').each(function () {
            $(this).val(weight);
            weight++;
        });
    }
});

/* COPY CLIPBOARD */
$(document).delegate('.copy-clipboard', 'click', function (evt) {
    evt.preventDefault();
    const tmp = document.createElement("textarea");
    tmp.value = this.href;
    tmp.style.height = "0";
    tmp.style.overflow = "hidden";
    tmp.style.position = "fixed";
    document.body.appendChild(tmp);
    tmp.focus();
    tmp.select();
    document.execCommand("copy");
    document.body.removeChild(tmp);
});
/**
 * Evenements de la page des profils de fichier.
 */
if (all = document.getElementById('file_extensions_all')) {
    all.addEventListener('click', function () {
        const extensions = document.querySelectorAll('.ext');
        const checked = this.checked;
        extensions.forEach(function (el) {
            el.checked = checked;
        });
    });
}
document.querySelectorAll('.ext').forEach(function (el) {
    el.addEventListener('click', function () {
        const all = document.getElementById('file_extensions_all');
        if (all.checked) {
            all.checked = false;
        }
    });
});
/**
 * Ajoute les événements des action (voir, modifier, supprimer) de fichiers.
 */
$(document).delegate('#modal_filemanager input[name="submit"]', 'click', function (evt) {
    evt.preventDefault();
    const $formModal = $(this).parent('form');
    const $modal = $(this).closest('.modal');
    $.ajax({
        url: $formModal.attr('action'),
        type: $formModal.attr('method'),
        data: $formModal.serialize(),
        dataType: 'json',
        success: function () {
            $modal.toggleClass('modal-open');
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
    const link = $(this).data('link_show');
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
    const link = $(this).data('link');
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

$(document).delegate('#file_create', 'click', function (evt) {
    evt.preventDefault();
    const link = $(this).data('link');
    
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            $('.modal-content').html(data);
            dropFile();
        }
    });
});

$(document).delegate('.filemanager-dropfile #file', 'change', function (evt) {
    const $form = $('.filemanager-dropfile');
    uploadFile($form);
});

/**
 * Ajoute les événements à la création de fichier.
 */
function dropFile() {
    const $form = $('.filemanager-dropfile');
    $form.on('dragover dragleave drop', function (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }).on('dragover', function () {
        $form.css('outline-offset', '-10px');
    }).on('dragleave', function () {
        $form.css('outline-offset', '0px');
    }).on('drop', function (evt) {
        const droppedFiles = evt.originalEvent.dataTransfer.files;
        $form.find('input[type="file"]').prop('files', droppedFiles);
        evt.preventDefault();
        uploadFile($form);
    });
}

function uploadFile($form) {
    const data = new FormData($form.get(0));
    const action = $form.attr("action");

    $.ajax({
        xhr: function () {
            $(".filemanager-dropfile__label").hide();
            $(".filemanager-dropfile__progress_bar")
                    .append('<div class="filemanager-dropfile__progress_bar_complete"></div>');
            $(".filemanager-dropfile__progress").show();


            var xhr = new window.XMLHttpRequest();

            xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.floor((evt.loaded / evt.total) * 100);
                    $(".filemanager-dropfile__progress_percent").html(percentComplete + '%');
                    $(".filemanager-dropfile__progress_bar_complete").width(percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        url: action,
        type: 'POST',
        data: data,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        complete: function () {
            setTimeout(function () {
                $form.removeClass('filemanager-dropfile__success');
                $form.removeClass('filemanager-dropfile__error');
                $form.css('outline-offset', '0px');
                $(".filemanager-dropfile__progress").hide();
                $(".filemanager-dropfile__label").show();
                $(".filemanager-dropfile__progress_bar_complete").remove();
            }, 1000);
        },
        success: function () {
            $form.addClass('filemanager-dropfile__success');
            $(".filemanager-dropfile__progress_percent").html('Complete !');
            var action = $('#table-file').data('link_show');
            updateManager(action);
        },
        error: function (data) {
            $form.addClass('filemanager-dropfile__error');
            $(".filemanager-dropfile__progress_percent").html('Error !');
            renderMessage('.modal-messages', data.responseJSON);
        }
    });
}


function renderMessage(selector, data) {
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
        }
    });
}