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
     $putData = file_get_contents("php://input");     
     $data = json_decode($putData, true); 
     
    $nroPedido = $data['nroPedido'] ?? null;
    $email = $data['email'] ?? null;
    $nombre = $data['nombre'] ?? null;
    $tipo = $data['tipo'] ?? null;
    $talla = $data['talla'] ?? null;
    $stock = $data['stock'] ?? null;

    if(Venta::modificarVenta($nroPedido, $email,$nombre,$tipo,$talla,$stock)){        
        $responseData = [
            "mensaje" => "venta modificada con exito",
            'nroPedido' => $nroPedido,
            'email' => $email,
            'nombre' => $nombre,
            'tipo' => $tipo,
            'talla' => $talla,
            'stock' => $stock
         ];
    }
    else {
        $responseData = ["mensaje" => "no se pudo moficiar la venta"];
    }

    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public static function traerVentas()
{   
    $lista = Venta::obtenerVentas();
    return $lista;
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