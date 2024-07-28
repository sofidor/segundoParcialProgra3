<?php
require_once './models/Usuario.php';

class UsuarioController{

    public function cargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mail = $parametros['mail'];
        $usuarioNombre = $parametros['usuario'];
        $contra = $parametros['contra'];
        $tipoUsuario = $parametros['tipoUsuario'];
        
        $usuario = new Usuario();
        $usuario->mail = $mail;
        $usuario->usuario = $usuarioNombre;
        $usuario->contra = $contra;
        $usuario->tipoUsuario = $tipoUsuario;

        $usuarioId = $usuario->crearUsuario();

        if($usuarioId != null)
        {
            $archivo = isset($_FILES['foto']) ? $_FILES['foto'] : null;
            $tempFilePath = $archivo['tmp_name']; // Ruta temporal del archivo

            $rutaImagen = Usuario::guardarImagenUsuario("ImagenesDeUsuarios/2024/", $usuario->usuario, $usuario->tipoUsuario, $tempFilePath);
            if($rutaImagen != false)
            {
                $usuario->actualizarFoto( $usuario->usuario . "_" . $usuario->tipoUsuario . ".png"); // Actualiza la ruta de la imagen en la base de datos
                $payload = json_encode(array("mensaje" => "Se creó el usuario y se guardó la imagen"));
            }
            else
            {
                $payload = json_encode(array("mensaje" => "Se creó el usuario pero no se pudo guardar la imagen"));
            }
        }
        else
        {
            $payload = json_encode(array("mensaje" => "No se pudo crear el usuario"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function loginUsuario($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $user = $parametros['usuario'];
      $tipoUsuario = $parametros['tipoUsuario'];
      $contra = $parametros['contra'];

      $existe = false;
      $listaUsuarios = Usuario::obtenerTodos();

      foreach ($listaUsuarios as $usuario) {
        if($usuario->usuario == $user && $usuario->contra == $contra)
        {
          $existe = true;          
        }
      }
      if($existe)
      {
        $datos = array('tipoUsuario' => $tipoUsuario, 'usuario' => $user, 'contrsenia' => $contra);
        $token = AutentificadorJWT::CrearToken($datos);
        $payload = json_encode(array('jwt' => $token));
      }
      else
      {
        $payload = json_encode(array('error' => 'Nombre de usuario o clave incorrectos'));
      }

      $response->getBody()->write($payload);

      return $response->withHeader('Content-Type', 'application/json');

    }

}

?>