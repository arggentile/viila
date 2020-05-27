<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\servicios;

use Yii;
use yii\web\HttpException;

use app\models\Alumno;
use app\models\ServicioOfrecido;
use app\models\ServicioAlumno;
use app\models\EstadoServicio;
use app\helpers\GralException;
/**
 * Description of MovimientosFiscaliaFinderService
 *
 * @author agentile
 */
class ServicioOfrecidoServices {

    
    public static function eliminarServicioOfrecido($id){       
        try{
            $transaction = Yii::$app->db->beginTransaction(); 
            $model = ServicioOfrecido::findOne($id);
            if(empty($model))
                throw new GralException('No se encontró el Servicio a eliminar');
            
            $serviciosDevengados = ServicioAlumno::find()->andWhere(['id_servicio'=>$id])->all();
            if(count($serviciosDevengados)>0)
                throw new GralException('No se puede realizar la eliminación; el servicio dispone de servicios devengados.');
                
            $divisionesAsociadas = \app\models\ServicioDivisionEscolar::find()->andWhere(['id_servicio'=>$id])->all();
            if(count($divisionesAsociadas)>0)
                throw new GralException('No se puede realizar la eliminación; el servicio a sido asignado a Establecimientos para su posterior devengamiento.');
             
            if($model->delete()){
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
    
    public function cargarServicioOfrecido(\app\models\ServicioOfrecido $dataModel){
        $transaction = Yii::$app->db->beginTransaction();
        try{                   
            $nuevoModelo = new ServicioOfrecido;
            $nuevoModelo->setAttributes($dataModel->attributes);
            
            if($nuevoModelo->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModelo'] = $nuevoModelo;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';               
                $response['error_models'] =   $nuevoModelo->errors; 
            }
            return $response;
        }catch (\Exception $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new HttpException(null, $e->getMessage());                
        }
    }
    
    public function actualizarServicioOfrecido($idModelActualiar, \app\models\ServicioOfrecido $dataModel){
        $transaction = Yii::$app->db->beginTransaction(); 
        try{          
            $nuevoModelo =  ServicioOfrecido::findOne($idModelActualiar);
            
            $montoviejo = $nuevoModelo->importe;
            $montoviejohijo = $nuevoModelo->importe_hijoprofesor;
            
            $nuevoModelo->setAttributes($dataModel->attributes);
            $valid = true;
            $servicioDevengado = \app\models\ServicioAlumno::find()->joinWith(['servicio so'])->where('so.id='.$idModelActualiar)->all();
        
            if (($nuevoModelo->importe != $montoviejo) && (count($servicioDevengado)>0))  {
                $valid = false;
                $nuevoModelo->addError('importe', 'No se puede modificar el valor a un Servicio que ya a sido devengado.');
            }
            if (($nuevoModelo->importe_hijoprofesor!=$montoviejohijo) && (count($servicioDevengado)>0))  {
                $valid = false;
                $nuevoModelo->addError('importe_hijoprofesor', 'No se puede modificar el valor a un Servicio que ya a sido devengado.');
            }
            
            if($valid && $nuevoModelo->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Actualización correcta';   
                $response['nuevoModelo'] = $nuevoModelo;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Actualización incorrecta';               
                $response['error_models'] =   $nuevoModelo->errors; 
            }
            return $response;
        }catch (\Exception $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new HttpException(null, $e->getMessage());                
        }
    }
       
    
    public function asociarDivisionEscolarAlServicio($idServicio, $idDivision){
        try{
            $transaction = Yii::$app->db->beginTransaction();
            $modelServicio = \app\models\ServicioOfrecido::findOne($idServicio);
            if(!$modelServicio)
                throw new GralException('Servicio Ofrecido inexistente.');
            
            $modelDivision = \app\models\DivisionEscolar::findOne($idDivision);
            if(!$modelDivision)
                throw new GralException('Division escolar inexistente.');
            
            $modelServicioEstablecimiento = new \app\models\ServicioDivisionEscolar();
            $modelServicioEstablecimiento->id_servicio = $idServicio;
            $modelServicioEstablecimiento->id_divisionescolar = $idDivision;
            if($modelServicioEstablecimiento->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModelo'] = $modelServicioEstablecimiento;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';                
                $response['error_models'] =   $modelServicioEstablecimiento->errors; 
            }
            return $response;
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e) {            
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new yii\web\HttpException(500, $e->getMessage());
        }
    }
    
    public function quitarDivisionEscolarAlServicio($idServicio, $idDivision){
        try{
            $transaction = Yii::$app->db->beginTransaction();
            
            $modelServicio = \app\models\ServicioOfrecido::findOne($idServicio);
            if(empty($modelServicio))
                throw new GralException('Servicio Ofrecido inexistente.');
            
            $modelDivision = \app\models\DivisionEscolar::findOne($idDivision);
            if(empty($modelDivision))
                throw new GralException('Division escolar inexistente.');
            
             $modelServicioEscolar = \app\models\ServicioDivisionEscolar::find()
                   ->andWhere(['id_divisionescolar' => $idDivision])
                   ->andWhere(['id_servicio' => $idServicio])->one();
            if(!$modelServicioEscolar)
                throw new GralException('Servicino asociado.');
             
            if($modelServicioEscolar->delete()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModelo'] = $modelServicioEscolar;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';                
                $response['error_models'] =   $modelServicioEscolar->errors; 
            }
            return $response;
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e) {            
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new yii\web\HttpException(500, $e->getMessage());
        }  
        
        
    }
        
    /*************************************************************************/
    /*************************************************************************/
    /*
     * Realiza el devengamiento de servicio a los alummos que no lo tengas;
     * Si separametriza un id; se realiza el devengamiento para ese servicio;
     * sino se realiza los devengamientos para los servicios que se devengen autmaticamente en un periodo determinado
     */
    public function devengarServicio($idServicioOfrecido = null) {
        ini_set('memory_limit', '-1');
        ini_set('set_time_limite', '-1');
        ini_set('max_execution_time', '-1');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $connection= \Yii::$app->db; 
            
            $descuentoFamiliaAutomatico = Yii::$app->params['devenga.descuentosFamiliaresAutomatico'];
            
            $sql_servicio = "SELECT 
                        so.id as idservicioofrecido, 
                        se.id as idservicio, 
                        so.importe as montoservicio, 
                        so.importe_hijoprofesor as montoservicio_hijoprofesor , 
                        se.id_divisionescolar as divisionescolar,
                        a.id as idalumno,
                        a.id_divisionescolar as divisionescolar,
                        a.hijo_profesor as hijo_profesor,
                        B.canthijosfamilia as canthijosfamilia
                    FROM alumno a
                    INNER JOIN division_escolar AS de ON (a.id_divisionescolar=de.id)
                    INNER JOIN  servicio_divisionescolar AS se ON (se.id_divisionescolar = de.id)
                    INNER JOIN servicio_ofrecido AS so  ON (so.id = se.id_servicio)      
                    LEFT JOIN servicio_alumno AS sa ON (sa.id_servicio = so.id and sa.id_alumno = a.id)
                   
                    INNER JOIN 
                    (	SELECT al.id_grupofamiliar as id_familia, count(al.id_grupofamiliar) as canthijosfamilia FROM alumno al INNER JOIN grupo_familiar fam ON (al.id_grupofamiliar = fam.id) WHERE al.activo = '1' and (al.egresado='0' or egresado is null) GROUP BY al.id_grupofamiliar ORDER BY `id_familia` ASC
                    ) as B ON (B.id_familia = a.id_grupofamiliar)";
            
            $where = "WHERE (a.activo = '1' and (a.egresado='0' or egresado is null)) && sa.id is null";
            if($idServicioOfrecido !== null){
                $where.=" and (so.devengamiento_automatico = '1')  and so.id=".$idServicioOfrecido;    
            }
                      
            $sql_servicio.=$where;            
            $valid = true;

            $command_servicios  =  $connection->createCommand($sql_servicio);
            $servicios = $command_servicios->queryAll(); 
            if(count($servicios)==0){
                $txt_resultado = 'No se encontraron alumnos a devengar el servicio.';
            }else{
                 
                foreach ($servicios as $servicio) {
                    // chequeamos si ya se devengo la matricula                   
                    $modelServicio = new ServicioAlumno();
                    $modelServicio->id_alumno = $servicio['idalumno'];
                    $modelServicio->id_servicio = $idServicioOfrecido; // $servicio['idservicioofrecido'];
                    $modelServicio->id_estado = EstadoServicio::ID_ABIERTA ;
                    $modelServicio->importe_descuento = 0;
                    $modelServicio->importe_abonado = 0;                        
                    $modelServicio->fecha_otorgamiento = date('Y-m-d');
                    
                    if( $servicio['hijo_profesor']=='0' || $servicio['hijo_profesor']==0)
                        $modelServicio->importe_servicio = $servicio['montoservicio'];
                    else
                        $modelServicio->importe_servicio = $servicio['montoservicio_hijoprofesor'];

                    $valid = $valid && $modelServicio->save();
                    if(!$valid){
                        \Yii::$app->getModule('audit')->data('errorDevengando_errorModelServicioAlumno', \yii\helpers\VarDumper::dumpAsString($modelServicio->getErrors()));      
                    }
                    
                    $total_descuentos = 0;
                    
                    
                    //colocamos los descuentos aplicados a cada alumno en particular
                    $descuentosAlumno = \app\models\BonificacionAlumno::find()->where('id_alumno='. $servicio['idalumno'])->all();
                    
                    if($servicio['canthijosfamilia']>=4)
                            $bonificacionesFamiliares  = \app\models\Bonificaciones::find()
                                ->andWhere(['activa'=>'1'])
                                ->andWhere(['cantidad_hermanos'=>4])->one();
                    else
                        $bonificacionFamiliar  = \app\models\Bonificaciones::find()
                                ->andWhere(['activa'=>'1'])
                                ->andWhere(['cantidad_hermanos'=>$servicio['canthijosfamilia']])->one();
                    
                    if(!empty($descuentosAlumno)){
                        foreach($descuentosAlumno as $descuento) {
                            //
                            $modelDescuentoServicio = new \app\models\BonificacionServicioAlumno();
                            $modelDescuentoServicio->id_bonificacion = $descuento->id_bonificacion;
                            $bonificacion = \app\models\Bonificaciones::findOne($descuento->id_bonificacion); 
                            $modelDescuentoServicio->id_servicioalumno = $modelServicio->id;
                            $total_descuentos += $bonificacion->valor;
                            $valid = $valid && $modelDescuentoServicio->save();
                            if(!$valid){
                                \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($modelDescuentoServicio->getErrors()));      
                                }
                        }
                    }
                    
                     if(!empty($bonificacionFamiliar)){
                        $modelDescuentoServicio = new \app\models\BonificacionServicioAlumno();
                        $modelDescuentoServicio->id_bonificacion = $bonificacionFamiliar->id;
                        $modelDescuentoServicio->id_servicioalumno = $modelServicio->id;
                        $total_descuentos += $bonificacionFamiliar->valor;
                        $valid = $valid && $modelDescuentoServicio->save();
                        if(!$valid){
                            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($modelDescuentoServicio->getErrors()));      
                            }                        
                    }
                    
                    
                    $modelServicio->importe_descuento = ( $modelServicio->importe_servicio * $total_descuentos) / 100;
                    if($modelServicio->importe_descuento>=$modelServicio->importe_servicio)
                        $modelServicio->id_estado = EstadoServicio::ID_DESCONTADA;
                    $valid = $valid && $modelServicio->save();
                    if(!$valid){
                        \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($modelServicio->getErrors()));      
                    }
                }
            
                $txt_resultado = 'El servicio se devengo con exito.';
            }

            if ($valid){                
                $transaction->commit();
                $response['success'] = true;
                $response['model'] = null;
                $response['error'] = false;
                $response['mensaje'] = $txt_resultado;
            }else{
                if ($transaction->isActive)
                    $transaction->rollBack();
                $response['success'] = false;
                $response['model'] = null;
                $response['error'] = true;
                $response['detalle_error'] = $txt_resultado;
            } 

            return $response;
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e) {            
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new yii\web\HttpException(500, $e->getMessage());
        }  
    }
    
    
    public function quitarDevengarServicio($idServicioOfrecido) {
        ini_set('memory_limit', '-1');
        ini_set('set_time_limite', '300');
        ini_set('max_execution_time', 300);
        try {
            $transaction = Yii::$app->db->beginTransaction();
            $connection= \Yii::$app->db; 
            
            $valid = ServicioAlumno::deleteAll(
                    ['id_servicio'=> $idServicioOfrecido,
                    'importe_abonado' => '0',
                    'id_estado' => EstadoServicio::ID_ABIERTA]);
    
            if ($valid>0){                
                $transaction->commit();
                $response['success'] = true;
                $response['model'] = null;
                $response['error'] = false;
                $response['mensaje'] = 'El proceso de restauración de los devengamientos se realizo con exito';
            }else{
                if ($transaction->isActive)
                    $transaction->rollBack();
                $response['success'] = false;
                $response['model'] = null;
                $response['error'] = true;
                $response['detalle_error'] = 'Error al procesar la solicitud';
            }                
            

            return $response;
        } catch (Exception $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('catchedexc', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, "Error interno al procesar la solicitud.");
        }
    }
    
    
}
