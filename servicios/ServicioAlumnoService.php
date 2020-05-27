<?php

namespace app\servicios;

use Yii;
use yii\base\Model;

use app\models\ServicioOfrecido;
use app\models\ServicioAlumno;
use app\models\ServicioEstablecimiento;
use app\models\CategoriaBonificacion;
use app\models\BonificacionServicioAlumno;

use app\helpers\GralException;

class ServicioAlumnoService extends \yii\base\Component
{

    /*
     * Elimina un determinado ServioAlumno siguiendo una logicoa de eliminacion con 
     * saldo abonado igual a cero. Remueve las bonificaciones sobre el servicio en el caso de 
     * que existieran.
     * 
     * Si se parametriza y fuerza a su eliminacion se obvia la restriccion de que el saldo 
     * abonado sea nulo.
     */
    public static  function eliminarSevicioAlumno($idServicioAlumno, $forceDelete=false){           
        try{
            $transaction = Yii::$app->db->beginTransaction(); 
            $modelServicioAlumno = ServicioAlumno::findOne($idServicioAlumno);
            if(!$modelServicioAlumno)
                throw new GralException('Servicio Alumno inexistente.');
            
            
            if (($modelServicioAlumno->id_estado == \app\models\EstadoServicio::ID_ABIERTA)
                    &&   ($modelServicioAlumno->importe_abonado == 0) ){
               
                $bonificacionesServicioAlumno = $modelServicioAlumno->bonificacionServicioAlumnos;
               
                $valid = true;
                if(!empty($bonificacionesServicioAlumno)){
                    foreach($bonificacionesServicioAlumno as $bonificacion)
                        $valid = $valid && $bonificacion->delete();
                }
                if(!$valid)
                    \Yii::$app->getModule('audit')->data('errorAction', json_encode($bonificacion->getErrors()));     

                if($valid && $modelServicioAlumno->delete()){
                    $transaction->commit();
                    $response['success'] = true;
                    $response['mensaje'] = 'Eliminación correcta';
                    return $response;
                }else{
                    $transaction->rollBack();
                    $response['success'] = true;
                    $response['mensaje'] = 'Eliminación correcta';
                    return $response;
                }
              
            }else
                throw new GralException('No se puede eliminar el Servicio, el mismo esta en un estado incompatible o ya se a abonado.');
           
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new GralException($e->getMessage());            
        }
        catch (\Exception $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new HttpException(500, $e->getMessage());            
        }
    }
    
    
    
    public function editarServicioAlumno($idServicio, $dataModelServicio){
        $transaction = Yii::$app->db->beginTransaction(); 
        try{         
            $modelNuevo = ServicioAlumno::findOne($idServicio);            
            $modelNuevo->setAttributes($dataModelServicio->attributes);
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
    
    /*
     * Funcion que se encarga de dado un identificador de cliente (familia);
     * devulelve todos los servicios asociados al mismo; que esten sin abonar o 
     *  sin asociar a ningun convenio de pago
     * 
     * @params $familia   - identificador de la familia cliente
     * @params $servicios - array id de servicios a excluir
     */
    public static function getDevolverServiciosImpagosLibres(ServicioAlumno $dataSearch, $familia, array $idsEstadoServicios = null, array $servicios = null){ 
        try{
            $query = \app\models\ServicioAlumno::find();        
            $query->joinWith('miAlumno a');
        
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder'=>'id desc'],
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
        
            $query->andFilterWhere(['a.id' => $dataSearch->id_alumno]);
            $query->andFilterWhere(['id_servicio' => $dataSearch->id_servicio]);
            //$query->andFilterWhere(['a.id_grupofamiliar' => $familia]);
            
            $query->andFilterWhere(['a.id_grupofamiliar' => $familia]);
            
            if(!empty($idsEstadoServicios))
                $query->andFilterWhere(['in', 'id_estado', $idsEstadoServicios]);
            else
                $query->andFilterWhere(['=', 'id_estado', \app\models\EstadoServicio::ID_ABIERTA]);
                
            if(!empty($servicios))
                $query->andFilterWhere(['not in', ServicioAlumno::tableName(). '.id' ,  $servicios]);
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', json_encode($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e) {      
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new yii\web\HttpException(500, $e->getMessage());
        }    
        
        return $dataProvider;
    }
    
    
    /*
     * Retorna un query conteniendo los servicios impagos de una determinado grupo familiar.
     * El query presenta tanto los servicios impagos; como las cuotas impagas
     * de los convenios de pago
     */
    public static function devolverDeudaFamilia($idFamilia= null, $tipoServicio=null, $idsServiciosAlumno = null, $idsCuotas = null){
        
        $sqlServicios = "SELECT 
                        a.nrofamilia as idfamilia, 
                        sa.id as idservicio, 
                        sa.importe_servicio as importeservicio,
                        sa.importe_descuento as importedescuento,
                        sa.importe_abonado as importeabonado,
                        (sa.importe_servicio - sa.importe_descuento - sa.importe_abonado) as importeaabonar,
                        ". \app\models\DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS  . " as tiposervicio
                        FROM servicio_alumno sa 
                        INNER JOIN ( 
                            SELECT a.id as idalumno, f.id as nrofamilia, f.nro_tarjetacredito as nrotarjeta 
                             FROM alumno a INNER JOIN grupo_familiar f ON (f.id = a.id_grupofamiliar) 
                        ) a ON (a.idalumno = sa.id_alumno) 
                        INNER JOIN servicio_ofrecido so ON (so.id = sa.id_servicio)                     
                        INNER JOIN categoria_servicio_ofrecido cts ON (cts.id = so.id_categoriaservicio)";
        
        $whereServicio =  " WHERE (sa.id_estado = ". \app\models\EstadoServicio::ID_ABIERTA  .")";
        if($idFamilia){
            $whereServicio .= " and (a.nrofamilia = ". $idFamilia .")";
        }
       if(!is_null($idsServiciosAlumno))
            $whereServicio .= " and (sa.id in (". $idsServiciosAlumno ."))";
        $sqlCuotas = "
                    SELECT 
                        cp.id_familia as idfamilia, 
                        ccp.id as idservicio, 
                        ccp.monto as importeservicio,
                        0 as importedescuento,
                        0 as importeabonado,
                        (ccp.monto) as importeaabonar, 
                        ". \app\models\DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO . " as tiposervicio
                        FROM  cuota_convenio_pago ccp
                        INNER JOIN convenio_pago cp ON cp.id = ccp.id_conveniopago";
                        
        $whereCuotas = " WHERE (ccp.id_estado = ". \app\models\EstadoServicio::ID_ABIERTA  .")";
        
        if($idFamilia){
            $whereCuotas .= " and (cp.id_familia = ". $idFamilia .")";
        }
        if(!is_null($idsCuotas))
            $whereCuotas .= " and (cp.id in (". $idsCuotas ."))";
        $sqlDeuda = "SELECT * FROM (
                   ( " . $sqlServicios . $whereServicio .
                ")
                   UNION
                   (".
                $sqlCuotas . $whereCuotas .
                ") 
                ) as D";
       
            $queryCountDeuda = "SELECT COUNT(*) FROM ($sqlDeuda) as total";
            $queryCountDeuda = Yii::$app->db->createCommand($queryCountDeuda)->queryScalar();            
            
            $serviciosImpagos = new \yii\data\SqlDataProvider([
                'sql' => $sqlDeuda,   
                'key' => 'idservicio',
                'totalCount' => $queryCountDeuda,
                'pagination' => [
                    'pageSize' => 1500,
                ],
                'sort' => [
                    'attributes' => ['idservicio', 'idfamilia', 'importeservicio','importedescuento','importeabonado','importeaabonar','tiposervicio'],
                ],                    
            ]);
            return $serviciosImpagos;
    }
    
    
    
}