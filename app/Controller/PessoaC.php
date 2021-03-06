<?php
/**
 * Created by PhpStorm.
 * User: jairo.sousa
 * Date: 08/10/2015
 * Time: 16:16
 */
include_once 'app/Config.inc.php';

class PessoaC{
    private $id;
    private $idT;
    private $dados;
    private $LinkAnexo;

    public  function index(){
        include_once 'app/View/header.php';
        include_once 'app/View/form-palestrante.php';
        include_once 'app/View/footer.php';

    }
    public function Pegar(){
        $this->dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        //$delUltimo = array_pop($this->dados);


        $pessoa = new Pessoa();
        $pessoa->ExeCreate($this->dados);
        if($pessoa->getResult()){
            $this->id = $pessoa->getResult();
            echo "<script>window.location.assign('".BASE."p.php?c=PessoaC&m=Trabalho&p=$this->id')</script>";
        }else{
            echo $pessoa->getMsg();

            echo "<script>window.location.assign('".BASE."s.php?c=PessoaC&m=index')</script>";
        }
    }

    public function Trabalho($parametro){
        include_once 'app/View/header.php';
        $tipoAtividade = new Read();
        $tipoAtividade->ExeRead('tipo_atividades');

        include_once 'app/View/form-trabalho.php';
        $parametro = $parametro;



include_once 'app/View/footer.php';
    }

    public function GravarTrabalho(){

       $this->dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);


        if(isset($_FILES['fileUpload']))
        {
            $nome = $this->dados['titulo'];
            $ext = strtolower(substr($_FILES['fileUpload']['name'],-4)); //Pegando extensão do arquivo
            $new_name =  $nome . $ext; //Definindo um novo nome para o arquivo
            $this->LinkAnexo = $new_name;
            $dir = 'uploads/'; //Diretório para uploads
            move_uploaded_file($_FILES['fileUpload']['tmp_name'], $dir.$new_name); //Fazer upload do arquivo

        }else{
            echo "<script>alert('nao foi possivel anexar o arquivo!');</script>";
        }

        if($this->dados['tipoAtividade'] == "Palestra"){
            $tipoA = 1;
        }elseif($this->dados['tipoAtividade'] == "MiniCurso"){
            $tipoA = 2;
        }else{
            $tipoA = 3;
        }
        //atualiza pessoa

        $updateP = new Update();
        $dadosUp = [
            "curriculo" => $this->dados['perfil'],
            "nivel" => 1,
            "telefone" => $this->dados['telefone']
        ];
        $updateP->ExeUpdate('pessoas',$dadosUp, "where codigo = :id","id={$this->dados['idPes']}");


        //cadastra Trabalho
        $cadastrarT= new Create();
        $Dados = [
           "resumo" => $this->dados['resumo'],
           "data_submetido" => date("Y-m-d"),
           "tipo_atividade" => $tipoA,
           "anexo" => $this->LinkAnexo,
           "status" => "N",
            "titulo" => $this->dados['titulo']
        ];
        $cadastrarT->ExeCreate('trabalhos', $Dados);
          $this->idT = $cadastrarT->getResult();

        // Vincula o auto trabalho

        $cadastraAT =  new Create();
        $DadosAT =[
            "codigo_trabalho" => (int) $this->idT,
            "codigo_autor" => (int) $this->dados['idPes'],
            "codigo_evento" => 1
        ];

        $cadastraAT->ExeCreate('autor_trabalho', $DadosAT);


        // pega dados da pessoa
        $pessoa= new Read();
        $pessoa->ExeRead('pessoas', "where codigo = :id", "id={$this->dados['idPes']}");
        foreach($pessoa->getResult() as $resulPes){
            extract($resulPes);
            //Enviar o Email.
            $enviarEmail = new Email();
            $DadosEmail = [
                "Assunto" => "Confirmação da Submição de Trabalho DeepDay",
                "Mensagem" => "Seu Trabalho foi submetido com sucesso.",
                "RemetenteNome" => "Equipe DeepDay",
                "RemetenteEmail" => "atendimento@deepday.com.br",
                "DestinoNome" => $nome,
                "DestinoEmail" => $email
            ];
            $enviarEmail->Enviar($DadosEmail) ;
        }


        echo "<script>alert('Seu trabalho foi submetido com sucesso!');</script>";
        echo "<script>window.location.assign('".BASE."/painel')</script>";


    }

    public function aluno(){
        include_once 'app/View/header.php';
        include_once 'app/View/form-aluno.php';
        include_once 'app/View/footer.php';

    }

    public function sAluno(){
        $this->dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        var_dump($this->dados);
        //cadastra participante
        $cadastrarT= new Create();
        $Dados = [
            "nome" => $this->dados['nome'],
            "cpf" => $this->dados['cpf'],
            "email" => $this->dados['email'],
            "senha" => $this->dados['senha'],
            "nivel" => 0

        ];
        $cadastrarT->ExeCreate('pessoas', $Dados);
        echo "<script>alert('Seu Cadastro foi realizado com sucesso!');</script>";
        echo "<script>window.location.assign('".BASE."/painel')</script>";
    }
}