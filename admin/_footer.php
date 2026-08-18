    </main>
</div>
</div>

<script>
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const self = this;
        Swal.fire({
            title: self.dataset.confirm,
            text: 'ไม่สามารถกู้คืนได้หลังจากลบ',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                popup:         'swal-popup',
                title:         'swal-title',
                htmlContainer: 'swal-text',
                actions:       'swal-actions',
                confirmButton: 'swal-confirm',
                cancelButton:  'swal-cancel',
            },
        }).then(result => {
            if (!result.isConfirmed) return;
            if (self.dataset.loading === 'overlay') window.showLoadingOverlay();
            self.submit();
        });
    });
});
</script>

<?php include('../components/loading-script.php'); ?>
<?php include('../components/password-script.php'); ?>

</body>
</html>
