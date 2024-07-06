<?php
/**
 * +-------------------------- 04-JUL-2024 (RJM) ------------------------------+
 * | Creación   04-JUL-2024                                                    |
 * +---------------------------------------------------------------------------+
 *
 * Comentarios
 *
 * @author    Rafael Molina Osias (rmolina@sistemasenlinea.com)
 * @version   1
 * @copyright Copyright (c) 2024 Sistemas EnLinea S.A
 */

require_once(Configurar::getDirInclude() . 'pdf/class.Cpdf.php');
require_once(Configurar::getDirInclude() . 'pdf/class.Cezpdf.php');

class ReporteGrFamiliar
{

  private $asociado;

  public function __construct($Asociado){
   $this->asociado = $Asociado;
  }

  public function generarDocumento(){
    Configurar::addPath("/include/pdf", ".php", "class.");
    $pdf = & new Cezpdf('LETTER');
    $pdf->selectFont(APLICACION_DIR . '/include/pdf/fonts/Helvetica.afm');
    $pdf->addJpegFromFile(APLICACION_DIR.'/web/imagen/'.Configuracion::getLogo(), 40, 680,120,83);
    $pdf->addJpegFromFile(APLICACION_DIR.'/web/imagen/firma.JPG', 25, 170,105,105);
    $pdf->addJpegFromFile(APLICACION_DIR.'/web/imagen/vigilado_fogacoop.jpg', 2, 150,25,210);

    /*======================*
     | Cabecera del reporte |
     *======================*/
    $sql = "DECLARE BEGIN pk_we_general.pr_traer_licencia(:xml); END;";
    $parametros = [ 'xml' => ['valor' => '', 'longitud' => 'CLOB'] ];

    $info3 = Configurar::getDb()->ejecutaSentencia( $sql, $parametros );
    $options = ['justification' => 'center'];
    $pdf->ezText("\n\n\n\n\n\n\n\n<b>COOPERATIVA CEMCOP</b>", 12, $options);
    $pdf->ezText("<b>Nit: 890.301.310-1\n\nCERTIFICA QUE:</b>", 12, $options);

    $options = ['justification' => 'full'];
    $introduccion = "\n\n\nEl(la) asociado <b>{$this->asociado->getNnasocia()}</b> identificado(a) con Cédula de Ciudadanía No.<b> {$this->asociado->getAanumnit()}</b>, quien se encuentra vinculado a la Cooperativa desde el(los) {$this->asociado->getF_afilia()}, presenta al {$this->asociado->getAcceso()} la siguiente información en su grupo familiar:\n\n";
    $pdf->ezText($introduccion, 11, $options);
    
    /*==========================*
     | Informacion de la grilla |
     *==========================*/
    $sql = "BEGIN pk_we_benef_ahorro_programado.pr_info_benef_json(:p_k_idterc,:p_json); END;";
    $parametrosSql = [ 
      'p_k_idterc' => ['valor' => $this->asociado->getK_idterc(), 'longitud' => -1],
      'p_json'     => ['valor' => '', 'longitud' => 'CLOB']
    ];

    Configurar::getDb()->ejecutaSentencia($sql, $parametrosSql);
    $datos = json_decode($parametrosSql['p_json']['valor'], true);

    $datosTabla = array_map(
       function ($beneficiario) {
          return [
             'A_CODIGO_CLIENTE' => utf8_decode($beneficiario['A_CODIGO_CLIENTE']),
             'A_PARENTESCO_D'   => utf8_decode($beneficiario['A_PARENTESCO_D']),
             'N_BENEFI_D'       => utf8_decode("{$beneficiario['A_PRIMER_NOMBRE']} {$beneficiario['A_SEGUNDO_NOMBRE']} {$beneficiario['A_PRIMER_APELLIDO']} {$beneficiario['A_SEGUNDO_APELLIDO']}") 
          ];
       },
       $datos['informacion_beneficiario']
    );
    
    $titulosTabla = [
       'A_CODIGO_CLIENTE' => '<b>Identificación</b>',
       'N_BENEFI_D'       => '<b>Beneficiario</b>',
       'A_PARENTESCO_D'   => '<b>Parentesco</b>'
       
    ];

    $opcionesTabla = [
       'showLines'    => 1,
       'showHeadings' => 1,
       'shaded'   => 1,
       'shadeCol' => [0.9, 0.9, 0.9],
       'fontSize' => 11,
       'titleFontSize' => 14,
       'xOrientation'  => 'center',
       'xPos'  => 'center',
       'width' => 400,
       'cols'  => [
          'A_CODIGO_CLIENTE' => ['justification' => 'center'],
          'A_PARENTESCO_D'   => ['justification' => 'center'],
          'N_BENEFI_D'       => ['justification' => 'center']
        ]
    ];

    $pdf->ezTable($datosTabla,$titulosTabla,'',$opcionesTabla);

    $texto= "Se expide el día {$this->asociado->getAcceso()}.
    \n\nSe omite firma autógrafa según Art. 10 del D.R. 836/91.";

    $pdf->ezText("\n\n\n\n".$texto, 11, $options);
    
    $pdf->ezText("\nSi requiere confirmar información suministrada en este certificado puede comunicarse con nuestra sede principal en Cali Tel: 489 05 82", 11, $options);
    
    $options = ['justification'=>'center'];

    $firma =Configurar::getParametros()->getParametro("VS", 66)->getO_alfabe();
    $cargo = Configurar::getParametros()->getParametro("VS", 67)->getO_alfabe();

/*prueba*/

    /*header('Content-Description: File Transfer target: "_blank"');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=CERTIFICADO_GR_FAMILIAR.pdf');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    ob_clean();
    flush();
    readfile($pdf->ezStream());
    exit;*/

/*fin prueba*/

    $pdf->ezStream($parametros);
    die;

  }

  private function imprimirVariable($variable) {
    print_r('<pre>');
    print_r($variable);
    print_r('</pre>');
    die();
  }  
}