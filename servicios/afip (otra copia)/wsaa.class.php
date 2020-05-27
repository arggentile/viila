<?php
namespace app\models\afip;

class WSAA {
	const CERT = "keys/archivocrt.crt";        	# The X.509 certificate in PEM format. Importante setear variable $path
	const PRIVATEKEY = "keys/archivokey.key";  	# The private key correspoding to CERT (PEM). Importante setear variable $path
	const PASSPHRASE = "";         				# The passphrase (if any) to sign
	
        const PROXY_ENABLE = false;
	
        //https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL // para obtener WSDL
	//const URL = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms"; // homologacion (testing)
	const URL = "https://wsaa.afip.gov.ar/ws/services/LoginCms"; // produccion 
	
	const TA 	= "xml/TA.xml";  # Archivo con el Token y Sign
	const WSDL 	= "wsaa.wsdl";   # The WSDL corresponding to WSAA	
  
	/*
	* RUTAS  A LOS PATH QUE CONTIENE LOS ARCHIVOS PARA EL FUNCIONAMIENTO
	*/
        private $path =''; 
        private $pathPtoVta = '';
        
        /*
	 * manejo de errores
	 */
	public $error = '';
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
            ini_set('default_socket_timeout', 600);
            
            $this->service = $service;    
            $this->path = \Yii::getAlias('@app'). "/models/afip/";
            
            $this->pathPtoVta.= $this->path . 'ptovta'.$ptovta."/";
            
            // validar archivos necesarios
            if (empty($ptovta)) {
                $this->error.="** INGRESE EL PTO VTA ";
            }
            if (!file_exists($this->pathPtoVta . self::CERT)) {
                $this->error.="** NO EXISTE EL CERTIFICADO KEY: " . self::CERT;
            }
            if (!file_exists($this->pathPtoVta . self::PRIVATEKEY)) {
              $this->error.= " ** NO EXISTE EL ARCHIVO DE LLAVE DEL CERIFICADO:  " . self::PRIVATEKEY;
            }
            if (!file_exists($this->path . self::WSDL)) {
                $this->error.= " ** NO EXISTE EL ARCHIVO WSDL PARA WSAA " . self::WSDL;
            }

            if(!empty($this->error)) {
                $this->huboerror = true; 
            }else{    
                $this->client = new \SoapClient($this->path.self::WSDL, array(
                                    'soap_version'   => SOAP_1_2,
                                    'location'       => self::URL,
                                    'trace'          => 1,
                                    'exceptions'     => 0
                                    )
                                );
                
                if(!$this->huboerror){
$this->generar_TA();
                    // esto lo agrego yo; ya directamente desde aca a generar el 
                    // Ta siempre sin preguntar porla fecha de vencimiento.                    
                    $fecha_expiracion = substr($this->get_expiration(), 0,10) . " " .substr($this->get_expiration(), 11,8);
                    $f_menor = new \DateTime($fecha_expiracion);
                    $f_mayor = new \DateTime(date('Y-m-d H:m:s'));
                    if($f_menor<=$f_mayor){
                       if ($this->generar_TA()) {
                            
                        } else {
                            $this->error.='  ** Error al obtener el TA';
                        }
                    } 
                             
                }
            }
	} // FIN DEL CONSTRUCTOR
  
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
            $TRA->header->addChild('generationTime', date('c',date('U')-60));
            $TRA->header->addChild('expirationTime', date('c',date('U')+60));
            $TRA->addChild('service', $this->service);
            $TRA->asXML($this->pathPtoVta.'xml/TRA.xml');
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
            $STATUS = openssl_pkcs7_sign($this->pathPtoVta . "xml/TRA.xml", $this->pathPtoVta . "xml/TRA.tmp", "file://" . $this->pathPtoVta.self::CERT,
                        array("file://" . $this->pathPtoVta.self::PRIVATEKEY, self::PASSPHRASE),
                        array(),
                        !PKCS7_DETACHED
                    );
    
            if (!$STATUS){
                $this->huboerror = true;
                $this->error.= ' ** ERROR EN LA GENERACION DE LA FIRMA DEL CERTIFICADO';
                exit;
            }
            
            $inf = fopen($this->pathPtoVta."xml/TRA.tmp", "r");
            $i = 0;
            $CMS = "";
            while (!feof($inf)) { 
                        $buffer = fgets($inf);
                        if ( $i++ >= 4 ) $CMS .= $buffer;
            }
    
            fclose($inf);
            unlink($this->pathPtoVta."xml/TRA.tmp");
    
            return $CMS;
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
                $this->error.= ' ** EROR EN LA LLAMADA SOAP AL CLIENTE WSAA';
                exit;
            } 
	
            return $results->loginCmsReturn;
	}
  
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
					
            if (!file_put_contents($this->pathPtoVta.self::TA, $TA)){
                $this->huboerror = true;
                $this->error.= ' ** ERROR EN LA GENERACION DEL ARCHIVO TA';
                exit;
            }

            $this->TA = $this->xml2Array($TA);
	  
            return true;
	}
  
       /*
	* Obtener la fecha de expiracion del TA
	* si no existe el archivo, devuelve false
	*/
	public function get_expiration() 
	{    
            if(empty($this->TA)) {
                if (file_exists($this->pathPtoVta.self::TA)) {
                    $TA_file = file($this->pathPtoVta.self::TA, FILE_IGNORE_NEW_LINES);
                    if($TA_file){
                            $TA_xml = '';
                            for($i=0; $i < sizeof($TA_file); $i++)
                                    $TA_xml.= $TA_file[$i];        
                            $this->TA = $this->xml2Array($TA_xml);
                            $r = $this->TA['header']['expirationTime'];
                    } else {
                            $r = false;
                    }
                }else
                    $r=false;
            } else {
		$r = $this->TA['header']['expirationTime'];
            }
            return $r;
	} 
}
?>
