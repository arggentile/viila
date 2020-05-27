<?php

namespace app\components;

use Yii;
use yii\base\Model;

use app\models\Cuentas;
use app\models\MovimientoCuenta;
use app\models\TipoMovimientoCuenta;
use app\models\ServicioOfrecido;
use app\models\ServicioAlumno;
use app\models\ServicioEstablecimiento;
use app\models\CategoriaBonificacion;
use app\models\BonificacionServicioAlumno;


class ServicioCajaComponents extends \yii\base\Component
{
    
    /*************************************************************/
    /*************************************************************/
    /*
     * Metodo que se encarga de recibir una serie de parametros destinados
     * a acentar los movimientos realiozados por las operaciones de cobro de servicios
     * y las operaciones de sacado de plata
     */
    public static function AcentarMovimientosCaja($idcuenta, $tipooperacion, $importe, $detalleMovimiento, $fechaoperacion, $comentario = '', $tipopago, $id_servicio) {
        
        try {
            $modelCuenta = Cuentas::findOne($idcuenta); 

            if (!empty($modelCuenta)) {
                $modelMovimientos = new MovimientoCuenta();
      
                if ($tipooperacion == Cuentas::IDtipo_moviento_ingreso) {
                    $modelCuenta->saldo_actual += $importe;
                }elseif ($tipooperacion == self::IDtipo_moviento_egreso){
                    $modelCuenta->saldo_actual -= $importe;
                }
        
                $modelMovimientos->id_cuenta = $modelCuenta->id;
                $modelMovimientos->tipo_movimiento = $tipooperacion;
                $modelMovimientos->detalle_movimiento = $detalleMovimiento;
                $modelMovimientos->importe = $importe;                
                $modelMovimientos->fecha_realizacion =$fechaoperacion;               
                $modelMovimientos->comentario = $comentario;                
                
                $modelMovimientos->id_tipopago = $tipopago;                
         
                $modelMovimientos->id_hijo = $id_servicio;
            
                if ($modelCuenta->save() && $modelMovimientos->save())
                    return $modelMovimientos->id;
                else{        
                    $error = $modelMovimientos->getErrors();                    
                    return false;
                }
            }
            else {
                return false;
            }
        } catch (\Exception $e) {    
            \Yii::$app->getModule('audit')->data('errorAction', json_encode($e)); 
            return false;
        }
    }    
    

    
    
}