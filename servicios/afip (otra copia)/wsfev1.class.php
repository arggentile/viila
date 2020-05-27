<?php

namespace app\models\afip;

class WSFEV1 {    
	const CUIT 	= 30657171723;   # CUIT del emisor de las facturas. Solo numeros sin comillas.
	const TA 	= "xml/TA.xml";  # Archivo con el Token y Sign
	
        //https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL // para obtener WSDL

        const WSDL = "wsfev1.wsdl"; # The WSDL corresponding to WSFEV1	
	const LOG_XMLS = true;      # For debugging purposes
	
        //const WSFEURL = "https://wswhomo.afip.gov.ar/wsfev1/service.asmx"; // homologacion wsfev1 (testing)
	const WSFEURL =	"https://servicios1.afip.gov.ar/wsfev1/service.asmx"; //produccion
	
//const WSFEURL = "?????????/wsfev1/service.asmx"; // produccion  

	private $path = ''; 
	private $pathPtoVta = '';
        
        
       /*
	* manejo de errores
	*/
        public $huboerror = false; //$array de objetos de errores
	public $error = ''; //$array de objetos de errores
	public $ObsCode = '';
	public $ObsMsg = '';
	public $Code = ''; //codigo del error
	public $Msg = ''; //mensaje descrpctivo del error

       /**
	* Cliente SOAP
	*/
	private $client;
  
       /*
	* objeto que va a contener el xml de TA
	*/
	private $TA;
  
        
	/*
	* Constructor
	*/
	public function __construct($ptovta=null)
	{    
            
            ini_set("soap.wsdl_cache_enabled", "0");
            ini_set('default_socket_timeout', 600);
            ini_set('default_socket_timeout', 5);
            
            $this->path = \Yii::getAlias("@app"). "/models/afip/";
            $this->pathPtoVta.= $this->path . 'ptovta'.$ptovta."/";
            
            // validar archivos necesarios
            if(empty($ptovta)){
                $this->error.= " ** ERROR EN EL INGRESO DEL PTO VTA";          
            }                
            if (!file_exists($this->path . self::WSDL)) {
                $this->error.= " ** ERROR AL ABRIR EL ARCHIVO wsfev1.WSDL.  " . self::WSDL;
            }

            if(!empty($this->error)) {
                       $this->huboerror = true;
            }else{
                $this->client = new \SoapClient($this->path.self::WSDL, array( 
                                        'soap_version' => SOAP_1_2,
                                        'location'     => self::WSFEURL,
                                        'exceptions'   => 0,
                                        'trace'        => 1)
                );
            }
	}
  
	/*
	* Chequea los errores en la operacion, si encuentra algun error faltal lanza una exepcion
	* si encuentra un error no fatal, loguea lo que paso en $this->error
	*/
	private function _checkErrors($results, $method)
	{
            if (self::LOG_XMLS) {
                        file_put_contents($this->pathPtoVta."xml/request-".$method.".xml",$this->client->__getLastRequest());
                        file_put_contents($this->pathPtoVta."xml/response-".$method.".xml",$this->client->__getLastResponse());
            }
            //si hubo error 
            if (is_soap_fault($results)) {
                    $this->huboerror = true;
                    $this->error.=' ** ERROR EN LA INTERPRETACION DEL ARCHIVO SOAP PARA . '.$method;
                    exit;
            }
    
            if ($method == 'FEDummy') {return;}

            $XXX=$method.'Result';
            
            if(isset($results->$XXX->Errors)){                
                if ($results->$XXX->Errors->Err->Code != 0) {
                        $this->error.= "Method=$method errcode=".$results->$XXX->Errors->Err->Code." errmsg=".$results->$XXX->Errors->Err->Msg;
                }
                        
            }    	
	
            //asigna error a variable
            if ($method == 'FECAESolicitar') {
                    if(isset($results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones)){
                        $this->error.=$results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->Code." ".$results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->Msg;
                    }
                    if (isset($results->FECAESolicitarResult->FeDetResp->FEDetResponse->Obs)){
                            $this->error.=$results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Obs->Observaciones->Code." ".$results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Obs->Observaciones->Msg;
                            $this->ObsCode = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Obs->Observaciones->Code;
                            $this->ObsMsg = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Obs->Observaciones->Msg;
                    }
             }
            
            if(isset($results->$XXX->Errors->Err->Code)){
                $this->error.=' *** '. $results->$XXX->Errors->Err->Code;
                $this->Code = $results->$XXX->Errors->Err->Code;                
            }
            if(isset($results->$XXX->Errors->Err->Msg)){
                $this->error.='  ' . $results->$XXX->Errors->Err->Msg;
                $this->Msg = $results->$XXX->Errors->Err->Msg;
            }	
            //fin asigna error a variable
	    if(isset($results->$XXX->Errors->Err->Code)){
                return $results->$XXX->Errors->Err->Code != 0 ? true : false;}
            else
                return false;
	}

	/**
	* Abre el archivo de TA xml,
	* si hay algun problema devuelve false
	*/
	public function openTA()
	{
            $this->TA = simplexml_load_file($this->pathPtoVta.self::TA);
            return $this->TA == false ? false : true;
	}
  
        /*
	 *   Retorna el Ultimo comprobante autorizado para el tipo de comprobante,
         * cuit, punto de ventay tipo de emision
	 */ 
	public function FECompUltimoAutorizado($ptovta, $tipo_cbte)
	{
            $results = $this->client->FECompUltimoAutorizado(
                            array('Auth'=>array('Token' => $this->TA->credentials->token,
						'Sign' => $this->TA->credentials->sign,
						'Cuit' => self::CUIT),
                                    'PtoVta' => $ptovta,
                                    'CbteTipo' => $tipo_cbte));
            
		
            $e = $this->_checkErrors($results, 'FECompUltimoAutorizado');
	   
            
            return $e == false ? $results->FECompUltimoAutorizadoResult->CbteNro : false;
	} 
        
        public function FEParamGetTiposDoc()
	{
                $results = $this->client->FEParamGetTiposDoc(
                                    array('Auth' =>array('Token' => $this->TA->credentials->token,
							'Sign' => $this->TA->credentials->sign,
							'Cuit' => self::CUIT),
                                    ));
		
                $e = $this->_checkErrors($results, 'FEParamGetTiposDoc');
				
                return $e == false ? $results->FEParamGetTiposDocResult->ResultGet->CbteTipo : false;
	} //end function recuperaLastCMP
	
	public function FEParamGetTiposCbte()
	{
                $results = $this->client->FEParamGetTiposCbte(
                                    array('Auth' =>array('Token' => $this->TA->credentials->token,
							'Sign' => $this->TA->credentials->sign,
							'Cuit' => self::CUIT),
                                    ));
		
                $e = $this->_checkErrors($results, 'FEParamGetTiposCbte');
               
                return $e == false ? $results->FEParamGetTiposCbteResult->ResultGet->CbteTipo : false;
	} //end function recuperaLastCMP

	public function FEDummy(){
		$results = $this->client->FEDummy();
                var_dump($results);
                exit;
		
	} 
        
	/*
	* Solicitud CAE y fecha de vencimiento 
	*/	
	public function FECAESolicitar($cbte, $ptovta, $regfe)
	{
	$params = array( 
                    'Auth'=>array(
                                'Token' => $this->TA->credentials->token,
				'Sign' => $this->TA->credentials->sign,
				'Cuit' => self::CUIT
                            ), 
                    'FeCAEReq' => 
                        array(
                            'FeCabReq'=>array( 
                                'CantReg' => 1,
                                'CbteTipo' => $regfe['CbteTipo'],
                                'PtoVta' => $ptovta
                        ),
                    'FeDetReq' => 
                        array('FECAEDetRequest' => 
                                array(  'Concepto' => $regfe['Concepto'],
					'DocTipo' => $regfe['DocTipo'],
					'DocNro' => $regfe['DocNro'],
					'CbteDesde' => $cbte,
					'CbteHasta' => $cbte,
					'CbteFch' => $regfe['CbteFch'],
                                        'ImpTotal'=> $regfe['ImpTotal'], 
                                        'ImpTotConc'=>$regfe['ImpTotConc'], 
					'ImpNeto' => $regfe['ImpNeto'],
					'ImpOpEx' => $regfe['ImpOpEx'],
                                        'ImpTrib' => $regfe['ImpTrib'],
					'ImpIVA' => $regfe['ImpIVA'],
					'FchServDesde' => $regfe['FchServDesde'], //null
					'FchServHasta' => $regfe['FchServHasta'], //null
					'FchVtoPago' => $regfe['FchVtoPago'], //null
					'MonId' => $regfe['MonId'], //PES 
					'MonCotiz' => $regfe['MonCotiz'],
					), 
			), 
		), 
	);
	
       
	$results = $this->client->FECAESolicitar($params);
        $e = $this->_checkErrors($results, 'FECAESolicitar');
        
        if ( ($results->FECAESolicitarResult->FeCabResp->Resultado == 'A') || 
                (($results->FECAESolicitarResult->FeCabResp->Resultado == 'P')) ){
            $nroCae = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE;
            if($nroCae === false || $nroCae <= 0) {
               $this->error.=' ** SE PRODUJO UN ERROR AL OBTENER EL CAE';
               return -1;
            }else{
                $resp_cae = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE;
                $resp_caefvto = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto;
               
                
                return  array( 'cae' => $resp_cae, 'fecha_vencimiento' => $resp_caefvto); 
            }  
        }else {
          $this->error.=' ** SE PRODUJO UN ERROR AL OBTENER EL CAE';
          return -1;
        }
            
            
        
	
	
	
	} //end function FECAESolicitar
	
} // class

?>
