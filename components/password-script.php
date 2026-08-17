<?php
/* Password field enhancements — progressive enhancement only.
 * 1) Show/hide toggle on every <input type="password">
 * 2) Live "passwords match" feedback on inputs marked data-pw-match="<id of source field>"
 * Without JS the fields still render and submit normally; server-side validation is unchanged.
 *
 * Inlined via PHP include (not a <script src>) so it works regardless of whether the
 * site is served from the domain root or a subdirectory.
 */
?>
<script>
(function () {
    'use strict';

    var EN = (document.documentElement.lang || 'th').toLowerCase().indexOf('en') === 0;

    var TEXT = {
        show:  EN ? 'Show password' : 'แสดงรหัสผ่าน',
        hide:  EN ? 'Hide password' : 'ซ่อนรหัสผ่าน',
        match: EN ? 'Passwords match' : 'รหัสผ่านตรงกัน',
        diff:  EN ? 'Passwords do not match' : 'รหัสผ่านไม่ตรงกัน'
    };

    var EYE     = '<circle cx="12" cy="12" r="3"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>';
    var EYE_OFF = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

    function icon(paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"'
             + ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"'
             + ' stroke-linejoin="round">' + paths + '</svg>';
    }

    /* ---------- show / hide toggle ---------- */
    function addToggle(input) {
        var wrap = document.createElement('div');
        wrap.className = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.style.paddingRight = '2.5rem';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.tabIndex = -1;
        btn.style.cssText = 'position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);'
                          + 'display:flex;align-items:center;color:#9ca3af;cursor:pointer;';
        btn.innerHTML = icon(EYE);
        btn.setAttribute('aria-label', TEXT.show);

        btn.addEventListener('click', function () {
            var shown = input.type === 'text';
            input.type = shown ? 'password' : 'text';
            btn.innerHTML = icon(shown ? EYE : EYE_OFF);
            btn.setAttribute('aria-label', shown ? TEXT.show : TEXT.hide);
        });

        wrap.style.position = 'relative';
        wrap.appendChild(btn);
    }

    /* ---------- live confirm-password match ---------- */
    function addMatchCheck(confirmInput) {
        var source = document.getElementById(confirmInput.getAttribute('data-pw-match'));
        if (!source) return;

        var wrap = confirmInput.parentNode;
        var msg  = document.createElement('p');
        msg.style.cssText = 'font-size:0.75rem;line-height:1rem;margin-top:0.25rem;display:none;';
        wrap.parentNode.insertBefore(msg, wrap.nextSibling);

        function check() {
            if (!confirmInput.value) {
                msg.style.display = 'none';
                return;
            }
            var ok = source.value === confirmInput.value;
            msg.style.display = 'block';
            msg.style.color   = ok ? '#16a34a' : '#ef4444';
            msg.textContent   = ok ? TEXT.match : TEXT.diff;
        }

        source.addEventListener('input', check);
        confirmInput.addEventListener('input', check);
    }

    /* ---------- live rule checklist ----------
     * Opt-in: <input data-pw-rules="<id of the <ul>">, each <li data-pw-rule="len|digit|lower|upper">
     * containing a <span data-pw-mark>. Rules here must stay in sync with the
     * server-side checks in register.php. */
    var RULES = {
        len:   function (v) { return v.length >= 8; },
        digit: function (v) { return /[0-9]/.test(v); },
        lower: function (v) { return /[a-z]/.test(v); },
        upper: function (v) { return /[A-Z]/.test(v); }
    };

    function addRuleList(input) {
        var list = document.getElementById(input.getAttribute('data-pw-rules'));
        if (!list) return;

        var items = list.querySelectorAll('[data-pw-rule]');

        function check() {
            var value = input.value;
            for (var i = 0; i < items.length; i++) {
                var rule = RULES[items[i].getAttribute('data-pw-rule')];
                var mark = items[i].querySelector('[data-pw-mark]');
                if (!rule) continue;

                if (!value) {                       /* nothing typed yet — stay neutral */
                    items[i].style.color = '';
                    if (mark) mark.textContent = '•';
                } else if (rule(value)) {
                    items[i].style.color = '#16a34a';
                    if (mark) mark.textContent = '✓';
                } else {
                    items[i].style.color = '#ef4444';
                    if (mark) mark.textContent = '✗';
                }
            }
        }

        input.addEventListener('input', check);
        check();                                    /* handle browser-restored values */
    }

    var pwFields = document.querySelectorAll('input[type="password"]');
    for (var i = 0; i < pwFields.length; i++) addToggle(pwFields[i]);

    var ruleFields = document.querySelectorAll('input[data-pw-rules]');
    for (var k = 0; k < ruleFields.length; k++) addRuleList(ruleFields[k]);

    var confirmFields = document.querySelectorAll('input[data-pw-match]');
    for (var j = 0; j < confirmFields.length; j++) addMatchCheck(confirmFields[j]);
})();
</script>
