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
    public $precio;
    public $foto;
    public $fecha;
    public $nroPedido; 

    public function crearVenta()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $rta = null;

        if (Tienda::existeProductoYTipo($this->nombre, $this->tipo))
        {  
            $this->precio = $this->obtenerPrecioProducto($this->nombre, $this->tipo);           

            $consulta = $objAccesoDb->RetornarConsulta("INSERT INTO ventas (email, nombre, tipo, talle, stock, precio, foto, fecha, nroPedido) VALUES (:email, :nombre, :tipo, :talle, :stock, :precio ,:foto, :fecha, :nroPedido)");
            $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':talle', $this->talle, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
            $consulta->bindValue(':precio', $this->precio * $this->stock, PDO::PARAM_INT);
            $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
            $fecha = new DateTime(date("d-m-Y"));
            $consulta->bindValue(':fecha', date_format($fecha, 'Y-m-d'));
            $this->nroPedido = $this->generarNumeroPedido();
            $consulta->bindValue(':nroPedido', $this->nroPedido, PDO::PARAM_STR);
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

    private function generarNumeroPedido()
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longitud = 5;
        $nroPedido = '';

        for ($i = 0; $i < $longitud; $i++) {
            $nroPedido .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }

        return $nroPedido;
    }

    private function obtenerPrecioProducto($nombre, $tipo)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT precio FROM tienda WHERE nombre = :nombre AND tipo = :tipo");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->execute();

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['precio'] : null;
    }

    public static function obtenerTodos()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT * FROM ventas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function existeNroPedido($nroPedido)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT 1 FROM ventas WHERE nroPedido = :nroPedido");
        $consulta->bindValue(':nroPedido', $nroPedido, PDO::PARAM_STR);
        $consulta->execute();        
        
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);// Verifica si la consulta devuelve alguna fila        
        return $resultado !== false;// Devuelve true si se encontró una fila, false si no
    }

    public static function modificarVenta($nroPedido, $email, $nombre, $tipo, $talle, $stock)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $rta = null;

        if(Venta::existeNroPedido($nroPedido))
        {
            $consulta = $objAccesoDb->RetornarConsulta("UPDATE ventas SET email = :email, nombre = :nombre, tipo = :tipo, talle = :talle, stock = :stock WHERE nroPedido = :nroPedido");
            $consulta->bindValue(':email', $email, PDO::PARAM_STR);
            $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $consulta->bindValue(':talle', $talle, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $stock, PDO::PARAM_INT);
            $consulta->bindValue(':nroPedido', $nroPedido, PDO::PARAM_STR);            
            $consulta->execute();

            $rta = true;
        }
        else 
        {
            $rta = false;
        }
        return $rta;
    }

    public static function guardarImagenVenta($path, $nombre , $nombreArchivo)
    {
        $rta = false;        
        $destino = $path . $nombre;        
      
        if(move_uploaded_file($nombreArchivo, $destino)){
            $rta = true;
        }
        
        return $rta;
    }

    public static function obtenerVentaParticular($fecha = null)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT * FROM ventas  WHERE fecha = :fecha");
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function obtenerVentasUsuario($usuario)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT * FROM ventas WHERE email = :email");
        $consulta->bindValue(':email', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function obtenerVentas()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT * FROM ventas");
        
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function obtenerVentasPorTipo($tipo)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT * FROM ventas WHERE tipo = :tipo");
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function obtenerVentasEntreValores($valor1, $valor2)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT * FROM ventas WHERE precio >= :valor1 AND precio <= :valor2");
        $consulta->bindValue(':valor1', $valor1 ,PDO::PARAM_INT);
        $consulta->bindValue(':valor2', $valor2 ,PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function obtenerIngresosPorDia($diaParticular)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();

        if($diaParticular == null)
        {
            $consulta = $objAccesoDb->RetornarConsulta("SELECT fecha, precio FROM ventas");
            $consulta->execute();
        }
        else
        {
            $consulta = $objAccesoDb->RetornarConsulta("SELECT fecha, precio FROM ventas WHERE fecha = :diaParticular");
            $consulta->bindValue(':diaParticular', $diaParticular);
            $consulta->execute();
        }

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerProductoMasVendido()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta(
            "SELECT nombre, SUM(stock) AS totalVentas FROM ventas GROUP BY nombre ORDER BY totalVentas DESC;"
        );
        $consulta->execute();
        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC); // obtiene los result en un array asoc
    
        if (count($resultados) > 1 && $resultados[0]['totalVentas'] == $resultados[1]['totalVentas']) {
            return array(
                'mensaje' => 'Hay productos con la misma cantidad de ventas',
                'producto' => null // si tienen la misma cant , retorna null
            );
        } else {
            return array(
                'mensaje' => null,
                'producto' => $resultados[0]
            );
        }
        //comprueba si hay + de 1 resultado.
    }

}

?>