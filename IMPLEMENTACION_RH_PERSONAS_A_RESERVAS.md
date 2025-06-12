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

- **Líneas 2205-2263**: Implementada función `procesarParametrosURLPaciente()`
```javascript
function procesarParametrosURLPaciente() {
  const urlParams = new URLSearchParams(window.location.search);
  const pacienteId = urlParams.get('paciente_id');
  const nombre = urlParams.get('nombre');
  const apellido = urlParams.get('apellido');
  const documento = urlParams.get('documento');
  
  if (pacienteId && nombre && apellido) {
    // Pre-llenar campos del paciente
    // Actualizar UI
    // Hacer scroll al siguiente paso
    // Limpiar URL
  }
}
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
2. **Estado visual**: Campo se marca como solo lectura con clase `selected-patient`
3. **Botones**: Se muestran botones de "Cambiar Paciente" en lugar de "Buscar Paciente"
4. **Resumen**: Se actualiza la información del resumen con datos del paciente
5. **UX**: Scroll automático al siguiente paso (fecha/médico)
6. **Notificación**: Toast informativo confirmando la carga del paciente
7. **Limpieza**: URL se limpia para evitar reprocesamiento

## Estado del Botón "Crear Reserva"

El botón ya estaba implementado visualmente en el archivo `rhPersonas.js` línea 144:
```javascript
const btnReserva = `<button class="btn btn-success btn-sm btn-crear-reserva" btnId="${data.person_id}" data-nombre="${data.first_name}" data-apellido="${data.last_name}" data-documento="${data.document_number}" title="Crear Reserva"><i class="fas fa-calendar-plus"></i></button>`;
```

## Características Técnicas

### Manejo de Errores
- Validación de parámetros URL antes del procesamiento
- Logs de depuración en consola para seguimiento
- Fallback graceful si faltan parámetros

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
3. **Flujo completo**: Completar una reserva iniciada desde RH Personas
4. **Limpieza URL**: Verificar que la URL se limpia después del procesamiento
5. **Compatibilidad**: Verificar que el flujo normal de reservas sigue funcionando

## Notas de Implementación

- Se siguió el mismo patrón usado para Reservas → Consultas
- La funcionalidad es completamente opcional y no afecta el flujo normal
- Se implementó limpieza automática de URL para evitar confusión
- Compatible con el sistema de navegación por pestañas existente
