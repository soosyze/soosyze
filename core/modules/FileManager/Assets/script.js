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

$(document).delegate('#form_filter_file', 'input', debounce(function () {
    const $this = $(this);

    $.ajax({
        url: $this.attr('action'),
        type: $this.attr('method'),
        data: $this.serialize(),
        dataType: 'html',
        success: function (data) {
            $('#table-file').html(data);
        }
    });
}, 250));

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
    $.ajax({
        url: $formModal.attr('action'),
        type: $formModal.attr('method'),
        data: $formModal.serialize(),
        dataType: 'json',
        success: function () {
            var action = $('#table-file').data('link_show');
            updateManager(action);
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

$(document).delegate('.filemanager-dropfile #files', 'change', function (evt) {
    const $form = $('.filemanager-dropfile');

    $.each($(".filemanager-dropfile input[type='file']")[0].files, function (i, file) {
        var data = new FormData();
        data.append('file', file);
        uploadFile($form, data);
    });
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

        $.each($(".filemanager-dropfile input[type='file']")[0].files, function (i, file) {
            var data = new FormData();
            data.append('file', file);
            uploadFile($form, data);
        });
        $form.css('outline-offset', '0px');
    });
}

function uploadFile($form, data) {
    const $percent = document.createElement('span');
    $percent.className = 'filemanager-dropfile__progress_percent';

    const $progressComplete = document.createElement('div');
    $progressComplete.className = 'filemanager-dropfile__progress_bar_complete';

    const $progress = document.createElement('div');
    $progress.className = 'filemanager-dropfile__progress_bar';
    $progress.appendChild($progressComplete);

    const $progressRender = document.createElement('div');
    $progressRender.className = 'filemanager-dropfile__progress_render';

    const $fileShowNew = document.createElement('div');
    $fileShowNew.className = 'filemanager-dropfile__progress';
    $fileShowNew.appendChild($progressRender);
    $fileShowNew.appendChild($percent);
    $fileShowNew.appendChild($progress);

    $.ajax({
        xhr: function () {
            $('#filemanager-dropfile__progress_cards').prepend($fileShowNew);

            var xhr = new window.XMLHttpRequest();

            xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.floor((evt.loaded / evt.total) * 100);
                    $percent.innerHTML = percentComplete + '%';
                    $progressComplete.style.width = percentComplete + '%';
                }
            }, false);
            return xhr;
        },
        url: $form.attr("action"),
        type: 'POST',
        data: data,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            $progressRender.title = `${data.name}.${data.ext}`;

            if (data.type === 'image') {
                $progressRender.style.backgroundImage = `url(${data.link_file})`;
            } else {
                const $fileIcon = document.createElement('span');
                $fileIcon.className = `file ${data.ext}`;

                const $fileIconName = document.createElement('span');
                $fileIconName.className = 'ext-name';
                $fileIconName.innerHTML = data.ext;

                const $fileName = document.createElement('div');
                $fileName.innerHTML = `${data.name}.<span class="ext">${data.ext}</span>`;

                $fileIcon.appendChild($fileIconName);
                $progressRender.appendChild($fileIcon);
                $progressRender.appendChild($fileName);
            }

            $percent.innerHTML = data.messages.type;
            $fileShowNew.classList.add('filemanager-dropfile__success');

            var action = $('#table-file').data('link_show');
            updateManager(action);
        },
        error: function (data) {
            $fileShowNew.classList.add('filemanager-dropfile__error');

            Object.entries(data.responseJSON.messages.errors).forEach(function ([key, val]) {
                const $message = document.createElement('p');
                $message.innerHTML = val;
                $progressRender.prepend($message);
            });

            $percent.innerHTML = data.responseJSON.messages.type;
        }
    });
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