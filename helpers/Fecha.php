<?php

namespace app\helpers;

class Fecha {
    
    const dias = array('Lun','Mar','Mier','Jue','Vie','Sab','Dom');
    const meses = array('Ene','Feb','Marzo','Abril','Mayo','Jun','Jul','Agos','Sept','Oct','Nov','Dic');
    
    /**
     * Chequea si un string formato fecha es valida o no
     *
     * DateTime::createFromFormat requires PHP >= 5.3
     *
     * @param string $date (fecha a formatear)
     * @param string $formato  (Los formatos de fechas deben ser tipo:  d-m-Y ó  Y-m-d )
     * @return bool
     * 
     */ 
    public static function esFechaValida($fecha, $formato){
        
      $date = \DateTime::createFromFormat($formato, $fecha);     
      $result = $date && \DateTime::getLastErrors()["warning_count"] == 0 && \DateTime::getLastErrors()["error_count"] == 0;
       \Yii::$app->getModule('audit')->data('asdsa', json_encode(\DateTime::getLastErrors()));  
      return $result;
    }
    
    /**
     * Formatea un string fecha al tipo de formato indicado.
     *
     * DateTime::createFromFormat requires PHP >= 5.3
     *
     * @param string $date (fecha a formatear)
     * @param string $formatoInicial    (Los formatos de fechas deben ser tipo:  d-m-Y ó  Y-m-d )
     * @param string $formatoConvertir  (Los formatos de fechas deben ser tipo:  d-m-Y ó  Y-m-d )
     * @return string
     * 
     */ 
    public static function formatear($fecha, $formatoInicial, $formatoConvertir){
        if (self::esFechaValida($fecha, $formatoInicial)) {             
            return \DateTime::createFromFormat($formatoInicial, $fecha)->format($formatoConvertir);}
        else{           
            return false;        
        }
    }
    
    public static function getNombreDia($fecha){
        $nombre_dia = self::dias[date('N', strtotime($fecha))-1];
        return $nombre_dia;        
    }
    
    public static function getNombreMes($fecha){
        $nombre_mes = self::meses[date('n', strtotime($fecha)) - 1];
        return $nombre_mes;
        
    }
    
    public function getFechaAhora(){
        return date('Y-m-d H:i:s');
    }

    /**
     * Compara dos fechas entre si, verificando si una de ellas es menor strictamente que la otra.
     *     
     *
     * @param string $fecha_menor  string fecha en formato yyyy-mm-dd
     * @param string $fecha_mayor  string fecha en formato yyyy-mm-dd  
     * 
     * @return boolean
     * 
     */     
    public static function esFechaMenor($fecha_menor, $fecha_mayor){
        $fecha_menor = new \DateTime($fecha_menor);
        $fecha_mayor = new \DateTime($fecha_mayor);
        if($fecha_menor < $fecha_mayor)
            return true;
        else
            return false;
        
    }
    
    /**
     * Compara dos fechas entre si, verificando si una de ellas es menor strictamente que la otra.
     *     
     *
     * @param string $fecha_menor  string fecha en formato yyyy-mm-dd
     * @param string $fecha_mayor  string fecha en formato yyyy-mm-dd  
     * 
     * @return boolean
     * 
     */     
    public static function esFechaMayor($fecha_menor, $fecha_mayor){
        $fecha_menor = new \DateTime($fecha_menor);
        $fecha_mayor = new \DateTime($fecha_mayor);
        if($fecha_menor < $fecha_mayor)
            return true;
        else
            return false;
        
    }
    
    /**
     * Compara dos fechas entre si, verificando si una de ellas es menor 
     * o igual que otra parametrizada.
     *     
     *
     * @param string $fecha_menor  string fecha en formato yyyy-mm-dd
     * @param string $fecha_mayor  string fecha en formato yyyy-mm-dd  
     * 
     * @return boolean
     * 
     */   
    public static function esFechaMenorIgual($fecha_menor, $fecha_mayor){
        $fecha_menor = new \DateTime($fecha_menor);
        $fecha_mayor = new \DateTime($fecha_mayor);
        if($fecha_menor <= $fecha_mayor)
            return true;
        else
            return false;
    }
    
    public static function sonFechaIguales($fecha_menor, $fecha_mayor){
        $fecha_menor = new \DateTime($fecha_menor);
        $fecha_mayor = new \DateTime($fecha_mayor);
        if($fecha_menor == $fecha_mayor)
            return true;
        else
            return false;
    }
       
       
        
    public static function getFechaDetalleSemanaDias($fecha){
        $dia = date('w', strtotime($fecha));
        
        $nrodia = date('d', strtotime($fecha));
        
        $mes = date('n', strtotime($fecha));
        $anio = date('Y', strtotime($fecha));
        
        $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        
        return $dias[$dia]." ". $nrodia . " de ".$meses[$mes]. " del ". $anio;
    
    }
    
    public static function devolverFechaMayor($fecha_menor, $fecha_mayor){
        $fechaMenor = new \DateTime($fecha_menor);
        $fechaMayor = new \DateTime($fecha_mayor);
        if($fechaMenor <= $fechaMayor)
            return $fecha_mayor;
        else
            return $fecha_menor;
    }
    
    /**
     * Suma la cantidad de meses parametrizada ala fecha dada     
     *
     * @param string $fecha  string Date en formato dd-mm-yyyy
     * @param string $cantMeses cantidad de meses a sumar a la fecha
     * 
     * @return date formato yyyy-mm-dd
     * 
     */   
    public static function sumarMeses($fecha, $cantMeses){
        $fecha = new \DateTime($fecha);    
        $fecha->add(new \DateInterval('P'.$cantMeses.'M'));
        return $fecha->format('Y-m-d');
    }
    
    public static function calcularDiasAFechaActual($fechaCalcular) {
        $dateHoy = new \DateTime(); 
        $dateHasta = new \DateTime($fechaCalcular);    
       
        $diff = $dateHoy->diff($dateHasta);
        // will output 2 days
        return $diff->days;
    }
    
       
    
     

}