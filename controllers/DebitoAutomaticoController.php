<?php

namespace app\controllers;

use Yii;
use app\models\DebitoAutomatico;
use app\models\search\DebitoAutomaticoSearch;
use app\models\ServicioDebitoAutomatico;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

use app\helpers\GralException;

/**
 * DebitoAutomaticoController implements the CRUD actions for DebitoAutomatico model.
 */
class DebitoAutomaticoController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors(){
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [     
                        'actions' => ['administrar','down-padron-excel'],
                        'allow' => true,
                        'roles' => ['listarDebitoAutomatico'],
                    ], 
                    [     
                        'actions' => ['alta','eliminar'],
                        'allow' => true,
                        'roles' => ['altaDebitoAutomatico'],
                    ], 
                    [     
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['visualizarDebitoAutomatico'],
                    ], 
                    [     
                        'actions' => ['procesar'],
                        'allow' => true,
                        'roles' => ['procesarDebitoAutomatico'],
                    ],
                    [     
                        'actions' => ['descargar-archivo-envio','descarga-txt','descarga-excel','convertir-a-excel'],
                        'allow' => true,
                        'roles' => ['gestionarDebitoAutomatico'],
                    ],  
                ],
                'denyCallback' => function($rule, $action){ 
                    if(Yii::$app->request->isAjax)
                        throw new GralException('Acceso denegado, usted no dispone de los permisos suficienes para realizar la accion');
                    else
                        return $this->redirect(['site']);         
                }
            ],  
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    public function actions()
    {
        return [           
            'down-padron-excel'=>'app\actions\DescargaPadronExcelAction',
        ];
    } 
    
    /*******************************************************/
    /*******************************************************/
    /**
     * Finds the DebitoAutomatico model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DebitoAutomatico the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id){
        if (($model = DebitoAutomatico::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    public function actionEliminar($id) {
        try{
            $transaction = Yii::$app->db->beginTransaction(); 
            $response = Yii::$app->serviceDebitoAutomatico->eliminarDebitoAutomatico($id);
            if($response['success']){  
                $transaction->commit();
                Yii::$app->session->setFlash('success',Yii::$app->params['eliminacionCorrecta']);
                return $this->redirect(['administrar']);    
            }else{
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', Yii::$app->params['eliminacionErronea']);
                return $this->redirect(Yii::$app->request->referrer); 
            }
        }catch (GralException $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);                       
        }catch (\Exception $e) { 
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            Yii::$app->session->setFlash('error', Yii::$app->params['operacionFallida']);
            return $this->redirect(Yii::$app->request->referrer);                        
        }
    }
    
    /****************************************************************/
    /****************************************************************/
    /**
     * Listado de Debitos Automaticos generados en el sistema con columna para su gestion.
     * @return mixed
     */
    public function actionAdministrar(){
        try{
            $export = Yii::$app->request->get('export');
            if(isset($export) && $export==1)
                return $this->exportarListado();
            
            $searchModel = new DebitoAutomaticoSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            
            $filter['tipoarchivo'] = [
                    DebitoAutomatico::ID_TIPODEBITO_CBU => 'Debito CBU', 
                    DebitoAutomatico::ID_TIPODEBITO_TC => 'Tarjeta Credito'
                    ];            
        } catch (Exception $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));             
            Yii::$app->session->setFlash('error', Yii::$app->params['errorExcepcion']);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'filter'=>$filter
        ]);
    } 
    
    /****************************************************************/
    /*****************************************************************/
    public function actionView($id){
        try{
            $model = $this->findModel($id);  
            
            $searchItemsDebitos = new \app\models\search\ServicioDebitoAutomaticoSearch();
            $searchItemsDebitos->id_debitoautomatico = $model->id;             
            $dataMisDebitos = $searchItemsDebitos->search(Yii::$app->request->queryParams); 
        }
        catch(\Exception $e)
        {
            Yii::app()->user->setFlash('error','ERROR SEVERO EN LA CARGA DEL ARCHIVO!!!');
            $this->redirect(array('administrar'));   
        }
        
        return $this->render('view',[
            'model'=>$model,
            'searchItemsDebitos' => $searchItemsDebitos,
            'dataMisDebitos' => $dataMisDebitos,
        ]);
    }
    
    /*****************************************************************/
    /*
     * Inicio Generacion de archivos envio BANCO
     */
    /*****************************************************************/
    /*
     * Da de alta un archivo de DebitoAutomatico para ser enviado al BANCO.
     * 
     * Para esto prove de una interfaz con un formulario para la carga de datos escenciales;
     * y asi poder buscar y adjuntar al archivo a genrar los servicios del periodo
     * provisto.
     * 
     * Una vez que el usuario ingreaa un periodo determinado se buscaran los servicios sin pago
     * a ser incroporados a el archivo a genrar.
     */
    public function actionAlta()
    {
        $transaction = Yii::$app->db->beginTransaction(); 
        try{
            $model = new DebitoAutomatico();
            $model->procesado = '0';
            $model->saldo_entrante = '0';
            $model->saldo_enviado = '0';
            $model->banco = 'PATAGONIA';
            $model->fecha_creacion =date('Y-m-d');    
            
            $filter['tipoarchivo'] = [
                    DebitoAutomatico::ID_TIPODEBITO_CBU => 'Debito CBU', 
                    DebitoAutomatico::ID_TIPODEBITO_TC => 'Tarjeta Credito'
                    ];
            
            if($model->load(Yii::$app->request->post()) && $model->save()){
                $response = Yii::$app->serviceDebitoAutomatico->armarDebitoAutomatico($model->id);            
                if($response['success']){
                    $transaction->commit();
                    Yii::$app->session->setFlash('ok', 'Se generó con éxito ');
                    return $this->redirect(['view', 'id' => $model->id]);               
                }
                else{
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'No se puede generar el archivo. No existen servicios en el periodo mencionado.');
                }
            }   
        }catch (GralException $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));          
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['administrar']);
        }
        catch (\Exception $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));          
            Yii::$app->session->setFlash('error','Atención!!! <br /> Se Produjo un error severo');
            return $this->redirect(['administrar']);
        }
        
        return $this->render('alta',[
            'model' => $model, 
            'filter' => $filter
        ]);
    }    
    
    
    /*******************************************************/
    /*******************************************************/     
//    private function ProcesarPatagoniaTc($id) {
//        $transaction = Yii::$app->db->beginTransaction();
//        try {
//            $model= $this->findModel($id);
//            
//            $filename = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/tc/devoluciones/debitos".$model->id.".txt";
//            $valid = true;
//
//            if (!file_exists($filename)) {
//                $valid = false;
//                $transaction->rollBack();
//                return
//                    ['error'=>'1','success'=>false, 'resultado'=>'No se encontró el archivo para su procesamiento.'];
//            } else {                
//                $file = fopen($filename, "r");
//                
//                $totalIngreso = 0;
//                $itemsCorrectos = 0; 
//                $itemsInCorrectos = 0; 
//                
//                $nrolinea = -1;
//                
//                while(!feof($file)){  
//                    $linea = fgets($file);
//                    $nrolinea += 1;
//                    if ($nrolinea == 0)
//                        continue;
//
//                    $idFamilia = (int) substr($linea, 86, 8);
//                    $detallesResultadoproceeso = substr($linea, 129, 32);
//                    $resultado_proceso = substr($linea, 129, 3);
//                    
//                    $fecha_pago = substr($linea, 50, 6);
//                    $fecha_pago1 = \app\helpers\Fecha::formatear($fecha_pago, 'dmy', 'Y-m-d');
//                    
//                    
//                    
//                    /*
//                    if($fechaArchivo!=$model->fecha_debito)
//                        throw new GralException('Fechas registros incompatibles');
//                    */
//                    //buscamos todos lo servicios asociados al debito de la familia
//                    $serviciosEnDebAut = \app\models\ServicioDebitoAutomatico::find()
//                            ->andWhere(['id_debitoautomatico' => $id])
//                            ->andWhere(['id_familia' => $idFamilia]) 
//                            ->all();
//                    //reccoremos todos los servicios asociados a la linea familia del archivo
//                    if(!empty($serviciosEnDebAut)){
//                        foreach($serviciosEnDebAut as $modelServicioDebAut){     
//                            $textoResultado = $resultado_proceso;
//                            
//                            $modelServicioDebAut->resultado_procesamiento = $detallesResultadoproceeso;
//                            
//                            $idEstado = ($resultado_proceso=='000')?\app\models\EstadoServicio::ID_ABONADA_EN_DEBITOAUTOMATICO:\app\models\EstadoServicio::ID_ABIERTA;
//                            if($modelServicioDebAut->tiposervicio== \app\models\DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS){
//                                $modelServicioAlumno = \app\models\ServicioAlumno::findOne($modelServicioDebAut->id_servicio);                              
//                                $modelServicioAlumno->id_estado = $idEstado;
//                                $modelServicioAlumno->importe_abonado += $modelServicioDebAut->importe;
//                                $valid = $valid && $modelServicioAlumno->save() && $modelServicioDebAut->save();
//                                $totalIngreso += $modelServicioDebAut->importe;
//                                $itemsCorrectos += 1;
//                            }else
//                            if($modelServicioDebAut->tiposervicio == \app\models\DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO){
//                                $modelCCP = \app\models\CuotaConvenioPago::findOne($modelServicioDebAut->id_servicio);                               
//                                $modelCCP->id_estado = $idEstado;
//                                $modelCCP->importe_abonado += $modelServicioDebAut->importe;
//                                $valid = $valid && $modelCCP->save() && $modelServicioDebAut->save();
//                                $totalIngreso += $modelServicioDebAut->importe;;
//                                $itemsCorrectos += 1;
//                            }                    
//                        }
//                    }                    
//                }               
//                
//                $model->procesado='1';
//                $model->registros_correctos=$itemsCorrectos;
//                $model->saldo_entrante=$totalIngreso;
//                
//                if($valid && $model->save()){
//                    $transaction->commit();
//                    return ['error'=>'0', 'success'=>true, 'resultado'=>'EL ARCHIVO SE PROCESO CON EXITO'];                    
//                }else{
//                    $transaction->rollBack();                
//                    return ['error'=>'1', 'success'=>false, 'resultado'=>'NO SE PUDO PROCESAR EL ARCHIVO'];
//                }
//            }
//        }catch (\Exception $e) {
//            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
//            throw new \yii\web\HttpException(500, $e->getMessage()); 
//        }catch (\Exception $e) {
//            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
//            throw new \yii\web\HttpException(500, $e->getMessage()); 
//        }
//    }      
    
    public function actionProcesar($id){
        try{
            $model = $this->findModel($id);
            
            if (Yii::$app->request->isPost) {
                $model->archivoentrante = UploadedFile::getInstance($model, 'archivoentrante');
                
                if($model->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_TC)                        
                    $ruta = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/tc/devoluciones/debitos".$model->id.".txt";  
                elseif($model->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_CBU)
                    $ruta = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/cbu/devoluciones/debitos".$model->id.".txt";
            
                if($model->archivoentrante->saveAs($ruta)){
                    $response = Yii::$app->serviceDebitoAutomatico->procesarDebitoAutomatico($model->id);  
                    if($response['success']){                       
                        Yii::$app->session->setFlash('ok', 'Se generó con éxito ');
                        return $this->redirect(['view', 'id' => $model->id]);               
                    }
                    else                     
                        Yii::$app->session->setFlash('error', $response['resultado']);
                }                
                else
                    throw  new GralException("No se puede grabar el archivo para su procesamiento");
            }
            return $this->redirect(['view','id'=>$id]);
        }catch (GralException $e) { 
            Yii::app()->user->setFlash('error', $e->getMessage());
            return $this->redirect(['view','id'=>$id]);
        }catch(Exception $e) {
            Yii::app()->user->setFlash('error', 'ATENCION!!! <br /> Se Produjo un error severo');
            return $this->redirect(['view','id'=>$id]);
        }
    }    
    
    /********************************************************************/   
    public function cellColor($objPHPExcel,$cells,$color)
    {
        $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startcolor' => array('rgb' => $color) ));
    }   
    
    public function exportarListado() 
    {  
        try{            
            $searchModel = new DebitoAutomaticoSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->setPagination(false);        
      
            $data = $dataProvider->getModels();           
            
            $i = 0;                        
            $contador = count($data);

           
                $objPHPExcel = new Spreadsheet();  
                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
            
                
                $this->cellColor($objPHPExcel, 'A1', 'F28A8C');
                $this->cellColor($objPHPExcel, 'B1', 'F28A8C');
                $this->cellColor($objPHPExcel, 'C1', 'F28A8C');
                $this->cellColor($objPHPExcel, 'D1', 'F28A8C');
                $this->cellColor($objPHPExcel, 'E1', 'F28A8C');
                $this->cellColor($objPHPExcel, 'F1', 'F28A8C');
                $this->cellColor($objPHPExcel, 'G1', 'F28A8C');
             
                
                $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Nro');
                $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Tipo');
                $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Nombre');                
                $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Periodo');                
                $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Registros Enviados');
                $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Registros Correctos');
                $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Importe Entrante');
               
                
                $letracolumnainicio = 'A';
                $letrafilainicio = 3;

                foreach ($data as $modelDebitoAutomatico)  {
                    $letrafilainicio1 = (string) $letrafilainicio;
                    $columnaA = 'A' . $letrafilainicio1;
                    $columnaB = 'B' . $letrafilainicio1;
                    $columnaC = 'C' . $letrafilainicio1;
                    $columnaD = 'D' . $letrafilainicio1;
                    $columnaE = 'E' . $letrafilainicio1;
                    $columnaF = 'F' . $letrafilainicio1;
                    $columnaG = 'G' . $letrafilainicio1;
                    $columnaH = 'H' . $letrafilainicio1;
                    $columnaI = 'I' . $letrafilainicio1;
                    $columnaJ = 'J' . $letrafilainicio1;
                    $columnaK = 'K' . $letrafilainicio1;
                    $columnaL = 'L' . $letrafilainicio1;
                    $columnaM = 'M' . $letrafilainicio1;

                    $objPHPExcel->getActiveSheet()->setCellValue($columnaA, $modelDebitoAutomatico->id);
                    if($modelDebitoAutomatico->tipo_archivo==1)
                        $objPHPExcel->getActiveSheet()->setCellValue($columnaB, "TC" );
                    else
                        $objPHPExcel->getActiveSheet()->setCellValue($columnaB, "CBU" );
                        
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaC, $modelDebitoAutomatico->nombre);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaD, $modelDebitoAutomatico->periodoBarrido );
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaE, $modelDebitoAutomatico->registros_enviados);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaF, $modelDebitoAutomatico->registros_correctos);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaG, $modelDebitoAutomatico->saldo_entrante);
                
                    $i = $i + 1;
                    $letrafilainicio += 1;
                }  
                
                $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados"; //carpeta a almacenar los archivos
                $nombre_archivo = "listadoDebitosAutomaticos" . Yii::$app->user->id . ".xlsx";                                
                $ruta_archivo = $carp_cont . "/" . $nombre_archivo;
            
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
                $writer->save($ruta_archivo);          
                
                $url_pdf = \yii\helpers\Url::to(['down-padron-excel', 'archivo' => $nombre_archivo]);               
                return $this->redirect($url_pdf); 
                
               
        }catch (\Exception $e) {           
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));    
            return $this->redirect(['/site/index']);            
        }  
    }
    
    /********************************************************************/
    /********************************************************************/
    public function actionDescargarArchivoEnvio($id){
        try{
            $modelDebito = $this->findModel($id);

            if($modelDebito->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_CBU){
                $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/cbu/generados";
                $file = 'DEBITOS.txt';
            }            
            else{
                $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/tc/generados";   
                $file = 'DEBLICQ.txt';
            }
            
            $filename = 'debitos-'. $modelDebito->id .'.txt';     
            $filename = $carp_cont."/".$filename;    

            if(!file_exists($filename)){
                Yii::app()->user->setFlash('error', 'Atención!!! <br /> No existe el archivo para su procesamiento/descarga');
                return $this->redirect(['debito-automatico/administrar']);
            }else{
                $url_pdf = \yii\helpers\Url::to(['/debito-automatico/descarga-txt', 'id' => $id]);
                Yii::$app->response->format = 'json';
                return ['result_error' => '0', 'result_texto' => $url_pdf];
            }
        } catch (\Exception $e) {           
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));    
            return $this->redirect(['/site/index']);            
        }  
    } 
    
    public function actionDescargaTxt($id) { 
        try{
            $modelDebito = $this->findModel($id);

            if($modelDebito->tipo_archivo == DebitoAutomatico::ID_TIPODEBITO_CBU){
                $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/cbu/generados";
                $file = 'DEBITOS.txt';
            }            
            else{
                $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados/patagonia/tc/generados";   
                $file = 'DEBLICQ.txt';
            }


            $filename = 'debitos-'. $modelDebito->id .'.txt';     
            $ruta_archivo = $carp_cont."/".$filename; 


            if (is_file($ruta_archivo)) {
                $size = filesize($ruta_archivo);   
                    ob_start();
                    header('Pragma: public');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Content-Description: File Transfer');
                    header('Content-Type: text/text');
                    header('Content-Disposition: attachment; filename="'.$file.'"');
                    header('Content-Transfer-Encoding: binary');
                    header('Cache-Control: max-age=0');      
                    ob_clean();
                    flush();
                    readfile($ruta_archivo);
                    exit;
            }
        } catch (\Exception $e) {           
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));    
            return $this->redirect(['/site/index']);            
        }  
    } 
    
    /****************************************************************/
    /*****************************************************************/
    public function actionConvertirAExcel($id){
        ini_set('memory_limit', -1);
        set_time_limit(-1);
        try{
            $modelDA = $this->findModel($id);
        
            $serviciosDA = \app\models\ServicioDebitoAutomatico::find()->where('id_debitoautomatico='.$id)->all();
        
            $objPHPExcel = new Spreadsheet();
            $objPHPExcel->setActiveSheetIndex(0);

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true); 
            $this->cellColor($objPHPExcel, 'A1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'B1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'C1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'D1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'E1', 'F28A8C');                
                
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'FAMILIA');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'FOLIO');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'ALUMNO');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'SERVICIO');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'MONTO SERVICIO ENVIADO'); 
            
            $letracolumnainicio = 'A';
            $letrafilainicio = 3;

            $i = 0;                        
            $contador = count($serviciosDA);
            
            while ($i < $contador) {
                $letrafilainicio1 = (string) $letrafilainicio;
                $columnaA = 'A' . $letrafilainicio1;
                $columnaB = 'B' . $letrafilainicio1;
                $columnaC = 'C' . $letrafilainicio1;
                $columnaD = 'D' . $letrafilainicio1;
                $columnaE = 'E' . $letrafilainicio1;

                if($serviciosDA[$i]['tiposervicio']== DebitoAutomatico::ID_TIPOSERVICIO_SERVICIOS){
                    $servicioAlumno = \app\models\ServicioAlumno::findOne($serviciosDA[$i]['id_servicio']);
                    $alumno = \app\models\Alumno::findOne($servicioAlumno->id_alumno);
                    $familia = \app\models\GrupoFamiliar::findOne($alumno->id_grupofamiliar);

                    $objPHPExcel->getActiveSheet()->setCellValue($columnaA, $familia->apellidos);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaB, $familia->folio);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaC, $alumno->miPersona->apellido . " " .$alumno->miPersona->nombre);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaD, $servicioAlumno->datosMiServicio);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaE, $serviciosDA[$i]['importe']);
                }else
                if($serviciosDA[$i]['tiposervicio']==DebitoAutomatico::ID_TIPOSERVICIO_CONVENIO_PAGO){
                    $cuotaConvenioPago = \app\models\CuotaConvenioPago::findOne($serviciosDA[$i]['id_servicio']);
                    $convenioPago = \app\models\ConvenioPago::findOne($cuotaConvenioPago->id_conveniopago); 
                    $familia = \app\models\GrupoFamiliar::findOne($convenioPago->id_familia);

                    $objPHPExcel->getActiveSheet()->setCellValue($columnaA, $familia->apellidos);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaB, $familia->folio);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaC, "FAMILIA");
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaD, "CUOTA CONVENIO PAGO - NRO CONVENIO: " . $convenioPago->id);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaE, $serviciosDA[$i]['importe']);
                }
                $i = $i + 1;
                $letrafilainicio += 1;
            } 
                
            $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados"; //carpeta a almacenar los archivos
            $nombre_archivo = "debitoAutomaticoExcel" . $modelDA->id . ".xlsx";                                
            $ruta_archivo = $carp_cont . "/" . $nombre_archivo;

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
            $writer->save($ruta_archivo);   
            
            $url_pdf = \yii\helpers\Url::to(['/debito-automatico/descarga-excel', 'id' => $id]); 
            
            Yii::$app->response->format = 'json';
            return ['result_error' => '0', 'result_texto' => $url_pdf];
        }catch (Exception $e) {
            Yii::$app->response->format = 'json';
            return ['result_error' => '1', 'result_texto' => 'ERROR'];
        }
    }
    
    public function actionDescargaExcel($id) {  
        try{
            $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados"; //carpeta a almacenar los archivos                                       
            $ruta_archivo = $carp_cont . "/debitoAutomaticoExcel" . $id.".xlsx";

            if (is_file($ruta_archivo)) {
                $size = filesize($ruta_archivo);
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=debitos.xlsx");
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: " . $size);
                readfile($ruta_archivo);
            }
        }catch (Exception $e) {
            Yii::$app->response->format = 'json';
            return ['result_error' => '1', 'result_texto' => 'ERROR'];
        }
    }
    
    
    
    
            

}
