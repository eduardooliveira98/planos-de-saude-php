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
  $sql=pg_query("SELECT cpplanodesaude,txnomeplano FROM planosdesaude ORDER by txnomeplano");
  # Podemos montar repetidas vezes os valores de UM VETOR com os dados lidos.
  # Aqui vamos montar um form 'passando' o valor $ bloco para 2 e repetindo o valor de $salto (sempre criado no programa principal)
  printf("<form action='planosdesaude.php' method='POST'>\n");
  printf("<input type='hidden' name='acao'  value='$acao'>\n");
  printf("<input type='hidden' name='bloco' value='$bloco'>\n");
  printf("<input type='hidden' name='salto' value='$salto'>\n");
  # A caixa de seleção DEVE ter um nome para ser identificado no vetor $_POST[] do programa que recebe os dados dos campos do form
  printf("<select name='cpplanodesaude'>\n");
  printf("<option value='escolha'>Escolha um plano de saúde</option>\n");

  while ( $reg=pg_fetch_array($sql) )
  {
    printf("<option value='$reg[cpplanodesaude]'>$reg[txnomeplano] - ($reg[cpplanodesaude])</option>\n");
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
  $cmdsql="SELECT M.*, L1.txnomelogradouro AS txlogradouro
                  FROM planosdesaude AS M, logradouros AS L1
                  WHERE M.celogradouro=L1.cplogradouro AND
                        M.cpplanodesaude='$cp'";
  $execsql   = pg_query($cmdsql);
  # O resultado do comando SQL acima é um registro com todos os dados do médico consultado e mais os campos das tabelas que tem CE em médicos.
  # o comando a seguir coloca os valores em um Vetor.
  $reg   = pg_fetch_array($execsql);
  # Os dois comandos a seguir mostram a mesma coisa só que de modos diferentes.
  # No primeiro usa-se a variável $txnomeespecialidade e no segundo a função mostracampos()
  #  printf("<tr><td>Especialidade M&eacute;dica</td>    <td>$reg[ceespecialidade] - ($txnomeespecialidade)</td></tr>\n");
  printf("<tr><td>Planos de saude</td>    <td>$reg[cpplanodesaude] - $reg[txnomeplano]</td></tr>\n");
  printf("<tr><td>Razão Social</td>    <td>$reg[txrazaosocial]</td></tr>\n");
  printf("<tr><td>CNPJ</td>    <td>$reg[nucnpj]</td></tr>\n");
  printf("<tr><td>Logradouro</td>    <td>$reg[celogradouro] - ($reg[txlogradouro])</td></tr>\n");
  printf("<tr><td>Complemento</td>           <td>$reg[txcomplemento]</td></tr>\n");
  printf("<tr><td>CEP</td>    <td>$reg[nucep]</td></tr>\n");

  # O comando a seguir executa um operador ternário dentro de um printf().
  
  # O comando a seguir executa a função DATE() e STRTOTIME() dentro do printf() para formatar a data de cadasttro do médico em DD/MM/YYYY
  printf("<tr><td>Cadastrado em</td>         <td>%s</td></tr>\n",date('d/m/Y',strtotime($reg['dtcadplansaude'])));
  printf("</table>\n");
}
function setupform($acao,$bloco,$salto)
{ # Esta função monta um formulário para inclusão ou alteração dos dados de um recebe um valor de cp,
  # monta a tela com os campos para digitação de valores nos campos.
  # Esta função pode ser usada no caso de programa onde o valor da cp será gerado pelo sistema.
  # Caso o valor da cp seja informado pelo usuário esta função deve ser ligeriamente alterada.
  #  $prg=( $acao=='Incluir' ) ? "./incluir.php" : "./alterar.php" ;
  # Montando o form de leitura dos dados dos campos que devem ser alterados na tabela
  #      (os campos FORM terao os mesmos NOMES dos campos da tabela).
  # Podemos montar um vetor com valores de um registro (para alteração) e se nada for lido o vetor fica vazio.
  # Criar um vetor com valores dos campos do registro lido na tabela.
  $cpplanodesaude = ( isset($_POST['cpplanodesaude']) ) ? $_POST['cpplanodesaude'] : 0;
  $reg=pg_fetch_array(pg_query("SELECT * FROM planosdesaude WHERE cpplanodesaude='$cpplanodesaude'"));
  /*
  # Caso se esteja usando campo com type=date para a dtcadmedico a sequencia a seguir pode ser excluída.
  # Entretanto, se o navegador não tratar este tipo de campo,
  # RETIRE o comentário deste segmento e na linha abaixo onde se declaram os campos para entrada da dtcadmedico.
  if ( isset($reg['cpmedico']) )
  {
    # Este vetor PODE ser manipulado normalmente
    $dtcad=explode("-",$reg['dtcadmedico']);
    $reg['diacad']=$dtcad[2];
    $reg['mescad']=$dtcad[1];
    $reg['anocad']=$dtcad[0];
  }
  */
  $mens=( isset($reg['cpplanodesaude']) ) ? $reg['cpplanodesaude']." - N&Atilde;O Ser&aacute; Alterado pelo sistema" : "Ser&aacute; gerado pelo sistema";
  # Aqui vamos montar um form'passando' o valor $bloco para $bloco+1.
  $bloco=$bloco+1;
  printf("<form action='./planosdesaude.php'  method='POST'>\n");
  printf("%s\n",( isset($reg['cpplanodesaude']) ) ? "<input type='hidden' name='cpplanodesaude' value='$reg[cpplanodesaude]'>" : "");
  printf("<input type='hidden' name='acao'  value='$acao'>\n");
  printf("<input type='hidden' name='bloco' value='$bloco'>\n");
  printf("<input type='hidden' name='salto' value='$salto'>\n");
  # Montar uma tabela com os campos para entrada de dados.
  # SE a acao for INCLUIR, então um vetor $reg sem conteúdo deve ser montado,
  # SE a acao for ALTERAR, então um vetor $reg é montado com campos da tabelas medicos (e com os valores para a cp escolhida)
  # printf("$acao<br>\n");
  printf("<table border=0>\n");
  printf("<tr><td>C&oacute;digo:</td><td>$mens</td></tr>\n");
  printf("<tr><td>Planos:</td><td><input type='text' name='txnomeplano' value=\"$reg[txnomeplano]\" size=60 maxlength=250></td></tr>\n");
  printf("<tr><td>Raz&atilde;o Social:</td><td><input type='text' name='txrazaosocial' value=\"$reg[txrazaosocial]\" size=60 maxlength=250></td></tr>\n");
  printf("<tr><td>CNPJ:</td><td><input type='text' name='nucnpj' value=\"$reg[nucnpj]\" size=60 maxlength=250></td></tr>\n");
  printf("<tr><td>Logradouro:</td><td>");
  $sql=pg_query("SELECT cplogradouro, txnomelogradouro FROM logradouros ORDER by txnomelogradouro");
  printf("<select name='celogradouro'>\n");
  while ( $sel=pg_fetch_array($sql) )
  {
    $selected=( $reg['celogradouro']==$sel['cplogradouro'] ) ? " SELECTED" : "" ; 
    printf("<option value='$sel[cplogradouro]'$selected>$sel[txnomelogradouro] - ($sel[cplogradouro])</option>\n");
  }
  printf("</select>\n");
  printf("</td></tr>\n");
  printf("<tr><td>Complemento:</td><td><input type='text' name='txcomplemento' value=\"$reg[txcomplemento]\" size=10 maxlength=10></td></tr>\n");
  printf("<tr><td>CEP:</td><td><input type='text' name='nucep' value=\"$reg[nucep]\" size=10 maxlength=10></td></tr>\n");
  
  # Comente a próxima linha se o seu navegador NÃO TRATAR campos type='date'
   printf("<tr><td>Data de Cadastro:</td><td><input type='date' name='dtcadplansaude' value='$reg[dtcadplansaude]'></td></tr>\n");
  printf("<tr><td>&nbsp;</td><td>");
  # montando os botões do form com a função botoes e os parâmetros:
  # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
  botoes(TRUE,TRUE,TRUE,TRUE,$acao,$salto); # função geral do toolskit
  printf("</td></tr>\n");
  printf("</table>\n");
  printf("</form>\n");
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
printf("%s",( (!$relatorio) ? "<tr><td><font color=red><strong>Planos de Sa&uacute;de</strong> - <i>".$titulo."</i></font><br>&nbsp;</td></tr>\n" : "" ));
printf("%s",( (!$relatorio) ? "<tr>\n<td>\n" : "" )); #<table border=1 width=700>\n
# Divisor principal do programa com base na variável $acao.
# Em cada acao ainda pode haver uma subdivisão com base na variável $bloco.
switch (TRUE)
{ # 1 - divisor principal de blocos -----------------------------------------------------------------------------------------------------------------
  case ($acao=='Abertura'):
  { # 1.1 - página de abertura do programa ----------------------------------------------------------------------------------------------------------
    printf("Este &eacute; o sistema de programas de Gerenciamento de fornecedores - vers&atilde;o compacta - para o SGBD PostgreSQL<br><br>\n");
    printf("Use o Menu acima para escolher as a&ccedil;&otilde;es que deseja realizar sobre os dados da tabela.<br>\n");
    printf("Para cada a&ccedil;&atilde;o disparada uma nova tela se abre neste painel (inferior).<br>\n");
    printf("Nesta nova tela, na &uacute;ltima linha (escrita centralizada na tela), no lado esquerdo surge a fun&ccedil;&atilde;o executada e no lado direito o c&oacute;digo do programa em execu&ccedil;&atilde;o.<br>\n");
    printf("Isso ajuda muito na hora de localizar eventuais erros no programa.<br><br>\n");
    printf("Se um erro ocorrer no uso do Programa entre em contato com o Suporte t&eacute;cnico informando a mensagem de erro e o c&oacute;digo do programa.<br><br>\n");
    printf("Este sistema foi desenvolvido por (Eduardo Oliveira) para contar como um dos trabalhos da disciplina.<br><br>\n");
    printf("Laborat&oacute;rio de Banco de Dados do curso de An&aacute;lise e Desenvolvimento de sistemas da FATEC de Ourinhos.<br><br>\n");
    break;
  } # 1.1 - Fim da tela de Abertura -----------------------------------------------------------------------------------------------------------------
  case ($acao=='Incluir'):
  { # 1.2 - funcionalidade: Incluir -----------------------------------------------------------------------------------------------------------------
    # Desvio de Blocos Principais baseado em $bloco. ------------------------------------------------------------------------------------------------
    SWITCH (TRUE)
    { # 1.2.1-montando a tela de form para digitação dos dados para inclusão ------------------------------------------------------------------------
      case ( $bloco==1 ):
      { # 1.2.1.1-Bloco para montagem do Formulário para entrada de dados. --------------------------------------------------------------------------
        # Montando o form de leitura dos dados dos campos da tabela (os campos FORM terão os mesmos NOMES dos campos da tabela.
        # Aqui vamos montar um form'passando' o valor $ bloco para 2.
        setupform($acao,$bloco,$salto);
        break;
      } # 1.2.1.1-Fim do Bloco que monta o form de entrada de dados ---------------------------------------------------------------------------------
      case ( $bloco==2 ):
      { # 1.2.1.2-Bloco para Tratamento da Transação ------------------------------------------------------------------------------------------------
        # Alguns campos podem ter conteúdo indevido para a construção do comando INSERT. Pode ser um SQL injection ou um simples caractere que rompe
        # a cadeia de caracteres que montam o comando de atualização no Banco. Podemos usar o PHP e fazer uma substituição de caracteres ou até mesmo
        # bloquear a execução dos comandos que seguem este trecho.
        #
        # Neste ponto do programa podemos usar funções do PHP para trocar caracteres indevidos para o INSERT.
        
        # Ajustando a tabela de simbolos recebidos/enviados para o BD para UTF8
        pg_query("SET NAMES'utf8'");
        pg_query("SET CLIENT_ENCODING TO'utf8'");
        pg_set_client_encoding('utf8');
        # exibindo mensagem de orientação
        printf("Incluindo o Registro...<br>\n");
        #--------------------------------------------------------------------------------------------------------------------------------------------
        # Executando o case que grava (INSERT) os dados na tabela medicos.
        # Tratamento da Transação
        # Inicio da transação - No PostgreSQL se inica com o comando BEGIN. Colocamos dentro de um WHILE para poder
        # controlar o reinicio da transação caso aconteça um DEADLOCK.
        $tentativa=TRUE;
        while ( $tentativa )
        { # 1.2.1.2.1-Laço de repetição para tratar a transação -------------------------------------------------------------------------------------
          $query = pg_send_query($link,"BEGIN");
          $result=pg_get_result($link);
          $erro=pg_result_error($result);
          # Depois que se inicia uma transação o comando enviado para o BD deve ser através da função pg_send_query().
          # Esta função avisa ao PostgreSQL que devem ser usados os LOGs de transação para acessar os dados.
          # A cada send_query o PostgreSQL responde com um sinal de status (erro ou não erro).
          # Por conta disso deve-se "ler" este status com as funções pg_getr_result() e pg_result_error().
          # Montando em uma variavel a data de cadastro no formato do BD
          # $dtcadmedico=$_POST['anocad'].'-'.$_POST['mescad'].'-'.$_POST['diacad'];
          # Vamos pegar o último código gravado na tabela medicos. Este trecho fica'dentro' da transação para gerar
          # o bloqueio na página de dados que vai gravar o próximo registro.
          # Estamos gerando o valor da cp e NÃO usando campos autoincrementados PORQUE este recurso não está disponível em todos os SGBDs
          # e SE UM DIA um ilustre aluno trabalhar com um destes SGBD vai se lembrar que um professor ensinou a trabalhar a determinação
          # do próximo valor de uma chave primária DENTRO da aplicação. Para'brincar' com o conceito...
          # SUPONDO que o bloco de incremento seja 5 (CINCO)... escrevemos.
          $proxcp=pg_result(pg_query("SELECT max(cpplanodesaude)+1 as CMAX FROM planosdesaude"),0,'CMAX');
          # A tabela pode estar vazia, neste caso o CMAX é nulo e $proxcp NÃO recebe valor. Então a proxima cp deve ser 1.
          $cp=( isset($proxcp) ) ? $proxcp : 5;
          # Montando o comando de INSERT (Dentro do laço de repatição das tentativas porque o valor da cp depende da leitura da tabela'dentro' da transação)
          $cmd="INSERT INTO planosdesaude VALUES ('$cp',
                                           '$_POST[txnomeplano]',
                                           '$_POST[txrazaosocial]',
                                           '$_POST[nucnpj]',
                                           '$_POST[celogradouro]',
                                           '$_POST[txcomplemento]',
                                           '$_POST[nucep]',
                                           
											'$_POST[dtcadplansaude]') RETURNING cpplanodesaude;";
          # O comando INSERT pode ser escrito em uma só linha (mais extenso), o que pode dificultar encontrar um erro eventual.
          # Na forma'quebrada' fica mais fácil entender o comando.
          # Para o SGBD os sinais de enter e os espaços em branco não afeta o comando INSERT.
          # printf("$cmd<br>\n"); # Se quiser ver o comando na fase de teste, tire o comentário no início da linha.
          $comando=pg_send_query($link,$cmd);
          $result=pg_get_result($link);
          $erro=pg_result_error($result);
          $volta=pg_fetch_array($result);
          $cp=$volta['cpplanodesaude'];
          # O Próximo SWITCH trata as situações de erro. A função pg_get_result($link) retorna o número do erro do PostgreSQL.
          # Dentro deste SwitchCase atribui-se o valor de $mostra.
          # $mostra vale FALSE se acontecer algum erro na execução e TRUE se a transação terminar SEM erro.
          switch (TRUE)
          { # 1.2.1.1.3 - Avaliação da situação de erro (se existir).
            case $erro == "" :
            { # 1.2.1.1.3.1 - Nao tem erro! Concluir a transacao e Avisar o usuario. ----------------------------------------------------------------
              # Comando que foi EXECUTADO no BD podemos MOSTRAR o comando na tela para suporte ao usuário.
              #printf("$cmd<br>\n");
              #printf("$cp<br>\n");
              $query=pg_send_query($link,"COMMIT");
              printf("Registro <b>Inserido</b> com sucesso!<br>\n");
              $tentativa=FALSE;
              $mostra=TRUE;
              break;
            } # 1.2.1.1.3.1 -------------------------------------------------------------------------------------------------------------------------
            case $erro == "deadlock_detected" :
            { # 1.1.3.2 - Erro de DeadLock - Cancelar e Reiniciar a transacao -----------------------------------------------------------------------
              $query=pg_send_query($link,"ROLLBACK");
              $tentativa=TRUE;
              break;
            } # 1.2.1.1.3.2 -------------------------------------------------------------------------------------------------------------------------
            case $erro !='' AND  $erro!='deadlock_detected' :
            { # 1.2.1.1.3.3 - Erro! NÃO por deadlock. AVISAR o usuario. CANCELAR A transacao --------------------------------------------------------
              printf("<b>Erro na tentativa de Inserir!</b><br>\n");
              $mens=$result." : ".$erro;
              printf("Mensagem: $mens<br>\n");
              $query=pg_send_query($link,"ROLLBACK");
              $tentativa=FALSE;
              $mostra=FALSE;
              break;
            } # 1.2.1.1.3.3 -------------------------------------------------------------------------------------------------------------------------
          } # 1.2.1.1.3 - Fim do SWITCH tratando os status da transação -----------------------------------------------------------------------------
          $resultfinal=pg_get_result($link);
          $errofinal=pg_result_error($resultfinal);
        } # 1.2.1.2.1 - Fim do Laço de repetição para tratar a transação ----------------------------------------------------------------------------
        if ( $mostra )
        { # Executando a função do subprograma com o valor de $CP como cp. --------------------------------------------------------------------------
          showdata("$cp");
        } # -----------------------------------------------------------------------------------------------------------------------------------------
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(FALSE,TRUE,TRUE,FALSE,NULL,$salto);
        printf("<br>\n");
        break;
      } # 1.2.1.3-Fim do Bloco de Tratamento da Transação -------------------------------------------------------------------------------------------
    } # 1.2.1-Fim do divisor de blocos principal ----------------------------------------------------------------------------------------------------
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
        showdata("$_POST[cpplanodesaude]");
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(TRUE,TRUE,TRUE,FALSE,NULL,$salto);
        printf("<br>\n");
        break;
      } # 1.3.1.2 -----------------------------------------------------------------------------------------------------------------------------------
    }  # 1.3.1 --------------------------------------------------------------------------------------------------------------------------------------
    break;
  } # 1.3 - fim da função de Consulta ---------------------------------------------------------------------------------------------------------------
  case ($acao=='Alterar'):
 { # 1.4 - funcionalidade: Alterar -----------------------------------------------------------------------------------------------------------------
    # Desvio de Blocos Principais baseado em $bloco.
    SWITCH (TRUE)
    { # 1.4.1 - Este é o comando de desvio principal do programa. -----------------------------------------------------------------------------------
      case ( $bloco==1 ):
      { # 1.4.1.1 - executa a função picklist() - monta a picklist escolhendo o registro de alteração ------------------------------------------
        picklist($acao,$bloco,$salto);
        break;
      } # 1.4.1.1 -----------------------------------------------------------------------------------------------------------------------------------
      case ( $bloco==2 ):
      { # 1.4.1.2 - ---------------------------------------------------------------------------------------------------------------------------------
        setupform($acao,$bloco,$salto);
        break;
      } # 1.4.1.2-Fim do Bloco de exibir registro ---------------------------------------------------------------------------------------------------
      case ( $bloco==3 ):
      { # 1.4.1.3-Bloco para Tratamento da Transação ------------------------------------------------------------------------------------------------
        # Alguns campos podem ter conteúdo indevido para a construção do comando UPDATE. Pode ser um SQL injection ou um simples caractere que rompe
        # a cadeia de caracteres que montam o comando de atualização no Banco. Podemos usar o PHP e fazer uma substituição de caracteres ou até mesmo
        # bloquear a execução dos comandos que seguem este trecho.
        #
        # Neste ponto do programa podemos usar funções do PHP para trocar caracteres indevidos para o UPDATE.
       
        # Montando o comando de UPDATE (Está quebrado em mais de uma linha mas isso não faz diferença para o PostgreSQL, porém fica mais fácil de achar algum eventual erro)
        # Montando em uma variavel a data de cadastro no formato do BD
        # $dtcadmedico=$_POST['anocad'].'-'.$_POST['mescad'].'-'.$_POST['diacad'];
        $cmd="UPDATE planosdesaude SET txnomeplano         ='$_POST[txnomeplano]',
                                 txrazaosocial                ='$_POST[txrazaosocial]',
                                 nucnpj     ='$_POST[nucnpj]',
                                 celogradouro        ='$_POST[celogradouro]',
                                 txcomplemento ='$_POST[txcomplemento]',
                                 nucep ='$_POST[nucep]',
                                 
								 dtcadplansaude ='$_POST[dtcadplansaude]'
                              WHERE cpplanodesaude='$_POST[cpplanodesaude]'";
        # Ajustando a tabela de simbolos recebidos/enviados para o BD para UTF8
        pg_query("SET NAMES'utf8'");
        pg_query("SET CLIENT_ENCODING TO'utf8'");
        pg_set_client_encoding('utf8');
        # exibindo mensagem de orientação
        printf("Alterando o Registro...<br>\n");
        # Executando o case que grava (UPDATE) os dados na tabela medicos.
        # Tratamento da Transação
        # Inicio da transação - No PostgreSQL se inica com o comando BEGIN. Colocamos dentro de um WHILE para poder
        # controlar o reinicio da transação caso aconteça um DEADLOCK.
        $tentativa=TRUE;
        while ( $tentativa )
        { # 1.4.1.2.1-Laço de repetição para tratar a transação -------------------------------------------------------------------------------------
          $query = pg_send_query($link,"BEGIN");
          $result=pg_get_result($link);
          $erro=pg_result_error($result);
          # Depois que se inicia uma transação o comando enviado para o BD deve ser através da função pg_send_query().
          # Esta função avisa ao PostgreSQL que devem ser usados os LOGs de transação para acessar os dados.
          # A cada send_query o PostgreSQL responde com um sinal de status (erro ou não erro).
          # Por conta disso deve-se "ler" este status com as funções pg_getr_result() e pg_result_error().
          # Executando o comando (montado for ado laço de repetição da tentativa) e capturando um eventual erro.
          $comando=pg_send_query($link,$cmd);
          $result=pg_get_result($link);
          $erro=pg_result_error($result);
          $volta=pg_fetch_array($result);
          # O Próximo SWITCH trata as situações de erro. A função pg_get_result($link) retorna o número do erro do PostgreSQL.
          switch (TRUE)
          { # 1.4.1.1.3 - Avaliação da situação de erro (se existir) --------------------------------------------------------------------------------
            case $erro == "" :
            { # 1.4.1.1.3.1 - Nao tem erro! Concluir a transacao e Avisar o usuario. ----------------------------------------------------------------
              # Comando que foi EXECUTADO no BD  SEM ERRO  podemos COMMITAR a transação.
              $query=pg_send_query($link,"COMMIT");
              printf("Registro <b>Alterado</b> com sucesso!<br>\n");
              $tentativa=FALSE;
              $mostra=TRUE;
              break;
            } # 1.4.1.1.3.1 -------------------------------------------------------------------------------------------------------------------------
            case $erro == "deadlock_detected" :
            { # 1.4.1.1.3.2 - Erro de DeadLock - Cancelar e Reiniciar a transacao -------------------------------------------------------------------
              $query=pg_send_query($link,"ROLLBACK");
              $tentativa=TRUE;
              break;
            } # 1.4.1.1.3.2 -------------------------------------------------------------------------------------------------------------------------
            case $erro !='' AND  $erro!='deadlock_detected' :
            { # 1.4.1.1.3.3 - Erro! NÃO por deadlock. AVISAR o usuario. CANCELAR A transacao --------------------------------------------------------
              printf("<b>Erro na tentativa de Alterar!</b><br>\n");
              $mens=$result." : ".$erro;
              printf("Mensagem: $mens<br>\n");
              $query=pg_send_query($link,"ROLLBACK");
              $tentativa=FALSE;
              $mostra=FALSE;
              break;
            } # 1.4.1.1.3.3 --------------------------------------------------------------------------------------------------------------------------
          } # 1.4.1.1.3 - Fim do SWITCH tratando os status da transação ------------------------------------------------------------------------------
          $resultfinal=pg_get_result($link);
          $errofinal=pg_result_error($resultfinal);
        } # 1.4.1.2.1-Fim do Laço de repetição para tratar a transação -------------------------------------------------------------------------------
        if ( $mostra )
        { # Executando a função do subprograma com o valor de $CP como cp.
          showdata("$_POST[cpplanodesaude]");
        } # -----------------------------------------------------------------------------------------------------------------------------------------
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(FALSE,TRUE,TRUE,FALSE,NULL,$salto);
        printf("<br>\n");
        break;
      } # 1.4.1.3-Fim do Bloco de Tratamento da Transação -------------------------------------------------------------------------------------------
    } # 1.4.1-Fim do divisor de blocos principAl ----------------------------------------------------------------------------------------------------
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
        showdata("$_POST[cpplanodesaude]");
        printf("<form action='./planosdesaude.php'  method='POST'>\n");
        printf("<input type='hidden' name='acao'  value='$acao'>\n");
        printf("<input type='hidden' name='bloco' value=3>\n");
        printf("<input type='hidden' name='salto' value='$salto'>\n");
        printf("<input type='hidden' name='cpplanodesaude' value='$_POST[cpplanodesaude]'>\n");
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(TRUE,TRUE,TRUE,FALSE,"Confirma a Exclus&atilde;o",$salto);
        printf("</form>\n");
        break;
      } # 1.5.1.2 - Fim do Bloco de exibir registro ---------------------------------------------------------------------------------------------------
      case ( $bloco==3 ):
      { # 1.5.1.3 - Bloco para Tratamento da Transação-------------------------------------------------------------------------------------------------
        # Montando o comando de DELETE
        $cmd="DELETE FROM planosdesaude WHERE cpplanodesaude='$_POST[cpplanodesaude]'";
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
              printf("<b>Erro na tentativa de excluir!</b><br>\n");
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
  { # 1.6 - funcionalidade: Listar ------------------------------------------------------------------------------------------------------------------
    $cordefundo = ($bloco==3) ? "white" : "navajowhite" ;
    # Iniciando a página
    #    printf("<html xml:lang='pt-BR' lang='pt-BR' dir='ltr'>\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n</head>\n");
    #    printf("<body bgcolor='$cordefundo'>\n");
    #    printf("<font color=red><b>medicos</b></font>\n");
    # SWITCH CASE com a variável $bloco
    SWITCH (TRUE)
    { # 1.6.1 ---------------------------------------------------------------------------------------------------------------------------------------
      case ($bloco==1):
      { # 1.6.1.1 Vamos montar o formulario para escolha da ordenação dos dados no relatório --------------------------------------------------------
        printf("<form action='./planosdesaude.php' method='post'>\n");
        printf("<input type='hidden' name='acao'  value='$acao'>\n");
        printf("<input type='hidden' name='bloco' value='2'>\n");
        printf("<input type='hidden' name='salto' value='$salto'>\n");
        printf("Escolha a ordena&ccedil;&atilde;o dos dados do relat&oacute;rio marcando um dos campos<br>\n");
        printf("<table>\n");
        printf("<tr><td>C&oacute;digo</td>             <td><INPUT TYPE=RADIO NAME='ordem' VALUE='P.cpplanodesaude' CHECKED></td></tr>\n");
        printf("<tr><td>Nome</td>                      <td><INPUT TYPE=RADIO NAME='ordem' VALUE='P.txnomeplano'></td></tr>\n");
        printf("<tr><td>CNPJ</td>        			   <td><INPUT TYPE=RADIO NAME='ordem' VALUE='C.nucnpj'></td></tr>\n");
		printf("<tr><td>Logradouro</td>        		   <td><INPUT TYPE=RADIO NAME='ordem' VALUE='L.txnomelogradouro'></td></tr>\n");
		printf("<tr><td>Data de Cadastro</td>          <td><INPUT TYPE=RADIO NAME='ordem' VALUE='C.dtcadplansaude'></td></tr>\n");
        printf("</table>\n");
        # montando os botões do form com a função botoes e os parâmetros:
        # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
        botoes(TRUE,TRUE,TRUE,TRUE,"Gerar Listagem",$salto);
        printf("</form>\n");
        break;
      } # 1.6.1.1 -----------------------------------------------------------------------------------------------------------------------------------
      case ($bloco==2 or $bloco==3):
      { # 1.6.1.2 - pegando o valor da variavel $ordena do formulario anterior ----------------------------------------------------------------------
        $ordem=$_POST['ordem'];
        # O proximo comando le a tabela de medicos ordenando os dados pela escolha indicada na variavel $ordem
        $sql = pg_query("SELECT P.*, L.txnomelogradouro
                                FROM planosdesaude AS P,
                                     logradouros AS L
                                WHERE P.celogradouro=L.cplogradouro
                                ORDER BY $ordem");
        printf("<table border=1 class=\"bordasimples\">\n");
        printf("<tr bgcolor='lightblue'><td>Planos</td>
                                        <td>Raz&atilde;o Social</td>
                                        <td>CNPJ</td>
                                        <td>Logradouro</td>
                                        <td>Complemento</td>
                                        <td>CEP</td>
										<td>Cadastrado</td></tr>\n");
        $cor="WHITE";
        while ($le = pg_fetch_array($sql))
        { # 1.2.1 -----------------------------------------------------------------------------------------------------------------------------------
          $dtcad=explode("-",$le['dtcadplansaude']);
          printf("<tr bgcolor='$cor'><td>$le[cpplanodesaude] - $le[txnomeplano]</td>
                                     <td>$le[txrazaosocial]</td>
                                     <td>$le[nucnpj]</td>
                                     <td>$le[celogradouro]</td>
                                     <td>$le[txcomplemento]</td>
									 <td>$le[nucep]</td>
                                     <td>$dtcad[2]/$dtcad[1]/$dtcad[0]</td> </tr>\n");
          $cor=( $cor == "WHITE" ) ? "LIGHTGREEN" : "WHITE";
        } # 1.2.1 -----------------------------------------------------------------------------------------------------------------------------------
        printf("</table>\n");
        if ( $bloco==2 )
        { # 1.6.1.2.2 vamos montar o botão para impressão -------------------------------------------------------------------------------------------
          printf("<form action='./planosdesaude.php' method='POST' target='_NEW'>\n");
          printf("<input type='hidden' name='acao'  value='$acao'>\n");
          printf("<input type='hidden' name='bloco' value='3'>\n");
          printf("<input type='hidden' name='ordem' value='$ordem'>\n");
          printf("<input type='hidden' name='salto' value='$salto'>\n");
          # montando os botões do form com a função botoes e os parâmetros:
          # (Página,Menu,Saída,Reset,Ação,$salto) TRUE | FALSE para os 4 parâmetros esq-dir.
          botoes(TRUE,TRUE,TRUE,FALSE,"Gerar para Impress&atilde;o",$salto);
          printf("O mesmo relat&oacute;rio ser&aacute; montado em uma janela!<br>Depois voc&ecirc; pode escolher a impress&atilde;o pelo navegador.\n");
          printf("</form>\n");
        } # 1.6.1.2.2 -------------------------------------------------------------------------------------------------------------------------------
        else
        { # 1.6.1.2.3 - O fluxo passa por aqui quando o $bloco valer 3 ------------------------------------------------------------------------------
          printf("<hr>\nDepois de Imprimir rasgue na linha acima<br>\n");
          printf("<input type='submit' value='Imprimir' onclick='javascript:window.print();'>");
          # Aqui montamos o final de página quando o relatório vai para a impressão ($bloco valendo 3)
            $ano=date('Y');
            printf("</dir>\n <hr> \n");
            printf("<font size=2 color='gray'>&copy; Copyright $ano, FATEC Ourinhos - Copie, divulgue, mas indique sempre quem fez!\n</font>\n");
        } # 1.6.1.2.3 -------------------------------------------------------------------------------------------------------------------------------
        break;
      } # 1.6.1.2 -----------------------------------------------------------------------------------------------------------------------------------
    } # 1.6.1 ---------------------------------------------------------------------------------------------------------------------------------------
    # o comando que emite as TAGs de fim de página acontecem SEMPRE (qualquer valor de $bloco).
    # Por isso o printf() está FORA do SWITCH-CASE
    # printf("</body>\n</html>\n");
    break;
  } # 1.6 - fim da funcionalidade: Listar -----------------------------------------------------------------------------------------------------------
} # 1 - Fim do divisor principal do programa --------------------------------------------------------------------------------------------------------
if ( !$relatorio )
{
  # Terminando a tabela aberta na <DIV>
  printf("<td>\n</tr>\n</table>\n");
  terminapagina($acao,"planosdesaude.php",FALSE);
}
printf("</center>\n</font>\n</div>\n</body>\n</html>\n");
?>