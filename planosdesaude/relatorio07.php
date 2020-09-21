<?php


header("Content-Type: text/html; charset=utf-8;");


require_once("../setfuncoes.php");# Atribuindo o valor de $bloco e $salto.

$corfundo="#FFF0FF";

iniciapagina($corfundo);

       
        $sql = pg_query("SELECT txnomeseguradora, count(cecarro) AS x
                                FROM seguradoras, seguros
                                WHERE ceseguradora = cpseguradora
                                GROUP BY txnomeseguradora");
        printf("<table border=1>\n");
        printf("<tr bgcolor='red'>Carros segurados por Seguradora<td>Seguradora</td><td>Numero Carros</td></tr>\n");
        $cor="lightgreen";
        while ($le = pg_fetch_array($sql))
        { # 1.7.1.2.1 -------------------------------------------------------------------------------------------------------------------------------
          printf("<tr bgcolor='$cor'><td>$le[txnomeseguradora] </td>
                                     <td>$le[x]</td>
                                     </tr>\n");
          $cor=( $cor == "WHITE" ) ? "LIGHTGREEN" : "WHITE";
        } # 1.7.1.2.1 -------------------------------------------------------------------------------------------------------------------------------
        printf("</table>\n");
       


  terminapagina('Relatoria desenvolvido para a materia de banco de dados professor Joao Mauricio','Caio Campos',FALSE); 

printf("</center>\n</font>\n</div>\n</body>\n</html>\n");
?>