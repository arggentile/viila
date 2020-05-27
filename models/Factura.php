<?php

namespace app\models;

use Yii;
use \app\models\base\Factura as BaseFactura;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "factura".
 */
class Factura extends BaseFactura
{
    public $ptovta;

 
    
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'bedezign\yii2\audit\AuditTrailBehavior'
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                ['ptovta','safe']
            ]
        );
    }
    
    
    /*
     * @params ptoVta
     * @params tipodoc  - tipo documento del cliente a generar la factura
     * @params nrodoc   - numero del documento a informaralaafip
     * @params monto    - monto de la factura abonado
     * @params fecha_factura  - fecha de la realizacion del pago  - formato Y-m-d
     * @params tike  - id del tiket asociado a la genracion de la factura 
     */
    
    //a los fines practicos se debe implementar el tipo de dni cuando melo avisen
    public static function generaFactura($ptovta, $monto, $fecha_factura, $idtiket) {      
        
        $transaction = Yii::$app->db->beginTransaction();
        try{
          
            $hoyDate = date('Y-m-d');
            //primero generamos la factura y luego nos comunicamos con la AFIP
            //a la cuenta de registrar su exito o fracaso y registrar loslogs de erroes de factura
            $modelFactura = new Factura();
            $modelFactura->ptovta = (string) $ptovta;
            $modelFactura->fecha_factura = $fecha_factura;
            $modelFactura->informada = '0';
            $modelFactura->fecha_informada = $hoyDate;
            $modelFactura->monto = $monto;
            $modelFactura->cae = '0';
            $modelFactura->nroFactura = '0';
            $modelFactura->id_tiket = $idtiket;
            
            if($modelFactura->save()){               
                $transaction->commit();
                $response['success']=true;
                $response['modelFactura'] = $modelFactura;
            }else{
                $transaction->rollBack();
                $response['success']=false;
                \Yii::$app->getModule('audit')->data('errorModeloFactura', \yii\helpers\VarDumper::dumpAsString($modelFactura->errors));  
                $response['errosModelFactura'] = $modelFactura->errors;
            }   
             
            return $response;
        }catch (\Exception $e){
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new HttpException(500, $e->getMessage());               
        }   
    }
    
    public static function avisarAfip($idfactura, $ptovta, $tipodoc, $nrodoc, $monto, $fecha_factura){
        try{
            $hoyDate = date('Y-m-d');
            $hoyDateTime= date('Y-m-d H:i:s');
            
            $transactionFactura = Yii::$app->db->beginTransaction();
            $modelFactura = Factura::findOne($idfactura);
            if(!$modelFactura)
                throw new HttpException('No se encuentra la factura para avisar a la AFIP');
            
            $facturaAfip = new \app\servicios\afip\FacturaAfipService($ptovta);
            $facturaAfip->tipoDoc = $tipodoc;
            $facturaAfip->nroDoc = str_replace("-", "", (str_replace(".", "", $nrodoc)));
            $facturaAfip->monto = $monto;        
            $valid = false;
                
            if ($facturaAfip->conerror === FALSE) {
                $facturaAfip->generaFactura();
                
                if($facturaAfip->conerror || !empty($facturaAfip->errores)){
                    $valid = false;
                }else 
                if ($facturaAfip->nroCae > 0) {
                  
//                    $fechaVencimientoCae = $facturaAfip->fechaVtoCae;
//                    $fechaVencimientoCae = substr($fechaVencimientoCae, 6, 2) . "-" . substr($fechaVencimientoCae, 4, 2) . "-" . substr($fechaVencimientoCae, 0, 4);
//                    $fechaVencimientoCae = \app\helpers\Fecha::formatear($fechaVencimientoCae,'d-m-Y','Y-m-d');

                    $modelFactura->cae = $facturaAfip->nroCae;
                    $modelFactura->nroFactura = (string) $facturaAfip->nroFactura;
                    $modelFactura->informada='1';
                    if ($modelFactura->save()) {                    
                       $valid = true;
                       $transactionFactura->commit();
                    }else
                        \Yii::$app->getModule('audit')->data('errorModeloFactura', \yii\helpers\VarDumper::dumpAsString($modelFactura->errors));  
                }
            }
            
            if($valid){
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['modelsFactura'] = $modelFactura;
            }else{
                    (isset($transactionFactura) && $transactionFactura->isActive)?$transactionFactura->rollBack():'';
                    $transactionLogs = Yii::$app->db->beginTransaction();
                    $logs = new LogFactura();
                    $logs->fecha_prueba = $hoyDateTime;
                    $logs->id_factura  = $modelFactura->id;
                    $logs->error  = json_encode($facturaAfip->errores);
                    if(!$logs->save())
                        \Yii::$app->getModule('audit')->data('errorModeloLogsFactura', \yii\helpers\VarDumper::dumpAsString($logs->errors));  
                    $transactionLogs->commit();
                    
                $response['success'] = false;
                $response['mensaje'] = 'Error';   
                //$response['modelsFactura'] = $modelFactura;                
            }       
                    
            return $response;
        }catch (\Exception $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new HttpException(500, $e->getMessage());            
        }   
    }

    
    
    /*     * ************************************************************ */

    public function getMiNroFactura() {
        if (!empty($this->id_tiket)) {
            return "000" . $this->ptovta . "-" . str_pad($this->nroFactura, 8, "0", STR_PAD_LEFT);
        } else
            return "";
    }

    public function getCantServiciosPagos() {
        if (!empty($this->id_tiket)) {
            $cantServicios = ServiciosAbogado::model()->findAll('id_tiket=' . $this->id_tiket);
            if (!empty($cantServicios)) {
                return "<span class='label label-warning'>" . count($cantServicios) . "</span>";
            } else {

                return "<span class='label label-warning'>1</span>";
            }
        } else
            return "";
    }
    
    public function getMiTiket() {
        return $this->hasOne(\app\models\Tiket::className(), ['id' => 'id_tiket']);        
    }
    
    public function getMiCliente() {
        $modelTiket = Tiket::findOne($this->id_tiket);
        $modelAbogado = Abogado::findOne($modelTiket->id_cliente);
        
        return $modelAbogado->getMisDatos();
        
    }
    
    
}
