<?php
echo "<h1>PHP PostgreSQL Extension Check</h1>";

// Check if PostgreSQL extension is loaded
if (extension_loaded('pgsql') && extension_loaded('pdo_pgsql')) {
    echo "<p style='color: green;'>PostgreSQL extensions are loaded correctly.</p>";
} else {
    echo "<p style='color: red;'>PostgreSQL extensions are NOT loaded!</p>";
    echo "<p>You need to enable the following extensions in your php.ini file:</p>";
    echo "<ul>";
    echo "<li>extension=pgsql</li>";
    echo "<li>extension=pdo_pgsql</li>";
    echo "</ul>";
}

// Show all loaded extensions for reference
echo "<h2>Loaded PHP Extensions:</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";
?>