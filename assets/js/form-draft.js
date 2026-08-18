/**
 * Unsaved-form draft, kept in localStorage.
 *
 * Used by the property edit forms (member and admin) so closing the tab or
 * navigating away mid-edit does not lose what was typed. The draft is restored
 * on the next visit to the same form and removed as soon as the form is
 * submitted.
 *
 * Values are stored per field name, in DOM order, so repeated names such as
 * highlights[] restore into the right rows.
 */
function initFormDraft(form, key) {
    if (!form || !window.localStorage) return;

    // Files cannot be restored, and hidden fields are CSRF tokens the server
    // re-issues on every render.
    var SKIP = ['file', 'hidden', 'submit', 'button', 'reset'];

    function fields() {
        return Array.prototype.filter.call(
            form.querySelectorAll('input[name], select[name], textarea[name]'),
            function (el) { return SKIP.indexOf(el.type) === -1; }
        );
    }

    function save() {
        var data = {};
        fields().forEach(function (el) {
            if (!data[el.name]) data[el.name] = [];
            if (el.type === 'checkbox' || el.type === 'radio') {
                data[el.name].push(el.checked ? el.value : null);
            } else {
                data[el.name].push(el.value);
            }
        });
        try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
    }

    function clear() {
        try { localStorage.removeItem(key); } catch (e) {}
    }

    function restore() {
        var data;
        try { data = JSON.parse(localStorage.getItem(key)); } catch (e) { return; }
        if (!data) return;

        // Highlight rows are added dynamically, so recreate the ones the draft
        // had beyond what the server rendered before filling values in.
        var drafted  = (data['highlights[]'] || []).length;
        var rendered = form.querySelectorAll('input[name="highlights[]"]').length;
        if (typeof addHighlight === 'function') {
            for (var i = rendered; i < drafted; i++) addHighlight();
            if (drafted > rendered && document.activeElement) document.activeElement.blur();
        }

        var seen = {};
        fields().forEach(function (el) {
            seen[el.name] = seen[el.name] === undefined ? 0 : seen[el.name] + 1;
            var values = data[el.name];
            if (!values) return;
            var value = values[seen[el.name]];
            if (value === undefined) return;
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = value !== null;
            } else {
                el.value = value;
            }
        });
    }

    restore();
    form.addEventListener('input', save);
    form.addEventListener('change', save);
    form.addEventListener('submit', clear);
}
