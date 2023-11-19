<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class LoginController extends Controller
{
    // Função para exibir a página de login
    public function index()
    {
        return view('aluno.login');
    }

    // Função para lidar com o processo de login dos usuários
    public function entrar(Request $req)
    {   
        try{
            $dados = $req->all();

            // Tentativa de autenticação do usuário com base no email e senha fornecidos
            if(Auth::attempt(['email' => $dados['email'], 'password' => $dados['password']])){
                $user = Auth::user();

                // Verifica se o usuário não está autenticado
                if($user->autenticado === false)
                {
                    $info = new \stdClass();
                    $codigo=rand(10000,99999);
                    $info->email=$dados['email'];
                    $info->nome=$user->name;
                    $info->time=time();
                    $info->codigo=$codigo; 

                    // Envio de um email de confirmação
                    \Illuminate\Support\Facades\Mail::send(new \App\Mail\EnviarEmail($info));
                
                    return view('email.confirmaremail', compact("info"));
                }
                else {
                    return redirect()->route('home');
                }
            }

            // Redireciona de volta para a página de login em caso de falha na autenticação
            return redirect()->route('aluno.login');
        }catch(Exception $e){
            return view('tela_erro.tela_erro', ['erro' => 'Erro na autenticação.']);
        }
        
    }
    
    // Função para fazer logout do usuário e redirecionar para a página de login
    public function sair(){

        // Remove os dados de exercícios da sessão (se houver)
        session()->forget('exercicios');

        // Realiza o logout do usuário
        Auth::logout();

        return redirect()->route('aluno.login');
    }
}
