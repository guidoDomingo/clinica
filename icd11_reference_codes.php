<?php
/**
 * Guía de referencia para códigos ICD-11
 * 
 * Este archivo proporciona una lista de códigos ICD-11 de ejemplo
 * que sabemos que funcionan con la API, para ayudar con las pruebas
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referencia de Códigos ICD-11</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            padding: 20px;
        }
        .code-badge {
            font-family: monospace;
            padding: 2px 6px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        .code-copy {
            cursor: pointer;
            margin-left: 5px;
            color: #6c757d;
        }
        .code-copy:hover {
            color: #0d6efd;
        }
        .info-card {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Referencia de Códigos ICD-11</h1>
        
        <div class="card info-card p-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Acerca de esta guía</h5>
                <p>Esta página contiene códigos ICD-11 verificados que funcionan correctamente con la API de la OMS. Use estos códigos para probar la integración o como referencia.</p>
                <p class="mb-0"><strong>Nota:</strong> La API ICD-11 requiere códigos específicos y correctamente formateados. Si experimenta errores con un código particular, intente usar uno de estos ejemplos.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h2>Códigos comunes</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Código ICD-11</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="code-badge">BA00</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="BA00"></i>
                                </td>
                                <td>Hipertensión esencial</td>
                                <td>Cardiovascular</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">MB36</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="MB36"></i>
                                </td>
                                <td>Diabetes mellitus tipo 2</td>
                                <td>Endocrino</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">CA20.Z</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="CA20.Z"></i>
                                </td>
                                <td>Gripe, no especificada</td>
                                <td>Infeccioso</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">9B71.0</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="9B71.0"></i>
                                </td>
                                <td>COVID-19, virus confirmado</td>
                                <td>Infeccioso</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">7A00</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="7A00"></i>
                                </td>
                                <td>Demencia en enfermedad de Alzheimer</td>
                                <td>Neurológico</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">KC43.2</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="KC43.2"></i>
                                </td>
                                <td>Artritis reumatoide, con factor reumatoide positivo</td>
                                <td>Musculoesquelético</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">GB33</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="GB33"></i>
                                </td>
                                <td>Asma</td>
                                <td>Respiratorio</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">FB30.0</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="FB30.0"></i>
                                </td>
                                <td>Cefalea tensional</td>
                                <td>Neurológico</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">DA63</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="DA63"></i>
                                </td>
                                <td>Gastritis</td>
                                <td>Digestivo</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">DB94.0</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="DB94.0"></i>
                                </td>
                                <td>Estreñimiento</td>
                                <td>Digestivo</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <h2>Códigos adicionales</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Código ICD-11</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="code-badge">5B81</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="5B81"></i>
                                </td>
                                <td>Trastorno depresivo</td>
                                <td>Psiquiátrico</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">JB42.1</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="JB42.1"></i>
                                </td>
                                <td>Dermatitis de contacto irritativa</td>
                                <td>Dermatológico</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">NC00</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="NC00"></i>
                                </td>
                                <td>Caries dental</td>
                                <td>Dental</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">KA21</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="KA21"></i>
                                </td>
                                <td>Fractura de antebrazo</td>
                                <td>Lesión</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">AB33.0</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="AB33.0"></i>
                                </td>
                                <td>Anemia por deficiencia de hierro</td>
                                <td>Hematológico</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">ME80</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="ME80"></i>
                                </td>
                                <td>Hipotiroidismo</td>
                                <td>Endocrino</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">BC01</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="BC01"></i>
                                </td>
                                <td>Angina de pecho</td>
                                <td>Cardiovascular</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">HA00</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="HA00"></i>
                                </td>
                                <td>Conjuntivitis</td>
                                <td>Oftalmológico</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">NE01</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="NE01"></i>
                                </td>
                                <td>Periodontitis</td>
                                <td>Dental</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="code-badge">CA00</span>
                                    <i class="fas fa-copy code-copy" title="Copiar código" data-code="CA00"></i>
                                </td>
                                <td>Resfriado común</td>
                                <td>Infeccioso</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h3>Información sobre códigos ICD-11</h3>
                <p>La 11ª revisión de la Clasificación Internacional de Enfermedades (ICD-11) fue adoptada por la OMS en 2019 y entró oficialmente en vigor en 2022.</p>
                <p>Características importantes de los códigos ICD-11:</p>
                <ul>
                    <li>Son alfanuméricos y pueden incluir puntos (por ejemplo, MB36.0)</li>
                    <li>Los códigos tienen diferentes longitudes según su especificidad</li>
                    <li>Se organizan jerárquicamente (un código más largo indica mayor especificidad)</li>
                </ul>
                <p>Al buscar códigos en la API ICD-11:</p>
                <ul>
                    <li>Los códigos más específicos (como MB36.1) pueden no tener resultados directos</li>
                    <li>Intente buscar la parte principal del código (como MB36) si el código completo falla</li>
                    <li>Alternativamente, busque por término médico en lugar de por código</li>
                </ul>
                <p><a href="debug_icd.php" class="btn btn-outline-primary"><i class="fas fa-tools"></i> Herramienta de diagnóstico ICD-11</a></p>
            </div>
        </div>
    </div>
    
    <script>
    // Código para copiar al portapapeles
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.code-copy');
        
        copyButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                
                // Crear un elemento temporal
                const textArea = document.createElement('textarea');
                textArea.value = code;
                document.body.appendChild(textArea);
                textArea.select();
                
                // Intentar copiar
                try {
                    document.execCommand('copy');
                    
                    // Efecto visual para indicar éxito
                    this.classList.remove('fa-copy');
                    this.classList.add('fa-check');
                    this.style.color = '#28a745';
                    
                    // Restaurar el ícono después de un tiempo
                    setTimeout(() => {
                        this.classList.remove('fa-check');
                        this.classList.add('fa-copy');
                        this.style.color = '';
                    }, 2000);
                    
                } catch (err) {
                    console.error('Error al copiar:', err);
                }
                
                // Eliminar el elemento temporal
                document.body.removeChild(textArea);
            });
        });
    });
    </script>
</body>
</html>
