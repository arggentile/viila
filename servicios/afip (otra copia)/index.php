<?php

include('exceptionhandler.php');
include('wsaa.class.php');
include('wsfev1.class.php');

/*****************
 //WSAA
 ****************/
$wsaa = new WSAA('wsfe'); 

$ptovta = 3; //Punto de Venta
$tipocbte = 11; //1=Factura A
if($wsaa->huboerror)
    echo "SE PRODUJO ALGUNA CLASE D ERROR";
else{
$wsfev1 = new WSFEV1(); 
// Carga el archivo TA.xml
$wsfev1->openTA();
$nro = $wsfev1->FECompUltimoAutorizado($ptovta, $tipocbte);
exit;

    

		

/*
/*****************
 //WSFEV1
 ****************/



$regfe['CbteTipo']=$tipocbte;
$regfe['Concepto']=1;
$regfe['DocTipo']=80; //80=CUIL
$regfe['DocNro']=20327097351;
//$regfe['CbteDesde']=$cbte; 	// nro de comprobante desde (para cuando es lote)
//$regfe['CbteHasta']=$cbte;	// nro de comprobante hasta (para cuando es lote)
$regfe['CbteFch']=date('Ymd'); 	// fecha emision de factura
$regfe['ImpNeto']=100;			// neto gravado
$regfe['ImpTotConc']=0;			// no gravado
$regfe['ImpIVA']=0;			// IVA liquidado
$regfe['ImpTrib']=0;			// otros tributos
$regfe['ImpOpEx']=0;			// operacion exentas
$regfe['ImpTotal']=100;			// total de la factura. ImpNeto + ImpTotConc + ImpIVA + ImpTrib + ImpOpEx
$regfe['FchServDesde']=null;	// solo concepto 2 o 3
$regfe['FchServHasta']=null;	// solo concepto 2 o 3
$regfe['FchVtoPago']=null;		// solo concepto 2 o 3
$regfe['MonId']='PES'; 			// Id de moneda 'PES'
$regfe['MonCotiz']=1;			// Cotizacion moneda. Solo exportacion

// Comprobantes asociados (solo notas de crédito y débito):
$regfeasoc['Tipo'] = 91; //91; //tipo 91|5			
$regfeasoc['PtoVta'] = 1;
$regfeasoc['Nro'] = 1;

// Detalle de otros tributos
$regfetrib['Id'] = 1; 			
$regfetrib['Desc'] = 'impuesto';
$regfetrib['BaseImp'] = 0;
$regfetrib['Alic'] = 0; 
$regfetrib['Importe'] = 0;
 
// Detalle de iva
$regfeiva['Id'] = 5; 
$regfeiva['BaseImp'] = 100; 
$regfeiva['Importe'] = 21;


$nro = $wsfev1->FECompUltimoAutorizado($ptovta, $tipocbte);

if($nro === FALSE) {
	echo "<br>Error al obtener el ultimo numero autorizado<br>";
	$nro=0;
	$nro1 = 0;
	echo "Code: ", $wsfev1->Code, "<br>";
	echo "Msg: ", $wsfev1->Msg, "<br>";
	echo "Obs: ", $wsfev1->ObsCode, "<br>";
	echo "Msg: ", $wsfev1->ObsMsg, "<br>";	
} else {
	echo "<br>FECompUltimoAutorizado: $nro <br>";
	$nro1 = $nro + 1;
}
exit;
$cae = $wsfev1->FECAESolicitar($nro1,  
                $ptovta,  // el punto de venta
                $regfe, // los datos a facturar
				$regfeasoc,
				$regfetrib,
				$regfeiva	
     );
if($cae === false || $cae['cae'] <= 0) {
	echo "<br>Error al obtener CAE<br>";
	echo "Code: ", $wsfev1->Code, "<br>";
	echo "Msg: ", $wsfev1->Msg, "<br>";
	echo "Obs: ", $wsfev1->ObsCode, "<br>";
	echo "Msg: ", $wsfev1->ObsMsg, "<br>";	
}

$caenum = $cae['cae'];
$caefvt = $cae['fecha_vencimiento'];

if($caenum <= 0 || $caenum == '' || $caenum == false){
	echo "<br>";
	echo "Error al obtener CAE";
	echo "<br>";	
} else {
	echo "<br>";
	echo "Ok";
	echo "<br>";
}

echo "<br>";
echo "Nro: ";
echo $nro + 1;
echo "<br>";
echo "Cae: ", $caenum;
echo "<br>";
echo "Fecha Vto: ", $caefvt;
echo "<br>";}
?>