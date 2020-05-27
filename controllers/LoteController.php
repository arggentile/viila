<?php

namespace app\controllers;

use Yii;
use app\models\Lote;
use app\models\search\LoteSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LoteController implements the CRUD actions for Lote model.
 */
class LoteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Lote models.
     * @return mixed
     */
    public function actionIndex()
    {
        try{
            $searchModel = new \app\models\LoteSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }catch (\Exception $e) {             
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            Yii::$app->session->setFlash('error', Yii::$app->params['operacionFallida']);
            return $this->redirect(Yii::$app->request->referrer);                        
        }    
    }

    /**
     * Displays a single Lote model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        try{
            $modelLote = $this->findModel($id);
            
            $searchModel = new \app\models\RegistroLoteSearch();
            $searchModel->id_lote = $id;
            $dataProviderregistros = $searchModel->search(Yii::$app->request->queryParams);
       
            
        }catch (\Exception $e) {             
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            Yii::$app->session->setFlash('error', Yii::$app->params['operacionFallida']);
            return $this->redirect(Yii::$app->request->referrer);                        
        }    
        
        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProviderregistros'=>$dataProviderregistros
        ]);
    }

    /**
     * Creates a new Lote model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {        
        try{
            $transaction = Yii::$app->db->beginTransaction();
            $model = new Lote();
            $valid = true;
            
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $ruta = Yii::getAlias('@webroot') . "/archivos_generados/".$model->id.".txt";
                
                $model->archivoentrante = \yii\web\UploadedFile::getInstance($model, 'archivoentrante');
                if($model->archivoentrante->saveAs($ruta)){                        
                    $documento =\PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
                    $hojaActual = $documento->getSheet(0);
                                            
                    $numeroMayorDeFila = (int) $hojaActual->getHighestRow(); // Numérico
                    $letraMayorDeColumna = $hojaActual->getHighestColumn(); // Letra
                    # Convertir la letra al número de columna correspondiente
                    $numeroMayorDeColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($letraMayorDeColumna);
//                    var_dump($numeroMayorDeFila);
//                    var_dump($letraMayorDeColumna);
//                    var_dump($numeroMayorDeColumna);
                   
//                    if($letraMayorDeColumna!=='F'){                       
//                        $valid = false;
//                        $detalleError = 'El archivo no cumple con las especificaciones; posee mas de 6 columnas';
//                    }else{
//                       
                           
                       
                    for($indiceFila = 2; $indiceFila <= $numeroMayorDeFila; $indiceFila++) {
                        $nombre = $hojaActual->getCellByColumnAndRow(1, $indiceFila);
                        $tipoDni = $hojaActual->getCellByColumnAndRow(2, $indiceFila);
                        $numeroDni = $hojaActual->getCellByColumnAndRow(3, $indiceFila);
                        $email = $hojaActual->getCellByColumnAndRow(4, $indiceFila);
                        $concepto = $hojaActual->getCellByColumnAndRow(5, $indiceFila);
                        $monto = $hojaActual->getCellByColumnAndRow(6, $indiceFila);

                        $nombre = (empty($nombre->getFormattedValue()))?'-':$nombre->getFormattedValue();   
                        $tipoDni = (empty($tipoDni->getFormattedValue()))?'-':$tipoDni->getFormattedValue();   
                        $numeroDni = (empty($numeroDni->getFormattedValue()))?'-':$numeroDni->getFormattedValue();   
                        $email = (empty($email->getFormattedValue()))?'-':$email->getFormattedValue();   
                        $concepto = (empty($concepto->getFormattedValue()))?'-':$concepto->getFormattedValue();   
                        $monto = (empty($monto->getFormattedValue()))?'-':$monto->getFormattedValue();   

                        $registro = new \app\models\RegistroLote();
                        $registro->id_lote = $model->id;
                        $registro->nombre_cliente = $nombre;
                        $registro->tipo_dni = $tipoDni;
                        $registro->dni = $numeroDni;
                        $registro->email = $email;
                        $registro->concepto = $concepto;
                        $registro->monto = $monto;
                        $errores='';
                         
                        if(!($tipoDni == 'DNI' || $tipoDni == 'CUIL' || $tipoDni == 'CUIT'))
                            $errores.=' El tipo de documento no es valido';                        
                        
                        if(($tipoDni == 'CUIL' || $tipoDni == 'CUIT') && (strlen($numeroDni)!==11))
                            $errores.='  Numero de documento no contiene los digitos correspondientes para erl tipo de dni';
                        $registro->error = $errores;     
                        if(!$registro->save()){
                            \Yii::$app->getModule('audit')->data('erorrregistro', \yii\helpers\VarDumper::dumpAsString($registro->getErrors()));  
                        }
                    }
                   
                  
                          
                    if($valid){ 
                        $transaction->commit();
                        $resultado = $this->armarFacturasLote($model->id);
                        if($resultado['success'])
                            var_dump("sii");
                        else
                            var_dump("no");
                    }else{
                        var_dump("noo");
                    }
                    exit;
                }else{
                    Yii::$app->session->setFlash('error', 'no se pudo grabar el archivo en el disco');
                    $transaction->rollBack();
                }
            }
           
       
        }catch (\Exception $e) { 
            var_dump($e->getMessage());
                    exit;
            (isset($transaction) && $transaction->isActive)?$transaction->rollBack():'';
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));  
            Yii::$app->session->setFlash('error', Yii::$app->params['operacionFallida']);
            return $this->redirect(Yii::$app->request->referrer);                        
        }
        



        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Lote model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Lote model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Lote model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Lote the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Lote::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    
    /*  */
    private function armarFacturasLote($idLote){
        try{
                $transaction = Yii::$app->db->beginTransaction();
                $modelLote = $this->findModel($idLote);
                if(!$modelLote)
                    throw new HttpException('Modelo de lote inexistente');
                
                $valid = true;
                $facturas=[];
                $registrosLote = \app\models\RegistroLote::find()->andWhere(['id_lote'=>$idLote])->all();
                if(!empty($registrosLote)){
                    foreach($registrosLote as $registro){
                       /* \app\models\RegistroLote $registro*/ 
                        $modelTiket = new \app\models\Tiket();
                        $modelTiket->xfecha_tiket = $modelLote->fecha;
                        $modelTiket->fecha_pago = $modelLote->fecha;                
                        $modelTiket->importe = $registro->monto;
                        $modelTiket->detalles=$registro->concepto;
                        $modelTiket->id_registro=$registro->id;
                        if(!$modelTiket->save()){
                            $valid = false;
                            \Yii::$app->getModule('audit')->data('errorTikets', \yii\helpers\VarDumper::dumpAsString($modelTiket->getErrors())); 
                        }else{
                            $ptoVta=1;
                            $resultFactura = \app\models\Factura::generaFactura($ptoVta, $modelTiket->importe, $modelTiket->fecha_tiket, $modelTiket->id);   
                            if(!$modelTiket->save()){
                                $valid = false;
                                \Yii::$app->getModule('audit')->data('errorTikets', \yii\helpers\VarDumper::dumpAsString($resultFactura->getErrors())); 
                            }else{
                                $idFactura = $resultFactura['modelFactura']->id;
                                $resultAvisoFactura = \app\models\Factura::avisarAfip($idFactura, $ptoVta, $registro->tipo_dni , $registro->dni, $modelTiket->importe, $modelTiket->fecha_tiket, $modelTiket->id);    
                            }
                        }
                    }
                }
                
                if($valid){
                    $transaction->commit();
                    
                       
                    $response['success'] = true;    
                    
                }else{
                     $transaction->rollBack();
                    $response['success'] = false;    
                }
                
               return $response;
                                     
                   
                   
                   
          
               
        }catch (\Exception $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            Yii::$app->session->setFlash('error',Yii::$app->params['operacionFallida']);             
        }   
    }
//    
      public function actionArmarPruebaFactura(){
//      phpinfo();
//      exit;
        try{
            var_dump("1111");
                $modelTiket = new \app\models\Tiket();
                $modelTiket->xfecha_tiket = '10-01-2020';
                $modelTiket->fecha_pago = '2020-01-10';                
                $modelTiket->importe = 50;
                $modelTiket->detalles="dasdasdasd";
                
                if($modelTiket->save()){
                    var_dump("siii");
                   
                    //$ptoVta = Yii::$app->params['ptoVtaAfip'];
                    $ptoVta=1;
                    var_dump("siii");
                        $resultFactura = \app\models\Factura::generaFactura($ptoVta, $modelTiket->importe, $modelTiket->fecha_tiket, $modelTiket->id);
                        
                        if($resultFactura['success']){
                            $generoFactura = true;
                            $idFactura = $resultFactura['modelFactura']->id;
                            $resultAvisoFactura = \app\models\Factura::avisarAfip($idFactura, $ptoVta, "CUIL", "20327097351", $modelTiket->importe, $modelTiket->fecha_tiket, $modelTiket->id);
                            if($resultAvisoFactura['success']){
                                
                            }                           
                        }                 
                        var_dump($resultAvisoFactura);
                   exit;
                    //var_dump("3333");
                    //var_dump($response);
                }else{
                         var_dump("222");       
                    var_dump($modelTiket->errors);
        }
        }catch (\Exception $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            Yii::$app->session->setFlash('error',Yii::$app->params['operacionFallida']);             
        }   
    }
}
