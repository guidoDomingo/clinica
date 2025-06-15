<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de API ICD-11</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            max-height: 400px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prueba de API ICD-11</h1>
        <p>Esta página prueba la conexión directa con la API de ICD-11 de la OMS.</p>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Test de autenticación</h5>
            </div>
            <div class="card-body">
                <button id="btn-test-auth" class="btn btn-primary">
                    <i class="fa fa-key"></i> Probar autenticación
                </button>
                <div id="auth-results" class="mt-3">
                    <div class="alert alert-secondary">
                        Haga clic en el botón para probar la autenticación.
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Búsqueda por código</h5>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" id="code-search" class="form-control" placeholder="Código ICD-11 (ej: MD12)">
                    <button id="btn-search-code" class="btn btn-primary">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>
                <div id="code-results" class="mt-3">
                    <div class="alert alert-secondary">
                        Ingrese un código ICD-11 y haga clic en buscar.
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Búsqueda por término</h5>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" id="term-search" class="form-control" placeholder="Término médico (ej: tos)">
                    <button id="btn-search-term" class="btn btn-primary">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>
                <div id="term-results" class="mt-3">
                    <div class="alert alert-secondary">
                        Ingrese un término médico y haga clic en buscar.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar resultados en formato JSON bonito
        function showResult(containerId, data) {
            const container = document.getElementById(containerId);
            
            // Crear elemento pre con formato JSON
            const pre = document.createElement('pre');
            pre.textContent = JSON.stringify(data, null, 2);
            
            // Limpiar contenedor y agregar nuevo contenido
            container.innerHTML = '';
            container.appendChild(pre);
        }

        // Función para mostrar error
        function showError(containerId, error) {
            const container = document.getElementById(containerId);
            
            // Crear alerta de error
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger';
            alert.textContent = `Error: ${error.message || 'Error desconocido'}`;
            
            // Limpiar contenedor y agregar nuevo contenido
            container.innerHTML = '';
            container.appendChild(alert);
        }

        // Probar autenticación
        document.getElementById('btn-test-auth').addEventListener('click', async function() {
            const button = this;
            const resultsContainer = document.getElementById('auth-results');
            
            // Mostrar cargando
            button.disabled = true;
            resultsContainer.innerHTML = '<div class="alert alert-info">Probando autenticación...</div>';
            
            try {
                const response = await fetch('ajax/icd11.ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'searchByCode',
                        code: 'MD12' // Código de prueba
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    resultsContainer.innerHTML = '<div class="alert alert-success">¡Autenticación exitosa! La API está funcionando correctamente.</div>';
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error de autenticación:', error);
                showError('auth-results', error);
            } finally {
                button.disabled = false;
            }
        });

        // Búsqueda por código
        document.getElementById('btn-search-code').addEventListener('click', async function() {
            const button = this;
            const resultsContainer = document.getElementById('code-results');
            const code = document.getElementById('code-search').value.trim();
            
            if (!code) {
                resultsContainer.innerHTML = '<div class="alert alert-warning">Por favor ingrese un código ICD-11.</div>';
                return;
            }
            
            // Mostrar cargando
            button.disabled = true;
            resultsContainer.innerHTML = '<div class="alert alert-info">Buscando código...</div>';
            
            try {
                const response = await fetch('ajax/icd11.ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'searchByCode',
                        code: code
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    showResult('code-results', data.data);
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error de búsqueda por código:', error);
                showError('code-results', error);
            } finally {
                button.disabled = false;
            }
        });

        // Búsqueda por término
        document.getElementById('btn-search-term').addEventListener('click', async function() {
            const button = this;
            const resultsContainer = document.getElementById('term-results');
            const term = document.getElementById('term-search').value.trim();
            
            if (!term) {
                resultsContainer.innerHTML = '<div class="alert alert-warning">Por favor ingrese un término médico.</div>';
                return;
            }
            
            // Mostrar cargando
            button.disabled = true;
            resultsContainer.innerHTML = '<div class="alert alert-info">Buscando término...</div>';
            
            try {
                const response = await fetch('ajax/icd11.ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'searchByTerm',
                        term: term
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    showResult('term-results', data.data);
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error de búsqueda por término:', error);
                showError('term-results', error);
            } finally {
                button.disabled = false;
            }
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
