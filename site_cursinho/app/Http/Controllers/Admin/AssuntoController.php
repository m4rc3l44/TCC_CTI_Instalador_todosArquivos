<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assunto;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\Exercicio;
use App\Models\RespostaAluno;
use App\Models\Alternativa;
use Illuminate\Http\Request;
use Exception; 


class AssuntoController extends Controller
{
    

    public function salvar(Request $req)
{
    try {
        // Coleta os dados enviados e cria um novo registro de assunto
        $dados = $req->all();
        Assunto::create($dados);

        // Redireciona para a listagem de assuntos com uma mensagem de sucesso
        return redirect()->route('assunto.list')->with('success', 'Assunto cadastrado com sucesso.');

    } catch(Exception $e) {
        // Em caso de exceção, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Assunto já cadastrado.']);
    }
}

public function edit($nome_assunto)
{
    try {
        // Busca o assunto pelo nome e também carrega todas as matérias
        $rows = Assunto::where('nome_assunto', $nome_assunto)->get();
        $materias = Materia::all(); 

        // Retorna a view para editar o assunto com os dados recuperados
        return view('admin.editar.editar_assunto', compact('rows', 'materias'));
    } catch (Exception $e) {
        // Em caso de erro ao buscar as informações, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações.']);
    }
}

public function update(Request $request, $nome_assunto)
{
    try {
        // Busca o assunto pelo nome e atualiza os dados com base na requisição
        $assuntoAntigo = Assunto::find($nome_assunto);
        $assuntoAntigo->update($request->all());

        // Redireciona para a listagem de assuntos após a atualização
        return redirect()->route('assunto.list');
    } catch(Exception $e) {
        // Em caso de erro ao atualizar, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao atualizar informações.']);
    }
}

public function destroy($nome_assunto)
{
    try {
        $assunto = Assunto::find($nome_assunto);

        $aulas = Aula::where('fk_assunto', $nome_assunto)->get();

        // ... (código para deletar exercícios, alternativas, e respostas associadas ao assunto)

        $assunto->delete();

        // Retorna para a listagem de assuntos após a exclusão
        return redirect()->route('assunto.list');
    } catch(Exception $e) {
        // Em caso de erro ao excluir, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
    }
}

public function listarMaterias()
{
    // Busca todas as matérias para serem utilizadas no cadastro de assuntos
    $rows = Materia::all();
    return view('admin.cadastros.cadAssunto', compact('rows'));
}

public function listar()
{
    try {
        // Lista os assuntos paginados
        $rows = Assunto::paginate(10);
        return view('admin.tabelas.tabela_assuntos', compact('rows'));
    } catch (Exception $e) {
        // Em caso de erro ao listar os assuntos, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao listar: ' + $e]);
    }
}

public function buscar(Request $req)
{
    $busca = $req->input('busca');
    try {
        // Realiza a busca por assuntos com base no termo inserido
        $rows = Assunto::whereRaw('LOWER(nome_assunto) LIKE ?', ['%' . strtolower($busca) . '%'])
                ->paginate(10)
                ->appends(['busca' => $busca]);

        return view('admin.tabelas.tabela_assuntos', compact('rows'));
    } catch (Exception $e) {
        // Em caso de erro na filtragem dos assuntos, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao filtrar assuntos.']);
    }
}

}
