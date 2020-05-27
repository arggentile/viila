<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use kartik\mpdf\Pdf;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

use \app\helpers\GralException;

/**
 * Description of CajaController
 *
 * @author agus
 */
class CajaController extends \yii\web\Controller {
    
    
    
    
    /**
     * @inheritdoc
     */
    /*
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [ 
                    [     
                        'actions' => ['cobrar',''],
                        'allow' => true,              
                        'roles' => ['egresarAlumnos'],
                    ],
                ],
                'denyCallback' => function($rule, $action){           
                    if(Yii::$app->request->isAjax){                       
                        throw new GralException('Acceso denegado, usted no dispone de los permisos suficienes para realizar la acción');
                    }else{   
                        throw new \yii\web\ForbiddenHttpException();
                    }
                }
            ],  
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['GET'],
                ],
            ],
        ];
    }*/
    
    
    
    
    
    public function actions()
    {
        return [
            'buscarFamilia' => 'app\actions\BuscarFamiliaAction',
            'buscarDeudaFamilia' => 'app\actions\BuscarDeudaFamiliaAction',
            //accion comun para descargar archivos excel
            'down-padron-excel'=>'app\actions\DescargaPadronExcelAction',            
        ];
    } 
    
    
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    /*******************************************************************/
    /*******************************************************************/
     public function actionCobrar() {
        try 
        {
            $modelTiket = new \app\models\Tiket(); 
            $familiaTiket = null;
            
            if($modelTiket->load(Yii::$app->request->post())){
                $response = Yii::$app->serviceCaja->generarTiket($modelTiket);
                if($response['success']){                      
                    Yii::$app->session->setFlash('success', Yii::$app->params['eliminacionCorrecta']);  
                    $idTiket = $response['modelsTiket']->id;
                    return $this->redirect(['detalle-tiket', 'id'=>$idTiket]);
                }else{                    
                    Yii::$app->session->setFlash('error', Yii::$app->params['eliminacionErronea']);                   
                }    
            }
        }catch (\Exception $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            Yii::$app->session->setFlash('error', 'Atención!!! <br /> Se Produjo un error severo');
            $this->redirect(['/site/index']);
        }
        
        return $this->render('formCobro', [
            'modelTiket'=>$modelTiket, 
            
        ]);     
    }
    
    public function actionCobrarServicios() {
        try 
        {
            $modelTiket = new \app\models\Tiket(); 
            $familiaTiket = null;
            
            if($modelTiket->load(Yii::$app->request->post())){
                $selectServicios = Yii::$app->request->post('servicios-tiket');
                $selectCuotasCP = Yii::$app->request->post('cuotascp-tiket');
                $servicios = null;
                if(!empty($selectServicios)){               
                    $servicios = explode(',', $selectServicios);
                    array_unique($servicios);
                }
            
                $cuotasCP = null;
                if(!empty($selectCuotasCP)){               
                    $cuotasCP = explode(',', $selectCuotasCP);
                    array_unique($cuotasCP);
                }
            
                $response = Yii::$app->serviceCaja->generarTiket($modelTiket, $servicios,$cuotasCP);
                if($response['success']){                      
                    Yii::$app->session->setFlash('success', Yii::$app->params['eliminacionCorrecta']);  
                    $idTiket = $response['modelsTiket']->id;
                    return $this->redirect(['detalle-tiket', 'id'=>$idTiket]);
                }else{                    
                    Yii::$app->session->setFlash('error', Yii::$app->params['eliminacionErronea']);                   
                }    
            }
            
            
            $selectServicios = Yii::$app->request->get('selectServicios');
            $selectCuotasCP = Yii::$app->request->get('selectCuotasCP');
            $familia = Yii::$app->request->get('familia');
            
            $servicios = null;
            if(!empty($selectServicios)){               
                $servicios = explode(',', $selectServicios);
                array_unique($servicios);
                $serviciosEnTiket = implode(",", $servicios);
            }else
                $serviciosEnTiket = null;
            
            
            $cuotasCP = null;
            if(!empty($selectCuotasCP)){               
                $cuotasCP = explode(',', $selectCuotasCP);
                array_unique($cuotas);
                $cuotasEnTiket = implode(",", $cuotas);
            }else
                $cuotasEnTiket = null;
            
            
            if(!empty($familia))
                $dataProvider =  Yii::$app->serviceServicioAlumno::devolverDeudaFamilia($familia, null,(is_null($serviciosEnTiket))?0:$serviciosEnTiket, (is_null($cuotasEnTiket))?0:$cuotasEnTiket);
            else
                $dataProvider = '';
           
          
            
        } catch (\Exception $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            Yii::$app->session->setFlash('error', 'Atención!!! <br /> Se Produjo un error severo');
            $this->redirect(['/site/index']);
        }
        
        return $this->render('formTiketCobroServicios', [
            'modelTiket'=>$modelTiket, 
            'serviciosEnTiket'=>$serviciosEnTiket,               
            'cuotasEnTiket'=>$cuotasEnTiket,
            'dataProvider'=>$dataProvider
        ]);     
    }
    
    /************************************************************************/
    /************************************************************************/
    public function actionReporteTiketEmitidos(){
        try{
            $export = Yii::$app->request->get('export');
            if(isset($export) && $export==1)
                return $this->exportarReporteTiketsEmitidos();
            
            $searchModelTiket = new \app\models\search\TiketSearch();
            $dataProviderTiketEmitidos = $searchModelTiket->search(Yii::$app->request->queryParams);  
            
            
        }catch (GralException $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            Yii::$app->session->setFlash('error', 'Atención!!! <br /> Se Produjo un error severo');
            $this->redirect(['/site/index']);
        } catch (\Exception $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            Yii::$app->session->setFlash('error', 'Atención!!! <br /> Se Produjo un error severo');
            $this->redirect(['/site/index']);
        }
        
        return $this->render('tiketsEmitidos/tiketsEmitidos', [
                'searchModelTiket'=>$searchModelTiket, 
                'dataProviderTiketEmitidos'=>$dataProviderTiketEmitidos,               
            ]);  
        
        
    }
    
    
    public function exportarReporteTiketsEmitidos() { 
        try{
            $searchModelTiket = new \app\models\search\TiketSearch();
            $dataProviderTiketEmitidos = $searchModelTiket->search(Yii::$app->request->queryParams);  
            $dataProviderTiketEmitidos->setPagination(false); 
           
            $data = $dataProviderTiketEmitidos->getModels();          
            
            $i = 0;                        
            $contador = count($data);
           
            $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet(); 
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);


            $this->cellColor($objPHPExcel, 'A1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'B1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'C1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'D1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'E1', 'F28A8C');
            $this->cellColor($objPHPExcel, 'F1', 'F28A8C');
               
                
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Nro Tiket');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Fecha');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Medio Pago');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Importe');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Nro Factura');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', 'CAE');

            $letracolumnainicio = 'A';
            $letrafilainicio = 3;
            if(!empty($data))
             foreach($data as $modelTiket){
                /* @var Tiket $modelTiket */
            
                $letrafilainicio1 = (string) $letrafilainicio;
                $columnaA = 'A' . $letrafilainicio1;
                $columnaB = 'B' . $letrafilainicio1;
                $columnaC = 'C' . $letrafilainicio1;
                $columnaD = 'D' . $letrafilainicio1;
                $columnaE = 'E' . $letrafilainicio1;  
                $columnaF = 'F' . $letrafilainicio1;  

                $objPHPExcel->getActiveSheet()->setCellValue($columnaA, $modelTiket->id);
                $objPHPExcel->getActiveSheet()->setCellValue($columnaB, $modelTiket->xfecha_tiket);
                $objPHPExcel->getActiveSheet()->setCellValue($columnaC, $modelTiket->tipopago->nombre);
                $objPHPExcel->getActiveSheet()->setCellValue($columnaD, $modelTiket->importe);
                if($modelTiket->miFactura && ($modelTiket->miFactura->informada || !empty($modelTiket->miFactura->cae))){
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaE, "SI: ". $modelTiket->miFactura->fecha_informada);
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaF, $modelTiket->miFactura->cae);     
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaE, "No se informo a la AFIP. Error");
                    $objPHPExcel->getActiveSheet()->setCellValue($columnaF, "No se informo a la AFIP. Error");   
                } 
                
                
                $i = $i + 1;
                $letrafilainicio += 1;
            }  

            $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados"; //carpeta a almacenar los archivos
            $nombre_archivo = "listadoBonificacionesAlumnos" . Yii::$app->user->id . ".xlsx";                                
            $ruta_archivo = $carp_cont . "/" . $nombre_archivo;

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
            $writer->save($ruta_archivo); 
            $url_pdf = \yii\helpers\Url::to(['down-padron-excel', 'archivo' => $nombre_archivo]);               
            return $this->redirect($url_pdf); 
        }catch (\Exception $e) {
            Yii::error('exportar Alumnos '.$e);
            Yii::$app->session->setFlash('error', Yii::$app->params['errorExcepcion']);
            return $this->redirect(['/site/index']);            
        }  
    }
    /***********************************************************/
    
    
    public function actionDetalleTiket($id){
        try{
            $idTiket = Yii::$app->request->get('id');
            $modelTiket = \app\models\Tiket::findOne($idTiket);
            if(!$modelTiket)
                throw  new GralException('No se encontró el modelo del Tiket');
            
            $modelFactura = \app\models\Factura::find()->andWhere(['id_tiket'=>$modelTiket->id])->one();
            if(!$modelFactura)
                $modelFactura = null;
            
            $serviciosTiket = \app\models\ServiciosTiket::find()->andWhere(['id_tiket'=>$modelTiket->id_factura])->all();
            
        }catch (GralException $e) {   
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            throw new GralException($e->getMessage());            
        }catch (\Exception $e){ 
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, $e->getMessage());
        }  
        
        return $this->render('detalleTiket', [
            'modelTiket'=>$modelTiket,
            'modelFactura'=>$modelFactura,   
            'serviciosTiket'=>$serviciosTiket
        ]);   
    }
    
    
    /*********************************************************************/
        /*
     * Arma un modelo de plantilla para el tiket; detallando su detales y servicios en modo html;
     * el mismo puede ser enviado apantalla o incrustado en un pdf
     */
    private function armarPdfFactura($idFactura, $idTiket, $idCliente) {
       
        $modelFactura = \app\models\Factura::findOne($idFactura);
        $modelTiket = \app\models\Tiket::findOne($idTiket);
        $cliente = \app\models\GrupoFamiliar::findOne($idCliente);

        $encabezado = "<table border='1' cellpadding='0' cellspacing='0'  style='width:100%;'>";
        $encabezado .= "<thead>
                            <tr>
                            <td style='width:50%; text-align:center; font-size: 10px; padding-top: 8px; padding-bottom: 8px;'>
                                <img src='./images/logodonbsco2.png' alt='logo' class='img-responsive'/><br />
                            </td>
                            <td style='width:50%; text-align:center;'>
                                <br />
                                <b> Recibo C </b> <br />
                                <b> Nro ". \str_pad($modelTiket->id, 8, "0", \STR_PAD_LEFT) . "</b> <br />
                                <b> Fecha: " . \Yii::$app->formatter->asDate($modelTiket->fecha_pago) . "</b>
                                    
                            </td>
                        </tr>
                        <tr> 
                        <td style='padding:8px;' colspan='2'>
                            <b> Familia: </b> " . $cliente->apellidos ." (Folio: ".$cliente->folio. " )<br />
                        </td>                    
                       </tr>
                       </thead>";

        $cuerpo = "<tbody><tr> 
                     <td  tyle='padding-top: 10px; padding-bottom:10px;' colspan='2'>";
        $cuerpo .= "<table border='0' cellpadding='0' cellspacing='0'  style='width:100%;'>";
        $cuerpo .= "<tr><td style='padding-left: 8px; padding-right:8px;'> <b> En concepto de: </b><br /><br /></td></tr>";

        //buscamos los servicios de la factura
        $serviciosTiket = \app\models\ServiciosTiket::find()->where(['id_tiket' =>  $modelTiket->id])->all();
        if (count($serviciosTiket) == 0) {
            $cuerpo .= "<tr><td style='padding-left: 8px; padding-right:8px;'> " . $modelTiket->detalles . "</td></tr>";
        } else {
            foreach ($serviciosTiket as $servicio) {                
                    $cuerpo .= "<tr><td style='padding-left: 8px; padding-right:8px;'>" . $servicio->miDetalleFactura . "</td></tr>";                   
                
            }
        }
        $cuerpo .= "</table>";
        $cuerpo .= '</td></tr></tbody>';

        $pie = "<tr><td colspan='2' style='text-align:right; padding-top:10px;padding-right:10px;'>TOTAL:  ";
        $pie .= " $ $modelTiket->importe</td></tr>";
        
        $pie .= '</tfoot></table>';

        $html = $encabezado . $cuerpo . $pie;
        return $html;
    } 
    
    public function actionPdfTiket() {
        try{
            $idTiket = Yii::$app->request->get('idTiket');
            $modelTiket = \app\models\Tiket::findOne($idTiket);
            if (empty($modelTiket))
                throw new GralException('El tiket no existe.');
            
            $modelFactura = \app\models\Factura::find()->andWhere(['id_tiket'=>$idTiket])->one();
            if (empty($modelFactura))
                throw new GralException('La factura no existe.');
            
            $cliente = \app\models\GrupoFamiliar::findOne($modelTiket->id_cliente);
            if (empty($cliente))
                throw new GralException('Cliente no enconrado.');
            
            $plantilla = $this->armarPdfFactura($modelFactura->id, $modelTiket->id, $cliente->id); 
            
            $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados"; //carpeta a almacenar los archivos
            $nombre_archivo = "tiket-" . $modelTiket->id . ".pdf";
            $archivo = $carp_cont . '/' . $nombre_archivo;
            
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'format' => Pdf::FORMAT_A4,
                'orientation' => Pdf::ORIENT_PORTRAIT,
                'destination' => Pdf::DEST_BROWSER,                
                'options' => ['title' => 'Krajee Report Title'],
                'methods' => [
                    'SetHeader' => ['Krajee Report Header'],
                    'SetFooter' => ['{PAGENO}'],
                ]
            ]);
            
            $pdf->output($plantilla, $archivo, 'F');
            $url_pdf = \yii\helpers\Url::to(['/caja/down-pdf', 'name' => $nombre_archivo]);
            Yii::$app->response->format = 'json';
            return ['result_error' => '0', 'result_texto' => $url_pdf];
            //return $this->redirect($url_pdf);
        }catch(GralException $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, $e->getMessage());
        }catch(Exception $e) {
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            throw new \yii\web\HttpException(500, $e->getMessage());
        }
    }

    /*
     * Inicia la descarga delpdf en el navegador
     */
    public function actionDownPdf($name) {
        $carp_cont = Yii::getAlias('@webroot') . "/archivos_generados"; //carpeta a almacenar los archivos
        $archivo = $carp_cont . '/' . $name;
        if (is_file($archivo)) {
            $size = filesize($archivo);
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=$name");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $size);
            readfile($archivo);
            unlink($archivo);
        }
    } // FIN DescargaPdfConvenio  
    
    
    
    /****************************************/
    public function cellColor($objPHPExcel,$cells,$color)
    {
        $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startcolor' => array('rgb' => $color) ));
    }  
    
}
