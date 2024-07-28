<?php
require_once './models/ventas.php';

class VentasController 
{
    public function cargarUna($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $email = $parametros['email'];
        $nombre = $parametros['nombre'];
        $tipo = $parametros['tipo'];
        $talle = $parametros['talle'];
        $stock = $parametros['stock'];        
     
        $venta = new Venta();
        $venta->email = $email;
        $venta->nombre = $nombre;
        $venta->tipo = $tipo;
        $venta->talle = $talle;
        $venta->stock = $stock;       
       

        $archivo = isset($_FILES['foto']) ? $_FILES['foto'] : null;
        if ($archivo) {
            $tempFilePath = $archivo['tmp_name']; //Ruta temporal del archivo
            $fecha_actual = date('d-m-Y');       
            $parts = explode('@', $email);        
            $email_recortado = $parts[0]; 
            $nombreImagen = $venta->nombre . '_' . $venta->tipo . '_' . $venta->talle . '_' . $email_recortado . '_'. $fecha_actual .'.jpg';
            $guardadoImagen = Venta::guardarImagenVenta("ImagenesDeVenta/2024/", $nombreImagen, $tempFilePath);
            if ($guardadoImagen) {
                $venta->foto = $nombreImagen;
            } else {
                $payload = json_encode(array("mensaje" => "Se cargó el venta pero hubo un error al cargar la imagen"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        if($venta->crearVenta() != null)
        {
          $payload = json_encode(array("mensaje" => "venta creado con éxito"));
          if ($archivo) {
              $payload = json_encode(array("mensaje" => "venta e imagen creados con éxito"));
          }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "No se pudo cargar la venta"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
  }

  public function modificarVenta($request, $response, $args) 
  {
    $params = $request->getParsedBody();

    $nroPedido = $params['nroPedido'];
    $email = $params['email'];
    $nombre = $params['nombre'];
    $tipo = $params['tipo'];
    $talle = $params['talle'];
    $stock = $params['stock'];

    $rta = Venta::modificarVenta($nroPedido, $email, $nombre, $tipo, $talle, $stock);
    if ($rta) {
        $payload = json_encode(array("mensaje" => "Venta modificada con éxito"));
    } else {
        $payload = json_encode(array("error" => "No existe el número del pedido"));
    }
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function traerVentaParticular($request, $response, $args)
  {
    $parametros = $request->getQueryParams();
    $fecha = $parametros['fecha'] ?? null;

    if (!$fecha) {
        $fecha = date('Y-m-d', strtotime('-1 day'));
    }

    $lista = Venta::obtenerVentaParticular($fecha);

    if (empty($lista)) {
        $payload = json_encode(array("ventas" => 'No hay ventas realizadas el dia de ayer'));
    } else {
        $payload = json_encode(array("ventas" => $lista));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
}

public function traerVentasPorUsuario($request, $response, $args)
{
    $email = isset($_GET['email']) ? $_GET['email'] : null;
    $lista = Venta::obtenerVentasUsuario($email);
    $payload = json_encode(array("Lista Ventas" => $lista));

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
}

public function traerVentasPorProducto($request, $response, $args)
{
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
    $lista = Venta::obtenerVentasPorTipo($tipo);
    $payload = json_encode(array("Lista Ventas" => $lista));

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
}

public function traerVentasEntreValores($request, $response, $args)
{
  $parametros = $request->getQueryParams();
  $valor1 = $parametros['valor1'] ?? null;
  $valor2 = $parametros['valor2'] ?? null;

  if ($valor1 === null || $valor2 === null) {
      $payload = json_encode(array("error" => "Debe proporcionar ambos valores"));
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
  }

  $lista = Venta::obtenerVentasEntreValores($valor1, $valor2);

  if (empty($lista)) {
      $payload = json_encode(array("mensaje" => "No hay ventas dentro del rango especificado"));
  } else {
      $payload = json_encode(array("Lista Ventas" => $lista));
  }

  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
}

public function traerVentasPorIngresos($request, $response, $args)
{
    $diaParticular = isset($_GET['diaParticular']) ? $_GET['diaParticular'] : null;
    $lista = Venta::obtenerIngresosPorDia($diaParticular);

    $preciosAcumulados = [];

    foreach ($lista as $venta) 
    {
        $fecha = $venta["fecha"];
        $precio = $venta["precio"];
        
        if (isset($preciosAcumulados[$fecha])) 
        {
            $preciosAcumulados[$fecha] += $precio;
        } else 
        {
            $preciosAcumulados[$fecha] = $precio;
        }
    }
    
    $listaPrecioAcumulablePorFecha = [];
    foreach ($preciosAcumulados as $fecha => $precio) 
    {
        $listaPrecioAcumulablePorFecha[] = ["Fecha" => $fecha, "Total" => $precio];
    }
    
    $payload = json_encode(array("Lista ganancias por dia" => $listaPrecioAcumulablePorFecha));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
}

public function traerProductoMasVendido($request, $response, $args)
{
  $resultado = Venta::obtenerProductoMasVendido();

  if ($resultado['mensaje']) {
      $payload = json_encode(array("mensaje" => $resultado['mensaje']));
  } else {
      $payload = json_encode(array("Producto mas vendido" => $resultado['producto']));
  }

  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
}          

public function guardarCSV($request, $response, $args) 
{
    $nombreArchivo = "ventas.csv";

    if($archivo = fopen($nombreArchivo, "w"))
    {        
    $lista = Venta::obtenerTodos();

    foreach( $lista as $venta )
    {
        fputcsv($archivo, [$venta->email, $venta->nombre, $venta->tipo, $venta->talle, $venta->stock, $venta->precio ,$venta->fecha, $venta->nroPedido]);
    }
    fclose($archivo);
    
    $csvContent = file_get_contents($nombreArchivo); //leer archivo
   
    $response->getBody()->write($csvContent);
    return $response
        ->withHeader('Content-Type', 'text/csv')
        ->withHeader('Content-Disposition', 'attachment; filename=' . $nombreArchivo);        
    }
    else
    {
    $payload =  json_encode(array("mensaje" => "No se pudo abrir el archivo"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
    }
}

}

?>