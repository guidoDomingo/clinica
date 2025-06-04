<?php
// Script to replace alertify with toastr in rs_servicios.js
$file = 'view/js/rs_servicios.js';
$content = file_get_contents($file);

// Replace all instances of alertify.success with toastr.success
$content = str_replace('alertify.success(', 'toastr.success(', $content);

// Replace all instances of alertify.error with toastr.error
$content = str_replace('alertify.error(', 'toastr.error(', $content);

// Replace all instances of alertify.confirm with Swal.fire
$content = str_replace('alertify.confirm(', 'Swal.fire({
    title: ', $content);

// Replace all instances of alertify.prompt with Swal.fire
$content = str_replace('alertify.prompt(', 'Swal.fire({
    title: ', $content);

// Save the updated file
file_put_contents($file, $content);

echo "Replaced all instances of alertify with toastr and Swal.";
?>
