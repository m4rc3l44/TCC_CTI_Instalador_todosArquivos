<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Professor;
use App\Models\Aluno;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmailController extends Controller
{
    // Função para exibir a página de confirmação de email
    public function index(){
        return view("admin.email.emailconfirmar",compact("user"));
    }

    // Função para confirmar o email do usuário
    public function confirmar(Request $req){
        try{
            // Obter os dados do usuário a partir da requisição
            $time = $req->input('time');
            $resposta = $req->input('codigoresp');
            $respostacorreta = $req->input('codigo');
            $email = $req->input('email');

            // Verificar se a resposta fornecida pelo usuário é igual à resposta correta
            if($resposta == $respostacorreta)
            {
                // Verificar se o tempo desde o envio do email é inferior a 1 hora (3600 segundos)
                if (time() - $time < 3600)
                {
                    // Marcar o usuário como autenticado
                    User::where('email', $email)->update(['autenticado' => true]);

                    // Redirecionar o usuário para a página inicial
                    return redirect()->route('home');
                }
                else
                {
                    // Redirecionar o usuário de volta para a página de login caso o tempo tenha expirado
                    return redirect()->route('aluno.login');
                }
            }
            else{
                // Redirecionar o usuário de volta para a página de login caso a resposta seja incorreta
                return redirect()->route('aluno.login');
            }
        }
        catch(Exception $e)
        {
            // Exibir uma mensagem de erro em caso de exceção
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações do usuário']);
        }
    }

    // Função para gerar um código de confirmação e enviar por email
    public function gerarCodigo(Request $req){  
        try{
            $email = $req->all();

            $info = new \stdClass();
            $info->email = $email['email'];
            $info->codigo = $codigo = rand(10000,99999);
            $info->time = $time = time();

            // Enviar um email com o código de confirmação
            \Illuminate\Support\Facades\Mail::send(new \App\Mail\EmailSenha($info));

            // Exibir a view de confirmação de email
            return view('senha.confirmar_email_senha', compact('info'));
        }catch(Exception $e){
            // Exibir uma mensagem de erro em caso de exceção
            return view('tela_erro.tela_erro', ['erro' => 'Falha ao gerar código.']);
        }
    }

    // Função para alterar a senha do usuário
    public function mudarSenha(Request $req){
        try{
            // Obter os dados da requisição
            $time = $req->input('time');
            $resposta = $req->input('codigoresp');
            $respostacorreta = $req->input('codigo');
            $email = $req->input('email');
            $senha = bcrypt($req->input('senhaNova'));

            // Verificar se a resposta fornecida pelo usuário é igual à resposta correta e o tempo não expirou
            if($resposta == $respostacorreta && time() - $time < 3600)
            {
                // Verificar se o email pertence a um professor
                if(Professor::where('email_professor', $email))
                {
                    $dados = [
                        'senha_professor' => $senha,
                    ];

                    $user = [
                        'password' => $senha,
                    ];

                    // Atualizar a senha do professor e do usuário
                    User::where('email', $email)->update($user);
                    Professor::where('email_professor', $email)->update($dados);
                }
                // Verificar se o email pertence a um aluno
                else if(Aluno::where('email_aluno', $email))
                {
                    $dados = [
                        'senha_aluno' => $senha,
                    ];

                    $user = [
                        'password' => $senha,
                    ];

                    // Atualizar a senha do aluno e do usuário
                    User::where('email', $email)->update($user);
                    Aluno::where('email_aluno', $email)->update($dados);
                }
                
                // Redirecionar o usuário de volta para a página de login
                return view('aluno.login');
            }        
            else 
            {
                // Exibir uma mensagem de erro em caso de resposta incorreta ou tempo expirado
                return view('tela_erro.tela_erro', ['erro' => 'Resposta incorreta']);
            }
        }catch(Exception $e){
            // Exibir uma mensagem de erro em caso de exceção
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao autenticar.']);
        }
    }
}
