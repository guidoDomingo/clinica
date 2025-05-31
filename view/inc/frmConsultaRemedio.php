<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Buscador de Remedios</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <div class="input-group">
                <input type="text" class="form-control" id="txtBuscarRemedio" placeholder="Ingrese nombre del medicamento...">
                <div class="input-group-append">
                    <button class="btn btn-primary" id="btnBuscarRemedio">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-3" id="resultadosBusquedaRemedios">
            <!-- Los resultados de la búsqueda se mostrarán aquí -->
            <div class="text-center text-muted">
                <i class="fas fa-info-circle"></i> Ingrese un nombre de medicamento para buscar
            </div>
        </div>
    </div>
    <div class="card-footer">
        <small class="text-muted">Los resultados se obtienen de una base de datos externa. Consulte el prospecto oficial para información detallada.</small>
    </div>
</div>
