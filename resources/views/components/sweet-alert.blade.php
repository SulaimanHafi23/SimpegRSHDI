{{-- Sweet Alert helper functions + automatic flash popups --}}
<script>
const flashToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4500,
    timerProgressBar: true,
    customClass: {
        popup: 'colored-toast'
    }
});

const modalDefaults = {
    allowOutsideClick: false,
    reverseButtons: true,
    buttonsStyling: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya',
    cancelButtonText: 'Batal'
};

window.showSuccessAlert = function(title, message) {
    flashToast.fire({
        icon: 'success',
        title: title || 'Berhasil!',
        text: message || ''
    });
};

window.showErrorAlert = function(title, message) {
    flashToast.fire({
        icon: 'error',
        title: title || 'Gagal!',
        text: message || ''
    });
};

window.showWarningAlert = function(title, message) {
    flashToast.fire({
        icon: 'warning',
        title: title || 'Peringatan',
        text: message || ''
    });
};

window.showInfoAlert = function(title, message) {
    flashToast.fire({
        icon: 'info',
        title: title || 'Informasi',
        text: message || ''
    });
};

window.showConfirmAlert = function(title, message, confirmCallback) {
    Swal.fire({
        ...modalDefaults,
        title: title || 'Konfirmasi',
        text: message,
        icon: 'question',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed && confirmCallback) {
            confirmCallback();
        }
    });
};

window.showConfirmDialog = function(options = {}) {
    return Swal.fire({
        ...modalDefaults,
        ...options
    });
};

window.showDeleteConfirm = function(confirmCallback) {
    Swal.fire({
        ...modalDefaults,
        title: 'Hapus Data?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed && confirmCallback) {
            confirmCallback();
        }
    });
};

document.addEventListener('DOMContentLoaded', function () {
    if (!window.__logoutSwalHandlerBound) {
        const logoutForms = document.querySelectorAll('form[action$="/logout"]');

        logoutForms.forEach((form) => {
            if (form.dataset.logoutSwalBound === 'true') {
                return;
            }

            form.dataset.logoutSwalBound = 'true';

            form.addEventListener('submit', function (event) {
                if (form.dataset.logoutConfirmed === 'true') {
                    return;
                }

                event.preventDefault();

                if (window.Swal) {
                    Swal.fire({
                        ...modalDefaults,
                        title: 'Logout sekarang?',
                        text: 'Sesi Anda akan diakhiri.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Ya, logout',
                        cancelButtonText: 'Batal',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.logoutConfirmed = 'true';
                            form.submit();
                        }
                    });

                    return;
                }

                if (window.confirm('Anda yakin ingin logout?')) {
                    form.dataset.logoutConfirmed = 'true';
                    form.submit();
                }
            });
        });

        window.__logoutSwalHandlerBound = true;
    }

    @if(session('success'))
        window.showSuccessAlert('Berhasil', @json(session('success')));
    @endif

    @if(session('error'))
        window.showErrorAlert('Gagal', @json(session('error')));
    @endif

    @if(session('warning'))
        window.showWarningAlert('Peringatan', @json(session('warning')));
    @endif

    @if(session('info'))
        window.showInfoAlert('Informasi', @json(session('info')));
    @endif

    {{-- Field validation errors ditampilkan di bawah masing-masing field, bukan di modal --}}
});
</script>

<style>
/* Clean white toast styles */
.colored-toast {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

.colored-toast .swal2-title {
    color: #1f2937 !important;
    font-weight: 600 !important;
    font-size: 14px !important;
}

.colored-toast .swal2-html-container,
.colored-toast .swal2-content {
    color: #6b7280 !important;
    font-size: 13px !important;
}

.colored-toast .swal2-timer-progress-bar {
    background: #3b82f6 !important;
}

/* Success icon styling */
.colored-toast.swal2-icon-success .swal2-icon {
    border-color: #10b981 !important;
    color: #10b981 !important;
}

.colored-toast.swal2-icon-success .swal2-success-line-tip,
.colored-toast.swal2-icon-success .swal2-success-line-long {
    background-color: #10b981 !important;
}

/* Error icon styling */
.colored-toast.swal2-icon-error .swal2-icon {
    border-color: #ef4444 !important;
    color: #ef4444 !important;
}

.colored-toast.swal2-icon-error .swal2-x-mark-line-left,
.colored-toast.swal2-icon-error .swal2-x-mark-line-right {
    background-color: #ef4444 !important;
}

/* Warning icon styling */
.colored-toast.swal2-icon-warning .swal2-icon {
    border-color: #f59e0b !important;
    color: #f59e0b !important;
}

/* Info icon styling */
.colored-toast.swal2-icon-info .swal2-icon {
    border-color: #3b82f6 !important;
    color: #3b82f6 !important;
}

/* Modal/Popup styles with better contrast */
.swal2-popup {
    border-radius: 16px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}

.swal2-title {
    color: #1f2937 !important;
    font-weight: 700 !important;
}

.swal2-html-container {
    color: #4b5563 !important;
}

.swal2-confirm {
    font-weight: 600 !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

.swal2-cancel {
    font-weight: 600 !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
}

.swal2-icon.swal2-success {
    border-color: #059669 !important;
    color: #059669 !important;
}

.swal2-icon.swal2-success .swal2-success-ring {
    border-color: rgba(5, 150, 105, 0.3) !important;
}

.swal2-icon.swal2-success .swal2-success-line-tip,
.swal2-icon.swal2-success .swal2-success-line-long {
    background-color: #059669 !important;
}

.swal2-icon.swal2-error {
    border-color: #dc2626 !important;
    color: #dc2626 !important;
}

.swal2-icon.swal2-warning {
    border-color: #d97706 !important;
    color: #d97706 !important;
}

.swal2-icon.swal2-info {
    border-color: #0284c7 !important;
    color: #0284c7 !important;
}

.swal2-icon.swal2-question {
    border-color: #7c3aed !important;
    color: #7c3aed !important;
}
</style>
