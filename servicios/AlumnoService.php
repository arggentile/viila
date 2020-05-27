<?php

namespace app\servicios;

use Yii;
use yii\base\Model;


use app\models\Persona;
use app\models\Alumno;
use app\models\Establecimiento;
use app\models\DivisionEscolar;
use app\models\Responsable;
use app\models\GrupoFamiliar;

use app\helpers\GralException;
use yii\web\HttpException;
class AlumnoService extends \yii\base\Component
{    
    
    public static function eliminarAlumno($id){        
        try{
            $transaction = Yii::$app->db->beginTransaction(); 
            $model = Alumno::findOne($id);
            if(empty($model))
                throw new GralException('No se encontró el Alumno a eliminar');
            
            $bonificacionesAlumno = \app\models\BonificacionAlumno::find()->andWhere(['id_alumno'=>$id])->all();
            if(!empty($bonificacionesAlumno)){
                throw new GralException('No se puede realizar la eliminación, el alumno posee bonificaciones asignadas.');
            }
            
            $serviciosAlumno = \app\models\ServicioAlumno::find()->andWhere(['id_alumno'=>$id])->all();
            if(!empty($serviciosAlumno)){
                throw new GralException('No se puede realizar la eliminación, el alumno posee servicios asignados(devengados).');    
            }
            
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
            \Yii::$app->getModule('audit')->data('errorAction', json_encode($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e) {      
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new yii\web\HttpException(500, $e->getMessage());
        }              
    }

    public static function inactivarAlumno($idAlumno){        
        try{
            $transaction = Yii::$app->db->beginTransaction();
            $model = Alumno::findOne($idAlumno);
            if(empty($model))
                throw new GralException('No se encontró el Alumno a INACTIVAR con el identificador dado.');
            
            $model->activo ='0';
            if($model->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Operación éxitosa';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Operación erronea';
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
            throw new yii\web\HttpException(500, $e->getMessage());
        }              
    }
    
    public static function activarAlumno($idAlumno){        
        try{
            $transaction = Yii::$app->db->beginTransaction();
            $model = Alumno::findOne($idAlumno);
            if(empty($model))
                throw new GralException('No se encontró el Alumno a ACTIVAR con el identificador dado.');
            
            $model->activo ='1';
            if($model->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Operación éxitosa';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Operación erronea';
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
            throw new yii\web\HttpException(500, $e->getMessage());
        }              
    }
      
    
    public static function cargarAlumno(Alumno $dataModelAlumno, Persona $dataModelPersona){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{            
            $nuevaPersona = new Persona();
            $nuevaPersona->setAttributes($dataModelPersona->attributes);
                        
            $nuevoAlumno = new Alumno();
            $nuevoAlumno->setAttributes($dataModelAlumno->attributes);
            
            if(!$nuevaPersona->save()){
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';              
                $response['error_modelPersona'] =   $nuevaPersona->errors; 
                $nuevoAlumno->validate();
                $response['error_modelAlumno'] =   $nuevoAlumno->errors;                
            }else{
                $nuevoAlumno->id_persona = $nuevaPersona->id;
                if($nuevoAlumno->save()){
                    $transaction->commit();
                    $response['success'] = true;
                    $response['mensaje'] = 'Carga correcta';   
                    $response['nuevoModeloAlumno'] = $nuevoAlumno;  
                    $response['nuevoModeloPersona'] = $nuevaPersona; 
                }else{
                    $transaction->rollBack();
                    $response['success'] = false;                    
                    $response['error_modelPersona'] =   $nuevaPersona->errors; 
                    $response['error_modelAlumno'] =   $nuevoAlumno->errors;                    
                }
            }
            return $response;
        }catch (\Exception $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new HttpException(null, $e->getMessage());                
        }        
    }
    
    public static function actualizarAlumno($idAlumno, Alumno $dataModelAlumno, Persona $dataModelPersona){        
        try{            
            $transaction = Yii::$app->db->beginTransaction();   
            
            $nuevoAlumno =  Alumno::findOne($idAlumno);
            if(empty($nuevoAlumno))
                throw new GralException('No se encontró el Alumno a actualizar los datos con el identificador dado.');       
            
            $nuevaPersona = Persona::findOne($nuevoAlumno->id_persona);
            if(empty($nuevaPersona))
                throw new GralException('No se encontró los datos de la persona a actualizar con el identificador dado.');
            
            $nuevaPersona->setAttributes($dataModelPersona->attributes);
            $nuevoAlumno->setAttributes($dataModelAlumno->attributes);
            
            if(!$nuevaPersona->save()){
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';
                
                $response['error_modelPersona'] =   $nuevaPersona->errors; 
                $nuevoAlumno->validate();
                $response['error_modelAlumno'] =   $nuevoAlumno->errors; 
                
            }else{                
                if($nuevoAlumno->save()){
                    $transaction->commit();
                    $response['success'] = true;
                    $response['mensaje'] = 'Carga correcta';   
                    $response['nuevoModeloAlumno'] = $nuevoAlumno;  
                    $response['nuevoModeloPersona'] = $nuevaPersona; 
                }else{
                    $transaction->rollBack();
                    $response['success'] = false;                   
                    $response['error_modelPersona'] =   $nuevaPersona->errors; 
                    $response['error_modelAlumno'] =   $nuevoAlumno->errors;                     
                }
            }
            return $response;
        }catch (\Exception $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new HttpException(null, $e->getMessage());                
        }        
    }
    
    /*****************************************************************/
    public static function asignarBonificacion($idAlumno, \app\models\BonificacionAlumno $dataModelBonificacion){        
        try{
            $transaction = Yii::$app->db->beginTransaction();
            
            $model = Alumno::findOne($idAlumno);
            if(empty($model))
                throw new GralException('No se encontró el Alumno asignar la bonificación con el identificador dado.');
            
            $bonificacionesAlumno = \app\models\BonificacionAlumno::find()
                    ->andWhere(["id_alumno"=> $idAlumno])
                   ->andWhere(['id_bonificacion'=>$dataModelBonificacion->id])->all();
                    
            if(!empty( $bonificacionesAlumno )){
                throw new GralException('No se puede asignar dos mismas bonificaciones.');
            }
            $nuevaBonificacion = new \app\models\BonificacionAlumno();
            $nuevaBonificacion->setAttributes($dataModelBonificacion->attributes);
           
            if($nuevaBonificacion->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Operación éxitosa';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Operación erronea';
                $response['error_models'] =   $nuevaBonificacion->errors; 
                return $response;
            }
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
    
    public static function eliminarBonificacion($idBonificacion){        
        try{
            $transaction = Yii::$app->db->beginTransaction();
                         
            $modelBonificacion = \app\models\BonificacionAlumno::findOne($idBonificacion);
            if(empty($modelBonificacion))
                throw new GralException('No se encontró la bonificación a eliminar.');
            
            
            if($modelBonificacion->delete()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Operación éxitosa';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Operación erronea';
                $response['error_models'] =   $modelBonificacion->errors; 
                return $response;
            }
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
    
    /*****************************************************************/
    public static function egresarAlumno($idAlumno, $fecha_egreso){        
        try{
           
            $transaction = Yii::$app->db->beginTransaction();
            $model = Alumno::findOne($idAlumno);
            if(empty($model))
                throw new GralException('No se encontró el Alumno a EGRESAR.');
            
            $modelHistoriaEgreso = new \app\models\HistoriaEgresosAlumno();
            $modelHistoriaEgreso->id_alumno = $model->id;
            $modelHistoriaEgreso->id_division_actual = $model->id_divisionescolar;
            $modelHistoriaEgreso->fecha = \app\helpers\Fecha::formatear($fecha_egreso, 'd-m-Y', 'Y-m-d');
            
            $model->egresado = '1';
            $model->activo = '0';
            $model->xfecha_egreso = $fecha_egreso;
            if($model->save() && $modelHistoriaEgreso->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Operación éxitosa';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Operación erronea';
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
            throw new yii\web\HttpException(500, $e->getMessage());
        }      

    }
    public static function egresarAlumnoDivision($idAlumno, $idDivision, $fecha_egreso){        
        try{
            $transaction = Yii::$app->db->beginTransaction();
            $model = Alumno::findOne($idAlumno);
            if(empty($model))
                throw new GralException('No se encontró el Alumno a migrar de Division Escolar.');
            
            $modelDivision = DivisionEscolar::findOne($idDivision);
            if(empty($modelDivision))
                throw new GralException('No se encontró la Division a migrar el alumno');
            
            $model->id_divisionescolar = $idDivision;
            
            $modelHistoriaEgreso = new \app\models\HistoriaEgresosAlumno();
            $modelHistoriaEgreso->id_alumno = $model->id;
            $modelHistoriaEgreso->id_division_actual = $model->id_divisionescolar;
            $modelHistoriaEgreso->id_division_egreso = $idDivision;            
            $modelHistoriaEgreso->fecha = \app\helpers\Fecha::formatear($fecha_egreso, 'd-m-Y', 'Y-m-d');
            
            if($model->save() && $modelHistoriaEgreso->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Operación éxitosa';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Operación erronea';
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
            throw new yii\web\HttpException(500, $e->getMessage());
        }              
    }
}