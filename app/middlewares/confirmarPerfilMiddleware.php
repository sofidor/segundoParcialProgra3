<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class confirmarPerfilMiddleware{

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            AutentificadorJWT::VerificarToken($token);
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verificarToken(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            AutentificadorJWT::VerificarToken($token);
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verificarRolAdmin(Request $request, RequestHandler $handler) : Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        if($token!=null)
        {
            AutentificadorJWT::VerificarToken($token);
            $parametros = (array)AutentificadorJWT::ObtenerData($token);
    
            $usuario = $parametros['tipoUsuario'];
    
            if ($usuario === '3') {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = json_encode(array('mensaje' => 'Acceso denegado'));
                $response->getBody()->write($payload);
            }
        }
        else
        {
            $response = new Response();
            $payload = json_encode(array('error' => 'Token vacio'));
            $response->getBody()->write($payload);
        }


        return $response->withHeader('Content-Type', 'application/json');
    }

    public function verificarRolEmpleado(Request $request, RequestHandler $handler)
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        if($token!=null)
        {
            AutentificadorJWT::VerificarToken($token);
            $parametros = (array)AutentificadorJWT::ObtenerData($token);
    
            $usuario = $parametros['tipoUsuario'];
    
            if ($usuario === '2') {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = json_encode(array('mensaje' => 'Acceso denegado'));
                $response->getBody()->write($payload);
            }
        }
        else
        {
            $response = new Response();
            $payload = json_encode(array('error' => 'Token vacio'));
            $response->getBody()->write($payload);
        }


        return $response->withHeader('Content-Type', 'application/json');
    }

    public function verificarAdminYEmpleado(Request $request, RequestHandler $handler)
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        if($token!=null)
        {
            AutentificadorJWT::VerificarToken($token);
            $parametros = (array)AutentificadorJWT::ObtenerData($token);
    
            $usuario = $parametros['tipoUsuario'];
    
            if ($usuario === '3' || $usuario === '2') {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = json_encode(array('mensaje' => 'Acceso denegado'));
                $response->getBody()->write($payload);
            }
        }
        else
        {
            $response = new Response();
            $payload = json_encode(array('error' => 'Token vacio'));
            $response->getBody()->write($payload);
        }


        return $response->withHeader('Content-Type', 'application/json');
    }

    
   
}

?>