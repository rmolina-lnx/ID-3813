CREATE OR REPLACE PACKAGE PK_WE_BENEF_AHORRO_PROGRAMADO IS


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
                                n_json IN OUT CLOB);

    /* +-----------------------!!! 12-FEB-2013 (ACCV)---------------------------+
      | PROCEDURE pr_informacion_beneficiarios                                  |
      +-------------------------------------------------------------------------+
      |  Crea un XML con dos datos del los integrantes del grupo familiar del   |
      |  tercero que recibe como parametro                                      1
      +--------------------------------- ---------------------------------------+ */
   PROCEDURE pr_informacion_beneficiarios(v_k_idterc   NUMBER,
                                          o_xml IN OUT CLOB);

END;
/
