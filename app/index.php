<?php

require_once '../vendor/autoload.php';
require_once './controllers/tiendaController.php';
require_once './controllers/ventasController.php';
require_once './controllers/usuarioController.php';

require_once './middlewares/autentificadorJWT.php';
require_once './middlewares/confirmarPerfilMiddleware.php';
require_once './middlewares/tiendaMiddleware.php';
require_once './middlewares/usuarioMiddleware.php';

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

//TIENDA
$app->group('/tienda', function (RouteCollectorProxy $group) {
    $group->post('/alta', \tiendaController::class . ':cargarUno')->add(\tiendaMiddleware::class . ':verificarTipoYTalle')->add(\confirmarPerfilMiddleware::class . ':verificarRolAdmin');
    $group->post('/consultar', \tiendaController::class . ':consularProducto');
});

//VENTAS
$app->group('/ventas', function (RouteCollectorProxy $group) {
    $group->post('/alta', \ventasController::class . ':cargarUna')->add(\tiendaMiddleware::class . ':existeStock');
    $group->get('/descargar', \ventasController::class . ':guardarCSV')->add(\confirmarPerfilMiddleware::class . ':verificarRolAdmin');
    $group->put('/modificar', \ventasController::class . ':modificarVenta')->add(\confirmarPerfilMiddleware::class . ':verificarRolAdmin');

    $group->group('/consultar', function (RouteCollectorProxy $groupConsultar) {
        $groupConsultar->get('/productos/vendidos', \ventasController::class . ':traerVentaParticular')->add(\confirmarPerfilMiddleware::class . ':verificarAdminYEmpleado');
        $groupConsultar->get('/ventas/porUsuario', \ventasController::class . ':traerVentasPorUsuario')->add(\confirmarPerfilMiddleware::class . ':verificarAdminYEmpleado');
        $groupConsultar->get('/ventas/porProducto', \ventasController::class . ':traerVentasPorProducto')->add(\confirmarPerfilMiddleware::class . ':verificarAdminYEmpleado');
        $groupConsultar->get('/productos/entreValores', \ventasController::class . ':traerVentasEntreValores')->add(\tiendaMiddleware::class . ':verificarValores')->add(\confirmarPerfilMiddleware::class . ':verificarAdminYEmpleado');
        $groupConsultar->get('/ventas/ingresos', \ventasController::class . ':traerVentasPorIngresos')->add(\confirmarPerfilMiddleware::class . ':verificarRolAdmin');
        $groupConsultar->get('/productos/masVendido', \ventasController::class . ':traerProductoMasVendido')->add(\confirmarPerfilMiddleware::class . ':verificarAdminYEmpleado');
    });    
});

//RECUPERATORIO
$app->group('/recuperatorio', function (RouteCollectorProxy $group) {

    $group->group('/consultas/productos', function (RouteCollectorProxy $groupConsultar) {
        $groupConsultar->get('/porStock', \tiendaController::class . ':traerPorStock');
        $groupConsultar->get('/porPrecio', \tiendaController::class . ':traerPorPrecio');
        $groupConsultar->get('/menosVendido', \tiendaController::class . ':traerMenosVendido');
    });    
});

//LOGIN Y ALTA
$app->post('/altaUsuario', \usuarioController::class . ':cargarUno')->add(\usuarioMiddleware::class . ':verificarPerfil');
$app->post('/login', \usuarioController::class . ':loginUsuario')->add(\usuarioMiddleware::class . ':verificarPerfil');

  

$app->run();


?>