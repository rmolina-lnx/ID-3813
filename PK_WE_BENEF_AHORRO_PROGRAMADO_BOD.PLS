CREATE OR REPLACE PACKAGE BODY PK_WE_BENEF_AHORRO_PROGRAMADO IS


 /*
   +----------------------------------------------------------------------------+
   | Sistemas Enlinea S.A.                                                      |
   +----------------------------------------------------------------------------+
   | Fecha de Creación :  12-FEB-2013                                           |
   | Autor: Andres Camilo Cruz Villamil      email:acruz@opnet.com.co           |
   +----------------------------------------------------------------------------+
   | Actualizaciones :                                                          |
   | 11-JUN-2013 (CDCV) - se agrego el manejo de excepciones a:                 |
   |                      pr_informacion_beneficiarios                          |
   +----------------------------------------------------------------------------+
*/

    /* +-----------------------!!! 05-JUL-20124(RJM)---------------------------+
      | PROCEDURE pr_informacion_beneficiarios_json                            |
      +------------------------------------------------------------------------+
      |  Crea un JSON con dos datos del los integrantes del grupo familiar del |
      |  tercero que recibe como parametro                                     |
      +--------------------------------- --------------------------------------+ */  
   PROCEDURE pr_info_benef_json(v_k_idterc   NUMBER,
                                n_json IN OUT CLOB) IS
                                
      r_gr001   gr001mtercero%ROWTYPE;
      t_gr1068  pk_gr_gr1068.ty_t_gr1068;
      c_loop    NUMBER;
      jsondoc       JSON;
      jsonaux       JSON;
      jsonlist      JSON_LIST;                                               
   BEGIN

      jsondoc       := JSON;
      jsonaux       := JSON;
      jsonlist      := JSON_LIST;
      
      t_gr1068.DELETE;      
      pk_gr_gr1068.pr_listar_gr1068_k_idterc(v_k_idterc, t_gr1068);
      c_loop:=t_gr1068.FIRST;
      
      LOOP
         EXIT WHEN c_loop IS NULL;
         pk_gr_gr001.pr_buscar_gr001_registro(t_gr1068(c_loop).k_idterc_ben, r_gr001);
         jsonaux.put('A_CODIGO_CLIENTE', r_gr001.a_numnit);
         jsonaux.put('A_CODIGO_CLIENTE', r_gr001.a_numnit);
         jsonaux.put('V_CODIGOINTERNO_BEN', r_gr001.k_idterc);
         jsonaux.put('A_PRIMER_APELLIDO', r_gr001.n_apell1);
         jsonaux.put('A_SEGUNDO_APELLIDO', r_gr001.n_apell2);
         jsonaux.put('A_PRIMER_NOMBRE', r_gr001.n_nombr1);
         jsonaux.put('A_SEGUNDO_NOMBRE', r_gr001.n_nombr2);
         jsonaux.put('F_NACIMIENTO', TO_CHAR(r_gr001.f_nacimi, 'MM/DD/RRRR'));
         jsonaux.put('A_TIPO_IDENTIFICACION', r_gr001.i_ideter);
         jsonaux.put('A_TIPO_IDENTIFICACION_D', pk_gr_parametro1.fu_descripcion_dominio('DM_IDETER', r_gr001.i_ideter));
         jsonaux.put('A_GENERO', r_gr001.i_sexo);
         jsonaux.put('A_GENERO_D', pk_gr_parametro1.fu_descripcion_dominio('DM_SEXO', r_gr001.i_sexo));         
         jsonaux.put('A_PARENTESCO', t_gr1068(c_loop).i_parent);
         jsonaux.put('A_PARENTESCO_D', pk_gr_parametro1.fu_descripcion_dominio('DM_PARENT', t_gr1068(c_loop).i_parent));
         jsonaux.put('A_DEPENDE_ECONO', t_gr1068(c_loop).i_econom);
         jsonaux.put('A_NIVEL_ESTUDIO', r_gr001.o_estudi);
         jsonaux.put('A_NIVEL_ESTUDIO_D', pk_gr_parametro1.fu_descripcion_dominio('DM_NIVEDU', r_gr001.o_estudi));
         jsonaux.put('A_ACTIVIDAD_ECONOMICA', TO_CHAR(NULL));
         jsonaux.put('A_ACTIVIDAD_ECONOMICA_D', TO_CHAR(NULL));
         jsonaux.put('A_PROFESION', r_gr001.k_profes);
         jsonaux.put('A_EXCLUIR', 'N');
         jsonaux.put('V_PORCENTAJE_BEN', '');
         jsonaux.put('A_BENEFICIARIO', 'N');
         jsonaux.put('A_ACCION', 'N');        
         jsonlist.append(jsonaux.TO_JSON_VALUE);
         c_loop := t_gr1068.NEXT(c_loop);
      END LOOP;
      
      jsondoc.put('informacion_beneficiario', jsonlist);
      DBMS_LOB.CREATETEMPORARY(n_json, CACHE => TRUE);
      jsondoc.TO_CLOB(n_json);
      
   EXCEPTION WHEN OTHERS THEN
      pk_gr_control_error.pr_control_error (dbms_utility.format_error_backtrace()||' '||SQLERRM, SQLCODE,'CONSULTA',SQLERRM,'AH');
      RAISE_APPLICATION_ERROR( -20000,dbms_utility.format_error_backtrace()||' '||SQLERRM);
   END pr_info_benef_json;



    /* +-----------------------!!! 12-FEB-2013 (ACCV)---------------------------+
      | PROCEDURE pr_informacion_beneficiarios                                  |
      +-------------------------------------------------------------------------+
      |  Crea un XML con dos datos del los integrantes del grupo familiar del   |
      |  tercero que recibe como parametro                                      1
      +--------------------------------- ---------------------------------------+ */
   PROCEDURE pr_informacion_beneficiarios(v_k_idterc   NUMBER,
                                          o_xml IN OUT CLOB) IS
      r_gr001   gr001mtercero%ROWTYPE;
      t_gr1068  pk_gr_gr1068.ty_t_gr1068;
      c_loop    NUMBER;
      
      XMLDoc      dbms_xmldom.DOMDocument;
      XMLMainNode dbms_xmldom.DOMNode;
      XMLRootNode dbms_xmldom.DOMNode;
      XMLRootElmt dbms_xmldom.DOMElement;
      nodoAux     dbms_xmldom.DOMNode;
   BEGIN
   
      t_gr1068.DELETE;      
      pk_gr_gr1068.pr_listar_gr1068_k_idterc(v_k_idterc, t_gr1068);
      c_loop:=t_gr1068.FIRST;
      XMLDoc := dbms_xmldom.newDOMDocument;
      --DBMS_XMLDOM.SETCHARSET(XMLDoc, 'ISO-8859-1');   !!! 20-DIC-2016(ACCV), instrucción eliminada
      dbms_xmldom.SETVERSION(XMLDoc, '1.0');
      XMLMainNode := dbms_xmldom.makeNode(XMLDoc);
      XMLRootElmt := dbms_xmldom.createElement(XMLDoc,'informacion_beneficiario');
      XMLRootNode := dbms_xmldom.appendChild(XMLMainNode, dbms_xmldom.makeNode(XMLRootElmt));
      
      LOOP
         EXIT WHEN c_loop IS NULL;
         PK_GR_GR001.pr_buscar_gr001_registro(t_gr1068(c_loop).k_idterc_ben, r_gr001);
         nodoAux := pk_gr_xml.fu_adicionar_nodo( XMLDoc, XMLRootNode,'R_RegistroFamiliar',NULL );
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux, 'A_CODIGO_CLIENTE', r_gr001.a_numnit);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'V_CODIGOINTERNO_BEN', r_gr001.k_idterc);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_PRIMER_APELLIDO', r_gr001.n_apell1);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_SEGUNDO_APELLIDO', r_gr001.n_apell2);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_PRIMER_NOMBRE', r_gr001.n_nombr1);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_SEGUNDO_NOMBRE', r_gr001.n_nombr2);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'F_NACIMIENTO', TO_CHAR(r_gr001.f_nacimi, 'MM/DD/RRRR'));
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_TIPO_IDENTIFICACION', r_gr001.i_ideter);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_TIPO_IDENTIFICACION_D', pk_gr_parametro1.fu_descripcion_dominio('DM_IDETER', r_gr001.i_ideter));
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_GENERO', r_gr001.i_sexo);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_GENERO_D', pk_gr_parametro1.fu_descripcion_dominio('DM_SEXO', r_gr001.i_sexo));         
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_PARENTESCO', t_gr1068(c_loop).i_parent);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_PARENTESCO_D', pk_gr_parametro1.fu_descripcion_dominio('DM_PARENT', t_gr1068(c_loop).i_parent));
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_DEPENDE_ECONO', t_gr1068(c_loop).i_econom);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_NIVEL_ESTUDIO', r_gr001.o_estudi);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_NIVEL_ESTUDIO_D', pk_gr_parametro1.fu_descripcion_dominio('DM_NIVEDU', r_gr001.o_estudi));
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_ACTIVIDAD_ECONOMICA', NULL);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_ACTIVIDAD_ECONOMICA_D', NULL);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_PROFESION', r_gr001.k_profes);
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_EXCLUIR', 'N');
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'V_PORCENTAJE_BEN', '');
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_BENEFICIARIO', 'N');
         pk_gr_xml.pr_adiciona_atributo(XMLDoc, nodoAux,'A_ACCION', 'N');
         c_loop := t_gr1068.NEXT(c_loop);
      END LOOP;
      
      dbms_lob.createtemporary(o_xml, CACHE=>TRUE);
      dbms_xmldom.WRITETOCLOB(XMLDoc, o_xml);

      /* ------------------- YGJ 19/ABR/2021. FLUJO 30276 PRESENTE ------------------- | 
      |  Se vuelve a habilitar esta línea debido a que en PRESENTE cuándo se agrega un |
      |  beneficiarioy se vuelve a consultar, no se visualizan correctamente los       |
      |  familiares del asociado                                                       |
      | ------------------------------------------------------------------------------*/
      o_xml := REPLACE(o_xml,'version="1.0"','version="1.0" encoding="ISO-8859-1"');   
   EXCEPTION WHEN OTHERS THEN
      pk_gr_control_error.pr_control_error (dbms_utility.format_error_backtrace()||' '||SQLERRM, SQLCODE,'CONSULTA',SQLERRM,'AH');
      RAISE_APPLICATION_ERROR( -20000,dbms_utility.format_error_backtrace()||' '||SQLERRM);
   END pr_informacion_beneficiarios;

END;