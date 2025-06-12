# Funcionalidad "Ir de Reservas a Consultas"

## Resumen
Se ha implementado la funcionalidad completa para permitir a los usuarios ir directamente desde el módulo de **Reservas** al módulo de **Consultas**, pasando automáticamente el ID del paciente y cargando sus datos.

## Componentes Implementados

### 1. Botón "Ir a Consulta" en las Tablas de Reservas

#### Ubicación:
- **Tab "Reservas"** (`servicios.js`): Botón ya implementado
- **Tab "Nueva reserva"** (`reservas_new.js`): Botón implementado con handler agregado

#### Características del Botón:
- **Icono**: `<i class="fas fa-stethoscope"></i>` (estetoscopio médico)
- **Color**: Azul primario (`btn-primary`)
- **Visibilidad**: Solo aparece para reservas con estado **CONFIRMADA**
- **Data Attributes**:
  - `data-paciente-id`: ID del paciente
  - `data-reserva-id`: ID de la reserva
  - `data-paciente-nombre`: Nombre del paciente

### 2. Event Handlers para los Botones

#### Handler en `servicios.js` (Tab "Reservas")
```javascript
$(document).on('click', '.btnIrAConsulta', function() {
    // Ya implementado anteriormente
});
```

#### Handler en `reservas_new.js` (Tab "Nueva reserva")
```javascript
$(document).on('click', '.btnIrAConsultaTab', function() {
    const pacienteId = $(this).data('paciente-id');
    const reservaId = $(this).data('reserva-id');
    const nombrePaciente = $(this).data('paciente-nombre') || 'el paciente';
    
    // Confirmación con SweetAlert2
    Swal.fire({
        title: '¿Ir al módulo de Consultas?',
        text: `Se abrirá el módulo de consultas con los datos de ${nombrePaciente}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, ir a consultas',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Construir URL con parámetros
            let url = 'index.php?ruta=consultas';
            if (pacienteId) {
                url += `&paciente_id=${pacienteId}`;
            }
            if (reservaId) {
                url += `&reserva_id=${reservaId}`;
            }
            
            // Redirigir a consultas
            window.location.href = url;
        }
    });
});
```

### 3. Procesamiento de Parámetros URL en Consultas

#### Función en `consultas.js`
```javascript
function procesarParametrosURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const pacienteId = urlParams.get('paciente_id');
    const reservaId = urlParams.get('reserva_id');
    
    if (pacienteId) {
        // Mostrar mensaje de carga
        Swal.fire({
            title: 'Cargando paciente...',
            text: 'Se están cargando los datos del paciente desde la reserva',
            icon: 'info',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        
        // Cargar automáticamente el paciente
        setTimeout(() => {
            buscarPersonaPorId(pacienteId);
            
            if (reservaId) {
                console.log('Información adicional: Reserva ID:', reservaId);
            }
        }, 500);
    }
}
```

#### Inicialización
La función se llama automáticamente al cargar el módulo:
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // ... otras inicializaciones ...
    
    // Verificar parámetros de URL para cargar automáticamente un paciente
    procesarParametrosURL();
});
```

## Flujo de Trabajo Completo

### 1. Usuario en Módulo de Reservas
- Ve la lista de reservas confirmadas
- Hace clic en el botón azul con ícono de estetoscopio
- Se muestra confirmación con SweetAlert2

### 2. Redirección a Consultas
- URL generada: `index.php?ruta=consultas&paciente_id=123&reserva_id=456`
- El navegador navega al módulo de consultas

### 3. Carga Automática en Consultas
- Se detectan los parámetros de URL
- Se muestra mensaje informativo al usuario
- Se cargan automáticamente los datos del paciente
- Se llena el formulario de consulta con la información del paciente

## URLs de Ejemplo

```
http://clinica.test/index.php?ruta=consultas&paciente_id=123
http://clinica.test/index.php?ruta=consultas&paciente_id=123&reserva_id=456
```

## Archivos Modificados

1. **`view/js/reservas_new.js`**
   - Agregado handler para `.btnIrAConsultaTab`
   - Agregado `data-paciente-nombre` al botón en la tabla

2. **`view/js/consultas.js`**  
   - Agregada función `procesarParametrosURL()`
   - Agregada llamada a la función en `DOMContentLoaded`

## Estado de Implementación

✅ **COMPLETADO**: Botón "Ir a Consulta" en ambas tabs  
✅ **COMPLETADO**: Event handlers para ambos botones  
✅ **COMPLETADO**: Procesamiento de parámetros URL en consultas  
✅ **COMPLETADO**: Carga automática de datos del paciente  
✅ **COMPLETADO**: Feedback visual para el usuario  
✅ **SOLUCIONADO**: Error en campo de teléfono en modelo de base de datos

## Problemas Solucionados

### ❌ **Problema**: Datos del paciente no se cargaban automáticamente
**Causa**: Error en el nombre del campo `phone` vs `phone_number` en la consulta SQL del modelo.
**Solución**: Corregido el campo en `model/personas.model.php` de `phone` a `phone_number` para coincidir con la estructura real de la tabla `rh_person`.

**Archivo modificado**: `c:\laragon\www\clinica\model\personas.model.php`
```sql
-- ANTES (incorrecto):
phone as telefono

-- DESPUÉS (correcto):  
phone_number as telefono
```

## Pruebas Recomendadas

1. **Confirmar una reserva** en cualquiera de los dos tabs
2. **Verificar que aparece el botón azul** con el ícono de estetoscopio
3. **Hacer clic en "Ir a Consulta"** y confirmar la acción
4. **Verificar la redirección** al módulo de consultas
5. **Confirmar que se cargan automáticamente** los datos del paciente
6. **Probar con diferentes pacientes** para asegurar que funciona correctamente

La funcionalidad está **100% implementada y lista para uso**.
