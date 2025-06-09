<?php
echo "Ubicación del archivo php.ini: " . php_ini_loaded_file() . "\n";
echo "Extensiones cargadas: \n";
print_r(get_loaded_extensions());
echo "\n\nPara habilitar PostgreSQL, necesitas: \n";
echo "1. Editar el archivo php.ini\n";
echo "2. Buscar y descomentar (quitar el ; del inicio) las líneas:\n";
echo "   extension=pdo_pgsql\n";
echo "   extension=pgsql\n";
echo "3. Reiniciar el servidor web\n";
