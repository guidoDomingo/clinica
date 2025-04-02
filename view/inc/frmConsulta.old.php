<form id="tblConsulta" method="post" enctype="multipart/form-data">
                    <div class="form-row fx">
                        <div class="form-group col-md-2">
                            <label for="txtdocumento">Documento</label>
                            <input type="text" class="form-control" id="txtdocumento" name="txtdocumento" placeholder="Cedula de identidad">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="txtficha">Ficha</label>
                            <input type="text" class="form-control" id="txtficha" name="txtficha" placeholder="Ficha médica">
                        </div>
                        <div class="col-md-6 col-md-8">
                            <label for="txtnombres">Nombres</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="paciente" placeholder="Buscar paciente..." aria-label="Buscar paciente">
                                <div class="input-group-append">
                                    
                                    <button class="btn btn-primary" onclick="buscar(event, document.getElementById('txtdocumento').value, document.getElementById('txtficha').value)" aria-label="Buscar">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-success" >
                                        <i class="fa-solid fa-user-plus"></i>
                                    </button>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-dark" onclick="limpiarBusqueda(event)" aria-label="Buscar">
                                    <i class="fa-solid fa-eraser"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="idPersona" name="idPersona" require>
                    </div>
                 
                        <div class="form-row">
                            <div class="form-group col-md-6">
                            <label for="motivoscomunes">Motivos comunes</label>
                            <select class="form-control select2bs4" id="motivoscomunes" name="motivoscomunes" style="width: 100%;">
                                <option selected="selected">Seleccionar</option> 
                            </select>
                            </div>
                            <div class="form-group col-md-6">
                            <label for="formatoConsulta">Preformato</label>
                            <select class="form-control select2bs4 selectFormato" id="formatoConsulta"  name="formatoConsulta" style="width: 100%;">
                                <option selected="selected">Seleccionar</option> 
                            </select>
                            </div>
                        </div>
                       
                        <div class="form-group">
                            <label for="txtmotivo">Motivo</label>
                            <input type="text" class="form-control" id="txtmotivo" name="txtmotivo" placeholder="Motivo de consulta">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                            <label for="visionod">Visión OD</label>
                            <input type="text" class="form-control" id="visionod" name="visionod" placeholder="Visión OD">
                            </div>
                            <div class="form-group col-md-6">
                            <label for="visionoi">Vision OI</label>
                            <input type="text" class="form-control" id="visionoi" name="visionoi" placeholder="Visión OI">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                            <label for="tensionod">Tensión OD</label>
                            <input type="text" class="form-control" id="tensionod" name="tensionod" placeholder="Email">
                            </div>
                            <div class="form-group col-md-6">
                            <label for="tensionoi">Tensión OI</label>
                            <input type="text" class="form-control" id="tensionoi" name="tensionoi" placeholder="Password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="consulta-textarea">Descripción</label>
                            <!-- <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St">
                              -->
                              <textarea id="consulta-textarea" name="consulta-textarea" class="form-control compose-textarea" style="height: 180px">
                                    <h1><u>Heading Of Message</u></h1>
                                    <h4>Subheading</h4>
                                    <p>But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain
                                        was born and I will give you a complete account of the system, and expound the actual teachings
                                        of the great explorer of the truth, the master-builder of human happiness.</p>
                                    <ul>
                                        <li>List item one</li>
                                        <li>List item two</li>
                                    </ul>
                                    <p>Thank you,</p>
                                    <p>John Doe</p>
                                </textarea>
                        </div>
                        <div class="form-group">
                            <label for="formatoreceta">Preformato de receta</label>
                            <select class="form-control select2bs4" id="formatoreceta" name="formatoreceta" style="width: 100%;">
                                <option selected="selected">Seleccionar</option> 
                            </select>
                            <!-- <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor"> -->
                        </div>
                        <div class="form-group">
                            <label for="receta-textarea">Receta</label>
                            
                              <textarea id="receta-textarea" name="receta-textarea" class="form-control compose-textarea" style="height: 180px">
                                    <!-- <h1><u>Heading Of Message</u></h1> -->
                                    <h4>Subheading</h4>
                                    <p>But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain
                                        was born and I will give you a complete account of the system, and expound the actual teachings
                                        of the great explorer of the truth, the master-builder of human happiness.</p>
                                    <ul>
                                        <li>List item one</li>
                                        <li>List item two</li>
                                    </ul>
                                    <p>Thank you,</p>
                                    <p>John Doe</p>
                                </textarea>
                        </div>
                        <div class="form-group">
                            <label for="txtnota">Nota</label>
                            <input type="text" class="form-control" id="txtnota" name="txtnota" placeholder="Nota ">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="proximaconsulta">Proxima consulta</label>
                                <input type="date" class="form-control" id="proximaconsulta" name="proximaconsulta"> 
                            </div>
                            <div class="form-group col-md-3">
                            <label for="whatsapptxt">Nro. whatsapp</label>
                            <input type="text" class="form-control" id="whatsapptxt" name="whatsapptxt" placeholder="595983222999">
                            </div>
                            
                            <div class="form-group col-md-5">
                            <label for="email">Email del Paciente</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="jhondoe@gmail.com">
                            </div>
                        </div>
                           <div class="row">
                            <div class="col-md-12">
                                <div class="card card-default">
                                <div class="card-header">
                                    <h3 class="card-title">Adjuntar archivos <small><em>(Imagen/Pdf)</em> hasta 25 mb</small></h3>
                                </div>
                                <div class="card-body">
                                    <div id="actions" class="row">
                                    <div class="col-lg-6">
                                        <div class="btn-group w-100">
                                        <span class="btn btn-success col fileinput-button">
                                            <i class="fas fa-plus"></i>
                                            <span>Agregar</span>
                                        </span>
                                        <button type="submit" class="btn btn-primary col start">
                                            <i class="fas fa-upload"></i>
                                            <span>Subir</span>
                                        </button>
                                        <button type="reset" class="btn btn-warning col cancel">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Cancelar</span>
                                        </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 d-flex align-items-center">
                                        <div class="fileupload-process w-100">
                                        <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="table table-striped files" id="previews">
                                    <div id="template" class="row mt-2">
                                        <div class="col-auto">
                                            <span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
                                        </div>
                                        <div class="col d-flex align-items-center">
                                            <p class="mb-0">
                                            <span class="lead" data-dz-name></span>
                                            (<span data-dz-size></span>)
                                            </p>
                                            <strong class="error text-danger" data-dz-errormessage></strong>
                                        </div>
                                        <div class="col-4 d-flex align-items-center">
                                            <div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                            </div>
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                        <div class="btn-group">
                                            <button class="btn btn-primary start">
                                            <i class="fas fa-upload"></i>
                                            <span>Start</span>
                                            </button>
                                            <button data-dz-remove class="btn btn-warning cancel">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Cancel</span>
                                            </button>
                                            <button data-dz-remove class="btn btn-danger delete">
                                            <i class="fas fa-trash"></i>
                                            <span>Delete</span>
                                            </button>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                              
                                </div>
                                <!-- /.card -->
                            </div>
                            </div>
                        <div class="form-group">
                            <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="gridCheck">
                            <label class="form-check-label" for="gridCheck">
                                Enviar informe
                            </label>
                            </div>
                        </div>
                        <input type="hidden" id="id_user" name="id_user" value="1">
                        <input type="hidden" id="id_reserva" name="id_reserva" value="0">
                        <button type="button" class="btn btn-primary" id="btnGuardarConsulta">Guardar</button>
                    </form>