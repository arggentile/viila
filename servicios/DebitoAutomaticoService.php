<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\servicios;

use Yii;

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
class DebitoAutomaticoService {    
    
    public static function eliminarDebitoAutomatico($id){        
        try{
            $transaction = Yii::$app->db->beginTransaction(); 
            $model = DebitoAutomatico::findOne($id);
            if(empty($model))
                throw new GralException('Modelo no encontrado para su eliminación');
            
            if(!$model->getSePuedeEliminar())
                throw new GralException('No se puede eliminar el Debito, el mismo ya fue procesado.');
            
            $valid = true;
            $modelsRegistrosDebito = \app\models\DebitoAutomaticoRegistro::deleteAll(['id_debitoautomatico'=>$model->id]);
            
            $serviciosDebitoAutomatico = ServicioDebitoAutomatico::find()->andWhere(['id_debitoautomatico'=>$id])->all();
            if(!empty($serviciosDebitoAutomatico)){
                foreach($serviciosDebitoAutomatico as $serDA){
                    /* @var ServicioDebitoAutomatico $serDA */
                    if($serDA->tiposervicio == DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS){
                        $modelServicioAlumno = ServicioAlumno::findOne($serDA->id_servicio);
                        if(empty($modelServicioAlumno))
                            throw new GralException('No se encuentra el servicio dentro del Debito a cambiar su estado');
                        
                        $modelServicioAlumno->id_estado = EstadoServicio::ID_ABIERTA;
                        if(!$modelServicioAlumno->save()){
                            $valid = false;
                            \Yii::$app->getModule('audit')->data('errorAction', json_encode($modelServicioAlumno->errors));  
                            throw new GralException('Error al retroceder el Servicio del Alumno');
                        }
                    }elseif($serDA->tiposervicio == DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO){
                        $modelCCP = \app\models\CuotaConvenioPago::findOne($serDA->id_servicio);
                        if(empty($modelCCP)){
                           throw new GralException('No se encuentra la Cuota del Convenio de Pago a revertir');
                        }
                        
                        $modelCCP->id_estado = EstadoServicio::ID_ABIERTA;
                        if(!$modelCCP->save()){
                            $valid = false;
                            \Yii::$app->getModule('audit')->data('errorAction', json_encode($modelServicioAlumno->errors));  
                            throw new GralException('Error al retroceder la Cuota del Convenio de Pago.');
                        }
                    }
                    if(!$serDA->delete())
                        throw new GralException('No se pudo eliminar el servicio asociado al Debito.');
                }
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
            throw new \yii\web\HttpException(500, $e->getMessage());
        }              
    }
        
    /****************************************************************/
    /****************************************************************/
    public function armarDebitoAutomatico($idDA) {
        try{
            $modelDebAut = DebitoAutomatico::findOne($idDA);
            if(!$modelDebAut)
              throw new GralException('No se encontró el Debito Automatico para armar el debito.');                
            else{
                //segun tipo de archivo a generar, llamamos a sus respetivos metodos de generacion
                if($modelDebAut->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_TC){                      
                    return $this->generaArchivoPatagoniaTC($idDA);                 
                }
                if($modelDebAut->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_CBU){                      
                    return $this->generaArchivoPatagoniaCBU($idDA);                 
                }
            }    
        }catch (GralException $e) {        
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (Exception $e){           
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, "Error interno al procesar la solicitud.");
        }
    }    

    
    /************ DEBITOS TC BANCO PATAGONIA ************************/
    public function generaArchivoPatagoniaTC($idDA){    
        ini_set('memory_limit', -1);
        set_time_limit(-1);        
        try{        
            $transaction = Yii::$app->db->beginTransaction();
        
            $modelArchivo = DebitoAutomatico::findOne($idDA);
            if(!$modelArchivo)
                throw new GralException('No se encontró el Debito Automatico a armar el debito.');              
        
            $periodoIni = $modelArchivo->inicio_periodo;
            $periodoFin = $modelArchivo->fin_periodo;
            $fechaVencimiento = $modelArchivo->fecha_debito;
        
            $sqlServiciosXItems = "SELECT 
                        D.idfamilia as idfamilia, 
                        D.nro_tarjetacredito as nro_tarjetacredito, 
                        D.idservicio as idservicio, 
                        D.monto as monto, 
                        D.tiposervicio  as tiposervicio FROM (
                    (
                    SELECT 
                        alu.id_grupofamiliar as idfamilia, 
                        fam.nro_tarjetacredito as nro_tarjetacredito, 
                        sa.id as idservicio, 
                        (sa.importe_servicio - sa.importe_descuento - sa.importe_abonado) as monto, 
                        ". DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS . " as tiposervicio

                    FROM servicio_alumno sa 
                    INNER JOIN alumno as alu ON (alu.id = sa.id_alumno)
                    INNER JOIN grupo_familiar as fam ON (fam.id = alu.id_grupofamiliar)
                    INNER JOIN servicio_ofrecido so ON (so.id = sa.id_servicio)
                    WHERE (alu.activo = '1') and (fam.id_pago_asociado= ". FormaPago::ID_DEBITO_TC . ") 
                        and (sa.id_estado = ". EstadoServicio::ID_ABIERTA  .") 
                        and ((so.fecha_vencimiento >= '".$periodoIni."') and (so.fecha_vencimiento <= '".$periodoFin."'))
                    ORDER BY fam.id
                    )
                    UNION                   
                    (
                    SELECT 
                        fam.id as idfamilia, 
                        fam.nro_tarjetacredito as nro_tarjetacredito, 
                        ccp.id as idservicio, 
                        (ccp.monto) as monto, 
                        " . DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO . " as tiposervicio
                    FROM cuota_convenio_pago ccp 
                      INNER JOIN convenio_pago cp ON (cp.id = ccp.id_conveniopago)	
                      INNER JOIN grupo_familiar as fam ON (fam.id = cp.id_familia) 
                    WHERE (cp.deb_automatico = 1) and (fam.id_pago_asociado = ". FormaPago::ID_DEBITO_TC . ")
                       and (ccp.id_estado =  ". EstadoServicio::ID_ABIERTA  .")
                       and ((ccp.fecha_establecida >= '".$periodoIni."') and (ccp.fecha_establecida <= '".$periodoFin."'))

                    ) 
                ) as D 
                ORDER BY D.idfamilia";
            
            
           $sqlMontoTotalFamiliar = "SELECT DEB.idfamilia, DEB.nro_tarjetacredito, SUM(DEB.monto) as montototal FROM (" .
                   $sqlServiciosXItems . ") AS DEB GROUP BY DEB.idfamilia,DEB.nro_tarjetacredito";
           
         
       
            $connection = Yii::$app->db;        
            $command = $connection->createCommand($sqlServiciosXItems);
            
            $commandMontosTotales = $connection->createCommand($sqlMontoTotalFamiliar);
            
            
            $result = $command->queryAll();            
            
            if(count($result)==0){
                throw new GralException('No se puede armar el archivo; no existen servicos de alumnos/familiar a debitar.');      
            }
            
            $resultMontosTotales = $commandMontosTotales->queryAll();  
            
            $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/tc/generados";
            $filename = 'debitos-'. $modelArchivo->id .'.txt';
            $filename_1 = $filename;
            $filename = $carp_cont."/".$filename;          

            $contenido="";  
            /////HEADER  - encabezado
            $encabezado="";
            $encabezado.="0DEBLIQC ";
            $encabezado.="0025255886"; 
            $encabezado.="900000    ";
            $encabezado.=date('Ymdhm');
            $encabezado.="0                                                         *";
            $encabezado.="\r\n"; 

            $contenido.=$encabezado;

            $saldo_total = 0;                   
            $cantidad = 0;
            $procesa = true;

            $nroServicioFamilia = 1;
            $nroFamiliaAnterior = 0;
            
            //por un lado armamos el archivos
            
            foreach($resultMontosTotales as $rowTotalFamilia){
                if($procesa){ 
                    $cantidad +=1;
                    
                    $nrofamilia = $rowTotalFamilia['idfamilia'];
                    $nrotarjeta = $rowTotalFamilia['nro_tarjetacredito'];
                    $monto = $rowTotalFamilia['montototal'];
                    
                    
                    $modelRegistroDA = new \app\models\DebitoAutomaticoRegistro();
                    $modelRegistroDA->id_debitoautomatico=$idDA;
                    $modelRegistroDA->id_familia=$nrofamilia;
                    $modelRegistroDA->monto=$monto;
                    $modelRegistroDA->correcto = '0';
                    if(!$modelRegistroDA->save())
                        throw  new GralException ('Error al grabar el registro del debito automatico');
                   
                    $saldo_total = $saldo_total + $monto;  
                    $nroServicioFamilia = 1;       
                    $contenido.= $this->devolverLinea_PATAGONIA_TC($nrofamilia, $nrotarjeta, $cantidad, $monto, $fechaVencimiento);  
                }
            }
            
            
            //creamos servicio en el debito y actualizamos servicios de alumno y cuotas a sus estados correspondientes
            foreach($result as $rowServicio){
                $nroServicioFamilia = 1;
                if($procesa){ 
                    $servicio_da = new ServicioDebitoAutomatico();
                    $servicio_da->id_debitoautomatico = $modelArchivo->id;
                    $servicio_da->id_servicio = $rowServicio['idservicio'];
                    $servicio_da->tiposervicio = $rowServicio['tiposervicio'];
                    $servicio_da->linea = 'FAMILIA '.$rowServicio['idfamilia'] .' - MATRICULA ' .$nroServicioFamilia; 
                    $servicio_da->id_familia = $rowServicio['idfamilia'];
                    $servicio_da->importe = (float) $rowServicio['monto'] ;
                    $servicio_da->correcto = '0';
                    if($rowServicio['tiposervicio'] == DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS){
                        $miServicio = ServicioAlumno::findOne($rowServicio['idservicio']);
                        $miServicio->id_estado = EstadoServicio::ID_EN_DEBITOAUTOMATICO;
                        $procesa = $servicio_da->save() && $miServicio->save();    
                    }else
                    if($rowServicio['tiposervicio'] == DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO){
                        $miServicio = \app\models\CuotaConvenioPago::findOne($rowServicio['idservicio']);
                        $miServicio->id_estado = EstadoServicio::ID_EN_DEBITOAUTOMATICO;
                        $procesa = $servicio_da->save() && $miServicio->save();    
                    }                                               
                }    
            }
            
            
         
            $modelArchivo->saldo_enviado = $saldo_total;
                
            $pie="9DEBLIQC ";
            $pie.="0025255886"; 
            $pie.="900000    ";
            $pie.=date('Ymdhm');
            $pie.=str_pad($cantidad,7,"0",STR_PAD_LEFT);
            $saldo_total=number_format($saldo_total, 2);
            $saldo_total = str_replace(",","",str_replace(".","",$saldo_total));
            $pie.=str_pad($saldo_total,15,"0",STR_PAD_LEFT);
            $pie.="                                    ";
            $pie.="*";
            $pie.="\r\n";

            $contenido.=$pie;

            $modelArchivo->registros_enviados = $cantidad;                
            $modelArchivo->saldo_entrante = 0;             
            $procesa = $procesa && $modelArchivo->save();

            if($procesa){                    
                if (!$handle = fopen("$filename", "w")) { 
                    throw new GralException('Error severo; nose puede abrir o grabar el archivo en el disco.');      
                   // $se_genero = false;  
                    //return false;
                    //exit;
                }else {
                    ftruncate($handle,filesize("$filename"));
                }

                $link = "";
                if (fwrite($handle, $contenido) === FALSE){
                    throw new GralException('Error severo; nose puede abrir o grabar el archivo en el disco.');      
                    //$se_genero = false;
                    //return false;
                    //exit;
                }else{ 
                    fclose($handle);	 
                    $se_genero = true; 
                    $archivo  = $modelArchivo->id;
                }

                $transaction->commit();
                //colocar codigo para avisar por correo
                $response['success'] = true;
                return $response;           
            } 
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (Exception $e){
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));   
            throw new \yii\web\HttpException(500, $e->getMessage());
        }           
    } //actionGenArc_Patagonia_TC       
    
    private function devolverLinea_PATAGONIA_TC($nrofamilia, $nrotarjeta, $cantidad, $monto, $fecha_vencimiento_pago){
        try{
            $contenido='';
            $contenido.="1";
            $nrotarjeta = str_replace("","0",$nrotarjeta);
            $contenido.=str_pad($nrotarjeta,16," ",STR_PAD_LEFT);                
            $contenido.="   ";
            $contenido.=str_pad($nrofamilia,8,"0",STR_PAD_LEFT); 
            $fechavencimiento = $fecha_vencimiento_pago;
            $fechavencimiento = str_replace("-","",$fechavencimiento);            
            $contenido.=str_replace("-","",$fechavencimiento); //formato de la fecha yyyymmdd
            $contenido.="0005";    
            $montoCuota = number_format($monto, 2);
            $montoCuota = str_replace(",","",str_replace(".","",$montoCuota));            
            $contenido.=str_pad($montoCuota,15,"0",STR_PAD_LEFT); 
            $identificador = 'F99999F'.  str_pad($nrofamilia,8,"0",STR_PAD_LEFT);
            $contenido.=   $identificador;       
            $contenido.="E"; 
            $contenido.="  ";
            $contenido.="                          ";
            $contenido.="*";
            $contenido.="\r\n";
            return $contenido;
        }catch (\Exception $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));   
            throw new \yii\web\HttpException(500, $e->getMessage());
        }   
    }  
    
    /************ DEBITOS CBU BANCO PATAGONIA ***********************/
    public function generaArchivoPatagoniaCBU($idDA){          
        ini_set('memory_limit', -1);      
        set_time_limit(-1);        
        try{
            $transaction = Yii::$app->db->beginTransaction();
        
            $modelArchivo = DebitoAutomatico::findOne($idDA);
            if(!$modelArchivo)
                throw new GralException('No se encontró el Debito Automatico armar el debito.');  
        
            $periodoIni = $modelArchivo->inicio_periodo;
            $periodoFin = $modelArchivo->fin_periodo;
            $fechaVencimiento = $modelArchivo->fecha_debito;
        
            $fechaVencimientoLinea = \app\helpers\Fecha::formatear($modelArchivo->fecha_debito, "Y-m-d","d-m-Y");
                
            $sqlServiciosXItems = "SELECT 
                        D.idfamilia as idfamilia, 
                        D.cbu as cbu, 
                        D.idservicio as idservicio, 
                        D.monto as monto, 
                        D.tiposervicio  as tiposervicio FROM (
                    (
                    SELECT 
                        alu.id_grupofamiliar as idfamilia, 
                        fam.cbu_cuenta as cbu, 
                        sa.id as idservicio, 
                        (sa.importe_servicio - sa.importe_descuento - sa.importe_abonado) as monto, 
                        ". DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS . " as tiposervicio

                    FROM servicio_alumno sa 
                    INNER JOIN alumno as alu ON (alu.id = sa.id_alumno)
                    INNER JOIN grupo_familiar as fam ON (fam.id = alu.id_grupofamiliar)
                    INNER JOIN servicio_ofrecido so ON (so.id = sa.id_servicio)
                    WHERE (alu.activo = '1') and (fam.id_pago_asociado= ". FormaPago::ID_DEBITO_CBU . ") 
                        and (sa.id_estado = ". EstadoServicio::ID_ABIERTA  .") 
                        and ((so.fecha_vencimiento >= '".$periodoIni."') and (so.fecha_vencimiento <= '".$periodoFin."'))
                    ORDER BY fam.id
                    )
                    UNION                   
                    (
                    SELECT 
                        fam.id as idfamilia, 
                        fam.cbu_cuenta as cbu, 
                        ccp.id as idservicio, 
                        (ccp.monto) as monto, 
                        " . DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO . " as tiposervicio
                    FROM cuota_convenio_pago ccp 
                      INNER JOIN convenio_pago cp ON (cp.id = ccp.id_conveniopago)	
                      INNER JOIN grupo_familiar as fam ON (fam.id = cp.id_familia) 
                    WHERE (cp.deb_automatico = 1) and (fam.id_pago_asociado = ". FormaPago::ID_DEBITO_CBU . ")
                       and (ccp.id_estado =  ". EstadoServicio::ID_ABIERTA  .")
                       and ((ccp.fecha_establecida >= '".$periodoIni."') and (ccp.fecha_establecida <= '".$periodoFin."'))

                    ) 
                ) as D 
                ORDER BY D.idfamilia";
            
            
           $sqlMontoTotalFamiliar = "SELECT DEB.idfamilia, DEB.cbu, SUM(DEB.monto) as montototal FROM (" .
                   $sqlServiciosXItems . ") AS DEB GROUP BY DEB.idfamilia,DEB.cbu";
           
         
       
            $connection = Yii::$app->db;        
            $command = $connection->createCommand($sqlServiciosXItems);
            
            $commandMontosTotales = $connection->createCommand($sqlMontoTotalFamiliar);
            
            
            $result = $command->queryAll();            
            
            if(count($result)==0){
                throw new GralException('No se puede armar el archivo; no existen servicos de alumnos/familiar a debitar.');      
            }
            
            $resultMontosTotales = $commandMontosTotales->queryAll();  
            
            $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/cbu/generados";
            $filename = 'debitos-'. $modelArchivo->id .'.txt';                
            $filename = $carp_cont."/".$filename;          

            $contenido="";

            /////HEADER  - encabezado
            $encabezado="";
            $encabezado.="H30630291727";
            $encabezado.="CUOTA     ";
            $encabezado.='618';
            $encabezado.= str_replace("/","",date('d/m/Y'));
            $encabezado.='            ';
            $encabezado.='COLEGIO VECCHI                     ';
            $encabezado.=str_pad('',120," ",STR_PAD_LEFT);
            $encabezado.="\r\n"; 

            $contenido.=$encabezado;

            $saldo_total = 0;                   
            $cantidad = 0;
            $procesa = true;

            $nroServicioFamilia = 1;
            $nroFamiliaAnterior = 0;
            
            //por un lado armamos el archivos
            
            foreach($resultMontosTotales as $rowTotalFamilia){
                if($procesa){ 
                    $cantidad +=1;

                    $nrofamilia = $rowTotalFamilia['idfamilia'];
                    $cbu = $rowTotalFamilia['cbu'];
                    $monto = $rowTotalFamilia['montototal'];
                    
                    $modelRegistroDA = new \app\models\DebitoAutomaticoRegistro();
                    $modelRegistroDA->id_debitoautomatico=$idDA;
                    $modelRegistroDA->id_familia=$nrofamilia;
                    $modelRegistroDA->monto=$monto;
                    $modelRegistroDA->correcto = '0';
                    if(!$modelRegistroDA->save())
                        throw  new GralException ('Error al grabar el registro del debito automatico');
                    
                    
                    $saldo_total = $saldo_total + $monto;  
                    $nroServicioFamilia = 1;                   
                    $contenido.= $this->devolverLinea_PATAGONIA_CBU($nrofamilia, $cbu, $cantidad, $monto, $fechaVencimientoLinea, $nroServicioFamilia); 
                }
            }
            
            
            //creamos servicio en el debito y actualizamos servicios de alumno y cuotas a sus estados correspondientes
            foreach($result as $rowServicio){
                $nroServicioFamilia = 1;
                if($procesa){ 
                    $servicio_da = new ServicioDebitoAutomatico();
                    $servicio_da->id_debitoautomatico = $modelArchivo->id;
                    $servicio_da->id_servicio = $rowServicio['idservicio'];
                    $servicio_da->tiposervicio = $rowServicio['tiposervicio'];
                    $servicio_da->linea = 'FAMILIA '.$rowServicio['idfamilia'] .' - MATRICULA ' .$nroServicioFamilia; 
                    $servicio_da->id_familia = $rowServicio['idfamilia'];
                    $servicio_da->importe = (float) $rowServicio['monto'] ;
                    $servicio_da->correcto = '0';
                    if($rowServicio['tiposervicio'] == DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS){
                        $miServicio = ServicioAlumno::findOne($rowServicio['idservicio']);
                        $miServicio->id_estado = EstadoServicio::ID_EN_DEBITOAUTOMATICO;
                        if(!$servicio_da->save()){
                            $procesa = false;
                            \Yii::$app->getModule('audit')->data('errorServicioDebitoAutomatico', \yii\helpers\VarDumper::dumpAsString($servicio_da->errors)); 
                        }
                        if(!$miServicio->save()){
                            $procesa = false;
                            \Yii::$app->getModule('audit')->data('errorServicioAlumno', \yii\helpers\VarDumper::dumpAsString($miServicio->errors)); 
                        }
                        
                    }else
                    if($rowServicio['tiposervicio'] == DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO){
                        $miServicio = \app\models\CuotaConvenioPago::findOne($rowServicio['idservicio']);
                        $miServicio->id_estado = EstadoServicio::ID_EN_DEBITOAUTOMATICO;
                        if(!$servicio_da->save()){
                            $procesa = false;
                            \Yii::$app->getModule('audit')->data('errorServicioDebitoAutomatico', \yii\helpers\VarDumper::dumpAsString($servicio_da->errors)); 
                        }
                        if(!$miServicio->save()){
                            $procesa = false;
                            \Yii::$app->getModule('audit')->data('errorServicioCupta', \yii\helpers\VarDumper::dumpAsString($miServicio->errors)); 
                        }
                    }                                               
                }    
            }
            
           

            $modelArchivo->saldo_enviado = $saldo_total;

            $pie="T";
            $pie.=str_pad($cantidad,7,"0",STR_PAD_LEFT); 
            $saldo_total=number_format($saldo_total, 2);
            $saldo_total = str_replace(",","",str_replace(".","",$saldo_total));
            $pie.=str_pad($saldo_total,15,"0",STR_PAD_LEFT);
            $pie.=str_pad("",177," ",STR_PAD_LEFT);
            $pie.="\r\n";
            $contenido.=$pie;

            $modelArchivo->registros_enviados = $cantidad;                
            $modelArchivo->saldo_entrante = 0;                
            $procesa = $procesa && $modelArchivo->save();                

            if($procesa){                    
                if (!$handle = fopen("$filename", "w")) { 
                    throw new GralException('Error severo; nose puede abrir o grabar el archivo en el disco.'); 
                    //$se_genero = false;  
                    //return false;
                    //exit;
                }else {
                    ftruncate($handle,filesize("$filename"));
                }

                $link = "";
                if (fwrite($handle, $contenido) === FALSE){
                    throw new GralException('Error severo; nose puede abrir o grabar el archivo en el disco.'); 
                    //$se_genero = false;
                    //return false;
                    //exit;
                }else{ 
                    fclose($handle);	 
                    $se_genero = true; 
                    $archivo  = $modelArchivo->id;
                }                    

                $transaction->commit();
                //colocar codigo para avisar por correo
                $response['success'] = true;
                return $response;                 
            }else
               throw new GralException("Error al generar el archivo");         //fin del procesa == TRUE            
        }catch (GralException $e) {
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e){
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));   
            throw new \yii\web\HttpException(500, $e->getMessage());
        }                 
    } //actionGenArc_Patagonia_TC         
    
    private function devolverLinea_PATAGONIA_CBU($nrofamilia, $cbu, $cantidad, $monto, $fecha_vencimiento_pago, $nroServicioFamilia){
        try{
            $contenido='';
            $contenido.="D";
            $contenido.='00000000000';
            $contenido.=str_pad($cbu,22," ",STR_PAD_RIGHT);

            $identificadorFamilia   = "FG1".str_pad($nrofamilia,5,"0",STR_PAD_LEFT);        
            $contenido.= str_pad($identificadorFamilia,22," ",STR_PAD_RIGHT);

            $fechavencimiento = $fecha_vencimiento_pago;
            $fechavencimiento = str_replace("-","",$fechavencimiento);            
            $contenido.=str_replace("-","",$fechavencimiento); 

            $contenido.="CUOTA     ";
            $contenido.=str_pad("",15," ",STR_PAD_RIGHT);

            $servicio='MATRICULA'.$nroServicioFamilia;

            $contenido.=str_pad($servicio,15,"M",STR_PAD_LEFT);

            $montoCuota = number_format($monto, 2);
            $montoCuota = str_replace(",","",str_replace(".","",$montoCuota));            
            $contenido.=str_pad($montoCuota,10,"0",STR_PAD_LEFT); 
            $contenido.='P';
            $contenido.='                                                                          30630291727';
            $contenido.="\r\n";
            return $contenido;
        }catch (\Exception $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));   
            throw new \yii\web\HttpException(500, $e->getMessage());
        }   
    }  

    
    /*************************************************************************/
    /*
     * Procesa un archivo de entrada según el tipo de archivo.
     * Basicamente; leee registro aregistro y segun la estructura del mismo,
     * lee la info y según la info procesa los registros del debito
     */
    public function procesarDebitoAutomatico($idDA) {
        ini_set('memory_limit', -1);      
        set_time_limit(-1); 
        try{        
            $modelDebAut = DebitoAutomatico::findOne($idDA);
            if(!$modelDebAut)
              throw new GralException('No se encontró el Debito Automatico para armar el debito.');                
            else{
                //segun tipo de archivo a generar, llamamos a sus respetivos metodos de generacion
                if($modelDebAut->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_TC){                      
                    return $this->procesarPatagoniaTc($idDA);                 
                }
                if($modelDebAut->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_CBU){                      
                    return $this->procesarPatagoniaCBU($idDA);                 
                }
            }    
        }catch (GralException $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());     
        }catch (Exception $e){             
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, "Error interno al procesar la solicitud.");
        }
    }    
      
    
    
    /********************** procesamiento del Archivo  CBU *********/
    private function cantidadLineasArchivoCBU($archivo) {
        try {
            $file = fopen($archivo, "r");
            $cantidadLineas = 0;                
            while(!feof($file)){
                $linea = fgets($file);
                if(substr($linea, 0,1)=='D')
                    $cantidadLineas+=1;
            }  
            fclose($file);
            return $cantidadLineas;
        }catch (GralException $e) {  
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));             
            throw new GralException($e->getMessage()); 
        }catch (\Exception $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));         
            throw new \yii\web\HttpException(500, $e->getMessage()); 
        }
    }
    
    private  function procesarPatagoniaCBU($id) {
        ini_set('memory_limit', -1);      
        set_time_limit(-1); 
        $transaction = Yii::$app->db->beginTransaction();
        try {
            
            $filename = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/cbu/devoluciones/debitos" . $id . ".txt";
            $valid = true;           
            if (!file_exists($filename)) {
                $valid = false;
                $transaction->rollBack();
                return ['error'=>'1', 
                        'success'=>false, 
                        'resultado'=>'No se encontró el archivo para su procesamiento.'];
            } else {
                $model= DebitoAutomatico::findOne($id);
                $fechaDebito =  \app\helpers\Fecha::formatear( $model->fecha_debito, 'Y-m-d', 'd-m-Y'); 
                $cantLineas = $this->cantidadLineasArchivoCBU($filename);
                
                if($cantLineas !== $model->registros_enviados){
                    return ['error'=>'1', 
                        'success'=>false, 
                        'resultado'=>'La cantidad de registros procesados no coincide con los enviados.'];    
                }
                
                $file = fopen($filename, "r");
                $totalIngreso = 0;
                $itemsCorrectos = 0; 
                $itemsInCorrectos = 0; 
                $cantRegistrosProcesados = 0;
                $nrolinea = -1;                
           
                while(!feof($file)){  
                    $linea = fgets($file);
                    $nrolinea += 1;
                    if (($nrolinea == 0))
                        continue;

                    $cantRegistrosProcesados+=1;
                    $inicioRegistro = substr($linea, 0, 1);
                    if($inicioRegistro=='D') {
                        $idFamilia = (int) substr($linea, 37, 5);
                        $nro_cbu = substr($linea, 12, 22);
                        $resultado_proceso = substr($linea, 151, 3);                    
                        $serviciomatricula= trim(substr($linea, 89, 15));        
                        $fechaArchivo = substr($linea, 56, 8);      
                        $fechaArchivo = \app\helpers\Fecha::formatear($fechaArchivo, 'dmY', 'Y-m-d');   
                        //control de que sea el archivo correcto
                    
                        if($fechaArchivo != $model->fecha_debito)
                            throw new GralException('Fechas registros incompatibles');
                    
                    
                        $registroDebitoFamiliar = \app\models\DebitoAutomaticoRegistro::find()
                                ->andWhere(['id_debitoautomatico'=>$id])
                                ->andWhere(['id_familia'=>$idFamilia])
                                ->one();
                            //control de que sea el archivo correcto
        //                    if(!$registroDebitoFamiliar)
        //                        throw new GralException('Error, uno de los registros encontrados en el archivo no coincide con el que se envio');

                        $textoResultado = $resultado_proceso;
                        $modelResultadoCBU = \app\models\ResultadoCbuPatagonia::find()->andWhere(['like', 'codigo', $resultado_proceso])->one();
                        if(!empty($modelResultadoCBU))
                            $textoResultado.=" ".$modelResultadoCBU->descripcion;
                            
                        if(!empty($registroDebitoFamiliar)){
                            $registroDebitoFamiliar->resultado = $textoResultado;
                            if($resultado_proceso=='R00'){
                                $itemsCorrectos+=1;
                                $registroDebitoFamiliar->correcto = '1';
                            }
                            if(!$registroDebitoFamiliar->save()){
                                \Yii::$app->getModule('audit')->data('errorAction', json_encode($registroDebitoFamiliar->errors));
                                throw new GralException('Error, al grabar el resultado de procesamiento');                   
                            }
                        }
                        
                        //buscamos todos lo servicios asociados al debito de la familia
                        $serviciosEnDebAut = \app\models\ServicioDebitoAutomatico::find()
                                ->andWhere(['id_debitoautomatico' => $id])
                                ->andWhere(['id_familia' => $idFamilia]) 
                                ->all();
                        //reccoremos todos los servicios asociados a la linea familia del archivo
                        if(!empty($serviciosEnDebAut)){
                            foreach($serviciosEnDebAut as $modelServicioDebAut){     
                                
                                $modelServicioDebAut->resultado_procesamiento = $resultado_proceso;
                                $modelServicioDebAut->correcto = '1';

                                $idEstado = ($resultado_proceso=='R00')?\app\models\EstadoServicio::ID_ABONADA_EN_DEBITOAUTOMATICO:\app\models\EstadoServicio::ID_ABIERTA;
                                if($modelServicioDebAut->tiposervicio== \app\models\DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS){
                                    $modelServicioAlumno = \app\models\ServicioAlumno::findOne($modelServicioDebAut->id_servicio);                              
                                    $modelServicioAlumno->id_estado = $idEstado;
                                    $modelServicioAlumno->importe_abonado += $modelServicioDebAut->importe;
                                    $valid = $valid && $modelServicioAlumno->save() && $modelServicioDebAut->save();
                                    $totalIngreso += $modelServicioDebAut->importe;                                
                                }else
                                if($modelServicioDebAut->tiposervicio == \app\models\DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO){
                                    $modelCCP = \app\models\CuotaConvenioPago::findOne($modelServicioDebAut->id_servicio);                               
                                    $modelCCP->id_estado = $idEstado;
                                    $modelCCP->importe_abonado += $modelServicioDebAut->importe;
                                    $valid = $valid && $modelCCP->save() && $modelServicioDebAut->save();
                                    $totalIngreso += $modelServicioDebAut->importe;                                
                                }
                            }
                        }                    
                    }    
                }//while  
                
                $model->procesado='1';
                $model->registros_correctos=$itemsCorrectos;
                $model->saldo_entrante=$totalIngreso;                
                fclose($file);
                if($valid && $model->save()){
                    //$transaction->commit();
                    //generamos los tiket 
                     $serviciosEnDebAut = \app\models\DebitoAutomaticoRegistro::find()
                            ->andWhere(['id_debitoautomatico' => $model->id])                            
                            ->all();
                    if(!empty($serviciosEnDebAut))
                        foreach($serviciosEnDebAut as $registro){
                        $cccc = 0;
                            if($registro->correcto=='1' || $registro->correcto==1)
                                $cccc+=1;
                                //$procTiket = $this->armarTiketProceso($model->id, $registro->id_familia, FormaPago::ID_DEBITO_CBU, $registro->monto , $fechaDebito);
                        }
                 var_dump("lll " .$cccc);
                 exit;
                    
                    return ['error'=>'0', 'success'=>true, 'resultado'=>'EL ARCHIVO SE PROCESO CON EXITO'];                    
                }else{
                    $transaction->rollBack();                
                    return ['error'=>'1', 'success'=>false, 'resultado'=>'NO SE PUDO PROCESAR EL ARCHIVO'];
                }
            }
        }catch (GralException $e) { 
             \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            return ['error'=>'1', 
                        'success'=>false, 
                        'resultado'=> $e->getMessage()];
        }catch (\Exception $e) {
             \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            throw new \yii\web\HttpException(500, $e->getMessage()); 
        }
    }
    
    
    /* armamos iun tiket para el total de lo debitado a cada registro **/
    private function armarTiketProceso($idDebito, $idFamilia, $idTipoPago, $monto, $fechaTiket){
        ini_set('memory_limit', -1);      
        set_time_limit(-1); 
        try{
            $modelFamilia = \app\models\GrupoFamiliar::findOne($idFamilia);
            if(!$modelFamilia){
                var_dump("No existe la familia");
                exit;
            }
                
            $modelTiket = new \app\models\Tiket();
            $modelTiket->id_cliente = $idFamilia;
            $modelTiket->id_tipopago = $idTipoPago;
            $modelTiket->importe = $monto;
            $modelTiket->xfecha_tiket = $fechaTiket; 
            $modelTiket->detalles = 'Pago Debito Automatico'; 
            if(!empty($modelFamilia) && !empty($modelFamilia->cuil_afip_pago))
                $modelTiket->dni_cliente = $modelFamilia->cuil_afip_pago; 
            
            $serviciosDebito = ServicioDebitoAutomatico::find()->select('id_servicio')
                    ->andWhere(['id_debitoautomatico'=>$idDebito])
                    ->andWhere(['id_familia'=>$idFamilia])
                    ->andWhere(['tiposervicio'=>\app\models\DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS])
                    ->asArray()->all();
            
            if(count($serviciosDebito)<=0)
                $serviciosDebito = [];
            else
                $serviciosDebito = \yii\helpers\ArrayHelper::getColumn($serviciosDebito, 'id_servicio');           
            
            $cuotasDebito = ServicioDebitoAutomatico::find()->select('id_servicio')
                    ->andWhere(['id_debitoautomatico'=>$idDebito])
                    ->andWhere(['id_familia'=>$idFamilia])
                    ->andWhere(['tiposervicio'=>\app\models\DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO])
                     ->asArray()->all();
            
            if(count($cuotasDebito)<=0)
                $cuotasDebito1 = [];
            else
                $cuotasDebito1 = \yii\helpers\ArrayHelper::getColumn($cuotasDebito, 'id_servicio');
            
           
            $response = Yii::$app->serviceCaja->generarTiket($modelTiket, $serviciosDebito, $cuotasDebito);
            if($response['success']){                      
                return true;
            }else{                    
                return false;
            }
        }catch (\Exception $e) {
             \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            throw new \yii\web\HttpException(500, $e->getMessage()); 
        }catch (\Exception $e) {
             \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            throw new \yii\web\HttpException(500, $e->getMessage()); 
        }
    }
    
}
