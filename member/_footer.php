    </main>
</div>
</div>

<script>
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const self = this;
        Swal.fire({
            title: self.dataset.confirm,
            text: 'ไม่สามารถกู้คืนได้',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: { popup:'swal-popup', confirmButton:'swal-confirm', cancelButton:'swal-cancel' },
        }).then(r => {
            if (!r.isConfirmed) return;
            if (self.dataset.loading === 'overlay') window.showLoadingOverlay();
            self.submit();
        });
    });
});
</script>
<?php /* no password fields remain under member/ — password changes go through /forgot-password */ ?>
<?php include('../components/loading-script.php'); ?>
<?php include('../components/email-script.php'); ?>
</body>
</html>
