<?php
require_once './models/tienda.php';

class TiendaController {
    
    public function cargarUno($request, $response, $args){

        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $talle = $parametros['talle'];
        $color = $parametros['color'];
        $stock = $parametros['stock'];

        $producto = new Tienda();
        $producto->nombre = $nombre;
        $producto->precio = $precio;
        $producto->tipo = $tipo;
        $producto->talle = $talle;
        $producto->color = $color;
        $producto->stock = $stock;
     
        $archivo = isset($_FILES['foto']) ? $_FILES['foto'] : null;
        if ($archivo) {
            $tempFilePath = $archivo['tmp_name']; //Ruta temporal del archivo
            $nombreImagen = $producto->nombre . '_' . $producto->tipo . '.jpg';
            $guardadoImagen = Tienda::guardarImagen("ImagenesDeRopa/2024/", $nombreImagen, $tempFilePath);
            if ($guardadoImagen) {
                $producto->foto = $nombreImagen;
            } else {
                $payload = json_encode(array("mensaje" => "Se cargó el producto pero hubo un error al cargar la imagen"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        if($producto->cargarProducto() != null)
        {
          $payload = json_encode(array("mensaje" => "Producto creado con éxito"));
          if ($archivo) {
              $payload = json_encode(array("mensaje" => "Producto e imagen creados con éxito"));
          }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "No se pudo cargar el producto"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


public function consularProducto($request, $response, $args)
{
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $tipo = $parametros['tipo'];
    $color = $parametros['color'];
 
    $tienda = new Tienda();
    $tienda->nombre = $nombre;
    $tienda->tipo = $tipo;
    $tienda->color = $color;
    $rta = $tienda->consultarProducto();

    $payload = json_encode(array("mensaje" => $rta));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
}

public function traerPorStock($request, $response, $args){
    $lista = Tienda:: obtenerOrdenados('stock');
    $payload = json_encode($lista);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
}

public function traerPorPrecio($request, $response, $args){
    $lista = Tienda:: obtenerOrdenados('precio');
    $payload = json_encode($lista);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
}

public function traerMenosVendido($request, $response, $args)
{
    $producto = Tienda::obtenerMenosVendido();
    $payload = json_encode($producto);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
}





}

?>