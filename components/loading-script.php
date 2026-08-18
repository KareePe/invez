<?php
/**
 * Submit feedback shared by the login/edit forms and the admin/member list pages.
 *
 * Opt in per form:
 *   data-loading="button"   spinner inside its own submit button, button disabled
 *   data-loading="overlay"  full-page overlay that also blocks a second click
 *
 * Forms that confirm with SweetAlert first are submitted programmatically, which
 * fires no submit event, so the confirm handlers in admin/_footer.php and
 * member/_footer.php call window.showLoadingOverlay() themselves.
 */
?>
<style>
#loading-overlay { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; background: rgba(255, 255, 255, .65); }
#loading-overlay.is-active { display: flex; }
.loading-spinner { width: 38px; height: 38px; border: 3px solid rgba(201, 169, 110, .25); border-top-color: #c9a96e; border-radius: 9999px; animation: loading-spin .7s linear infinite; }
.btn-spinner { display: inline-block; width: 14px; height: 14px; margin-right: .5rem; vertical-align: -2px; border: 2px solid rgba(255, 255, 255, .45); border-top-color: #fff; border-radius: 9999px; animation: loading-spin .7s linear infinite; }
button.is-loading { opacity: .7; cursor: not-allowed; }
@keyframes loading-spin { to { transform: rotate(360deg); } }
</style>

<div id="loading-overlay"><div class="loading-spinner"></div></div>

<script>
(function () {
    var overlay = document.getElementById('loading-overlay');

    function showOverlay() {
        overlay.classList.add('is-active');
    }

    function showButtonLoading(form) {
        var btn = form.querySelector('button[type="submit"]');
        if (!btn || btn.disabled) return;
        btn.classList.add('is-loading');
        btn.innerHTML = '<span class="btn-spinner"></span>' + btn.innerHTML;
        // Disabled after the current event, so the form still posts in every browser.
        setTimeout(function () { btn.disabled = true; }, 0);
    }

    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return; // a confirm dialog is handling this form
        var mode = e.target.dataset.loading;
        if (mode === 'overlay') showOverlay();
        else if (mode === 'button') showButtonLoading(e.target);
    });

    window.showLoadingOverlay = showOverlay;
})();
</script>
