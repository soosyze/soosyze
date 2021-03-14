$(function () {
    addEditor();
});
function addEditor() {
    $.trumbowyg.svgPath = '/core/modules/Trumbowyg/vendor/trumbowyg/dist/icons.svg';
    $("textarea.editor").trumbowyg({
        lang: config.trumbowyg.lang,
        btnsDef: {
            // Create a new dropdown
            image: {
                dropdown: ["insertImage", "upload", "noembed"],
                ico: "insertImage"
            },
            customFormatting: {
                dropdown: ["p", "h2", "h3", "h4", "blockquote", "superscript", "subscript"],
                ico: "p"
            }
        },
        // Redefine the button pane
        btns: [
            ["viewHTML"],
            ["undo", "redo"], // Only supported in Blink browsers
            ["customFormatting", "preformatted", "removeformat"],
            ["emoji"],
            ["strong", "em", "del"],
            ["link"],
            ["image"],
            ["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],
            ["unorderedList", "orderedList"],
            ["horizontalRule"],
            ['table'],
            ["fullscreen"]
        ],
        imageWidthModalEdit: true,
        plugins: {
            // Add imagur parameters to upload plugin for demo purposes
            upload: {
                serverPath: config.trumbowyg.serverPath,
                fileFieldName: "image",
                urlPropertyName: "link"
            },
            table: {
                styler: "table table-striped table-hover"
            }
        },
        semantic: {
            "b": "strong",
            "s": "del",
            "strike": "del",
            "div": "div"
        },
        tagsToKeep: ["hr", "img", "embed", "iframe", "i"],
        tagsToRemove: ["applet", "embed", "form", "input", "link", "option", "script", "select", "textarea"]
    });
}