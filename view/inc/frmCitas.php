            <form id="tblCitas" method="post" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                            <label for="ctcedula">Cedula</label>
                            <input type="text" class="form-control" id="ctcedula" name="ctcedula" placeholder="Buscar">
                            </div>
                            <div class="form-group col-md-2">
                            <label for="ctficha">Ficha</label>
                            <input type="text" class="form-control" id="ctficha" name="ctficha" placeholder="Buscar">
                            </div>
                            <div class="form-group col-md-8">
                            <label for="ctnombres">Nombres</label>
                            <input type="text" class="form-control" id="ctnombres" name="ctnombres" placeholder="Nombres">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                            <label for="ctservicios">Servicios</label>
                            <select class="form-control select2bs4 ctservicios" id="ctservicios" name="ctservicios" style="width: 100%;">
                                <option selected="selected">Seleccionar</option> 
                            </select>
                            </div>
                           
                        </div>
                        <div class="form-row">
                           
                            <div class="form-group col-md-12">
                            <label for="cttratante">Ttte.</label>
                            <select class="form-control select2bs4 selecttte" id="cttratante"  name="cttratante" style="width: 100%;">
                                <option selected="selected">Seleccionar</option> 
                            </select>
                            </div>
                        </div>
                    
                        <div class="form-row">
                            <div class="form-group col-md-6">
                            <label for="ctremitente">Rtte.</label>
                            <select class="form-control select2bs4 selectrtte" id="ctremitente" name="ctremitente" style="width: 100%;">
                                <option selected="selected">Seleccionar</option> 
                            </select>
                            </div>
                            <div class="form-group col-md-6">
                            <label for="ctseguro">Seguro.</label>
                            <select class="form-control select2bs4 selectseguro" id="ctseguro"  name="ctseguro" style="width: 100%;">
                                <option selected="selected">Seleccionar</option> 
                            </select>
                            </div>
                        </div>
                       
                        <div class="form-group">
                            <label for="ctmotivo">Motivo</label>
                            <input type="text" class="form-control" id="ctmotivo" name="ctmotivo" placeholder="Motivo de consulta">
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ctcobertura">Cobertura</label>
                            <input type="text" class="form-control" id="ctcobertura" name="ctcobertura" placeholder="Agregar plan">
                            </div>
                            <div class="form-group col-md-6">
                            <label for="ctimporte">Importe</label>
                            <input type="number" class="form-control" id="ctimporte" name="ctimporte" placeholder="Importe">
                            </div>
                            
                        </div>
                      
                       
                        <div class="form-group">
                            <label for="ctnotainfo">Información adicional</label>
                            <input type="text" class="form-control" id="ctnotainfo" name="ctnotainfo" placeholder="Nota ">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="ctproximacita">Proxima consulta</label>
                                <input type="date" class="form-control" id="ctproximacita" name="ctproximacita"> 
                            </div>
                            <div class="form-group col-md-3">
                            <label for="ctwhatsapp">Nro. whatsapp</label>
                            <input type="text" class="form-control" id="ctwhatsapp" name="ctwhatsapp" placeholder="595983222999">
                            </div>
                            
                            <div class="form-group col-md-5">
                            <label for="ctemail">Email del Paciente</label>
                            <input type="email" class="form-control" id="ctemail" name="ctemail" placeholder="jhondoe@gmail.com">
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
                        <input type="hidden" id="id_user" name="id_user" value="1">
                        <!-- <input type="hidden" id="id_reserva" name="id_reserva" value="123"> -->
                        <button type="button" class="btn btn-primary" id="btnsavecitas">Guardar</button>
                </form>