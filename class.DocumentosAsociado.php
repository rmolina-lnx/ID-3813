<?php

class DocumentosAsociado {
   private $xml_clasificacion;
   private $xml_clasificacion_antes;

   private $xml_documento;
   private $asociado;
   private $parametroHtml = array();
   
   function __construct(gr_nucleo_Asociado $gr_nucleo_Asociado) {
      $this->asociado = $gr_nucleo_Asociado;
      $this->cargarInfo();
   }
   
   
      public function cargarInfo() {
      $this->obtenerClasificacion();
      $domXml = new DOMDocument();
      $domXml->loadXML($this->xml_clasificacion);
      $xpath = new DOMXPath($domXml);
      $valor =  $xpath->query("//clasificaciones/detalle");
      if( $valor->length > 1 ){
         $this->parametroHtml["mostrar_clasificaciones"] = 'Y';
         $this->parametroHtml["mostrar_documentos"] = 'N';    
         $this->parametroHtml["mostrar_dia"] = 'N';
         $this->parametroHtml["mostrar_mes"] = 'N';
         $this->parametroHtml["mostrar_ano"] = 'N';
         $this->parametroHtml["mostrar_asHtml"] = 'Y';
      }else{
         $this->parametroHtml["mostrar_clasificaciones"] = 'N';
         $this->parametroHtml["mostrar_documentos"] = 'Y';
         $this->parametroHtml["mostrar_dia"] = 'N';
         $this->parametroHtml["mostrar_mes"] = 'N';
         $this->parametroHtml["mostrar_ano"] = 'N';
         $this->parametroHtml["mostrar_asHtml"] = 'N';
         $valor =  $xpath->query("//clasificaciones/detalle/k_cladoc");
         if (!is_null($valor)) {
           foreach ($valor as $element) {
             Configurar::getFUncionario()->setNomina($element->nodeValue);
           }
         }
         $this->getObtenerDocumento(FALSE);
      }
    }


     public function obtenerClasificacion(){
      $sql = "BEGIN pk_we_descargue_documentos.pr_lista_clasificaciones( :funcionario,:xml ); END;";
      $parametros = array('funcionario' => array('valor' => Configurar::getFUncionario()->getNusuari(), 'longitud' =>'-1'),
                                   'xml' => array('valor' => '', 'longitud' => 'CLOB'));
      Configurar::getDb()->ejecutaSentencia($sql, $parametros);

      $this->xml_clasificacion = utf8_encode($parametros['xml']['valor']);
      $this->xml_clasificacion_antes = utf8_encode($parametros['xml']['valor']);
   }

     public function getObtenerDocumento($k_cladoc){
      try{
        if( is_array($k_cladoc) ){
           $parametro = explode("-",$k_cladoc['k_cladoc']);
           Configurar::getFUncionario()->setNomina($parametro[0]);
        }


        $sql = "BEGIN pk_we_descargue_documentos.pr_lista_documentos (:n_k_usuario, :n_tipo, :v_k_idterc, :n_k_nomina, :n_k_cladoc,:xml ); END;";
        if (Configurar::getFuncionario()->getNusuari() != 'WEB') {
         $asociado2 = Configuracion::unserialize("gr_nucleo_Asociado");
         $this->asociado = $asociado2;
        }

        $parametros = array('n_k_usuario' => array('valor' => Configurar::getFUncionario()->getNusuari(), 'longitud' =>'-1'),
                              'n_tipo' => array('valor' => 'A', 'longitud' =>'-1'),
                              'v_k_idterc' => array('valor' => $this->asociado->getK_idterc(), 'longitud' => '-1'),
                              'n_k_nomina' => array('valor' => NULL, 'longitud' =>'-1'),
                              'n_k_cladoc' => array('valor' => Configurar::getFUncionario()->getNomina(), 'longitud' =>'-1'),
                              'xml' => array('valor' => '', 'longitud' => 'CLOB'));

       Configurar::getDb()->ejecutaSentencia($sql, $parametros);



       // $this->xml_documento = /*$this->validarArchivos(*/utf8_encode($parametros['xml']['valor'])/*)*/;

       $this->xml_documento = $this->validarArchivos(utf8_encode($parametros['xml']['valor']));

       $this->parametroHtml["mostrar_clasificaciones"] = 'N';
       $this->parametroHtml["mostrar_documentos"] = 'Y';
       $this->parametroHtml["mostrar_dia"] = 'N';
       $this->parametroHtml["mostrar_mes"] = 'N';
       $this->parametroHtml["mostrar_ano"] = 'N';

       $this->xml_clasificacion = $this->xml_clasificacion_antes;
       $dom = new DOMDocument();
       $dom->loadXML($this->xml_clasificacion);
       $domDoc = new DOMDocument();
       $domDoc->loadXML(utf8_encode($this->xml_documento));
       $infoDocumento = $dom->createElement('descarga');
       $infoDocumento->appendChild($dom->importNode($domDoc->documentElement, true));
       $dom->documentElement->appendChild($infoDocumento);
       $this->xml_clasificacion =  $dom->saveXML();
      
       if($k_cladoc || is_array($k_cladoc)){
          gr_util_Html::presentar("lnxDocumentosAsociado.xsl", $this->xml_clasificacion, true, $this->parametroHtml);
       }
       //Configuracion::serialize($this);
       Configuracion::serialize();

      }catch(Excepcion $e){
        echo $e->getMessage();
      }


   }


   public function validarArchivos($xml) {
      $dom = new DOMDocument();
      $dom->loadXML($xml);
      $elements = $dom->getElementsByTagName("documento");

      foreach ($elements as $node) {
         if ($node->getElementsByTagName("fecha")->length == 0) {
            //$val_archivo = "ID_".$this->asociado->getAanumnit()."_";
            $val_archivo = $node->getElementsByTagName("n_direct")->item(0)->nodeValue .$val_archivo;
            $val_archivo.= $node->getElementsByTagName("n_archivo")->item(0)->nodeValue;
            $val_archivo.= "." . $node->getElementsByTagName("n_extens")->item(0)->nodeValue;
            if (file_exists($val_archivo) || $this->url_exists($val_archivo)) {
               $atrib_existe = $dom->createAttribute('existe');
               $node->appendChild($atrib_existe);
               $existe_text = $dom->createTextNode('Y');
               $atrib_existe->appendChild($existe_text);
            }
            if ($node->getElementsByTagName("i_ejecuc")->item(0)->nodeValue == "LI") {
               $atrib_existe = $dom->createAttribute('existe');
               $node->appendChild($atrib_existe);
               $existe_text = $dom->createTextNode('Y');
               $atrib_existe->appendChild($existe_text);
            }
         } else {
            $fechas = $node->getElementsByTagName("dia");
            foreach ($fechas as $dia) {
               $val_archivo = $dia->getAttribute('valor') . "_ID_" . $this->asociado->getAanumnit() . "_";
               $val_archivo = $node->getElementsByTagName("n_direct")->item(0)->nodeValue . $val_archivo;
               $val_archivo.= $node->getElementsByTagName("n_archivo")->item(0)->nodeValue;
               $val_archivo.= "." . $node->getElementsByTagName("n_extens")->item(0)->nodeValue;
               if (file_exists($val_archivo) || $this->url_exists($val_archivo)) {
                  $atrib_existe = $dom->createAttribute('existe');
                  $dia->appendChild($atrib_existe);
                  $existe_text = $dom->createTextNode('Y');
                  $atrib_existe->appendChild($existe_text);
                  //Si existe una fecha, adiciono el atributo al documento
                  $atrib_existe = $dom->createAttribute('existe');
                  $node->appendChild($atrib_existe);
                  $existe_text = $dom->createTextNode('Y');
                  $atrib_existe->appendChild($existe_text);
               }
            }
         }
         $val_archivo = "";
      }
      return $dom->saveXML();
   }

   /*---------------------------------!!! 28-MAR-2014(ACCV) ----------------------------------------+
   | Se modifica para que el directorio desde el cual descargar el archivo, sea el que se encuentra |
   | asociado al documento descargado, porque antes se tomaba el mismo directorio para todos los    |
   | documentos, y no había ninguna consideración para esa desición                                 |
   +----------------------------------!!! 09-MAR-2017(ACCV) ----------------------------------------+
   | Se modifica para que los datos del documentos, los deduzca del XML que está cargado en memoria |
   | en la clase PHP, en lugar de recibirlos como parámetros desde el formulario, debido a que, por |
   | ejemplo, en el caso de los FTP, en el nombre del directorio se encuentra el servidor, el       |
   | usuario y la contraseña de acceso al FTP, lo que podría provocar que cualquiera encuentre esos |
   | datos, ya que estaban siendo incluidos en el HTML de la página                                 |
   +------------------------------------------------------------------------------------------------*/
   public function getDescargar($parametros) {
      $o_dom = gr_util_XML::getPath("/archivos/documento[k_docume='".$parametros['k_docume']."']", $this->xml_documento);
      $path = $o_dom->getElementsByTagName('n_direct')->item(0)->nodeValue;
      $n_nombre = $o_dom->getElementsByTagName('n_archivo')->item(0)->nodeValue;
      $n_extens = $o_dom->getElementsByTagName('n_extens')->item(0)->nodeValue;
      $archivo = $n_nombre . "." . $n_extens;
      if ($parametros['i_ejecuc'] != 'LI') {
         if (!($parametros['i_tipdto'] == 'GR' || $parametros['i_tipdto'] == 'GE' || $parametros['i_tipdto'] == 'GA')) {
            if (isset($parametros['n_dia'])) {
               $archivo = $parametros['n_dia'] . "_ID_" . $this->asociado->getAanumnit() . "_" . $archivo;
            }
         }
         $descarga = new Descarga();

         try {
            $descarga->descargar($path, $archivo);
         } catch (Excepcion $e) {
            echo $e->getMessage();
         }
      } else {
         if ($n_nombre == 'OBLI_AL_DIA') {
            $reporte = new ReporteCtaCobro($this->asociado);
            $reporte->generarDocumento();
         }
         if ($n_nombre == 'PAZYSALVO') {
            $reporte = new ReportePazysalvo($this->asociado);
            $reporte->generarDocumento($parametros['n_dia']);
         }
         if ($n_nombre == 'SALDO_APORTES') {
            $reporte = new ReporteSaldAportes($this->asociado);
            $reporte->generarDocumento();
         }
         if ($n_nombre == 'CERTIFICADO_CRE_DIA') {
            $reporte = new ReporteCreDia($this->asociado);
            $reporte->generarDocumento();
         }
         if ($n_nombre == 'CERTIFICADO_ASOCIADO') {
            $reporte = new ReporteAsociado($this->asociado);
            $reporte->generarDocumento();
         }
         if ($n_nombre == 'CERTIFICADO_APORTES') {
            $reporte = new ReporteAportes($this->asociado);
            $reporte->generarDocumento();
         }
      }
   }

   public function url_exists($url) {
       $hdrs = @get_headers($url);

    // print_r(get_headers($url));

     /*
       * +---------------------!!! 08-ENE-2014 -------------------------+
       * | Se almacena la dirección como error por petición de Carlos   |
       * | Cómbita, debido a que cuando se presentar algún error, es muy|
       * | dificil detectarlo                                           |
       * +--------------------------------------------------------------+
       */
      Excepcion::almacenarError($url, 'F');
      return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $hdrs[0]) : false;     
   }

   public function asHtml() {
      //!!! 05-ago-2013(ACCV), Se modifica el parametro, antes era el 64
      $this->parametroHtml["n_descri"] = Configurar::getParametros()->getParametro("VS", 61)->getO_alfabe();
      $this->cargarInfo();
      gr_util_Html::presentar("lnxDocumentosAsociado.xsl", $this->xml_clasificacion, true, $this->parametroHtml);

   }
}
?>