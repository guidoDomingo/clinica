/**
 * Alertify.js compatibility layer
 * This script provides alertify-compatible functions using toastr and SweetAlert2
 */

// Configure Toastr defaults
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

// Create the alertify object if it doesn't exist
var alertify = {
    // Simple notifications using Toastr
    success: function(message) {
        toastr.success(message);
    },
    error: function(message) {
        toastr.error(message);
    },
    
    // Confirm dialog using SweetAlert2
    confirm: function(title, message, onOk, onCancel) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed && typeof onOk === 'function') {
                onOk();
            } else if (!result.isConfirmed && typeof onCancel === 'function') {
                onCancel();
            }
        });
    },
    
    // Prompt dialog using SweetAlert2
    prompt: function(title, message, defaultValue, onOk, onCancel) {
        Swal.fire({
            title: title,
            input: 'text',
            inputLabel: message,
            inputValue: defaultValue || '',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'El campo no puede estar vacío';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && typeof onOk === 'function') {
                onOk(null, result.value);
            } else if (!result.isConfirmed && typeof onCancel === 'function') {
                onCancel();
            }
        });
    }
};

console.log('Alertify compatibility layer loaded');
