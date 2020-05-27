<?php

namespace app\servicios;

use Yii;
use yii\base\Model;
use yii\web\HttpException;

use app\models\Persona;
use app\models\Establecimiento;
use app\models\DivisionEscolar;
use app\models\Responsable;
use app\models\GrupoFamiliar;

use app\helpers\GralException;

class GrupoFamiliarService extends \yii\base\Component
{
    
    public static function eliminarFamilia($idFamilia){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{
            
            $model = GrupoFamiliar::findOne($idFamilia);
            if(empty($model))
                throw new GralException('No se encontró el grupo familiar a eliminar');
            
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
            throw new HttpException(null, $e->getMessage());                
        }          
    }

    public static function cargarFamilia(GrupoFamiliar $model){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{    
            $modelNuevoGrupoFamiliar = new GrupoFamiliar;
            $modelNuevoGrupoFamiliar->setAttributes($model->attributes);
            
            if($modelNuevoGrupoFamiliar->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModelo'] = $modelNuevoGrupoFamiliar;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Carga incorrecta';
                \Yii::$app->getModule('audit')->data('sd', json_encode($modelNuevoGrupoFamiliar->errors)); 
                $response['error_models'] =   $modelNuevoGrupoFamiliar->errors; 
            }
            return $response;
        }catch (\Exception $e) {             
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new HttpException(null, $e->getMessage());                
        }        
    }
    
    public static function actualizarFamilia($idModeloViejo, GrupoFamiliar $dataModel){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{         
            $modelNuevo = GrupoFamiliar::findOne($idModeloViejo);            
            $modelNuevo->setAttributes($dataModel->attributes);
            if($modelNuevo->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Actualización correcta';    
                $response['nuevoModelo'] = $modelNuevo;   
            }else{
                $transaction->rollBack();
                $response['success'] = false;
                $response['mensaje'] = 'Actualización incorrecta';
                $response['error_models'] =   $modelNuevo->errors;            
            }
            return $response;
        }catch (\Exception $e) {    
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new HttpException(null, $e->getMessage());                
        }        
    }
    
    /***************************************************************/
    /***************************************************************/
    public static function asignarResponsableFamilia($idPersona, $idGrupoFamiliar, $idTiporesponsable, $cabecera){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{
            $existeIntegrante = Responsable::find()->andWhere(['id_grupofamiliar'=>$idGrupoFamiliar])
                    ->andWhere(['id_persona'=>$idPersona])->one();
            if($existeIntegrante)
                throw new GralException('Ya existe la persona asignada al Grupo Familiar');
            
            $modelResponsable = new Responsable();
            $modelResponsable->id_grupofamiliar= $idGrupoFamiliar;
            $modelResponsable->id_persona = $idPersona;
            $modelResponsable->id_tipo_responsable= $idTiporesponsable;
            $modelResponsable->cabecera = $cabecera;
            
            if($modelResponsable->save()){
                $transaction->commit();
                $response['success'] = true;
                $response['mensaje'] = 'Carga correcta';   
                $response['nuevoModelo'] = $modelResponsable;       
            }else{
                $transaction->rollBack();
                $response['mensaje'] = 'Carga incorrecta';
                \Yii::$app->getModule('audit')->data('sd', json_encode($modelResponsable->errors)); 
                $response['error_modelsResponsable'] =   $modelResponsable->errors; 
            }
                    
            return $response;            
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
    
    public static function cargarResponsable(Responsable $dataModelResponsable, Persona $dataModelPersona){        
        $transactionPersona = Yii::$app->db->beginTransaction(); 
        $transactionResponsable = Yii::$app->db->beginTransaction(); 
        try{
            $nuevaPersona  = new Persona();
            $nuevaPersona->setAttributes($dataModelPersona->attributes);
            $nuevoResponsable = new Responsable();
            $nuevoResponsable->setAttributes($dataModelResponsable->attributes);
            $nuevoResponsable->validate();
            
            if(!$nuevaPersona->save()){
                $transactionResponsable->rollBack();
                $transactionPersona->rollBack();
                $valid = false;
                $response['success'] = false;
                $response['error_modelsPersona'] =   $nuevaPersona->errors; 
                $response['error_modelsResponsable'] =   $nuevoResponsable->errors;                 
            }else{
                $transactionPersona->commit();                
                $nuevoResponsable->id_persona = $nuevaPersona->id;
                if($nuevoResponsable->save()){
                    $transactionResponsable->commit();
                    $response['success'] = true;
                    $response['mensaje'] = 'Carga correcta';   
                    $response['nuevoModelo'] = $nuevoResponsable;   
                }else{
                    $transactionResponsable->rollBack();
                    $response['success'] = false;
                    $response['mensaje'] = 'Carga incorrecta';
                    \Yii::$app->getModule('audit')->data('sd', json_encode($nuevoResponsable->errors)); 
                    $response['error_modelsPersona'] =   $nuevaPersona->errors; 
                    $response['error_modelsResponsable'] =   $nuevoResponsable->errors; 
                }
            }                    
            return $response;            
        }catch (\Exception $e) {
            (isset($transactionPersona) && $transactionPersona->isActive)?$transactionPersona->rollBack():'';
            (isset($transactionResponsable) && $transactionResponsable->isActive)?$transactionResponsable->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new HttpException(null, $e->getMessage());            
        }        
    }
    
    public static function actualizarResponsable($idResponsabe, Responsable $dataModelResponsable, Persona $dataModelPersona){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{
            $modelResponsable = Responsable::findOne($idResponsabe);
            if(empty($modelResponsable))
                throw new GralException('Responsable inexistente.');
            
            $modelPersona = Persona::findOne($modelResponsable->id_persona);
            if(empty($modelPersona))
                throw new GralException('Persona inexistente.');
            
            $modelPersona->setAttributes($dataModelPersona->attributes);
            $modelResponsable->setAttributes($dataModelResponsable->attributes);
            $modelPersona->validate();
            
            if(!$modelPersona->save()){
                $valid = false;
                $response['success'] = false;
                $response['error_modelsPersona'] =   $modelPersona->errors; 
                $response['error_modelsResponsable'] =   $modelResponsable->errors; 
                $transaction->rollBack();
            }else{
                if($modelResponsable->save()){
                    $transaction->commit();
                    $response['success'] = true;
                    $response['mensaje'] = 'Carga correcta';   
                    $response['nuevoModelo'] = $modelResponsable;   
                }else{
                    $transaction->rollBack();
                    $response['success'] = false;
                    $response['mensaje'] = 'Carga incorrecta';
                    \Yii::$app->getModule('audit')->data('sd', json_encode($modelResponsable->errors)); 
                    $response['error_modelsPersona'] =   $modelPersona->errors; 
                    $response['error_modelsResponsable'] =   $modelResponsable->errors; 
                }
            }
                    
            return $response;            
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
       
    public static function eliminarResponsable($idresponsable){        
        $transaction = Yii::$app->db->beginTransaction(); 
        try{
            $model = Responsable::findOne($idresponsable);
            if(empty($model))
                throw new GralException('No se encontró el responsable del grupo familiar a eliminar');
            
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
            throw new HttpException(null, $e->getMessage());                
        }          
    }
}