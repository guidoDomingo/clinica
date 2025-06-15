$ErrorActionPreference = "Stop"

# Script para probar la API ICD-11 solo (sin fallbacks)
Write-Host "=== Test de API ICD-11 (Solo respuestas de API oficial) ===" -ForegroundColor Cyan

function Test-ApiEndpoint {
    param (
        [string]$Name,
        [string]$Url,
        [object]$Body
    )
    
    Write-Host "`n>> Probando $Name..." -ForegroundColor Yellow
    
    try {
        # Convertir el cuerpo a JSON
        $jsonBody = $Body | ConvertTo-Json
        
        Write-Host "  URL: $Url" -ForegroundColor DarkGray
        Write-Host "  Body: $jsonBody" -ForegroundColor DarkGray
        
        # Hacer la solicitud HTTP
        $response = Invoke-RestMethod -Uri $Url -Method Post -Body $jsonBody -ContentType "application/json" -ErrorAction Stop
        
        # Verificar si la respuesta tiene datos de fallback
        $hasFallback = $false
        if ($response.data -and ($response.data.fallback -eq $true -or $response.data.local_service -eq $true)) {
            $hasFallback = $true
        }
        
        if ($response.success -and -not $hasFallback) {
            Write-Host "  [OK] Exito: Respuesta correcta de la API oficial" -ForegroundColor Green
            return $true
        }
        elseif ($response.success -and $hasFallback) {
            Write-Host "  [ERROR] Error: La respuesta contiene datos de fallback/locales" -ForegroundColor Red
            Write-Host "      Esto indica que todavia hay respuestas que no vienen de la API oficial" -ForegroundColor Red
            return $false
        }
        elseif (-not $response.success -and $response.api_required) {
            Write-Host "  [OK] Exito: El servicio local esta correctamente desactivado" -ForegroundColor Green
            return $true
        }
        else {
            Write-Host "  [ERROR] Error: La respuesta no es exitosa: $($response.message)" -ForegroundColor Red
            return $false
        }
        
    }
    catch {
        Write-Host "  [ERROR] Excepcion: $_" -ForegroundColor Red
        return $false
    }
}

# Test 1: Busqueda por codigo en el endpoint principal
$test1 = Test-ApiEndpoint -Name "API Principal (Busqueda por codigo)" -Url "http://localhost/clinica/ajax/icd11.ajax.php" -Body @{
    action = "searchByCode"
    code = "MD12" # Código conocido para tos
}

# Test 2: Verificar que el servicio local esta desactivado
$test2 = Test-ApiEndpoint -Name "Servicio Local (verificar desactivacion)" -Url "http://localhost/clinica/ajax/icd11_local.php" -Body @{
    action = "searchByCode"
    code = "MD12"
}

# Test 3: Codigo inexistente en el endpoint principal
$test3 = Test-ApiEndpoint -Name "API Principal (Codigo inexistente)" -Url "http://localhost/clinica/ajax/icd11.ajax.php" -Body @{
    action = "searchByCode"
    code = "NONEXISTENTCODE123" # Un codigo inventado que no deberia existir
}

# Test 4: Busqueda por termino en el endpoint principal
$test4 = Test-ApiEndpoint -Name "API Principal (Busqueda por termino)" -Url "http://localhost/clinica/ajax/icd11.ajax.php" -Body @{
    action = "searchByTerm"
    term = "diabetes"
    language = "es"
}

# Informe final
Write-Host "`n=== Resumen de Tests ===" -ForegroundColor Cyan
$totalPassed = ($test1, $test2, $test3, $test4 | Where-Object { $_ -eq $true }).Count
$totalFailed = ($test1, $test2, $test3, $test4 | Where-Object { $_ -eq $false }).Count
$totalTests = $totalPassed + $totalFailed

Write-Host "Total de pruebas: $totalTests" -ForegroundColor White
Write-Host "Pruebas superadas: $totalPassed" -ForegroundColor Green
Write-Host "Pruebas fallidas: $totalFailed" -ForegroundColor Red

if ($totalFailed -eq 0) {
    Write-Host "`n[OK] TODOS LOS TESTS PASARON - La API solo usa datos oficiales sin fallbacks" -ForegroundColor Green
}
else {
    Write-Host "`n[ERROR] HAY TESTS FALLIDOS - Revisar los errores para asegurar que solo se usen datos oficiales" -ForegroundColor Red
}
