<?php   

    class AccesoDb{

        private static $ObjetoAccesoDatos;
        private $objetoPDO;
     
        private function __construct()
        {
            try {
                $this->objetoPDO = new PDO('mysql:host='.$_ENV['MYSQL_HOST']. ':3307'.';dbname='.$_ENV['MYSQL_DB'].';charset=utf8', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $this->objetoPDO->exec("SET CHARACTER SET utf8");
            } catch (PDOException $e) {
                print "Error: " . $e->getMessage();
                die();
            }
        }
     
        public function RetornarConsulta($sql)
        { 
            return $this->objetoPDO->prepare($sql);
        }
    
         public function RetornarUltimoIdInsertado()
        { 
            return $this->objetoPDO->lastInsertId(); 
        }
     
        public static function dameUnObjetoAcceso()
        { 
            if (!isset(self::$ObjetoAccesoDatos)) {
                self::$ObjetoAccesoDatos = new AccesoDb(); 
            } 
            return self::$ObjetoAccesoDatos;
        }
    


    }

?>