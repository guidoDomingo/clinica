<?php
// Script to replace all alertify with toastr and SweetAlert2

$file_path = 'view/js/rs_servicios.js';
$file_content = file_get_contents($file_path);

// This will do a direct string replacement
$file_content = str_replace('alertify.error(', 'toastr.error(', $file_content);
$file_content = str_replace('alertify.success(', 'toastr.success(', $file_content);

// Now save back to the file
file_put_contents($file_path, $file_content);

echo "All alertify.error and alertify.success instances replaced with toastr.";
?>
