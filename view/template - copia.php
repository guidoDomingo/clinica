<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Legacy User Menu</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <!-- <link rel="stylesheet" href="view/dist/css/adminlte.min.css"> -->
  <link rel="stylesheet" href="view/dist/css/adminlte.css">
   <!-- Select2 -->
   <link rel="stylesheet" href="view/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="view/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
   <!-- summernote -->
   <link rel="stylesheet" href="view/plugins/summernote/summernote-bs4.min.css">
   <!-- dropzonejs -->
   <link rel="stylesheet" href="view/plugins/dropzone/min/dropzone.min.css">
    <!-- Estilos propios -->
  <link rel="stylesheet" href="view/css/frmConsulta.css">   
  <!-- Toastr -->
  <link rel="stylesheet" href="view/plugins/toastr/toastr.min.css">


  <!-- jQuery -->
  <script src="view/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="view/dist/js/adminlte.min.js"></script>
  <!-- AdminLTE for demo purposes -->
  <!-- <script src="view/dist/js/demo.js"></script> -->
  <script src="https://kit.fontawesome.com/8faaf42ade.js" crossorigin="anonymous"></script>
  <!-- Select2 -->
  <script src="view/plugins/select2/js/select2.full.min.js"></script>
  <!-- Summernote -->
  <script src="view/plugins/summernote/summernote-bs4.min.js"></script>
  <!-- dropzonejs -->
  <script src="view/plugins/dropzone/min/dropzone.min.js"></script>
   <!-- SweetAlert2  -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Toastr -->
<script src="view/plugins/toastr/toastr.min.js"></script>
</head>
<!-- <body class="hold-transition sidebar-mini"> -->
<body class="sidebar-mini layout-navbar-fixed layout-footer-fixed text-sm control-sidebar-slide-open sidebar-collapse layout-fixed" style="height: auto;">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
 <?php
  include "view/nav/navbar.php";
 ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <?php
  include "view/nav/sidebar.php";
  ?>

  <!-- Content Wrapper. Contains page content -->
  <?php
    // Aqui la logica de acceso a cada menu
    include "view/modules/consultas.php";
    // include "view/modules/citas.php";
  ?>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 3.2.0
    </div>
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
  </footer>

  <!-- Control Sidebar -->
  <!-- <aside class="control-sidebar control-sidebar-dark">
     
  </aside> -->
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
 
<!-- Template -->
<script src="view/js/template.js"></script>
<!-- Consulta -->
<script src="view/js/consulta.js"></script>
<!-- Citas -->
<script src="view/js/citas.js"></script>
<!-- Archivos -->
<script src="view/js/archivos.js"></script>
</body>
</html>
