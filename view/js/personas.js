function limpiarBuscarPersona() {
    document.getElementById('validarDocumento').value = '';
    document.getElementById('validarNombre').value = '';
    document.getElementById('validarApellidos').value = '';
    document.getElementById('validarFicha').value = '';
    document.getElementById('validarSexo').value = '0';
}

function calcularEdad(fechaNacimiento) {
    var fechaNac = new Date(fechaNacimiento);
    var hoy = new Date();
    var edad = hoy.getFullYear() - fechaNac.getFullYear();
    var mes = hoy.getMonth() - fechaNac.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
    }
    return edad;
}