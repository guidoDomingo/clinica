<?php
require_once "model/personas.model.php";

// Endpoint de debug para probar la búsqueda de personas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operacion']) && $_POST['operacion'] === 'debugPersonById') {
    $debug_info = [
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'post_data' => $_POST,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (isset($_POST['idPersona']) && is_numeric($_POST['idPersona'])) {
        $idPersona = intval($_POST['idPersona']);
        $debug_info['id_procesado'] = $idPersona;
        
        try {
            $response = ModelPersonas::mdlGetPersonaPorId($idPersona);
            $debug_info['consulta_exitosa'] = true;
            $debug_info['resultado'] = $response;
            
            if ($response) {
                echo json_encode([
                    'status' => 'success',
                    'persona' => $response,
                    'debug' => $debug_info
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se encontró la persona con el ID proporcionado',
                    'debug' => $debug_info
                ]);
            }
        } catch (Exception $e) {
            $debug_info['error_exception'] = $e->getMessage();
            echo json_encode([
                'status' => 'error',
                'message' => 'Error en la consulta: ' . $e->getMessage(),
                'debug' => $debug_info
            ]);
        }
    } else {
        $debug_info['error'] = 'ID de persona no proporcionado o inválido';
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de persona no proporcionado o inválido',
            'debug' => $debug_info
        ]);
    }
    exit;
}

// Si no es una petición AJAX, mostrar formulario de prueba
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug - Búsqueda de Personas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; }
        input { padding: 5px; width: 200px; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
        .result { margin-top: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <h1>Debug - Búsqueda de Personas por ID</h1>
    
    <form id="debugForm">
        <div class="form-group">
            <label for="idPersona">ID de Persona:</label>
            <input type="number" id="idPersona" name="idPersona" value="45" required>
        </div>
        <button type="submit">Buscar Persona</button>
    </form>
    
    <div id="resultado"></div>
    
    <script>
        document.getElementById('debugForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const idPersona = document.getElementById('idPersona').value;
            const resultDiv = document.getElementById('resultado');
            
            resultDiv.innerHTML = '<p>Buscando...</p>';
            
            const formData = new FormData();
            formData.append('operacion', 'debugPersonById');
            formData.append('idPersona', idPersona);
            
            fetch('debug_personas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let html = '<div class="result ' + (data.status === 'success' ? 'success' : 'error') + '">';
                html += '<h3>Resultado:</h3>';
                html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                html += '</div>';
                resultDiv.innerHTML = html;
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="result error"><h3>Error:</h3><p>' + error.message + '</p></div>';
            });
        });
    </script>
</body>
</html>
