<?php

use Config\Routes\Router;
use function Config\Routes\view;

// Rutas Publicas

Router::get('/', function () {
   view('inicio', [
      'title' => 'Inicio - Trayectoria CUCEA',
   ]);
});

Router::get('/iniciar-sesion', function () {
   view('iniciar-sesion', [
      'title' => 'Iniciar Sesión - CUCEA',
   ]);
});

Router::get('/registro-nuevo', function () {
   view('registro-nuevo', [
      'title' => 'Registro - CUCEA',
   ]);
});

Router::get('/perfil', function () {
   view('perfil', [
      'title' => 'Mi Perfil - CUCEA',
   ]);
});

Router::group('/alumno', [], function () {

   Router::get('/dashboard', function () {
      view('alumno/dashboard', [
         'title' => 'Dashboard - Alumno',
      ]);
   });

   Router::get('/materias', function () {
      view('alumno/materias', [
         'title' => 'Mis Materias - CUCEA'
      ]);
   });

   Router::get('/encuesta', function () {
      view('alumno/encuesta', [
         'title' => 'Encuesta - CUCEA'
      ]);
   });

   Router::get('/historial', function () {
      view('alumno/historial', [
         'title' => 'Historial Académico - CUCEA'
      ]);
   });

   Router::get('/materia/{codigo}', function ($codigo) {
      view('alumno/materia-detalle', [
         'title' => "Materia $codigo - CUCEA",
         'codigo' => $codigo
      ]);
   });
});

// Rutas de Testeo

Router::group("/tests", [], function () {
   Router::get("/connection", function () {
      view('tests/connection', [
         'title' => 'Testeo de conexión - CUCEA',
      ]);
   });

   Router::get("/database", function () {
      view('tests/database', [
         'title' => 'Testeo de base de datos - CUCEA',
      ]);
   });
});
