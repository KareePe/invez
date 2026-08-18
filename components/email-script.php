<?php
/* Live email validation — attaches to every <input type="email"> on the page.
 * Required-ness comes from the input's own `required` attribute, so no extra markup is needed.
 * Server-side FILTER_VALIDATE_EMAIL stays the authority; this is feedback only.
 *
 * Inlined via PHP include (not a <script src>) so it works regardless of whether the
 * site is served from the domain root or a subdirectory.
 */
?>
<script>
(function () {
    'use strict';

    var EN = (document.documentElement.lang || 'th').toLowerCase().indexOf('en') === 0;

    var REQUIRED = EN ? 'Please enter your email' : 'กรุณากรอกอีเมล';
    var INVALID  = EN ? 'Invalid email format'    : 'รูปแบบอีเมลไม่ถูกต้อง';

    /* Deliberately looser than PHP's FILTER_VALIDATE_EMAIL — it only needs to catch
       obvious typos while typing. The server still rejects anything it disagrees with. */
    var PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function attach(input) {
        var msg = document.createElement('p');
        msg.style.cssText = 'font-size:0.75rem;line-height:1rem;margin-top:0.25rem;color:#ef4444;display:none;';
        input.parentNode.insertBefore(msg, input.nextSibling);

        /* Don't nag about an empty field before the visitor has touched it. */
        var touched = false;

        function check() {
            var value = input.value.trim();
            var error = '';

            if (value === '') {
                if (touched && input.required) error = REQUIRED;
            } else if (!PATTERN.test(value)) {
                error = INVALID;
            }

            msg.textContent         = error;
            msg.style.display       = error ? 'block' : 'none';
            input.style.borderColor = error ? '#ef4444' : '';
        }

        function touch() { touched = true; check(); }

        input.addEventListener('input', touch);
        input.addEventListener('blur', touch);
        check();                       /* validate server-repopulated or restored values on load */
    }

    var fields = document.querySelectorAll('input[type="email"]');
    for (var i = 0; i < fields.length; i++) attach(fields[i]);
})();
</script>
