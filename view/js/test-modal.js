/**
 * Script de prueba para verificar el funcionamiento del modal
 */

console.log('Script de prueba cargado');

// Función para verificar si el elemento existe
function elementoExiste(id) {
    const elemento = document.getElementById(id);
    console.log(`Elemento ${id}: ${elemento ? 'Existe' : 'No existe'}`);
    return elemento !== null;
}

// Verificar elementos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente cargado');
    
    // Verificar si el botón existe
    if (elementoExiste('btnNuevaPersona')) {
        console.log('Agregando evento click al botón');
        
        // Agregar evento directamente con jQuery para probar
        $('#btnNuevaPersona').on('click', function() {
            console.log('Botón clickeado - Abriendo modal');
            $('#modalAgregarPersonas').modal('show');
        });
    }
    
    // Verificar si el modal existe
    elementoExiste('modalAgregarPersonas');
});