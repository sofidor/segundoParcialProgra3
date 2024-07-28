<?php

require_once './models/tienda.php';
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class TiendaMiddleware
{
    public function verificarTipoYTalle(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $tipo = $parametros['tipo'];
        $talle = $parametros['talle'];

        if($tipo != "camiseta" && $tipo != "pantalon")
        {
            $response = new Response();
            $payload = json_encode(array('error' => 'tipo incorrecto'));
            $response->getBody()->write($payload);
        }
        elseif($talle != "s" && $talle != "m" && $talle != "l")
        {
            $response = new Response();
            $payload = json_encode(array('error' => 'talle incorrecta'));
            $response->getBody()->write($payload);
        }
        else
        {
            $response = $handler->handle($request);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function existeStock(Request $request, RequestHandler $handler): Response
    {
        
        $parametros = $request->getParsedBody();
        $stockVenta = $parametros['stock'];
        $nombre = $parametros['nombre'];
        $tipo = $parametros['tipo'];

        $stockExistente = Tienda::obtenerStock($nombre, $tipo);

        if($stockExistente == null)
        {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No se encontro el producto'));
            $response->getBody()->write($payload);
        }
        elseif($stockVenta > $stockExistente)
        {
            $response = new Response();
            $payload = json_encode(array('error' => 'No hay suficiente stock'));
            $response->getBody()->write($payload);
        }
        else
        {
            $response = $handler->handle($request);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function verificarValores(Request $request, RequestHandler $handler): Response
    {   
        
        $queryParams = $request->getQueryParams();

        $valor1 = $queryParams['valor1'] ?? null;
        $valor2 = $queryParams['valor2'] ?? null;
    
        if (!isset($valor1, $valor2) || empty($valor1) || empty($valor2) || 
            !filter_var($valor1, FILTER_VALIDATE_INT) || !filter_var($valor2, FILTER_VALIDATE_INT)) { //filtra si es entero y devuelve el valor , sino false
            
            $response = new Response();
            $payload = json_encode(array('error' => 'Valores vacíos o no son numéricos enteros'));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            return $handler->handle($request);
        }
    }
}

?>