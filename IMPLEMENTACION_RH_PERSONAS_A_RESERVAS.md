# Implementación: Navegación de RH Personas a Reservas

## Resumen
Se implementó la funcionalidad para navegar desde el módulo "RH Personas" al módulo "Reservas" con información del paciente preseleccionado, siguiendo el mismo patrón usado para navegar de Reservas a Consultas.

## Archivos Modificados

### 1. `view/js/rhPersonas.js`

#### Cambios realizados:
- **Línea 165**: Agregado manejador de eventos para el botón "Crear Reserva"
```javascript
$("#tblPersonas").on("click", ".btn-crear-reserva", crearReservaParaPaciente);
```

- **Líneas 1026-1048**: Implementada función `crearReservaParaPaciente()`
```javascript
function crearReservaParaPaciente() {
  const personId = $(this).attr("btnId");
  const nombre = $(this).data("nombre");
  const apellido = $(this).data("apellido");
  const documento = $(this).data("documento");
  
  // Construir URL con parámetros del paciente
  const url = `index.php?ruta=servicios&paciente_id=${personId}&nombre=${encodeURIComponent(nombre)}&apellido=${encodeURIComponent(apellido)}&documento=${encodeURIComponent(documento)}`;
  
  // Navegar a la página de servicios/reservas
  window.location.href = url;
}
```

### 2. `view/js/reservas_new.js`

#### Cambios realizados:
- **Línea 123**: Agregada llamada a `procesarParametrosURLPaciente()` en la inicialización
```javascript
// Procesar parámetros URL para pre-llenar información del paciente
procesarParametrosURLPaciente();
```

- **Líneas 2205-2355**: Implementada función `procesarParametrosURLPaciente()` mejorada
```javascript
function procesarParametrosURLPaciente() {
  const urlParams = new URLSearchParams(window.location.search);
  // ... obtener parámetros ...
  
  if (pacienteId && nombre && apellido) {
    // Pre-llenar campos del paciente
    // Ejecutar búsqueda por ID via AJAX (método principal)
    // Fallback: búsqueda por nombre si falla la búsqueda por ID
    // Cargar tabla de resultados
    // Seleccionar paciente automáticamente
    // Actualizar UI completa
    // Hacer scroll al siguiente paso
    // Limpiar URL
  }
}
```

### 3. `controller/servicios.controller.php`

#### Cambios realizados:
- **Líneas 250-275**: Agregado método `ctrBuscarPacientePorId()`
```php
static public function ctrBuscarPacientePorId($pacienteId) {
    // Búsqueda específica por ID del paciente
    // Consulta SQL optimizada
    // Manejo de errores
    // Retorna array consistente con búsqueda por nombre
}
```

### 4. `ajax/servicios.ajax.php`

#### Cambios realizados:
- **Líneas 230-250**: Agregada acción `buscarPacientePorId`
```php
case 'buscarPacientePorId':
    if (isset($_POST['paciente_id'])) {
        $pacienteId = $_POST['paciente_id'];
        $paciente = ControladorServicios::ctrBuscarPacientePorId($pacienteId);
        // Logging y respuesta JSON
    }
    break;
```

## Funcionalidad Implementada

### Flujo de Trabajo
1. **En RH Personas**: Usuario hace clic en el botón "Crear Reserva" (ícono de calendario verde) en la tabla de personas
2. **Navegación**: Se navega a `index.php?ruta=servicios` con parámetros del paciente
3. **En Reservas**: Se pre-llena automáticamente la información del paciente en el formulario de nueva reserva

### Parámetros URL Transferidos
- `paciente_id`: ID del paciente
- `nombre`: Nombre del paciente
- `apellido`: Apellido del paciente  
- `documento`: Número de documento del paciente

### Comportamiento en Reservas
Cuando se detectan parámetros URL:
1. **Pre-llenado**: Campo de búsqueda de paciente se llena con el nombre completo
2. **Búsqueda por ID**: Se ejecuta automáticamente la búsqueda AJAX por ID del paciente (método principal)
3. **Búsqueda fallback**: Si falla la búsqueda por ID, se ejecuta búsqueda por nombre como respaldo
4. **Carga de tabla**: Se llena la tabla de resultados con la información del paciente específico
5. **Selección automática**: Se selecciona automáticamente el paciente correcto de los resultados
6. **Estado visual**: Campo se marca como solo lectura con clase `selected-patient`
7. **Botones**: Se muestran botones de "Cambiar Paciente" en lugar de "Buscar Paciente"
8. **Resumen**: Se actualiza la información del resumen con datos del paciente
9. **Campos ocultos**: Se llenan todos los campos necesarios (`selectPacienteNew`, `pacienteSeleccionadoId`, etc.)
10. **UX**: Scroll automático al siguiente paso (fecha/médico)
11. **Notificación**: Toast informativo confirmando la carga del paciente
12. **Limpieza**: URL se limpia para evitar reprocesamiento

## Estado del Botón "Crear Reserva"

El botón ya estaba implementado visualmente en el archivo `rhPersonas.js` línea 144:
```javascript
const btnReserva = `<button class="btn btn-success btn-sm btn-crear-reserva" btnId="${data.person_id}" data-nombre="${data.first_name}" data-apellido="${data.last_name}" data-documento="${data.document_number}" title="Crear Reserva"><i class="fas fa-calendar-plus"></i></button>`;
```

## Características Técnicas

### Manejo de Errores
- Validación de parámetros URL antes del procesamiento
- **Búsqueda dual**: Primero por ID (más preciso), luego por nombre como fallback
- Logs de depuración en consola para seguimiento de ambos métodos de búsqueda
- Manejo de errores AJAX con mensajes informativos específicos
- Fallback graceful si fallan ambos métodos de búsqueda
- Notificaciones diferenciadas para cada tipo de resultado

### Compatibilidad
- Usa APIs web estándar (`URLSearchParams`)
- Compatible con el sistema de navegación existente
- Mantiene coherencia con patrones de código existentes

### UX/UI
- Feedback visual inmediato
- Notificaciones toast informativas
- Scroll automático para guiar al usuario
- Estados visuales claros (campo pre-llenado, botones cambiados)

## Pruebas Sugeridas

1. **Navegación básica**: Hacer clic en "Crear Reserva" desde RH Personas
2. **Pre-llenado**: Verificar que los datos del paciente se cargan correctamente
3. **Búsqueda automática**: Verificar que la tabla se llena automáticamente con los resultados
4. **Selección automática**: Verificar que el paciente correcto se selecciona automáticamente
5. **Estados UI**: Verificar cambios visuales (campo readonly, botones cambiados, tabla llena)
6. **Flujo completo**: Completar una reserva iniciada desde RH Personas
7. **Limpieza URL**: Verificar que la URL se limpia después del procesamiento
8. **Manejo de errores**: Probar con parámetros inválidos o conexión fallida
9. **Compatibilidad**: Verificar que el flujo normal de reservas sigue funcionando

## Notas de Implementación

- Se siguió el mismo patrón usado para Reservas → Consultas
- La funcionalidad es completamente opcional y no afecta el flujo normal
- Se implementó limpieza automática de URL para evitar confusión
- Compatible con el sistema de navegación por pestañas existente
- **Corrección de ámbito**: Se implementó la lógica de carga de tabla inline para evitar problemas de ámbito de funciones
- **Búsqueda dual**: Método principal por ID + fallback por nombre para máxima confiabilidad

## Correcciones Aplicadas

### Problema de Ámbito de Funciones
**Problema**: `ReferenceError: cargarTablaPacientes is not defined`
**Causa**: La función `cargarTablaPacientes` estaba definida dentro del ámbito de `inicializarReservasNew` pero se llamaba desde `procesarParametrosURLPaciente` que está en ámbito global.
**Solución**: Se implementó la lógica de carga de tabla directamente inline en `procesarParametrosURLPaciente` para evitar dependencias de ámbito.

### Búsqueda por ID vs Nombre
**Problema**: La búsqueda por nombre devolvía `{"status":"success","data":[]}`
**Causa**: Los nombres en la base de datos podrían no coincidir exactamente con el formato de búsqueda.
**Solución**: Se implementó búsqueda principal por ID del paciente (más precisa) con fallback por nombre.
