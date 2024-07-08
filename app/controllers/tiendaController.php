<?php
require_once './models/tienda.php';

class TiendaController 
{
    public function cargarUno($request, $response, $args){
    
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $talle = $parametros['talle'];
        $color = $parametros['color'];
        $stock = $parametros['stock'];

        $tienda = new Tienda();
        $tienda->nombre = $nombre;
        $tienda->precio = $precio;
        $tienda->tipo = $tipo;
        $tienda->talle = $talle;
        $tienda->color = $color;
        $tienda->stock = $stock;    

        $payload = ["mensaje" => "No se pudo crear la tienda"];
        
        if ($tienda->cargarProducto() != null) {
            $payload = ["mensaje" => "Tienda creada/actualizada con éxito"];           
            if (isset($_FILES['foto'])) {
                $resultadoGuardado = $this->guardarImagenTienda($nombre, $tipo, $_FILES['foto']);
                
                if ($resultadoGuardado !== true) {
                    $payload['mensaje'] = "Tienda creada, pero hubo un error al cargar la imagen: $resultadoGuardado";
                } else {                    
                    $tienda->foto = $nombre . '_' . $tipo . '.jpg';                    
                    if ($tienda->cargarProducto() != null) {
                        $payload['mensaje'] = "Tienda e imagen creadas con éxito";
                    } else {
                        $payload['mensaje'] = "Tienda creada, pero hubo un error al guardar la ruta de la imagen en la base de datos";
                    }
                }
            }
        }

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
}

private function guardarImagenTienda($nombre, $tipo, $archivo)
{
    $tempFilePath = $archivo['tmp_name'];
    $directorioDestino = "ImagenesDeRopa/2024/";
    $nombreArchivo = $nombre . '_' . $tipo . '.jpg';

    if (move_uploaded_file($tempFilePath, $directorioDestino . $nombreArchivo)) {
        return true;
    } else {
        return "Error al guardar la imagen";
    }
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

}

?>