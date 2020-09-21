<?php
###################################################################################################################################################################################
# Programa....: setfuncoes
# Descrição...: Conjunto com as funções desenvolvidas para facilitar a construção de programas. Todas as funções são mantidas em arquivo para facilitar a edição e estrutura.
#               Este arquivio DEVE estar localizado no diretório FNTS (um nível acima do diretório onde devem estar os arquivos dos programas de manutenção de dados de uma tabela).
# Autor.......: João Maurício Hypólito - Use! Mas fale quem fez!
# Criação.....: 2014-11-10
# Atualização.: 2017-05-10 - Reorganização das função com mudança em parâmetros nas funções
#               2018-04-27 - Inclui a função concecta_my
#               2018-10-09 - Revisão da função iniciapagina, terminapagina, conectapg e conectamy
#               2018-10-10 - Tornei a função operacao INDISPONÍVEL. Ela passa a ser executada dentro do programa principal.
###################################################################################################################################################################################
# Trecho de declaração das funções. Para cada uma apresentamos um cabeçalho curto com nome/parâmetros/descrição/histórico de atualizações e objetivo
#----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function iniciapagina($cordefundo)
{ # Função.....: iniciapagina
  # Parametros.: Cor de fundo da página ($cordefundo), a cor do fonte das telas ($corfonte), texto com a funcionalidade em execução ($acao).
  # Descrição..: Emite as TAGS que iniciam uma tela com a cor de fundo padrao, alinha o texto com um TAB para a direita e a determina o fonte do projeto.
  ###################################################################################################################################################################################
  # Autor......: João Maurício Hypólito - Use! Mas fale quem fez!
  # Criação....: 2009-09-23
  # Atualização: 2018-04-27 - Tirei a variável $titulo colocando os operadores ternários dentro do printf();
  #              2018-09-17 - Escrevi o segmento de código que define a classe button em CSS para ser usado em tags de <form> ou <a href...>
  ###################################################################################################################################################################################
  printf("<!DOCTYPE html>\n");
  printf("<html>\n"); #  xml:lang='pt-BR' lang='pt-BR' dir='ltr'
  printf("<head>\n");
  printf(" <meta content='text/html' charset='UTF-8'>\n"); # declara o conjunto de caracteres universais (utf-8) (ou ISO-8859-1)
  printf(" <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n");
  printf(" <style>\n");
  printf("  body {margin:0;}\n");
  printf("  .navbar { overflow: hidden; background-color: #ffdead; position: fixed; top: 0; width: 100%%; }\n");
  printf("  .navbar a { float: left; display: block; color: #000000; text-align: center; padding: 14px 16px; text-decoration: none; font-size: 17px; }\n");
  printf("  .navbar a:hover { background: #ddd; color: black; }\n");
  printf("  .main { padding: 16px; margin-top: 30px; height: 100%%; }\n");
  printf("  .button { background-color: $cordefundo; border: none; color: black; padding: 0px 0px;
                      text-align: center; text-decoration: none; display: inline; font-size: 16px; margin: 0px 10px; cursor: pointer; }\n");
  printf("  table.bordasimples {border-collapse: collapse;}\n");
  printf(" </style>\n");
  printf("</head>\n");
  # inicia o corpo da pagina com a cor indicada no parametro
  printf("<body bgcolor='$cordefundo' style=\"overflow: scroll;\">\n");
  ################################ Fim da Função IniciaPagina ################################
}
#----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function terminapagina($texto,$prg,$center)
{ # Função.....: terminapagina
  # Parametros.: $texto - descreve a ação (apresentado no lado esquerdo da linha de rodapé),
  #              $prg - código do programa (apresentado lado direito da linha de rodapé) e
  #              $center - TRUE/FALSE para colocar a linha de rodapé centralizada ou não.
  # Descrição..: Esta Função emite uma linha no final da página e coloca uma mensagem de Autoria.
  #################################################################################################################################################################################
  # Autor......: João Maurício Hypólito - Use! Mas fale quem fez!
  # Criação....: 2009-03-27
  # Atualização: 2009-09-17
  #################################################################################################################################################################################
  printf("%s",($center) ? "<center>" : "" ); # Este comando combina um operador ternário DENTRO print().
  printf("<font size=2 color='gray'>$texto - Resolu&ccedil;&atilde;o m&iacute;nima de 1280x720 &copy; Copyright %s, FATEC Ourinhos - $prg</font>\n",date('Y'));
  # printf("</dir>\n</font>\n"); # Estas duas TAGS fecham TAGS aberta no iniciapágina.
  # printf("%s</body>\n</html>\n",($center) ? "</center>" : "" );
  ################################ Fim da Função terminapagina ################################
}
#----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function botoes($p,$m,$s,$r,$acao,$salto)
{ # Função.....: botoes
  # Parametros.: Esta Função recebe TRUE|FALSE para os parâmetros que apontam para montar as tags de exibição dos botões de navegação
  # Descrição..: Esta Função emite as TAGS para "< 1 Pag.", "< Menu","Saída","Limpar" e "Ação"
  #################################################################################################################################################################################
  # Autor......: João Maurício Hypólito - Use! Mas fale quem fez!
  # Criação....: 2017-05-31
  # Atualização: 2017-05-31 - Todo desenvolvimento e teste da função.
  #              2018-04-27 - Alterei a ordem dos textos que formam a barra de botões.
  #              2018-09-17 - Mudei a aparencia dos botões usando o CSS que foi codificado no iniciapágina.
  #################################################################################################################################################################################
  $barra=( ISSET($acao) ) ? "<input class='button' type='submit' value='$acao'>" : "";
  $barra=($r) ? $barra."<input class='button' type='reset'  value='Limpar'>" : $barra;
  $barra=($p) ? $barra."<input class='button' type='button' value='< 1 Pag.' onclick='history.go(-1)'>" : $barra;
  $barra=($m) ? $barra."<input class='button' type='button' value='< Abertura' onclick='history.go(-$salto)'>" : $barra;
  $barra=($s) ? $barra."<input class='button' type='button' value='< Sa&iacute;da' onclick='history.go(-($salto+1))'>" : $barra;
  printf("$barra<br>\n");
}
#----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function mostracampos($sgbd,$campo,$entidade,$cp,$valor)
{ # Função.....: mostracampos
  # Parametros.: $campo - nome do campo que deve ter seu valor retornado,
  #              $entidade - nome da entidade onde o campo está,
  #              $cp - nome da chave primária da tabela e
  #              $valor - valor assumido na $cp
  # Descrição..: Esta Função retorna no ponto de chamada o valor do campo da tabela que foi projetado.
  #################################################################################################################################################################################
  # Autor......: João Maurício Hypólito - Use! Mas fale quem fez!
  # Criação....: 2009-03-27
  # Atualização: 2009-09-17
  #################################################################################################################################################################################
  global $link;
  # em um só comando Projeta e retorna o valor de um campo como resposta da função.
  if ( $sgbd=="MySQL" )
  { # Se o SGBD for o MySQL
    return mysqli_fetch_array(mysqli_query($link,"SELECT $campo FROM $entidade WHERE $cp='$valor'"));
  }
  if ( $sgbd=="PostgreSQL" )
  { # Se o sgbd for o PostgreSQL
    $cmdsql="SELECT ".$campo." FROM ".$entidade." WHERE ".$cp."='".$valor."'";
    return pg_fetch_result(pg_query($link,$cmdsql),0,$campo);
  }
  ################################ Fim da Função mostracampos ################################
}
#----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function conectapg($host,$porta,$dbname,$user,$senha)
{ # Função.....: conecta_pg
  # Descrição..: Esta função faz monte a conexão com o SGBD PostgreSQL
  # Observação.: $host   - Nome do Host que executa o serviço do SGBD (localhost, para o servidor local)
  #              $porta  - Número da porta onde o servidor de banco de dados pode ser referenciado
  #              $dbname - Nome da Base de Dados que será acessada
  #              $user   - Nome do usuário que tem acesso permitido na Base (ver permissões com o DBA do SGBD)
  #              $senha  - Senha de conexão do usuário na base (e no SGBD).
  # Autor......: João Maurício Hypólito - Use! Mas fale quem fez!
  # Criação....: 2013-05-02
  # Alteração..: 2014-10-15
  #              2017-01-20 - inclui o parâmetro $porta para receber o número da porta para o caso de alguém ter mudado o número na instalação do PostgreSQL.
  #################################################################################################################################################################################
  $conexao = "host='".$host."' port=".$porta." dbname='".$dbname."' user='".$user."' password='".$senha."'";
  # Conectando o PostgreSQL. O Ponteiro que retorna na conexão DEVE SER armazenado em uma variavel GLOBAL.
  global $link;
  # Fazendo a conexão com o banco de dados.
  $link = pg_connect($conexao) or die ("Problemas para Conectar no Banco de Dados PostgreSQL: <br>$conexao");
  # Agora vamos 'ajustar' os caracteres acentuados
  pg_query($link,"SET NAMES 'utf8'");
  pg_query($link,"SET CLIENT_ENCODING TO 'utf8'");
  pg_set_client_encoding('utf8'); # para a conexão com o PostgreSQL
  # Fim da função conecta_pg
  #################################################################################################################################################################################
}
function conectamy($host,$user,$senha,$dbname)
{ # Função.....: conecta_my
  # Descrição..: Esta função faz monte a conexão com o SGBD PostgreSQL
  # Observação.: Recebe 4 parâmetros: $host   - Nome do Host que executa o serviço do SGBD (localhost, para o servidor local)
  #                                   $dbname - Nome da Base de Dados que será acessada
  #                                   $user   - Nome do usuário que tem acesso permitido na Base (ver permissões com o DBA do SGBD)
  #                                   $senha  - Senha de conexão do usuário na base (e no SGBD).
  # Autor......: João Maurício Hypólito - Use! Mas fale quem fez!
  # Criação....: 2013-05-02
  # Alteração..: 2014-10-15
  ################################################################################################################
  # Fazendo a conexão com o banco de dados.
  # Atribuicao de: - Nome de servidor, Nome do usuario, Senha do usuario e Base de dados
  global $link;
  $link = mysqli_connect($host,$user,$senha,$dbname);
  printf("host=$host, user=$user,senha=$senha,dbname=$dbname<br>");
  # Acertando a tabela de caracteres que sera usada no MySQL
  mysqli_query($link,"SET NAMES 'utf8'");
  mysqli_query($link,"SET character_set_connection=utf8");
  mysqli_query($link,"SET character_set_client=utf8");
  mysqli_query($link,"SET character_set_results=utf8");
  # aqui termina o trecho de conexão com o banco de dados
  ################################################################################################################
}
#----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
#----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
###################################################################################################################################################################################
# Aqui termina a declaração das Funções.
# EXECUTANDO a função de CONEXÃO
################################################### Fim das Funções ###################################################
# Ajustando os caracteres de acentução
header('Content-Type: text/html; charset=UTF-8;');
###################################################################################################################################################################################
# Em 2018, as aplicações rodando para o SGBD MySQL foram migradas para o uso das funções mysqli_.
# Estas funções passaram a ser executadas no PHP 5.4.
# O interpretador PHP passou a ter um rigor maior no tratamento do conceito de globalização de variáveis.
# Isso impôs uma alteração na forma como o PA executa a conexão com o banco de dados.
# Para isso foi criado um aplicativo exclusivo para fazer a conexão entre o ambiente do PH com o banco de dados MySQL.
# Entretanto isso não é necessário para o SGBD PostgreSQL.
# Para fazer a conexão com o PostgreSQL executamos a função conecta_pg com os 5 parâmetros: hostname, porta, database name, username e password
conectapg("localhost",5432,"lbdtudo","postgres","123456"); # Com esta linha comentada NÃO se executa a conexão.
?>