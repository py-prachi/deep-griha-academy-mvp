{{-- Auto-saves this form's fields to localStorage as the teacher types, and offers to
     restore them if the page is reopened before a successful submit (e.g. after the
     connection drops mid-save). Include with: @include('lesson-planning._draft-autosave', ['formId' => '...', 'draftKey' => '...']) --}}
<script>
(function () {
    var form = document.getElementById('{{ $formId }}');
    if (!form) return;
    var draftKey = '{{ $draftKey }}';
    var hasErrors = {{ $errors->any() ? 'true' : 'false' }};

    function getFieldsMap() {
        var map = {};
        form.querySelectorAll('[name]').forEach(function (el) {
            if (el.name === '_token' || el.name === '_method') return;
            if (el.type === 'radio' || el.type === 'checkbox') {
                if (el.checked) map[el.name] = el.value;
            } else {
                map[el.name] = el.value;
            }
        });
        return map;
    }

    function applyFieldsMap(map) {
        Object.keys(map).forEach(function (name) {
            form.querySelectorAll('[name="' + name + '"]').forEach(function (el) {
                if (el.type === 'radio' || el.type === 'checkbox') {
                    el.checked = (el.value === map[name]);
                    el.dispatchEvent(new Event('change'));
                } else {
                    el.value = map[name];
                    // Rich-text fields render through Quill, not the raw
                    // textarea — push the restored value into the editor too.
                    if (el.classList.contains('rich-textarea-source')) {
                        el.dispatchEvent(new Event('rich-textarea:restore'));
                    }
                }
            });
        });
    }

    function saveDraft() {
        try {
            localStorage.setItem(draftKey, JSON.stringify({ t: Date.now(), data: getFieldsMap() }));
        } catch (e) {}
    }

    var saveTimer;
    form.addEventListener('input', function () {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 800);
    });

    // Don't offer a restore over freshly re-shown validation input — old() already has it.
    if (hasErrors) return;

    try {
        var raw = localStorage.getItem(draftKey);
        if (!raw) return;
        var saved = JSON.parse(raw);
        if (!saved || !saved.data || !Object.keys(saved.data).length) return;

        var banner = document.createElement('div');
        banner.className = 'alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2';
        banner.innerHTML =
            '<span><i class="bi bi-exclamation-triangle me-1"></i> Unsaved changes were found from a previous visit (likely lost when your connection dropped). Restore them?</span>' +
            '<span>' +
                '<button type="button" class="btn btn-sm btn-warning me-2" id="draft-restore-btn">Restore</button>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary" id="draft-discard-btn">Discard</button>' +
            '</span>';
        form.parentNode.insertBefore(banner, form);

        document.getElementById('draft-restore-btn').addEventListener('click', function () {
            applyFieldsMap(saved.data);
            banner.remove();
        });
        document.getElementById('draft-discard-btn').addEventListener('click', function () {
            try { localStorage.removeItem(draftKey); } catch (e) {}
            banner.remove();
        });
    } catch (e) {}
})();
</script>
