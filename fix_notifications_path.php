<?php
$file = __DIR__ . '/view/js/rs_servicios.js';
$content = file_get_contents($file);

// Simple string replacements for notifications
$content = str_replace('alertify.error(', 'toastr.error(', $content);
$content = str_replace('alertify.success(', 'toastr.success(', $content);

file_put_contents($file, $content);
echo "Success! Replaced alertify notifications with toastr notifications.";
?>
