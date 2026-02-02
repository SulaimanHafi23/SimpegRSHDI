{{-- Sweet Alert Notifications Component --}}
@if(session('success') || session('error') || session('warning') || session('info'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            customClass: {
                popup: 'colored-toast'
            }
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            timer: 7000,
            timerProgressBar: true,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#ef4444'
        });
    @endif

    @if(session('warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: '{{ session('warning') }}',
            timer: 6000,
            timerProgressBar: true,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#f59e0b'
        });
    @endif

    @if(session('info'))
        Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: '{{ session('info') }}',
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    @endif
});

// Helper functions for Sweet Alert
window.showSuccessAlert = function(title, message) {
    Swal.fire({
        icon: 'success',
        title: title || 'Berhasil!',
        text: message,
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
};

window.showErrorAlert = function(title, message) {
    Swal.fire({
        icon: 'error',
        title: title || 'Error!',
        text: message,
        showConfirmButton: true,
        confirmButtonText: 'OK',
        confirmButtonColor: '#ef4444'
    });
};

window.showConfirmAlert = function(title, message, confirmCallback) {
    Swal.fire({
        title: title || 'Konfirmasi',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed && confirmCallback) {
            confirmCallback();
        }
    });
};

window.showDeleteConfirm = function(confirmCallback) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed && confirmCallback) {
            confirmCallback();
        }
    });
};
</script>

<style>
/* Toast styles with high contrast */
.colored-toast.swal2-icon-success {
    background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;
    box-shadow: 0 4px 12px rgba(4, 120, 87, 0.4) !important;
}

.colored-toast.swal2-icon-error {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
}

.colored-toast.swal2-icon-warning {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4) !important;
}

.colored-toast.swal2-icon-info {
    background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4) !important;
}

.colored-toast .swal2-title {
    color: white !important;
    font-weight: 600 !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
}

.colored-toast .swal2-html-container,
.colored-toast .swal2-content {
    color: white !important;
    text-shadow: 0 1px 1px rgba(0,0,0,0.1) !important;
}

.colored-toast .swal2-timer-progress-bar {
    background: rgba(255, 255, 255, 0.5) !important;
}

.colored-toast.swal2-icon-success .swal2-icon.swal2-success,
.colored-toast.swal2-icon-error .swal2-icon.swal2-error,
.colored-toast.swal2-icon-warning .swal2-icon.swal2-warning,
.colored-toast.swal2-icon-info .swal2-icon.swal2-info {
    border-color: rgba(255, 255, 255, 0.8) !important;
    color: white !important;
}

.colored-toast .swal2-success-line-tip,
.colored-toast .swal2-success-line-long {
    background-color: white !important;
}

.colored-toast .swal2-x-mark-line-left,
.colored-toast .swal2-x-mark-line-right {
    background-color: white !important;
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
@endif
