<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Professor;
use App\Models\User;
use App\Models\Materia;
use App\Models\Aluno;
use App\Services\FTPService; 
use Illuminate\Http\Request;
use Exception; 


class ProfessorController extends Controller
{

    

    // public function deleta($imagePath)
    // {
    //     $ftpService = new FTPService();
    //     $servidor = 'ftp.projetoscti.com.br';
    //     $usuario = 'projetoscti24';
    //     $senha = '730494'; 

    //     $ftp_conexao = $ftpService->conectarFTP($servidor, $usuario, $senha);

    //     if ($ftp_conexao !== false) {
    //         // Use o comando FTP 'delete' para excluir o arquivo
    //         if (ftp_size($ftp_conexao, $imagePath) !== -1) {
    //             // O arquivo existe, pode continuar com a exclusão
    //             if (ftp_delete($ftp_conexao, $imagePath)) {
    //                 // Arquivo FTP excluído com sucesso
    //             } else {
    //                 // Erro ao excluir o arquivo FTP
                   
    //             }
    //         } else {
    //             // O arquivo não existe, faça algo apropriado, como mostrar uma mensagem
                
    //         }
    //             // Erro ao excluir o arquivo FTP
                

    //         // Feche a conexão FTP quando terminar
    //         ftp_close($ftp_conexao);
    //     } else {
    //         // Falha na conexão FTP
    //         dd('Não foi possível conectar ao servidor FTP.');
    //     }
    // }

 


    public function salvar(Request $req)
    {
        try{
            $dados = $req->all();
            // dd($dados);
            // $var['nivel'] = 'professor';
            $dados['senha_professor'] = Hash::make($dados['senha_professor']);
            if($req->hasFile('imagem_professor')){
                $imagem = $req->file('imagem_professor');
                $num = rand(1111,9999);
                $dir = "img/professors/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_".$num.".".$ex;
                $imagem->move($dir,$nomeImagem);
                $dados['imagem_professor'] = $dir."/".$nomeImagem;
                $user_prof = [
                    'name' => $dados['nome_professor'],
                    'email' => $dados['email_professor'],
                    'cpf' => $dados['cpf_professor'],
                    'nivel'=>'professor',
                    'imagem' => $dados['imagem_professor'],
                    'password' => $dados['senha_professor'],
                ];
            }

            User::create($user_prof);

            Professor::create($dados);

            return view("admin.menu_admin");
            // return redirect()->route('professor.list')
            // ->with('success', 'Professor cadastrado com sucesso.');
        }

        catch (Exception $e){
            return view('tela_erro.tela_erro', ['erro' => 'Professor já existe. Email, cpf ou rg cadastrados anteriormente.']);

        }
    }




    public function edit($cpf_professor)
    {
        try {
            $rows = Professor::where('cpf_professor',$cpf_professor)->get();
            return view('admin.editar.editar_professor', compact('rows'));
        }

        catch (Exception $e){
            return view('tela_erro.tela_erro', ['erro' => 'Falha ao pegar informações do campo.']);
        }
    }


    public function update(Request $request, $email_professor ,$cpf_professor)
    {
        // try {
            $row = $request->only(['nome_professor', 'imagem_professor', 'rg_professor', 'cpf_professor', 'email_professor', 'celular_professor', 'descricao_professor', 'fk_materia']);
            $user;

            //Busca a linha do aluno antes das mudanças serem aplicadas
            $profAntigo = Professor::find($cpf_professor);

            if($request->hasFile('imagem_professor')){

                if(!is_null($profAntigo->imagem_professor)) {
                    $imagePath = $profAntigo->imagem_professor;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }

                $imagem = $request->file('imagem_professor');
                $num = rand(1111,9999);
                $dir = "img/professors/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_".$num.".".$ex;
                $imagem->move($dir,$nomeImagem);

                $row['imagem_professor'] = '';
                $row['imagem_professor'] = $dir."/".$nomeImagem;

                $user = [
                    'name'=> $row['nome_professor'],
                    'email' => $row['email_professor'],
                    'cpf' => $row['cpf_professor'],
                    'imagem' => $row['imagem_professor'],
                ];
            }
            else{

                $user = [
                    'name'=> $row['nome_professor'],
                    'email' => $row['email_professor'],
                    'cpf' => $row['cpf_professor'],
                ];
            }

            if($email_professor != $row['email_professor'])
            {
                User::where('email', $email_professor)->update(['autenticado' => false]);
            }
            
            User::where('email', $email_professor)->update($user);

            Professor::where('cpf_professor', $cpf_professor)->update($row);

            

            return redirect()->route('professor.list')
            ->with('success', 'Professor atualizado com sucesso.');
        
        // }catch(Exception $e){
        //     return view('tela_erro.tela_erro', ['erro' => 'Erro ao atualizar informações do professor.']);
        // }
    }


    // public function destroy($email_professor, $cpf_professor)
    // {
    //     try {
    //         User::where('email', $email_professor)->delete();
    //         Professor::where('cpf_professor', $cpf_professor)->delete();
    //             return redirect()->route('professor.list')
    //             ->with('success', 'Professor deletado com sucesso.');
    //     }catch(Exception $e){
    //         return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
    //     }
    // }
    public function destroy($email_professor, $cpf_professor)
{
    // try {
        // Retrieve the image file path from the database

        
        // $ftp_conexao = delete;();


        $professor = Professor::where('cpf_professor', $cpf_professor)->first();
        
        if(!is_null($professor->imagem_professor)) {
            $imagePath = $professor->imagem_professor;
            $ftpService = new FTPService();
            $ftpService->deleta($imagePath);
        }

        User::where('email', $email_professor)->delete();
        $professor->delete();
 
    
        return redirect()->route('professor.list')
            ->with('success', 'Professor deletado com sucesso.');
    // } catch (Exception $e) {
    //     return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
    // }
}





    public function listar(){
        try {
            $rows = Professor::paginate(10);
            return view('admin.tabelas.tabela_professores', compact('rows'));
        }

        catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Falha']);
        }
    }

    // public function listar()
    // {
    //     $rows = Materia::paginate(10); // Paginação com 10 registros por página
    
    //     return view('admin.tabelas.tabela_materias', ['rows' => $rows]);
    // }

    public function listar_home(){
        try {
            $rows = Professor::all();
            
            return view('aluno.homepage', compact('rows'));
        }
        catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações dos professores']);
        }
    }

    public function buscar(Request $req)
    {
        $busca = $req->input('busca');
        // dd($busca);
       
        try{
            $rows = Professor::whereRaw('LOWER(nome_professor) LIKE ?', ['%' . strtolower($busca) . '%'])
            ->paginate(10)
            ->appends(['busca' => $busca]);

            return view('admin.tabelas.tabela_professores', compact('rows'));
        }catch (Exception $e){
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao filtrar professores.']);
        }
    }
}
