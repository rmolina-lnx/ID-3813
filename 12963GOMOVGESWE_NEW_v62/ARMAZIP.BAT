set unidad=%1
set directorio=%2
set nombrezip=%3
set nombrezip_fte=%4
set archivo=%5
set tipo=%6
%unidad%
cd %directorio%
if %tipo%==1 (goto consulta) else (goto adicional)
  
:consulta
zip -r  %nombrezip% %archivo% 
zip -r  %nombrezip_fte% %archivo%
  
:adicional
zip -r  %nombrezip% %archivo% 
