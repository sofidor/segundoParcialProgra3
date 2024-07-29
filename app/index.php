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
//use FPDF;

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

// Ruta para descargar el PDF con el listado de ventas
$app->get('/ventas/pdf', function (Request $request, Response $response, $args) {
    
    $ventas = ventasController::traerVentas(); 
    $nombreArchivo = "ventas.pdf";

    // Crear el PDF
    $pdf = new FPDF('L');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Encabezado del PDF
    $pdf->Cell(10, 10, 'ID');
    $pdf->Cell(50, 10, 'Email');
    $pdf->Cell(20, 10, 'Nombre');
    $pdf->Cell(20, 10, 'Tipo');
    $pdf->Cell(20, 10, 'Talle');
    $pdf->Cell(20, 10, 'Stock');
    $pdf->Cell(20, 10, 'Precio');
    $pdf->Cell(60, 10, 'Foto');
    $pdf->Cell(40, 10, 'Fecha');
    $pdf->Cell(20, 10, 'Nro Pedido');
    $pdf->Ln();

    // Añadir datos de ventas al PDF
    foreach ($ventas as $venta) {
        $pdf->Cell(10, 10, $venta->id);
        $pdf->Cell(50, 10, $venta->email);
        $pdf->Cell(20, 10, $venta->nombre);
        $pdf->Cell(20, 10, $venta->tipo);
        $pdf->Cell(20, 10, $venta->talle);
        $pdf->Cell(20, 10, $venta->stock);
        $pdf->Cell(20, 10, $venta->precio);
        $pdf->Cell(60, 10, $venta->foto);
        $pdf->Cell(40, 10, $venta->fecha);
        $pdf->Cell(20, 10, $venta->nroPedido);
        $pdf->Ln();
    }

    // Enviar el PDF como descarga
    $pdf = $pdf->Output('F', $nombreArchivo);
    $pdfContent = file_get_contents($nombreArchivo);
    $response->getBody()->write($pdfContent);

    return $response->withHeader('Content-Type', 'application/pdf')
                    ->withHeader('Content-Disposition', 'attachment; filename =' . $nombreArchivo)
                    ->withHeader('Content-Lenght', strlen($pdfContent));

});

// Ruta para descargar el PDF con el listado de usuarios
$app->get('/usuarios/pdf', function (Request $request, Response $response, $args) {
    
    $usuarios = usuarioController::traerusuarios(); 
    $nombreArchivo = "usuarios.pdf";

    // Crear el PDF
    $pdf = new FPDF('L');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Encabezado del PDF
    $pdf->Cell(10, 10, 'ID');
    $pdf->Cell(50, 10, 'Email');
    $pdf->Cell(20, 10, 'Usuario');
    $pdf->Cell(30, 10, 'Contra');
    $pdf->Cell(20, 10, 'Tipo');
    $pdf->Cell(40, 10, 'Foto');
    $pdf->Cell(30, 10, 'Fecha Alta');
    $pdf->Cell(30, 10, 'Fecha Baja'); 
    $pdf->Ln();

    // Añadir datos de usuarios al PDF
    foreach ($usuarios as $usuario) {
        $pdf->Cell(10, 10, $usuario->id);
        $pdf->Cell(50, 10, $usuario->mail);
        $pdf->Cell(20, 10, $usuario->usuario);
        $pdf->Cell(30, 10, $usuario->contra);
        $pdf->Cell(20, 10, $usuario->tipoUsuario);
       // Agregar la imagen al PDF (asegúrate de que la ruta de la imagen sea correcta)
       $imagePath = __DIR__ . "/ImagenesDeUsuarios/2024/" . "agustin" . " - " . "3" . " - " . "28-07-2024" . ".png";       
       if (file_exists($imagePath)) {
           $pdf->Cell(40, 40, '', 0, 0, 'C');
           $pdf->Image($imagePath, $pdf->GetX() - 40, $pdf->GetY(), 30, 30);
       } else {
           $pdf->Cell(40, 10, 'No Image', 0, 0, 'C');
       }
        $pdf->Cell(30, 10, $usuario->fechaAlta);
        $pdf->Cell(30, 10, $usuario->fechaBaja);
        $pdf->Ln(40); // Ajusta la altura de la fila para la imagen
    }

    $pdf = $pdf->Output('F', $nombreArchivo);
    $pdfContent = file_get_contents($nombreArchivo);
    $response->getBody()->write($pdfContent);

    // Enviar el PDF como descarga
    return $response->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename =' . $nombreArchivo)
                ->withHeader('Content-Lenght', strlen($pdfContent));
});
  

$app->run();


?>