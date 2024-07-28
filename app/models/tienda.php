<?php

class Tienda
{
    public $id;
    public $nombre;
    public $precio;
    public $tipo;
    public $talle;
    public $color;
    public $stock;
    public $foto;

    public function cargarProducto()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $rta = null;
        
        if(!(Tienda::existeProductoYTipo($this->nombre, $this->tipo)))
        {
            $consulta = $objAccesoDb->RetornarConsulta("INSERT INTO tienda (nombre, precio, tipo, talle, color, stock ,foto) VALUES (:nombre, :precio, :tipo, :talle, :color, :stock, :foto)");
            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':talle', $this->talle, PDO::PARAM_STR);
            $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
            $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
            $consulta->execute();

            $rta = $objAccesoDb->RetornarUltimoIdInsertado();
        }
        else
        {
            $this->actualizarProducto();
            $rta = $objAccesoDb->RetornarUltimoIdInsertado();
        }
        return $rta;
    }

    public static function existeProductoYTipo($nombre, $tipo)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT 1 FROM tienda WHERE nombre = :nombre AND tipo = :tipo");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->execute();      
        
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);        
        return $resultado !== false;
    }

    public static function obtenerStock($nombre, $tipo)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT stock FROM tienda WHERE nombre = :nombre AND tipo = :tipo");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->execute();
        
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($resultado === false){            
            $rta = null;// no encontró
        }
        else {
            $rta = $resultado['stock'];
        }
    
        return $rta;
    }

    public function actualizarProducto()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $stockAnterior = Tienda::obtenerStock($this->nombre, $this->tipo);
        $nuevoStock = $this->stock + $stockAnterior;

        $consulta = $objAccesoDb->RetornarConsulta("UPDATE tienda SET precio = :precio, stock = :stock , foto = :foto WHERE nombre = :nombre AND tipo = :tipo");
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':stock', $nuevoStock, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function descontarStock($stock, $nombre, $tipo, $talle)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $stockAnterior = Tienda::obtenerStock($nombre, $tipo);
        $nuevoStock = $stockAnterior - $stock;

        $consulta = $objAccesoDb->RetornarConsulta("UPDATE tienda SET stock = :stock WHERE nombre = :nombre AND tipo = :tipo AND talle = :talle");
        $consulta->bindValue(':stock', $nuevoStock, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':talle', $talle, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function guardarImagen($path, $nombre, $tempName)
    {
        $rta = false;
        // Ruta a donde se quiere mover el archivo
        $destino = $path . $nombre;
        
        if(move_uploaded_file($tempName, $destino))
        {
            $rta = true;
        }
        return $rta;
    }

    public function consultarProducto()
    {
        $rta = "";
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT 1 FROM tienda WHERE nombre = :nombre AND tipo = :tipo AND color = :color");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
        $consulta->execute();
        
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        
        if($resultado !== false)
        {
            $rta = "Existe";
        }
        else
        {
            $consulta = $objAccesoDb->RetornarConsulta("SELECT 1 FROM tienda WHERE nombre = :nombre");
            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->execute();

            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

            if($resultado == false)
            {
                $rta = "No hay productos del nombre " . $this->nombre;
            }
            else
            {
                $consulta = $objAccesoDb->RetornarConsulta("SELECT 1 FROM tienda WHERE tipo = :tipo");
                $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
                $consulta->execute();
    
                $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

                if($resultado == false)
                {
                    $rta = "No hay productos del tipo " . $this->tipo;
                }
                else
                {
                    $consulta = $objAccesoDb->RetornarConsulta("SELECT 1 FROM tienda WHERE color = :color");
                    $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
                    $consulta->execute();
        
                    $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

                    if($resultado == false)
                    {
                        $rta = "No hay productos del color " . $this->color;
                    }
                    else
                    {
                        $rta = "Existe algun atributo de esos productos pero por separado.";
                    }
                }
            }
        }
        
        return $rta;
    }

    public static function obtenerOrdenados($ordenarPor)
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("SELECT * FROM tienda ORDER BY $ordenarPor");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Tienda');
    }

    public static function obtenerMenosVendido()
    {
        $objAccesoDb = AccesoDb::dameUnObjetoAcceso();
        $consulta = $objAccesoDb->RetornarConsulta("
            SELECT p.*, COUNT(v.nombre) as ventas 
            FROM tienda p 
            LEFT JOIN ventas v ON p.nombre = v.nombre
            GROUP BY p.nombre
            ORDER BY ventas ASC 
            LIMIT 1
        ");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }


    


   
}

?>