PROMPT --------------------------------------------------------------------------------------------;
PROMPT |                      S I S T E M A S  E N  L I N E A  S . A .                            |;
PROMPT |                                                                                          |;
PROMPT |         PRODUCTO: LINIX                                                                  |;
PROMPT |         MODULO  : MOVIMIENTOS GESTION DOCUMENTAL                            |;
PROMPT |         ESQUEMA : Linix                                                |;
PROMPT |         FECHA   : 07/09/2024                   |;
PROMPT --------------------------------------------------------------------------------------------;
 
PROMPT Por Favor ingrese los siguientes Datos ( CTRL + C para cancelar ): 
accept pUsuario char prompt '    Usuario Propietario de la Base de datos Linix: ';
accept pclave   char prompt '    Clave del Propietario de la Base de datos Linix: ';
accept pstring  char prompt '    String de conexión a la Base de datos Linix: ';
 
CONNECT &pUsuario/&pclave@&pstring
 
PROMPT --> Ejecutando Archivo *********** 12963GOMOVGESWE_NEW_v62.SQL
 
SPOOL 12963GOMOVGESWE_NEW_v62.LST
 
PROMPT Instalando ---> PK_WE_BENEF_AHORRO_PROGRAMADO_ESP.PLS
@@PK_WE_BENEF_AHORRO_PROGRAMADO_ESP.PLS
/
PROMPT Instalando ---> PK_WE_BENEF_AHORRO_PROGRAMADO_BOD.PLB
@@PK_WE_BENEF_AHORRO_PROGRAMADO_BOD.PLB
 
PROMPT ---> Verificando Objetos Invalidos
exec pk_se_admin01.pr_compilar_objetos;
 
PROMPT ---> Creando Sinonimos
exec pk_se_admin01.pr_crea_sinonimo;
 
 
SPOOL OFF
