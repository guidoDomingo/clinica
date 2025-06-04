<?php
// Check template.php for syntax errors
$result = shell_exec("php -l c:\\laragon\\www\\clinica\\view\\template.php");
echo $result;
?>
