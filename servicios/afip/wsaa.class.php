<?php
namespace app\servicios\afip;

use Yii;

class WSAA {
    
    const CERT = "sicapCert.pem";        	# The X.509 certificate in PEM format. Importante setear variable $path
    const PASSPHRASE = "";
    //const CERT = "keys/alias.pfx";        	# The X.509 certificate in PEM format. Importante setear variable $path
    //const PASSPHRASE = "NoaH???";         				# The passphrase (if any) to sign
    const PRIVATEKEY = "sicapkey.key";  	# The private key correspoding to CERT (PEM). Importante setear variable $path

    const PROXY_ENABLE = false;

    
    //https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL // para obtener WSDL
    const URL = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms"; // homologacion (testing)
    //const URL = "https://wsaa.afip.gov.ar/ws/services/LoginCms"; // produccion 

    const TA 	= "TA.xml";  # Archivo con el Token y Sign
    const WSDL 	= "wsaa.wsdl";   # The WSDL corresponding to WSAA	
  
    /*
     * RUTAS  A LOS PATH QUE CONTIENE LOS ARCHIVOS PARA EL FUNCIONAMIENTO
    */
    private $path =''; 
    private $pathPtoVta = '';
    private $pathCertificados = '';
    private $pathXML = '';
    
    /*
     * manejo de errores
     */
    public $errores = [];
    public $huboerror = false;

    /**
    * Cliente SOAP
    */
    private $client;

    /*
    * servicio del cual queremos obtener la autorizacion
    */
    private $service; 
        
    /***********************************************************/
    /***********************************************************/	
    public function __construct($service = 'wsfe', $ptovta=null) 
    {
        ini_set("soap.wsdl_cache_enabled", "0");    
        ini_set('default_socket_timeout', 900);      
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        try{                
            $this->service = $service;    
            $this->path = \Yii::getAlias('@afip'). "/";

            $this->pathPtoVta.= $this->path . 'certificados/ptovta/';
            $this->pathCertificados.= $this->pathPtoVta . 'keys/';
            $this->pathXML.= $this->pathPtoVta . 'xml/';
            
            // validar archivos necesarios
            if (empty($ptovta)) {
                $this->errores[]="** INGRESE EL PTO VTA ";
            }
            
            if (!file_exists($this->pathCertificados . self::CERT)) {
                $this->errores[]="** NO EXISTE EL CERTIFICADO KEY: " . self::CERT;
            }
            if (!file_exists($this->pathCertificados . self::PRIVATEKEY)) {
              $this->errores[]= " ** NO EXISTE EL ARCHIVO DE LLAVE DEL CERIFICADO:  " . self::PRIVATEKEY;
            }
            if (!file_exists($this->path . self::WSDL)) {
                $this->errores[]= " ** NO EXISTE EL ARCHIVO WSDL PARA WSAA " . self::WSDL;
            }
              
            if(!empty($this->errores)) {
                $this->huboerror = true; 
            }else{  
                
                $arrContextOptions= [
                        'ssl' => [
                                'ciphers' => 'AES256-SHA',
                        ]
                ];
                $this->client = new \SoapClient($this->path.self::WSDL, array(
                    //'proxy_host'     => '',
                    //'proxy_port'     => '',
                    'soap_version'   => SOAP_1_2,
                    'location'       =>  self::URL,
                    'trace'          => 1,
                    'exceptions'     => 0,
                    'stream_context' => stream_context_create($arrContextOptions),
                    )
                );

                if(!$this->huboerror){
                    if($this->generamosTA())
                        $this->generar_TA();
                    }
            }
        }catch (GralException $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));                
            //Yii::$app->session->setFlash('error',Yii::$app->params['operacionFallida']);             
        }catch (\Exception $e){
            \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
            //Yii::$app->session->setFlash('error',Yii::$app->params['operacionFallida']);             
        }   
    } // FIN DEL CONSTRUCTOR
  
  /*
    * Convertir un XML a Array
    */
    private function xml2array($xml) 
    {    
            $json = json_encode( simplexml_load_string($xml));
            return json_decode($json, TRUE);
    }    

   /*
    * Funcion principal que llama a las demas para generar el archivo TA.xml
    * que contiene el token y sign
    */
    public function generar_TA()
    {
        // lo primero que hacemos es crear el archivo xml
        $this->crearTRA();

        $TA = $this->call_WSAA($this->sign_TRA());


        if (!file_put_contents($this->pathXML.self::TA, $TA)){
            $this->huboerror = true;
            $this->errores[]= ' ** ERROR EN LA GENERACIÓN DEL ARCHIVO TA';
            exit;
        }

        $this->TA = $this->xml2Array($TA);

        return true;
    }


    //orden de llamadas

    /*
    * Crea el archivo xml de TRA
    */
    private function crearTRA()
    {
        $TRA = new \SimpleXMLElement(
                    '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<loginTicketRequest version="1.0">'.
                    '</loginTicketRequest>');
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId', date('U'));

        $TRA->header->addChild('generationTime', date('c',date('U')-600));
        $TRA->header->addChild('expirationTime', date('c',date('U')+600));
        $TRA->addChild('service', $this->service);
        $TRA->asXML($this->pathXML.'TRA.xml');
        \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString("Se creó el tra.xml"));
    }        
        
        
        /*
	* This functions makes the PKCS#7 signature using TRA as input file, CERT and
	* PRIVATEKEY to sign. Generates an intermediate file and finally trims the 
	* MIME heading leaving the final CMS required by WSAA.
	* 
	* devuelve el CMS
	*/
	private function sign_TRA()
	{
            try{
               $tra = realpath($this->pathXML . "TRA.xml") ;
               $tratmp = $this->pathXML. "TRA.tmp" ;
               $cert =  realpath($this->pathCertificados.self::CERT);
               $key =  realpath($this->pathCertificados . self::PRIVATEKEY);
              
          
               
               $STATUS = openssl_pkcs7_sign( $tra, $tratmp, "file://" . $cert ,
                            array("file://" . $key , self::PASSPHRASE),
                            array(),
                            !PKCS7_DETACHED
                        );
         
                if (!$STATUS){                 
                    \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString("ERROR EN LA GENERACION DE LA FIRMA DEL CERTIFICADO tra.xml"));
                    $this->huboerror = true;
                    $this->errores[]= ' ** ERROR EN LA GENERACION DE LA FIRMA DEL CERTIFICADO';
                }

                $inf = fopen($tratmp, "r");
                $i = 0;
                $CMS = "";

               while (!feof($inf)) 
                { 
                  $buffer=fgets($inf);
                  if ( $i++ >= 4 ) {$CMS.=$buffer;}
                }

                fclose($inf);
                
                unlink($tratmp);
                \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString("Se firmo correctamente el TRA.XML"));
                return $CMS;
            }catch (\Exception $e){
                \Yii::$app->getModule('audit')->data('errorAction', \yii\helpers\VarDumper::dumpAsString($e));
                Yii::$app->session->setFlash('error',Yii::$app->params['operacionFallida']);     
               
            }   
	}
        
        
        /*
	* Conecta con el web service y obtiene el token y sign
	*/
	private function call_WSAA($cms)
	{     
        
            $results = $this->client->loginCms(array('in0' => $cms));
             

            // para logueo
            file_put_contents($this->pathPtoVta."request-loginCms.xml", $this->client->__getLastRequest());
            file_put_contents($this->pathPtoVta."response-loginCms.xml", $this->client->__getLastResponse());

            if (is_soap_fault($results)){
                $this->huboerror = true;
                $this->errores[]= ' ** EROR EN LA LLAMADA SOAP AL CLIENTE WSAA';
               
            } 
            \Yii::$app->getModule('audit')->data('call_WSA', json_encode($results));
	    return $results->loginCmsReturn;
	}
        
        
       /*
	* Obtener la fecha de expiracion del TA
	* si no existe el archivo, devuelve false
	*/
	public function get_expiration() 
	{    
            $r = null;
            if(empty($this->TA)) {
                if (file_exists($this->pathXML.self::TA)) {
                    $TA_file = file($this->pathXML.self::TA, FILE_IGNORE_NEW_LINES);
                    if($TA_file){
                            $TA_xml = '';
                            for($i=0; $i < sizeof($TA_file); $i++)
                                    $TA_xml.= $TA_file[$i];        
                            $this->TA = $this->xml2Array($TA_xml);
                            $r = $this->TA['header']['expirationTime'];
                    } else {
                            $r = null;
                    }
                }else
                    $r=null;
            } else {
		$r = $this->TA['header']['expirationTime'];
            }
            return $r;
	} 
        
        private function generamosTA(){
            $generar = false;
            if (!file_exists($this->pathXML . self::TA)) {
                $generar = true; //
            }else{
                // esto lo agrego yo; ya directamente desde aca a generar el 
                // Ta siempre sin preguntar porla fecha de vencimiento.      
                $expriracionTA = $this->get_expiration();
                if($expriracionTA!==null){
                    $fecha_expiracion = substr($this->get_expiration(), 0,10) . " " .substr($this->get_expiration(), 11,8);
                    $f_menorExipracion = new \DateTime($fecha_expiracion);
                    $f_mayorActual = new \DateTime(date('Y-m-d H:m:s'));
                    
                    if($f_menorExipracion<=$f_mayorActual){                        
                        $generar = true;
                    }
                    else
                       $generar = false; 
                }else
                    $generar = true;
                
                
                return $generar;
            }
        }
        
        
}
?>
