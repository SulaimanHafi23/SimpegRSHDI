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
.colored-toast.swal2-icon-success {
    background-color: #a7f3d0 !important;
}

.colored-toast.swal2-icon-error {
    background-color: #fecaca !important;
}

.colored-toast.swal2-icon-warning {
    background-color: #fde68a !important;
}

.colored-toast.swal2-icon-info {
    background-color: #bfdbfe !important;
}

.colored-toast .swal2-title {
    color: white;
}

.colored-toast .swal2-content {
    color: white;
}
</style>
@endif