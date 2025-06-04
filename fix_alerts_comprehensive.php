<?php
// Script to replace alertify with toastr and SweetAlert2 in rs_servicios.js
$file = 'view/js/rs_servicios.js';
$content = file_get_contents($file);

// Replace simple notifications
$content = str_replace('alertify.success(', 'toastr.success(', $content);
$content = str_replace('alertify.error(', 'toastr.error(', $content);

// Handle confirm dialog - search for the pattern
$confirmPattern = '/alertify\.confirm\(\s*"([^"]+)",\s*"([^"]+)",\s*function\(\)\s*{/';
$confirmReplacement = 'Swal.fire({
        title: "$1",
        text: "$2",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {';

$content = preg_replace($confirmPattern, $confirmReplacement, $content);

// Remove the closing function part of alertify.confirm
$cancelPattern = '/},\s*function\(\)\s*{\s*\/\/\s*Cancelar eliminación\s*}\s*\)/';
$cancelReplacement = '}})';
$content = preg_replace($cancelPattern, $cancelReplacement, $content);

// Handle prompt dialog
$promptPattern = '/alertify\.prompt\(\s*"([^"]+)",\s*"([^"]+)",\s*([^,]+),\s*function\(evt,\s*value\)\s*{/';
$promptReplacement = 'Swal.fire({
        title: "$1",
        input: "text",
        inputLabel: "$2",
        inputValue: $3,
        showCancelButton: true,
        confirmButtonText: "Guardar",
        cancelButtonText: "Cancelar",
        inputValidator: (value) => {
            if (!value) {
                return "El campo no puede estar vacío";
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const value = result.value;';

$content = preg_replace($promptPattern, $promptReplacement, $content);

// Replace function close for prompt
$promptClosePattern = '/},\s*function\(\)\s*{\s*\/\/\s*Cancelar\s*}\s*\)/';
$promptCloseReplacement = '}})';
$content = preg_replace($promptClosePattern, $promptCloseReplacement, $content);

// Save the updated file
file_put_contents($file, $content);

echo "Replaced all instances of alertify with toastr and SweetAlert2 successfully.";
?>
