<?php
require_once './models/Tienda.php';

class Venta
{
    public $id;
    public $email;
    public $nombre;
    public $tipo;
    public $talle;
    public $stock;
    public $fecha;
    public $precio;

    public function crearVenta()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $rta = null;

        if(Tienda::existeProductoYTipo($this->nombre, $this->tipo))
        {            
            $consulta = $objAccesoDb->RetornarConsulta("INSERT INTO ventas (email, nombre, tipo, talle, stock, fecha, nroPedido) VALUES (:email, :nombre, :tipo, :talle, :stock, :fecha)");
            $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':talle', $this->talle, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
            $fecha = new DateTime(date("d-m-Y"));
            $consulta->bindValue(':fecha', date_format($fecha, 'Y-m-d'));
            $consulta->execute();

            $rta = $objAccesoDb->RetornarUltimoIdInsertado();
            Tienda::descontarStock($this->stock, $this->nombre, $this->tipo, $this->talle);
        }
        else
        {
            $rta = false;
        }

        return $rta;
    }    

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function guardarImagenVenta($path, $nombre, $tipo, $talla, $email, $nombreArchivo)
    {
        $rta = false;
    
        $fecha_actual = date('d-m-Y');       
        $parts = explode('@', $email);        
        $email_recortado = $parts[0];   
        
        $destino = $path . $nombre . " - " . $tipo . " - " . $talla . " - " . $email_recortado . " - " . $fecha_actual . ".png";        
      
        if(move_uploaded_file($nombreArchivo, $destino)){
            $rta = true;
        }
        
        return $rta;
    }

}

?>