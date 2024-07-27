<?php

require_once '../vendor/autoload.php';
require_once './controllers/tiendaController.php';
require_once './controllers/ventasController.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require_once './db/accesoDatos.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();//hace referencia al vendor

$app->group('/tienda', function (RouteCollectorProxy $group) {
    $group->post('/alta', \tiendaController::class . ':cargarUno');
    $group->post('/consultar', \tiendaController::class . ':consularProducto');
});

$app->group('/ventas', function (RouteCollectorProxy $group) {
    $group->post('/alta', \ventasController::class . ':cargarUna');
    $group->get('/descargar', \ventasController::class . ':guardarCSV');
    $group->put('/modificar', \ventasController::class . ':modificarVenta');

    $group->group('/consultar', function (RouteCollectorProxy $groupConsultar) {
        $groupConsultar->get('/productos/vendidos', \ventasController::class . ':traerVentaParticular');
        $groupConsultar->get('/ventas/porUsuario', \ventasController::class . ':traerVentasPorUsuario');
        $groupConsultar->get('/ventas/porProducto', \ventasController::class . ':traerVentasPorProducto');
        $groupConsultar->get('/productos/entreValores', \ventasController::class . ':traerVentasEntreValores');
        $groupConsultar->get('/ventas/ingresos', \ventasController::class . ':traerVentasPorIngresos');
        $groupConsultar->get('/productos/masVendido', \ventasController::class . ':traerProductoMasVendido');
    });    
});


  

$app->run();


?>