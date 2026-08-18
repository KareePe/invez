/**
 * Thousand separators while typing in the property price field.
 *
 * The field is type="text" because type="number" refuses to hold a value with
 * commas in it. Digits are regrouped on every keystroke and the separators are
 * stripped again right before the form posts, so the server still receives a
 * plain number. The server strips commas too — this is a typing aid, not
 * validation.
 */
function initPriceInput(input) {
    if (!input) return;

    function digits(value) {
        return String(value).replace(/\D/g, '');
    }

    function group(value) {
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Server-rendered and draft-restored values both come through here.
    input.value = group(digits(input.value));

    input.addEventListener('input', function () {
        // Count digits before the caret rather than characters, so inserting a
        // comma to the left does not push the caret out of place.
        var before = digits(input.value.slice(0, input.selectionStart)).length;
        input.value = group(digits(input.value));

        var pos = 0, seen = 0;
        while (pos < input.value.length && seen < before) {
            if (/\d/.test(input.value.charAt(pos))) seen++;
            pos++;
        }
        input.setSelectionRange(pos, pos);
    });

    if (input.form) {
        input.form.addEventListener('submit', function () {
            input.value = digits(input.value);
        });
    }
}
