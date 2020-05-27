<?php
namespace app\servicios\afip;



include('wsaa.class.php');
include('wsfev1.class.php');

use yii\web\HttpException;

class FacturaAfipService {
    
    public $errores = [];
    public $conerror = false;
    
    public $ptoVta;
    
    public $nroFactura;
    public $fechaVtoCae;
    public $nroCae;
    
    public $tipoDoc;
    public $nroDoc;
    public $monto;
    
    /*******************************************************************/
    /*******************************************************************/    
    //generamos directamente el archivo WSA para que 
    //genere los archivos necesarios como el TA y demas
    public function __construct($nroPtoVta=null) 
    {
        if(empty($nroPtoVta)){            
            $this->errores[]=' INGRESE EL PUNTO VENTA PARA GENERAR LA FACTURA';
            $this->conerror= true;            
        }else{            
            $ptovta =  $nroPtoVta; 
            $wsaa = new WSAA('wsfe', $ptovta);
           
            if($wsaa->huboerror){
               array_push($this->errores, $wsaa->errores);
               array_push($this->errores,' ** NO SE ENCONTRARON LOS ARCHIVOS NECESARIOS PARA EL PTO VTA SELECCIONADO');
               $this->conerror= true;              
            }else{                
               $this->ptoVta = $ptovta;
          }
        }   
    }
        
    
    //tipo de comprobante 11 = FACTURAS C
    public function generaFactura($tipocomprobante=11){
        
        $wsfev1 = new WSFEV1($this->ptoVta);
        // Carga el archivo TA.xml
        $wsfev1->openTA();

        if($wsfev1->huboerror){
            $this->errores[]=$wsfev1->errores;
            $this->conerror= true;  
        }else{
            $regfe['CbteTipo'] = $tipocomprobante; //FACTURA C                
            $regfe['Concepto']=1;   //1 producto 2 servicos 3 productos y servicios    

            if($this->tipoDoc=='CUIT')
               $regfe['DocTipo'] = 80; //80=CUIT
            else
            if($this->tipoDoc=='CUIL')
              $regfe['DocTipo'] = 86; //86=CUIL
            else
            if($this->tipoDoc=='DNI')
               $regfe['DocTipo'] = 96; //96=DNI

            $regfe['DocNro'] = $this->nroDoc;

            $regfe['CbteFch'] = date('Ymd'); 	// fecha emision de factura
            $regfe['ImpNeto'] = $this->monto;			// neto gravado
            $regfe['ImpTotConc'] = 0;			// no gravado
            $regfe['ImpIVA'] = 0;			// IVA liquidado
            $regfe['ImpTrib'] = 0;			// otros tributos
            $regfe['ImpOpEx'] = 0;			// operacion exentas
            $regfe['ImpTotal'] = $this->monto;			// total de la factura. ImpNeto + ImpTotConc + ImpIVA + ImpTrib + ImpOpEx

            $regfe['FchServDesde'] = null;	// solo concepto 2 o 3
            $regfe['FchServHasta'] = null;	// solo concepto 2 o 3
            $regfe['FchVtoPago'] = null;		// solo concepto 2 o 3
            $regfe['MonId'] = 'PES'; 			// Id de moneda 'PES'
            $regfe['MonCotiz'] = 1;			// Cotizacion moneda. Solo exportacion


            $nro = $wsfev1->FECompUltimoAutorizado($this->ptoVta, $tipocomprobante);

            if(($nro === FALSE) || ($wsfev1->huboerror)) {
                $this->errores[]=$wsfev1->errores;
                $this->conerror= true; 
            }else{       
                $this->nroFactura = $nro + 1;                     

                $regfe['CbteDesde']=null; //el nro de comprobantec
                $regfe['CbteHasta']=null;	//

            $cae = $wsfev1->FECAESolicitar( 
                            $this->nroFactura,  
                            $this->ptoVta,  // el punto de venta
                            $regfe// los datos a facturar
                        );
                if(($cae === -1) || ($wsfev1->huboerror)) {
                    $this->errores[]= $wsfev1->errores;
                    $this->conerror= true;                     
                }else{
                    $this->nroCae = $cae['cae'];
                    $this->fechaVtoCae = $cae['fecha_vencimiento'];   
                }      	
            }
        } 
    } // FIN DEL generaFactura
        
        
        
        
}