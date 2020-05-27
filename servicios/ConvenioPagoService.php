<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\servicios;

use Yii;
use yii\web\HttpException;

use app\models\ConvenioPago;
use app\models\CuotaConvenioPago;
use app\models\ServicioConvenioPago;

use app\models\GrupoFamiliar;
use app\models\Alumno;
use app\models\ServicioOfrecido;
use app\models\ServicioAlumno;
use app\models\EstadoServicio;
use app\models\DebitoAutomatico;
use app\models\ServicioDebitoAutomatico;
use app\models\FormaPago;

use app\helpers\GralException;

/**
 *
 * @author agentile
 */
class ConvenioPagoService {

    
    
    public static function eliminarConvenio($id){        
        try{
            $transaction = Yii::$app->db->beginTransaction(); 
            $model = ConvenioPago::findOne($id);
            if(empty($model))
                throw new GralException('No se encontró el Convenio de Pago a eliminar');
            
            $valid  = true;
            
            $cuotasPendientesConvenio =$model->sePuedeEliminar();
            if($cuotasPendientesConvenio==0)
                throw new GralException('No se puede realizar la eliminación, el convenio de pago dispone de cuotas ABONADAS o en DEBITO AUTOMATICO.');
            
            $cuotasConvenio = CuotaConvenioPago::find()->andWhere(['id_conveniopago'=>$id])->all();
            if(!empty($cuotasConvenio))
                foreach($cuotasConvenio as $cuota)
                    if(!$cuota->delete())
                        throw new GralException('Error al eliminar las cuotas del Convenio de Pago.');
            
            $serviciosConvenio = ServicioConvenioPago::find()->andWhere(['id_conveniopago'=>$id])->all();
            if(!empty($serviciosConvenio)){
                foreach($serviciosConvenio as $servicio){
                    $modelServicioAlumno = \app\models\ServicioAlumno::findOne($servicio->id_servicio);                         
                    $modelServicioAlumno->id_estado = \app\models\EstadoServicio::ID_ABIERTA;
                    $valid = $valid && $modelServicioAlumno->save();
                }
                $valid =$valid && ServicioConvenioPago::deleteAll('id_conveniopago='.$id); 
            }
            
            //eliminamos las cuotas
            
            
            if($valid && $model->delete()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Eliminación correcta';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Eliminación erronea';
                $response['error_models'] =   $model->errors; 
                return $response;
            }
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new GralException($e->getMessage());            
        }catch (\Exception $e) {      
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, $e->getMessage());
        }              
    }
    
    
    public function altaConvenioPago(ConvenioPago $dataModelConvenioPago, $dataServiciosConvenioPago = null, $dataCuotasConvenioPago = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
            
            $modelNewConvenioPago = new ConvenioPago();
            $modelNewConvenioPago->setAttributes($dataModelConvenioPago->attributes);
            $valid = false;
            
            if($modelNewConvenioPago->save()){
                $totalcuotas = 0;
                $nrocuota = 1;
                $valid = true;
                        
                foreach($dataCuotasConvenioPago as $cuota){
                    $modelNuevaCuota = new CuotaConvenioPago();
                    $modelNuevaCuota->setAttributes($cuota->attributes);
                    $cuota->id_conveniopago = $modelNewConvenioPago->id;
                    $cuota->nro_cuota = $nrocuota;
                    $cuota->importe_abonado='0';
                    $cuota->id_estado= \app\models\EstadoServicio::ID_ABIERTA;
                    $nrocuota+=1;
                    $totalcuotas+= ($cuota->monto)?$cuota->monto:0;
                    $valid = $valid && $cuota->save();
                }      
                    
                if($modelNewConvenioPago->saldo_pagar != $totalcuotas){
                    $valid = false;
                    $modelNewConvenioPago->addError('saldo_pagar','El saldo a pagar debe coincidir con el monto total de las cuotas!!!');
                }

                if(!empty($dataServiciosConvenioPago)){
                    foreach($dataServiciosConvenioPago as $idservicio){
                        $modelServicioAlumno = \app\models\ServicioAlumno::findOne($idservicio);
                        $modelServicioAlumno->id_estado = \app\models\EstadoServicio::ID_EN_CONVENIOPAGO;
                        $modelServicioCP = new ServicioConvenioPago();
                        $modelServicioCP->id_conveniopago = $modelNewConvenioPago->id;
                        $modelServicioCP->id_servicio = $modelServicioAlumno->id;
                        $valid = $valid && $modelServicioCP->save() && $modelServicioAlumno->save();                   
                    }
                }
            }
            
            if($valid){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModeloConvenioPago'] = $modelNewConvenioPago;
            }else{
                $transaction->rollBack();
                    $response['success'] = false;
                    $response['error_modelConvenioPago'] =   $modelNewConvenioPago->errors; 
                                 
            }
            
            return $response;
            
            
            
        }catch (GralException $e) {   
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e){           
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, $e->getMessage());
        }  
        
    }
    
    
    
    /***********************************************************************/
    private function esCuotaAEliminar($modelCuota, array $modlsNuevasCuotas){
        try{
            $return  = true;
            if(!empty($modlsNuevasCuotas))
                foreach($modlsNuevasCuotas as $cuota)
                    if($cuota->id == $modelCuota->id )
                        $return  = false;
            return $return;
        }catch (GralException $e) {   
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e){           
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, $e->getMessage());
        }  
        
    }
    
    
    public function editarConvenioPago($idConvenio, ConvenioPago $dataModelConvenioPago, $dataServiciosConvenioPago = null, $dataCuotasConvenioPago = null)
    {
        
        $transaction = Yii::$app->db->beginTransaction();
        try{
            
            $modelNewConvenioPago = ConvenioPago::findOne($idConvenio);
            $modelNewConvenioPago->setAttributes($dataModelConvenioPago->attributes);
            $valid = false;
            
            if($modelNewConvenioPago->save()){
                $totalcuotas = 0;
                $nrocuota = 1;
                $valid = true;
                
                $cuotasAnteriores = CuotaConvenioPago::find()->andWhere(['id_conveniopago'=>$modelNewConvenioPago->id])->all();
                foreach ($cuotasAnteriores as $cuota){
                    $seElimina = $this->esCuotaAEliminar($cuota, $dataCuotasConvenioPago);
                    if($seElimina)
                        $valid = $valid && $cuota->delete();
                }
                
                foreach($dataCuotasConvenioPago as $cuota){
                    if(empty($cuota->id)){
                        $modelNuevaCuota = new CuotaConvenioPago();
                    }
                    else
                        $modelNuevaCuota = CuotaConvenioPago::findOne($cuota->id);
                    
                    if( $modelNuevaCuota->isNewRecord || 
                            (!$modelNuevaCuota->isNewRecord && $modelNuevaCuota->id_estado == \app\models\EstadoServicio::ID_ABIERTA)){
                        $modelNuevaCuota->setAttributes($cuota->attributes);
                        $cuota->id_conveniopago = $modelNewConvenioPago->id;
                        $cuota->nro_cuota = $nrocuota;

                        if($modelNuevaCuota->isNewRecord)
                            $cuota->importe_abonado='0';


                        $cuota->id_estado= \app\models\EstadoServicio::ID_ABIERTA;
                    }
                    $nrocuota+=1;
                    $totalcuotas+= ($cuota->monto)?$cuota->monto:0;
                    $valid = $valid && $cuota->save();
                
                }
                    
                if($modelNewConvenioPago->saldo_pagar != $totalcuotas){
                    $valid = false;
                    $modelNewConvenioPago->addError('saldo_pagar','El saldo a pagar debe coincidir con el monto total de las cuotas!!!');
                }
                
                //* manejo de lo servicos - samos los viejos y colomas los nuevos */
                $modlsServiciosViejos = ServicioConvenioPago::find()->andWhere(['id_conveniopago'=>$modelNewConvenioPago->id])->all();
                if(!empty($modlsServiciosViejos)){
                    foreach($modlsServiciosViejos as $modelServicioViejo){
                        $modelServicioAlumno = \app\models\ServicioAlumno::findOne($modelServicioViejo->id_servicio);
                        $modelServicioAlumno->id_estado = EstadoServicio::ID_ABIERTA;
                        $valid = $valid && $modelServicioAlumno->save() && $modelServicioViejo->delete();
                    }
                }

                if(!empty($dataServiciosConvenioPago)){
                    foreach($dataServiciosConvenioPago as $idservicio){
                        $modelServicioAlumno = \app\models\ServicioAlumno::findOne($idservicio);
                        $modelServicioAlumno->id_estado = \app\models\EstadoServicio::ID_EN_CONVENIOPAGO;
                        $modelServicioCP = new ServicioConvenioPago();
                        $modelServicioCP->id_conveniopago = $modelNewConvenioPago->id;
                        $modelServicioCP->id_servicio = $modelServicioAlumno->id;
                        $valid = $valid && $modelServicioCP->save() && $modelServicioAlumno->save();                   
                    }
                }
            }
            
            if($valid){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModeloConvenioPago'] = $modelNewConvenioPago;
            }else{
                $transaction->rollBack();
                    $response['success'] = false;
                    $response['error_modelConvenioPago'] =   $modelNewConvenioPago->errors; 
                                 
            }
            
            return $response;
            
            
            
        }catch (GralException $e) {   
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e){           
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, $e->getMessage());
        }  
        
    }
}
