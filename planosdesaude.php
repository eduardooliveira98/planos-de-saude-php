<?php
#####################################################################################################################################################
# Programa....: planosdesaude.php
# Descrição...: Programa de Gerenciamento das funcionalidades de manutenção de dados da tabela medicos.
# Autor.......: João Maurício Hypólito - Use! Mas fale quem fez!
# Criação.....: 2018-10-08
# Atualização.: 2018-10-08 - Declarei a estrutura fundamental do programa e escrevemos a funcionalidade de consulta.
#               2018-10-14 - Mudei a referencia do setfuncoes para o diretório anterior (algumas funcoes novas estão no setfuncoes.
#####################################################################################################################################################
# Trecho de declaração das funções. Para cada uma apresentamos um cabeçalho curto com nome/parâmetros/descrição/histórico de atualizações e objetivo
function picklist($acao,$bloco,$salto)
{ # Esta função monta uma caixa de seleção para escolha de um registro para Consulta, Alteração ou Exclusão.
  # determinando qual programa será chamado pela função
  # $prg=( $acao=='Consultar' ) ? "./consultar.php" : (( $acao=='Alterar' ) ? "./alterar.php" : "./excluir.php");
  $bloco=$bloco+1;
  # Executando comandos em SQL nas tabelas da base que foi acessada. Usamos a função pg_query.
  # Esta função RETORNA: O NOME da tabela acessada, os CAMPOS e os ENDEREÇOS de Registros lidos no comando
  $sql=pg_query("SELECT cpcodplan,txnomeplano FROM planosdesaude ORDER by txnomeplano");
  # Podemos montar repetidas vezes os valores de UM VETOR com os dados lidos.
  # Aqui vamos montar um form 'passando' o valor $ bloco para 2 e repetindo o valor de $salto (sempre criado no programa principal)
  printf("<form action='planosdesaude.php' method='POST'>\n");
  printf("<input type='hidden' name='acao'  value='$acao'>\n");
  printf("<input type='hidden' name='bloco' value='$bloco'>\n");
  printf("<input type='hidden' name='salto' value='$salto'>\n");
  # A caixa de seleção DEVE ter um nome para ser identificado no vetor $_POST[] do programa que recebe os dados dos campos do form
  printf("<select name='cpcodplan'>\n");
  printf("<option value='escolha'>Escolha um plano de saúde</option>\n");

  while ( $reg=pg_fetch_array($sql) )
  {
    printf("<option value='$reg[cpcodplan]'>$reg[txnomeplano] - ($reg[cpcodplan])</option>\n");
  }
  printf("</select>\n");
  # montando os botões do form com a função botoes e os parâmetros:
  # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
  botoes(FALSE,TRUE,TRUE,TRUE,$acao,$salto);
  # Esta função é geral e está escrita no toolskit.
  printf("</form>\n<br>\n");
}
function showdata($cp)
{ # Esta função recebe um valor da cp, consulta o registro e mostra o registro em uma tabela.
  # Este próximo comando é uma junção de médicos, logradouros (duas vezes com nomes L1 e L2), especialidadesmedicas e instituicaoensino.
  # Esta junção+selecção+projeção monta um vetor com todos os campos necessários para mostrar os dados de um médico.
  $cmdsql="SELECT M.*, L1.txnomelogradouro AS txnomelogrmora, L2.txnomelogradouro AS txnomelogrclin, txnomeespecialidade, txnomeinstituicao
                  FROM medicos AS M, logradouros AS L1, logradouros AS L2, especialidadesmedicas AS E, instituicoesensino AS I
                  WHERE M.celogradouromoradia=L1.cplogradouro AND
                        M.celogradouroclinica=L2.cplogradouro AND
                        M.ceespecialidade=E.cpespecialidade AND
                        M.ceinstituicao=I.cpinstituicao AND
                        M.cpmedico='$cp'";
  $execsql   = pg_query($cmdsql);
  # O resultado do comando SQL acima é um registro com todos os dados do médico consultado e mais os campos das tabelas que tem CE em médicos.
  # o comando a seguir coloca os valores em um Vetor.
  $reg   = pg_fetch_array($execsql);
  # Mostrando o nome da especialidade usando a função mostracampos (está escrita no setfuncoes()).
  # a variável a seguir 'recebe' o valor do nome da especialidade médica.
  $txnomeespecialidade=mostracampos("PostgreSQL","txnomeespecialidade","especialidadesmedicas","cpespecialidade",$reg['ceespecialidade']);
  printf("<table>\n");
  printf("<tr><td>C&oacute;digo</td>         <td>$reg[cpmedico]</td></tr>\n");
  printf("<tr><td>Nome</td>                  <td>$reg[txnomemedico]</td></tr>\n");
  printf("<tr><td>CRM</td>                   <td>$reg[nucrm]</td></tr>\n");
  # Os dois comandos a seguir mostram a mesma coisa só que de modos diferentes.
  # No primeiro usa-se a variável $txnomeespecialidade e no segundo a função mostracampos()
  #  printf("<tr><td>Especialidade M&eacute;dica</td>    <td>$reg[ceespecialidade] - ($txnomeespecialidade)</td></tr>\n");
  printf("<tr><td>Especialidade M&eacute;dica</td>    <td>$reg[ceespecialidade] - (".mostracampos("PostgreSQL","txnomeespecialidade","especialidadesmedicas","cpespecialidade",$reg['ceespecialidade']).")</td></tr>\n");
  printf("<tr><td>Institui&ccedil;&atilde;o de Ensino</td>    <td>$reg[ceinstituicao] - ($reg[txnomeinstituicao])</td></tr>\n");
  printf("<tr><td>Logradouro-Moradia</td>    <td>$reg[txnomelogrmora] - ($reg[celogradouromoradia])</td></tr>\n");
  printf("<tr><td>Complemento</td>           <td>$reg[txcomplementomoradia]</td></tr>\n");
  printf("<tr><td>Logradouro-Clinica</td>    <td>$reg[txnomelogrclin] - ($reg[celogradouroclinica])</td></tr>\n");
  printf("<tr><td>Complemento</td>           <td>$reg[txcomplementoclinica]</td></tr>\n");
  # O comando a seguir executa um operador ternário dentro de um printf().
  printf("<tr><td>Situa&ccedil;&atilde;o</td><td>%s</td></tr>\n",($reg['aoativo']=='A') ? "Ativado" : "Desativado");
  # O comando a seguir executa a função DATE() e STRTOTIME() dentro do printf() para formatar a data de cadasttro do médico em DD/MM/YYYY
  printf("<tr><td>Cadastrado em</td>         <td>%s</td></tr>\n",date('d/m/Y',strtotime($reg['dtcadmedico'])));
  printf("</table>\n");
}
## Aqui começa o programa principal.
# Ajustando os caracteres de acentução
header('Content-Type: text/html; charset=UTF-8;');
## Este programa utliza duas DIVs:
## - (Menu - navbar) no topo da tela e FIXA e
## - (Principal - main) será variável e contendo as funcionalidades Abertura-ICAEL
## A DIV navbar não deve ser formatada caso o programa for executar a funcionalidade de listagem ou relatórios no bloco '3'
# Carregar o SetFuncoes(e executar as funções Gerais disponíveis no grupo de funções)
require_once("../setfuncoes.php");# Atribuindo o valor de $bloco e $salto.
# Atribuir valores às variáveis:
#    $acao =(Existe $_POST['acao']) então $_POST['acao'], senão "Abertura"
#    $bloco=(Existe $_POST['bloco']) então $_POST['bloco'], senão '1' e
#    $salto=(Existe $_POST['salto']) então$_POST['salto']+1, senão '1'
$acao = ( ISSET($_POST['acao'])  ) ? $_POST['acao'] : "Abertura";
$bloco= ( ISSET($_POST['bloco']) ) ? $bloco=$_POST['bloco'] : '1';
$salto= ( ISSET($_POST['salto']  ) ? $_POST['salto']+1 : '1');   // $salto recebe $_POST['salto']+1 (se houver), senão 1
$corfundo=( $acao=='Listar' AND $bloco==3 ) ? "#FFFFFF" /* white */ : "#FFDEAD" /* navajowhite */ ;
$corfonte="#000000"; # black - Cor da Fonte que é usada nos textos das telas (exceto títulos)
# A função a seguir está no toolskit.
iniciapagina($corfundo);
# Criando o vetor que tem os valores de controle para NÃO formatar a DIV navbar
$funcoes = array("Listar", "Relat1", "Relat2");
$relatorio = ( in_array($acao,$funcoes) AND ( $bloco==3 ) ) ? TRUE : FALSE ;
# O if(){} a seguir monta a DIV navbar SE a variável $relatoiro NÃO for verdadeira.
if ( !$relatorio )
{
    # Nesta div montamos o menu (uma tabela com uma linha e um form com sete botões) que deve aparecer na parte fixa da tela.
    # O comportamento desta DIV esta caracterizada na CSS navbar declarada no iniciapagina na classe .navbar
    printf("<div class='navbar'>\n");
    printf("<center>\n<table width=700>\n<tr>\n<td>\n");
    printf("<form action='./planosdesaude.php' method='POST' >\n");
    printf("<font face='TAHOMA' color=red size=4>Acesse:</font> ");
    printf("<button type='submit' class='button' name='acao' value='Incluir'> Incluir </button>");
    printf("<button type='submit' class='button' name='acao' value='Consultar'> Consultar </button>");
    printf("<button type='submit' class='button' name='acao' value='Alterar'> Alterar </button>");
    printf("<button type='submit' class='button' name='acao' value='Excluir'> Excluir </button>");
    printf("<button type='submit' class='button' name='acao' value='Listar'> Listar </button>");
    printf("<button type='submit' class='button' name='acao' value='Relat1'> Relat&oacute;rio 1 </button>");
    printf("<button type='submit' class='button' name='acao' value='Relat2'> Relat&oacute;rio 2 </button>\n");
    printf("</form>\n</td>\n</tr>\n</table>\n</center>\n");
    printf("</div>\n");
    printf("\n");
}
#Depois de montada a DIV de navegação, vamos montar a DIV de execução das funcionalides.
# Atribuindo o valor do tótulo da tela na variável $titulo, este valor depende do valor de $acao (atribuida no menu e carregada de modo recursivo).
$titulo = ( $acao=="Abertura")  ? "Abertura" : (( $acao=="Incluir")   ? "Inclus&atilde;o" : (( $acao=="Consultar") ? "Consulta" : (( $acao=="Alterar")   ? "Altera&ccedil;&atilde;o" : (( $acao=="Excluir")   ? "Exclus&atilde;o" : (( $acao=="Listar")    ? "Listagem" : "" ) ) ) ) );
# Começando a <DIV> principal. Aqui se desenvolvem as funcionalidades principais.
printf("<div class='main'>\n");
# Os próximos 4 comandos somente devem ser emitidos SE a passagem do programa NÃO for LISTAR, RELAT1 e RELAT2 no bloco 3
# (emite o relatório em aba nova, fundo branco e com botão para imprimir em impressora local).
# Por isso está dentro deste IF(){}.
# Determina a fonte TAHOMA com tamanho 3, cor indicada em $corfonte e posicionamento centralizado
printf("%s",( (!$relatorio) ? "<font face='tahoma' size=3 color='".$corfonte."'>\n<center>\n" : ""));
# Iniciando a tabela que "enquadra" os conteúdos no centro da tela e com alinhamento para a esquerda.
printf("%s",( (!$relatorio) ? "<table border=0 width=700>\n" : "" ));
printf("%s",( (!$relatorio) ? "<tr><td><font color=red><strong>M&eacute;dicos</strong> - <i>".$titulo."</i></font><br>&nbsp;</td></tr>\n" : "" ));
printf("%s",( (!$relatorio) ? "<tr>\n<td>\n" : "" )); #<table border=1 width=700>\n
# Divisor principal do programa com base na variável $acao.
# Em cada acao ainda pode haver uma subdivisão com base na variável $bloco.
switch (TRUE)
{ # 1 - divisor principal de blocos -----------------------------------------------------------------------------------------------------------------
  case ($acao=='Abertura'):
  { # 1.1 - página de abertura do programa ----------------------------------------------------------------------------------------------------------
    printf("Este &eacute; o sistema de programas de Gerenciamento de MOMOMOMO - vers&atilde;o compacta - para o SGBD PostgreSQL<br><br>\n");
    printf("Use o Menu acima para escolher as a&ccedil;&otilde;es que deseja realizar sobre os dados da tabela.<br>\n");
    printf("Para cada a&ccedil;&atilde;o disparada uma nova tela se abre neste painel (inferior).<br>\n");
    printf("Nesta nova tela, na &uacute;ltima linha (escrita centralizada na tela), no lado esquerdo surge a fun&ccedil;&atilde;o executada e no lado direito o c&oacute;digo do programa em execu&ccedil;&atilde;o.<br>\n");
    printf("Isso ajuda muito na hora de localizar eventuais erros no programa.<br><br>\n");
    printf("Se um erro ocorrer no uso do Programa entre em contato com o Suporte t&eacute;cnico informando a mensagem de erro e o c&oacute;digo do programa.<br><br>\n");
    break;
  } # 1.1 - Fim da tela de Abertura -----------------------------------------------------------------------------------------------------------------
  case ($acao=='Incluir'):
  { # 1.2 - função de Inclusão ----------------------------------------------------------------------------------------------------------------------
    break;
  } # 1.2 - fim da função de Inclusão ---------------------------------------------------------------------------------------------------------------
  case ($acao=='Consultar'):
  { # 1.3 - função de Consulta ----------------------------------------------------------------------------------------------------------------------
    # Desvio de Blocos Principais baseado em $bloco.
    SWITCH (TRUE)
    { # 1.3.1 - Este é o comando de desvio principal do programa. -----------------------------------------------------------------------------------
      case ( $bloco==1 ):
      { # 1.3.1.1 - executa a função picklist() - monta a picklist escolhendo o registro de consulta -------------------------------------------
        picklist($acao,$bloco,$salto);
        break;
      } # 1.3.1.1 -----------------------------------------------------------------------------------------------------------------------------------
      case ( $bloco==2 ):
      { # 1.3.1.2 - mostrando o registro escolhido --------------------------------------------------------------------------------------------------
        showdata("$_POST[cpmedico]");
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(TRUE,TRUE,TRUE,FALSE,NULL,$salto);
        printf("<br>\n");
        break;
      } # 1.3.1.2 -----------------------------------------------------------------------------------------------------------------------------------
    }  # 1.3.1 --------------------------------------------------------------------------------------------------------------------------------------
    break;
    break;
  } # 1.3 - fim da função de Consulta ---------------------------------------------------------------------------------------------------------------
  case ($acao=='Alterar'):
  { # 1.4 - função de Alteração ---------------------------------------------------------------------------------------------------------------------
    break;
  } # 1.4 - fim da função de Alteração --------------------------------------------------------------------------------------------------------------
  case ($acao=='Excluir'):
  { # 1.5 - função de Exclusão ----------------------------------------------------------------------------------------------------------------------
    # Desvio de Blocos Principais baseado em $bloco.
    SWITCH (TRUE)
    { # 1.5.1-Este é o comando de desvio principal do programa. -------------------------------------------------------------------------------------
      case ( $bloco==1 ):
      { # 1.5.1.1 - Bloco para Escolha do Registro para Excluir. Executa a função picklist() --------------------------------------------------------
        picklist($acao,$bloco,$salto);
        break;
      } # 1.5.1.1 -----------------------------------------------------------------------------------------------------------------------------------
      case ( $bloco==2 ):
      { # 1.5.1.2 - Bloco para exibir o registro e confirmar a exclusão -------------------------------------------------------------------------------
        showdata("$_POST[cpmedico]");
        printf("<form action='./planosdesaude.php'  method='POST'>\n");
        printf("<input type='hidden' name='acao'  value='$acao'>\n");
        printf("<input type='hidden' name='bloco' value=3>\n");
        printf("<input type='hidden' name='salto' value='$salto'>\n");
        printf("<input type='hidden' name='cpmedico' value='$_POST[cpmedico]'>\n");
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(TRUE,TRUE,TRUE,FALSE,"Confirma a Exclus&atilde;o",$salto);
        printf("</form>\n");
        break;
      } # 1.5.1.2 - Fim do Bloco de exibir registro ---------------------------------------------------------------------------------------------------
      case ( $bloco==3 ):
      { # 1.5.1.3 - Bloco para Tratamento da Transação-------------------------------------------------------------------------------------------------
        # Montando o comando de DELETE
        $cmd="DELETE FROM medicos WHERE cpmedico='$_POST[cpmedico]'";
        # Ajustando a tabela de simbolos recebidos/enviados para o BD para UTF8
        pg_query("SET NAMES 'utf8'");
        pg_query("SET CLIENT_ENCODING TO 'utf8'");
        pg_set_client_encoding('utf8');
        # exibindo mensagem de orientação
        printf("Excluindo o Registro...<br>\n");
        #--------------------------------------------------------------------------------------------------------------------------------------------
        # Executando o case que remove (DELETE) os dados na tabela medicos.
        # Tratamento da Transação
        # Inicio da transação - No PostgreSQL se inica com o comando BEGIN. Colocamos dentro de um WHILE para poder
        # controlar o reinicio da transação caso aconteça um DEADLOCK.
        $tentativa=TRUE;
        while ( $tentativa )
        { # 1.5.1.2.1-Laço de repetição para tratar a transação -------------------------------------------------------------------------------------
          $query = pg_send_query($link,"BEGIN");
          $result=pg_get_result($link);
          $erro=pg_result_error($result);
          # Depois que se inicia uma transação o comando enviado para o BD deve ser através da função pg_send_query().
          # Esta função avisa ao PostgreSQL que devem ser usados os LOGs de transação para acessar os dados.
          # A cada send_query o PostgreSQL responde com um sinal de status (erro ou não erro).
          # Por conta disso deve-se "ler" este status com as funções pg_getr_result() e pg_result_error().
          # Executando o comando (montado FORA do laço de tentativa) e capturando um eventual erro.
          $comando=pg_send_query($link,$cmd);
          $result=pg_get_result($link);
          $erro=pg_result_error($result);
          $volta=pg_fetch_array($result);
          # O Próximo SWITCH trata as situações de erro. A função pg_get_result($link) retorna o número do erro do PostgreSQL.
          switch (TRUE)
          { # 1.5.1.1.3 - Avaliação da situação de erro (se existir). -------------------------------------------------------------------------------
            case $erro == "" :
            { # 1.5.1.1.3.1 - Nao tem erro! Concluir a transacao e Avisar o usuario. ----------------------------------------------------------------
              # Comando que foi EXECUTADO no BD  SEM ERRO  podemos COMMITAR a transação.
              $query=pg_send_query($link,"COMMIT"); # A captura do erro fica fora do SWITCH CASE
              printf("Registro <b>Exclu&iacute;do</b> com sucesso!<br>\n");
              $tentativa=FALSE;
              break;
            } # 1.5.1.1.3.1 -------------------------------------------------------------------------------------------------------------------------
            case $erro == "deadlock_detected" :
            { # 1.5.1.1.3.2 - Erro de DeadLock - Cancelar e Reiniciar a transacao
              $query=pg_send_query($link,"ROLLBACK");
              $tentativa=TRUE;
              break;
            } # 1.5.1.1.3.2 -------------------------------------------------------------------------------------------------------------------------
            case $erro != '' AND  $erro!= 'deadlock_detected' :
            { # 1.5.1.1.3.3 - Erro! NÃO por deadlock. AVISAR o usuario. CANCELAR A transacao --------------------------------------------------------
              printf("<b>Erro na tentativa de Inserir!</b><br>\n");
              $mens=$result." : ".$erro;
              printf("Mensagem: $mens<br>\n");
              $query=pg_send_query($link,"ROLLBACK");
              $tentativa=FALSE;
              break;
            } # 1.5.1.1.3.3 -------------------------------------------------------------------------------------------------------------------------
          } # 1.5.1.1.3 - Fim do SWITCH tratando os status da transação -----------------------------------------------------------------------------
          $resultfinal=pg_get_result($link);
          $errofinal=pg_result_error($resultfinal);
        } # 1.5.1.2.1-Fim do Laço de repetição para tratar a transação ------------------------------------------------------------------------------
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(FALSE,TRUE,TRUE,FALSE,NULL,$salto);
        printf("<br>\n");
        break;
      } # 1.5.1.3 - Fim do Bloco de Tratamento da Transação -------------------------------------------------------------------------------------------
    } # 1.5.1-Fim do divisor de blocos principal ------------------------------------------------------------------------------------------------------
    break;
  } # 1.5 - fim da função de Exclusão ---------------------------------------------------------------------------------------------------------------
  case ($acao=='Listar'):
  { # 1.6 - função de Listagem ----------------------------------------------------------------------------------------------------------------------
    break;
  } # 1.6 - fim da função de Listagem ---------------------------------------------------------------------------------------------------------------
  case ($acao=='Relat1'):
  { # 1.7 - função de Relat1 ----------------------------------------------------------------------------------------------------------------------
    break;
  } # 1.7 - fim da função de Listagem ---------------------------------------------------------------------------------------------------------------
  case ($acao=='Relat2'):
  { # 1.8 - função de Listagem ----------------------------------------------------------------------------------------------------------------------
    break;
  } # 1.8 - fim da função de Listagem ---------------------------------------------------------------------------------------------------------------
} # 1 - Fim do divisor principal do programa --------------------------------------------------------------------------------------------------------
# Depois de terminar o divisor principal, deve-se emitir as tags que finalizam a página,
# notando que quando for relatório a linha de autoria não precisa ser emitida.
if ( !$relatorio )
{
  # Terminando a tabela aberta na <DIV>
  printf("<td>\n</tr>\n</table>\n");
  terminapagina($acao,"planosdesaude.php",FALSE);
}
printf("</center>\n</font>\n</div>\n</body>\n</html>\n");
?>