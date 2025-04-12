<!-- Modal Gestionar Especialidades -->
<div class="modal fade" id="modalEspecialidades" tabindex="-1" role="dialog" aria-labelledby="modalEspecialidadesLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEspecialidadesLabel">Información Profesional</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="especialidadesForm" action="post">
                    <input type="hidden" id="especialidadesPersonId" name="especialidadesPersonId">

                    <div class="form-group">
                        <label for="modalPerProfesion">Profesión</label>
                        <select id="modalPerProfesion" name="modalPerProfesion" class="form-control">
                            <option value="">Seleccionar...</option>
                            <option value="Médico">Médico</option>
                            <option value="Oftalmólogo">Oftalmólogo</option>
                            <option value="Pediatra">Pediatra</option>
                            <option value="Cardiólogo">Cardiólogo</option>
                            <option value="Dermatólogo">Dermatólogo</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modalPerEspecialidades">Especialidades</label>
                        <select id="modalPerEspecialidades" name="modalPerEspecialidades[]" class="form-control select2"
                            multiple="multiple" data-placeholder="Seleccione especialidades" style="width: 100%;">
                            <!-- Las opciones se cargarán dinámicamente desde JavaScript -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modalPerDireccionCorp">Dirección corporativa</label>
                        <input type="text" class="form-control" id="modalPerDireccionCorp" name="modalPerDireccionCorp"
                            placeholder="Ubicación">
                    </div>

                    <div class="form-group">
                        <label for="modalPerEmailProf">Email profesional</label>
                        <input type="email" class="form-control" id="modalPerEmailProf" name="modalPerEmailProf"
                            placeholder="Email">
                    </div>

                    <div class="form-group">
                        <label for="modalPerDenominacionCorp">Denominación corporativa</label>
                        <input type="text" class="form-control" id="modalPerDenominacionCorp"
                            name="modalPerDenominacionCorp" placeholder="Nombre corporativo">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="modalPerRuc">RUC</label>
                            <input type="text" class="form-control" id="modalPerRuc" name="modalPerRuc">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="modalPerWhatsapp">WhatsApp (0991123456)</label>
                            <input type="text" class="form-control" id="modalPerWhatsapp" name="modalPerWhatsapp">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modalPerPlan">Elegir plan</label>
                        <select id="modalPerPlan" name="modalPerPlan" class="form-control">
                            <option value="">Seleccionar...</option>
                            <option value="Básico">Básico</option>
                            <option value="Estándar">Estándar</option>
                            <option value="Premium">Premium</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEspecialidades">Guardar</button>
            </div>
        </div>
    </div>
</div>