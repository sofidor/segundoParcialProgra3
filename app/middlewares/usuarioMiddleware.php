<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class UsuarioMiddleware
{
    public function verificarPerfil(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $tipoUsuario = $parametros['tipoUsuario'];

        if($tipoUsuario != "1" && $tipoUsuario != "2" && $tipoUsuario != "3")
        {
            $response = new Response();
            $payload = json_encode(array('error' => 'tipoUsuario incorrecto'));
            $response->getBody()->write($payload);
        }
        else
        {
            $response = $handler->handle($request);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>