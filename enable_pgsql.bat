@echo off
echo Activando extensiones de PostgreSQL para PHP...
echo.

REM Encontrar php.ini
for /f "delims=" %%a in ('php -r "echo php_ini_loaded_file();"') do set "phpini=%%a"

if "%phpini%"=="" (
    echo No se pudo encontrar el archivo php.ini
    goto :error
)

echo Archivo php.ini encontrado: %phpini%
echo.

REM Verificar si podemos escribir en el archivo
echo Verificando permisos de escritura...
copy "%phpini%" "%phpini%.bak" >nul 2>&1
if %errorlevel% neq 0 (
    echo No se tiene permisos para modificar php.ini
    echo Por favor ejecute este script como administrador
    goto :error
)

echo Creando copia de seguridad en %phpini%.bak
echo.

REM Habilitar las extensiones
echo Habilitando las extensiones pdo_pgsql y pgsql...
powershell -Command "(Get-Content '%phpini%') -replace ';extension=pdo_pgsql', 'extension=pdo_pgsql' -replace ';extension=pgsql', 'extension=pgsql' | Set-Content '%phpini%'"

if %errorlevel% neq 0 (
    echo Error al modificar el archivo php.ini
    goto :error
)

echo.
echo Las extensiones han sido habilitadas correctamente.
echo Por favor, reinicie Laragon para que los cambios surtan efecto.
echo.
echo Después de reiniciar, visite http://localhost/clinica/check_and_enable_pgsql.php para verificar que todo esté funcionando.
goto :end

:error
echo.
echo Ha ocurrido un error. Por favor, habilite las extensiones manualmente:
echo 1. Abra el archivo php.ini ubicado en: %phpini%
echo 2. Busque la línea ";extension=pdo_pgsql" y quite el punto y coma del inicio
echo 3. Busque la línea ";extension=pgsql" y quite el punto y coma del inicio
echo 4. Guarde los cambios y reinicie Laragon

:end
echo.
pause
