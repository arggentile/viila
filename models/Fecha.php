<?php

namespace app\models;

class Fecha{
    
    /**
     * Check if a string is a valid date(time)
     *
     * DateTime::createFromFormat requires PHP >= 5.3
     *
     * @param string $str_date
     * @param string $str_dateformat
     * @param string $str_timezone (If timezone is invalid, php will throw an exception)
     * @return bool
     */
    static function  esFechaValida($str_date, $str_dateformat) {
      $date = \DateTime::createFromFormat($str_dateformat, $str_date);
      return $date && \DateTime::getLastErrors()["warning_count"] == 0 && \DateTime::getLastErrors()["error_count"] == 0;
    }
    
    static function convertirFecha($fecha,$formato_entrada,$formato_salida){
        if (self::esFechaValida($fecha, $formato_entrada))
            return \DateTime::createFromFormat($formato_entrada, $fecha)->format($formato_salida);
        else
            return "";
    }
    
    static function esFechaMayor($fechaMenor, $fechaMayor){
        $f_menor = new \DateTime($fechaMenor);
        $f_mayor = new \DateTime($fechaMayor);
        $dif = $f_menor->diff($f_mayor);
        $ll = (int) $dif->format('%R%a');
        if ($ll>=0)
            return true;
        else
            return false;
      
    }
    
    
}
