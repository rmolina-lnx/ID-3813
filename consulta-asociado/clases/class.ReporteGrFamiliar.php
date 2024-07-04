<?php
/**
 * +---------------------------------------------------------------------------+
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

  public function __construct($gr_nucleo_Asociado){
   $this->asociado = $gr_nucleo_Asociado;
  }

  public function generarDocumento($n_dia){
    Configurar::addPath("/include/pdf", ".php", "class.");
    $pdf = & new Cezpdf('LETTER');
    $pdf->selectFont(APLICACION_DIR . '/include/pdf/fonts/Helvetica.afm');
    $pdf->addJpegFromFile(APLICACION_DIR.'/web/imagen/'.Configuracion::getLogo(), 400,700,200,75);

    /*======================*
     | Cabecera del reporte |
     *======================*/

     $style = array('text-align'=>'center');
     $sql = "DECLARE BEGIN pk_we_general.pr_traer_licencia(:xml); END;";
     $parametross = array('xml' => array('valor' => '', 'longitud' => 'CLOB'));
      $info3 = Configurar::getDb()->ejecutaSentencia( $sql, $parametross );
     $resultado=utf8_encode($parametross['xml']['valor']);
      

    $options = array('justification'=>'center');
    $pdf->ezText("<b>\n\n\n\n\n\n\n\n{$parametross['xml']['valor']}</b>", 12, $options);
    $pdf->ezText("\n\nCERTIFICA QUE:", 12, $options);

    $options = array('justification'=>'left');
    $introduccion = utf8_decode("\n\n\n\nEl(la) señor(a) <b>{$this->asociado->getNnasocia()}</b> Identificado(a) con la Cédula de Ciudadanía No.<b> {$this->asociado->getAanumnit()}</b>, en su calidad de Asociado a {$resultado} desde el(los) {$this->asociado->getF_afilia()}, a la fecha se encuentra a <b>PAZ Y SALVO</b> por la(s) siguiente(s) obligacion(es) de crédito:"); 
    $pdf->ezText($introduccion, 11, $options);


    $sql = "DECLARE BEGIN pk_we_consulta.pr_estado_cuenta( :kIdterc,'TRUE',:xml,SYSDATE); END;";
    $parametros = array('xml' => array('valor' => '', 'longitud' => 'CLOB'),
    'kIdterc'  => array('valor' => $this->asociado->getK_idterc(), 'longitud' => -1));

    $info = Configurar::getDb()->ejecutaSentencia($sql, $parametros);
    $domPdf = gr_util_XML::getPath("/estado_cuenta/cartera_cancelada", utf8_encode($parametros['xml']['valor']));
    $gr_util_XmlToArray = new gr_util_XmlToArray($domPdf->saveXML());
    $array = $gr_util_XmlToArray->createArray();

    $titles = array(
          'a_tipodr' => '<b>Credito</b>',
          'n_modali' => '<b>Modalidad</b>',
                    'f_cancel' => '<b>Fec. Cancel</b>');

    $fecha =  strtotime($this->resta_fechas(date("m/d/Y"),$n_dia));

    for( $i=0; $i<count($array['cartera_cancelada']['registro']);$i++){

      $fecha2 = $array['cartera_cancelada']['registro'][$i]['f_cancel'];
      $fecha2 = strtotime(date('m/d/Y', strtotime($fecha2)));


      if($fecha <= $fecha2){
         $array2['cartera_cancelada']['registro'][$i]['a_tipodr'] = $array['cartera_cancelada']['registro'][$i]['a_tipodr']." ".
                                                                   $array['cartera_cancelada']['registro'][$i]['a_obliga'];
         $array2['cartera_cancelada']['registro'][$i]['n_modali'] = $array['cartera_cancelada']['registro'][$i]['n_modali'];
         $array2['cartera_cancelada']['registro'][$i]['f_cancel'] = $array['cartera_cancelada']['registro'][$i]['f_cancel'];
      }

    }

    $options = array('fontSize' => 9);
if($n_dia != 1){
    $pdf->ezTable($array2['cartera_cancelada']['registro'], $titles, ' ', $options);
}else{
    $pdf->ezTable($array['cartera_cancelada']['registro'], $titles, ' ', $options);
}


    $nota=utf8_decode("<b>Nota: El {$resultado} se reserva la posibilidad de efectuar el cobro de cualquier transacción realizada y no cobrada con anterioridad y que se encuentre debidamente documentada y contabilizada con posterior a la presente fecha. </b>");
    $pdf->ezText("\n\n\n\n".$nota, 11, $options);

    $hoy = date("d");
   $ano = date("Y");
   $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

   $texto= utf8_decode("La presente certificación se expide a solicitud del interesado(a) a los {$hoy} día(as) del mes de {$meses[date('n')-1]} del {$ano}.");
    $pdf->ezText("\n\n\n".$texto, 11, $options);

    $pdf->ezText("\n\n\n\n\nCordialmente,", 11, $options);
   
    $firma =Configurar::getParametros()->getParametro("VS", 66)->getO_alfabe();
    $cargo = Configurar::getParametros()->getParametro("VS", 67)->getO_alfabe();
    $pdf->ezText("\n\n\n\n\n".$firma."\n".$cargo,11,$options);
    $parametros=array("Content-Disposition"=>"CERTIFICADO_GR_FAMILIAR.pdf","Accept-Ranges"=>1);


/*prueba*/

    header('Content-Description: File Transfer target: "_blank"');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=CERTIFICADO_GR_FAMILIAR.pdf');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    ob_clean();
    flush();
    readfile($pdf->ezStream());
    exit;

/*fin prueba*/
   // $pdf->ezStream($parametros);
die;

  }

    
  function resta_fechas($fecha,$ndias){
      if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha)){
              list($mes,$dia,$ano)=preg_split("/\//", $fecha);
      }

      if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha)){
              list($mes,$dia,$ano)=preg_split("/-/",$fecha);
      }

      $nueva = mktime(0,0,0, $mes,$dia,$ano) - $ndias * 24 * 60 * 60;
      $nuevafecha=date("m/d/Y",$nueva);

      return $nuevafecha;
    }


    private function removeFromArray(&$array, $key){
       echo "eliminando<br />";
       foreach($array as $j=>$i){
          echo "recorriendo -".$i."- ".$key."<br/>";
          print_r($array);
          die;
          if($i == $key){
             echo "encontrado: ".$i." = ".$key;
             die;
             return true;
             break;
          }
       }
   }
}