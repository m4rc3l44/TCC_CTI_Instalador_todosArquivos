<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Models\Aluno;
use App\Models\User;
use App\Services\FTPService;
use Illuminate\Http\Request;
use Exception;

class AlunoController extends Controller
{
    // Função para salvar um novo aluno no sistema
    public function salvar(Request $req)
    {
        try {
            $dados = $req->all();

            // Criptografa a senha do aluno
            $dados['senha_aluno'] = Hash::make($dados['senha_aluno']);

            if ($req->hasFile('imagem_aluno')) {
                $imagem = $req->file('imagem_aluno');
                $num = rand(1111, 9999);
                $dir = "img/alunos/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_" . $num . "." . $ex;

                // Move e salva a imagem no diretório
                $imagem->move($dir, $nomeImagem);
                $dados['imagem_aluno'] = $dir . "/" . $nomeImagem;

                // Cria um registro de usuário associado ao aluno
                $user = [
                    'name' => $dados['nome_aluno'],
                    'email' => $dados['email_aluno'],
                    'cpf' => $dados['cpf_aluno'],
                    'nivel' => 'usuario',
                    'imagem' => $dados['imagem_aluno'],
                    'password' => $dados['senha_aluno'],
                ];
            }

            // Cria um registro de usuário
            User::create($user);

            // Cria um registro de aluno
            Aluno::create($dados);

            // Redireciona para a lista de alunos com uma mensagem de sucesso
            return redirect()->route('aluno.list')
                ->with('success', 'Cadastro feito com sucesso.');

        } catch (Exception $e) {
            // Em caso de exceção, redireciona para uma página de erro
            return view('tela_erro.tela_erro', ['erro' => 'Aluno já existe. Email, cpf ou rg cadastrados anteriormente.']);
        }
    }

    // Função para exibir um formulário de edição de aluno
    public function edit($cpf_aluno)
    {
        try {
            // Busca os dados do aluno com base no CPF
            $rows = Aluno::where('cpf_aluno', $cpf_aluno)->get();

            // Exibe a view de edição com os dados do aluno
            return view('admin.editar.editar_aluno', compact('rows'));
        } catch (Exception $e) {
            // Em caso de exceção, redireciona para uma página de erro
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações.']);
        }
    }

    // Função para atualizar os dados de um aluno
    public function update(Request $request, $email_aluno, $cpf_aluno)
    {
        //try {
            $row = $request->only(['nome_aluno', 'imagem_aluno', 'rg_aluno', 'cpf_aluno', 'email_aluno', 'celular_aluno', 'escola_aluno', 'serie_aluno']);
            $user;
            //Busca a linha do aluno antes das mudanças serem aplicadas
            $AlunoAntigo = Aluno::find($cpf_aluno);
            // Atualiza os dados do aluno, incluindo uma possível imagem nova
            if ($request->hasFile('imagem_aluno')) {
                //Apaga a imagem antiga do servidor
                if(!is_null($AlunoAntigo->imagem_aluno)) {
                    $imagePath = $AlunoAntigo->imagem_aluno;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }

                $imagem = $request->file('imagem_aluno');
                $num = rand(1111, 9999);
                $dir = "img/alunos/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_" . $num . "." . $ex;
  
                // Move e salva a imagem no diretório
                $imagem->move($dir, $nomeImagem);

                $row['imagem_aluno'] = '';
                $row['imagem_aluno'] = $dir . "/" . $nomeImagem;

                $user = [
                    'name' => $row['nome_aluno'],
                    'email' => $row['email_aluno'],
                    'cpf' => $row['cpf_aluno'],
                    'imagem' => $row['imagem_aluno'],
                ];
            } else {
                $user = [
                    'name' => $row['nome_aluno'],
                    'email' => $row['email_aluno'],
                    'cpf' => $row['cpf_aluno'],
                ];
            }

            // Verifica se o e-mail do aluno foi alterado e, se sim, desativa a autenticação do usuário
            if ($email_aluno != $row['email_aluno']) {
                User::where('email', $email_aluno)->update(['autenticado' => false]);
            }

            // Atualiza os dados do usuário relacionado
            User::where('email', $email_aluno)->update($user);

            // Atualiza os dados do aluno
            Aluno::where('cpf_aluno', $cpf_aluno)->update($row);

            // Redireciona para a lista de alunos com uma mensagem de sucesso
            return redirect()->route('aluno.list')
                ->with('success', 'Atualizacao feita com sucesso.');

        // } catch (Exception $e) {
            
        //     // Em caso de exceção, redireciona para uma página de erro
        //     return view('tela_erro.tela_erro', ['erro' => 'Erro ao atualizar informações do aluno.']);
        // }
    }

    // Função para excluir um aluno
    public function destroy($email_aluno, $cpf_aluno)
    {
        try {
            // Remove o registro do usuário relacionado
            User::where('email', $email_aluno)->delete();

            // Remove o registro do aluno
            $aluno = Aluno::where('cpf_aluno', $cpf_aluno)->first();

            if(!is_null($aluno->imagem_aluno)) {
                $imagePath = $aluno->imagem_aluno;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath);
            }

            $aluno->delete(); 

           

            // Redireciona para a lista de alunos com uma mensagem de sucesso
            return redirect()->route('aluno.list')
                ->with('success', 'Aluno deletado com sucesso.');

        } catch (Exception $e) {
            // Em caso de exceção, redireciona para uma página de erro
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
        }
    }

    // Função para listar todos os alunos
    public function listar()
    {
        try {
            // Recupera uma lista paginada de todos os alunos
            $rows = Aluno::paginate(10);

            // Exibe a view com a lista de alunos
            return view('admin.tabelas.tabela_alunos', compact('rows'));
        } catch (\Exception $e) {
            // Em caso de exceção, redireciona para uma página de erro
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações de alunos.']);
        }
    }

    // Função para buscar alunos com base em um termo de busca
    public function buscar(Request $req)
    {
        $busca = $req->input('busca');

        try {
            // Realiza uma pesquisa de alunos com base em um termo de busca e pagine os resultados
            $rows = Aluno::whereRaw('LOWER(nome_aluno) LIKE ?', ['%' . strtolower($busca) . '%'])->paginate(10)
                ->appends(['busca' => $busca]);

            // Exibe a view com os resultados da pesquisa
            return view('admin.tabelas.tabela_alunos', compact('rows'));
        } catch (Exception $e) {
            // Em caso de exceção, redireciona para uma página de erro
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao filtrar alunos.']);
        }
    }
}
