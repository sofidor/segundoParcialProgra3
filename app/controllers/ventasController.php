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
        $precio = $parametros['precio'];
     
        $venta = new Venta();
        $venta->email = $email;
        $venta->nombre = $nombre;
        $venta->tipo = $tipo;
        $venta->talle = $talle;
        $venta->stock = $stock;       
        $venta->precio = $precio;

        if($venta->crearVenta())
        {
            $archivo = isset($_FILES['foto']) ? $_FILES['foto'] : null;
            $tempFilePath = $archivo['tmp_name']; // Ruta temporal del archivo

            $imagenGuardada = Venta::guardarImagenVenta("ImagenesDeVenta/2024/", $venta->nombre, $venta->tipo, $venta->talle, $venta->email, $tempFilePath);
            if($imagenGuardada != false)
            {
                $payload = json_encode(array("mensaje" => "Se creo la venta y se guardo la imagen"));
            }
            else
            {
                $payload = json_encode(array("mensaje" => "Se creo la venta pero no se pudo guardar la imagen"));
            }
        }
        else
        {
            $payload = json_encode(array("mensaje" => "No se encontro el producto"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function guardarCSV($request, $response, $args) 
    {
      $nombreArchivo = "ventas.csv";

      if($archivo = fopen($nombreArchivo, "w"))
      {        
        $lista = Venta::obtenerTodos();
        foreach( $lista as $venta )
        {
          fputcsv($archivo, [$venta->id, $venta->email, $venta->nombre, $venta->tipo, $venta->talle, $venta->stock, $venta->fecha, $venta->nroPedido, $venta->precio]);
        }
        fclose($archivo);

        // Leer el archivo CSV recién creado
        $csvContent = file_get_contents($nombreArchivo);

        // Establecer la respuesta con el contenido del archivo CSV
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