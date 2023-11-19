<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alternativa;
use App\Services\FTPService; 
use App\Models\Exercicio;
use Exception; 


class AlternativaController extends Controller
{
    public function salvar(Request $req)
    {
        try {
            $dados = $req->all();
            if ($req->hasFile('imagem_alternativa')) {
                // Trata a imagem da alternativa, caso seja enviada
                $imagem = $req->file('imagem_alternativa');
                $num = rand(1111, 9999);
                $dir = "img/alternativas/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_" . $num . "." . $ex;
                $imagem->move($dir, $nomeImagem);
                $dados['imagem_alternativa'] = $dir . "/" . $nomeImagem;
            }
    
            // Cria uma nova entrada no banco de dados com os dados da alternativa
            Alternativa::create($dados);
    
            return redirect()->route('alternativa.list')
            ->with('success', 'Cadastro feito com sucesso.'); // Redireciona de volta para a página de cadastro de alternativas
        } catch (Exception $e) {
            // Em caso de erro,redireciona para uma tela de erro
            return view('tela_erro.tela_erro', ['erro' => 'Alternativa já cadastrada ou Id de exercício inexistente. Verifique as informações da questão.']);
        }
    }
    

    public function edit($id_alternativa)
    {
        try {
            // Busca as informações da alternativa com o ID específico
            $rows = Alternativa::where('id_alternativa', $id_alternativa)->get();
            $idExercicios = Exercicio::all(); // Busca todos os exercícios disponíveis
            return view('admin.editar.editar_alternativa', compact('rows', 'idExercicios'));
        } catch (Exception $e) {
            // Redireciona para a listagem com uma mensagem de erro em caso de exceção
            return redirect()->route('assunto.list')->with('error', 'Erro ao pegar infos de assuntos.');
        }
    }
    
    public function update(Request $req, $id_alternativa)
    {

        try {
            $dados = $req->all();
            $alternativaAntigo = Alternativa::find($id_alternativa);

            if ($req->hasFile('imagem_alternativa')) {
                //Apaga a imagem antiga do servidor
                if(!is_null($alternativaAntigo->imagem_alternativa))
                {
                    $imagePath = $alternativaAntigo->imagem_alternativa;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }
                // Lida com a atualização da imagem da alternativa, caso um novo arquivo seja fornecido
                $imagem = $req->file('imagem_alternativa');
                $num = rand(1111, 9999);
                $dir = "img/alternativas/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_" . $num . "." . $ex;
                $imagem->move($dir, $nomeImagem);
                $dados['imagem_alternativa'] = $dir . "/" . $nomeImagem;
            }
            $alternativaAntigo->update($dados); // Atualiza as informações da alternativa
            return redirect()->route('alternativa.list')
            ->with('success', 'Atualizacao feita com sucesso.'); // Redireciona de volta para a página de cadastro de alternativas
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Alternativa já cadastrada ou Id de exercício inexistente. Verifique as informações da questão.']);
        }
    }
    
    public function destroy($id_alternativa)
    {
        try {
            $alternativa = Alternativa::find($id_alternativa);

            if(!is_null($alternativa->imagem_alternativa)) {
                $imagePath = $alternativa->imagem_alternativa;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath);
            }

            $alternativa->delete(); 
            return redirect()->route('alternativa.list')
            ->with('success', 'Alternativa deletada com sucesso.'); // Redireciona de volta para a página de cadastro de alternativas
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => ' ']); // Exibe tela de erro em caso de exceção
        }
    }
    

    public function ListarIDExercicios()
    {
        $exercicios = Exercicio::all();
        return view('admin.cadastros.cadAlternativa', compact('exercicios'));
    }
    
    public function listar()
    {
        $rows = Alternativa::paginate(10);
        return view('admin.tabelas.tabela_alternativas', compact('rows'));
    }
    
    public function buscar(Request $req)
    {
        $busca = $req->input('busca');
        try {
            $rows = Alternativa::whereRaw('LOWER(descricao_alternativa) LIKE ?', ['%' . strtolower($busca) . '%'])
                ->paginate(10)
                ->appends(['busca' => $busca]);
    
            return view('admin.tabelas.tabela_alternativas', compact('rows'));
        } catch (Exception $e) {
            dd($e);
        }
    }
     
} 
