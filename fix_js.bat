@echo off
echo Fixing reservas_new.js file...
copy "c:\laragon\www\clinica\view\js\reservas_new.js" "c:\laragon\www\clinica\view\js\reservas_new.js.backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%" /Y
copy "c:\laragon\www\clinica\view\js\reservas_new.js.fixed" "c:\laragon\www\clinica\view\js\reservas_new.js" /Y
echo Fixed successfully!
pause
