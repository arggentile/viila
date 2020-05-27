<?php

namespace app\servicios;

use Yii;
use yii\base\Model;
use yii\web\HttpException;

use app\models\Persona;
use app\models\Establecimiento;
use app\models\DivisionEscolar;

use app\helpers\GralException;

class EstablecimientoService extends \yii\base\Component
{
    
    public static function eliminarEstablecimiento($idEstablecimiento){        
        $transaction = Yii::$app->db->beginTransaction();
        try{ 
            $model = Establecimiento::findOne($idEstablecimiento);
            if(empty($model))
                throw new GralException('No se encontró el establecimiento a eliminar');
            
            $modelsDivisiones = $model->divisionEscolars;
            if(!empty($modelsDivisiones))
                throw new GralException('No se puede eliminar el Establecimiento, el mismo tiene divisiones escolares cargadas.');
            
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
        }catch(\Exception $e){
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));                  
            throw new HttpException(null, $e->getMessage());
        }       
    }

    public static function cargarEstablecimiento(Establecimiento $model){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{  
            $modelNuevoEstablecimiento = new Establecimiento;
            $modelNuevoEstablecimiento->setAttributes($model->attributes);           
            if($modelNuevoEstablecimiento->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModelo'] = $modelNuevoEstablecimiento;   
            }else{                
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';
                \Yii::$app->getModule('audit')->data('errorModelo', json_encode($modelNuevoEstablecimiento->errors)); 
                $response['error_models'] =   $modelNuevoEstablecimiento->errors; 
            }
            return $response;
        }catch (\Exception $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e)); 
            throw new HttpException(null, $e->getMessage());                
        }        
    }
    
    public static function actualizarEstablecimiento($idEstablecimientoViejo, Establecimiento $modelEstablecimiento){
        $transaction = Yii::$app->db->beginTransaction(); 
        try{
            $model = Establecimiento::findOne($idEstablecimientoViejo);            
            $model->setAttributes($modelEstablecimiento->attributes);
            if($model->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Actualización correcta';    
                $response['nuevoModelo'] = $model;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                \Yii::$app->getModule('audit')->data('errorModelo', json_encode($model->errors)); 
                $response['mensaje'] = 'Actualización incorrecta';
                $response['error_models'] =   $model->errors;            
            }
            return $response;
        }catch (\Exception $e) {  
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e)); 
            throw new HttpException(null, $e->getMessage());                
        }        
    }
 
    
    /***************************************************************/
    public static function eliminarDivisionEscolar($idDivisionEscolar){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{
            $model = DivisionEscolar::findOne($idDivisionEscolar);
            if(empty($model))
                throw new GralException('No se encontró el registro de division a eliminar.');
            
            $alumnosDivision = \app\models\Alumno::find()->andWhere(['id_divisionescolar' =>$idDivisionEscolar])->all();
            if(count($alumnosDivision)>0)
                throw new GralException('No se puede realizar le eliminación; existen alumnos cargados.');
                
            if($model->delete()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Eliminación correcta';
                return $response;
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Eliminación erronea';
                \Yii::$app->getModule('audit')->data('errorModelo', json_encode($model->errors)); 
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
            throw new HttpException(null, $e->getMessage());                
        }          
    }
    
    public static function cargarDivisionEscolar(DivisionEscolar $model){        
        try{            
            $transaction = Yii::$app->db->beginTransaction(); 
            
            $modelNuevaDivision = new DivisionEscolar;
            $modelNuevaDivision->setAttributes($model->attributes);            
            if($modelNuevaDivision->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModelo'] = $modelNuevaDivision;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';
                \Yii::$app->getModule('audit')->data('errorModelo', json_encode($modelNuevaDivision->errors)); 
                $response['error_models'] =   $modelNuevaDivision->errors; 
            }
            return $response;
        }catch (\Exception $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e)); 
            throw new HttpException(null, $e->getMessage());                
        }        
    }
    
    public static function actualizarDivisionEscolar($idModeloActualizar, DivisionEscolar $dataModel){
        
        try{
            $transaction = Yii::$app->db->beginTransaction(); 
            
            $model = DivisionEscolar::findOne($idModeloActualizar);            
            $model->setAttributes($dataModel->attributes);
            if($model->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Actualización correcta';    
                $response['nuevoModelo'] = $model;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Actualización incorrecta';
                \Yii::$app->getModule('audit')->data('errorModelo', json_encode($model->errors)); 
                $response['error_models'] =   $model->errors;            
            }
            return $response;
        }catch (\Exception $e) {             
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
           \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e)); 
            throw new HttpException(null, $e->getMessage());                
        }        
    }
}