document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rich-textarea').forEach(function (wrapper) {
        var editorEl = wrapper.querySelector('.rich-textarea-editor');
        var sourceEl = wrapper.querySelector('.rich-textarea-source');
        var hasToolbar = wrapper.dataset.toolbar === '1';
        var minHeight = wrapper.dataset.minHeight || 140;

        editorEl.style.minHeight = minHeight + 'px';

        var quill = new Quill(editorEl, {
            theme: 'snow',
            formats: ['bold', 'italic'],
            placeholder: wrapper.dataset.placeholder || '',
            modules: {
                toolbar: hasToolbar ? [['bold', 'italic']] : false
            }
        });

        // Exposed so the draft-autosave restore flow (a plain <textarea>
        // value swap) can also push the restored content back into Quill.
        wrapper.quillInstance = quill;

        loadIntoQuill(quill, sourceEl.value || '');
        syncSource(quill, sourceEl);

        quill.on('text-change', function (delta, oldDelta, source) {
            if (!hasToolbar && source === 'user') {
                applyBoldShorthand(quill);
            }
            syncSource(quill, sourceEl);
        });

        // A draft-restore (see _draft-autosave.blade.php) sets sourceEl.value
        // directly and dispatches this event rather than a plain 'input',
        // to avoid feeding our own syncSource() output back into itself.
        sourceEl.addEventListener('rich-textarea:restore', function () {
            loadIntoQuill(quill, sourceEl.value || '');
        });
    });

    function loadIntoQuill(quill, value) {
        quill.setText('');
        if (value.indexOf('<') !== -1) {
            quill.clipboard.dangerouslyPasteHTML(value);
        } else if (value.trim() !== '') {
            quill.setText(value);
        }
    }

    function syncSource(quill, sourceEl) {
        var html = quill.root.innerHTML;
        sourceEl.value = (html === '<p><br></p>') ? '' : html;
        sourceEl.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Small fields have no visible toolbar — typing **word** converts it to
    // bold inline, so formatting is still possible without the toolbar UI.
    function applyBoldShorthand(quill) {
        var range = quill.getSelection();
        if (!range) return;

        var textBeforeCursor = quill.getText(0, range.index);
        var match = textBeforeCursor.match(/\*\*([^*]+)\*\*$/);
        if (!match) return;

        var start = range.index - match[0].length;
        quill.deleteText(start, match[0].length);
        quill.insertText(start, match[1], 'bold', true);
        quill.formatText(start + match[1].length, 0, 'bold', false);
        quill.setSelection(start + match[1].length, 0);
    }
});
