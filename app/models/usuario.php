<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

class Usuario
{   
    public $mail;
    public $usuario;
    public $contra;
    public $tipoUsuario;
    public $foto;
    public $fechaAlta;
    public $fechaBaja;

    public function crearUsuario()
    {       
        $rta = null;
        
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("INSERT INTO usuarios (mail, usuario, contra, tipoUsuario, fechaAlta) VALUES (:mail, :usuario, :contra, :tipoUsuario, :fechaAlta)");
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':contra', $this->contra, PDO::PARAM_STR);
        $consulta->bindValue(':tipoUsuario', $this->tipoUsuario, PDO::PARAM_INT);
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':fechaAlta', date_format($fecha, 'Y-m-d'));
        $consulta->execute();

        $rta = $objAccesoDb->RetornarUltimoIdInsertado();
        return $rta;
    }

    public function actualizarFoto($rutaFoto)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("UPDATE usuarios SET foto = :foto WHERE mail = :mail");
        $consulta->bindValue(':foto', $rutaFoto, PDO::PARAM_STR);
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        return $consulta->execute();
    }

    public static function guardarImagenUsuario($path, $usuario, $tipoUsuario, $nombreArchivo)
    {
        $rta = false;
    
        $fecha_actual = date('d-m-Y');    

        $destino = $path . $usuario . " - " . $tipoUsuario . " - " . $fecha_actual . ".png";        
        
        if(move_uploaded_file($nombreArchivo, $destino)){
            $rta = $destino; // Devuelve la ruta si se mueve correctamente
        }
        
        return $rta;
    }

    public static function obtenerTodos()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT id, mail, usuario, contra, tipoUsuario FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }
}

?>