@echo off
REM Configurar tarea programada para mantenimiento del sistema

echo ==========================================
echo  Configuración de Mantenimiento Automático
echo ==========================================
echo.
echo Este script creará una tarea programada en Windows para ejecutar
echo automáticamente el mantenimiento del sistema cada semana.
echo.
echo Presiona CTRL+C para cancelar o cualquier tecla para continuar...
pause > nul

REM Obtener la ruta completa al archivo PHP
set SCRIPT_PATH=%~dp0mantenimiento_sistema.php
set PHP_PATH=php

REM Crear la tarea programada
schtasks /create /tn "Mantenimiento Clínica - Limpieza" /tr "%PHP_PATH% %SCRIPT_PATH%" /sc WEEKLY /d SUN /st 01:00 /ru SYSTEM

echo.
echo ==========================================
echo  Tarea programada creada con éxito
echo ==========================================
echo.
echo La limpieza automática se ejecutará cada domingo a la 1:00 AM.
echo Para modificar esta configuración, use el Programador de tareas de Windows.
echo.
pause
