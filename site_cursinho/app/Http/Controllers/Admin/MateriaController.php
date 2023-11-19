<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\Assunto;
use App\Models\Exercicio;
use App\Models\RespostaAluno;
use App\Models\Alternativa;
use Illuminate\Http\Request;
use Exception; 

class MateriaController extends Controller
{
   
    public function salvar(Request $req)
{
    // Tenta salvar os dados de uma nova matéria
    try {
        $dados = $req->all();
        Materia::create($dados);
        return redirect()->route('materia.list')
        ->with('success', 'Atualizacao feita com sucesso.');
    } catch (Exception $e) {
        // Se ocorrer uma exceção ao tentar salvar, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Matéria já cadastrada.']);
    }
}

public function edit($nome_materia)
{
    // Tenta buscar e editar as informações de uma matéria existente
    try {
        $linhas = Materia::where('nome_materia', $nome_materia)->get();
        return view('admin.editar.editar_materia', compact('linhas'));
    } catch (Exception $e) {
        // Se uma exceção for lançada, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações.']);
    }
}

public function update(Request $request, $nome_materia)
{
    // Tenta atualizar as informações de uma matéria específica
    try {
        $materia = Materia::find($nome_materia);
        $materia->update($request->all());
        return redirect()->route('materia.list')
        ->with('success', 'Atualizacao feita com sucesso.');
    } catch (Exception $e) {
        // Se ocorrer uma exceção durante a atualização, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao atualizar']);
    }
}

public function destroy($nome_materia)
{
    // Tenta excluir uma matéria, juntamente com suas aulas, exercícios, assuntos e alternativas
    try {
        $materia = Materia::find($nome_materia);
        $aulas = Aula::where('fk_materia', $nome_materia)->get();

        // Deleta todas as aulas associadas à matéria
        foreach ($aulas as $aula) {
            $aula->delete();
        }

        // Encontra e exclui os exercícios relacionados à matéria, juntamente com suas alternativas
        $exercicios = Exercicio::where('fk_materia', $nome_materia)->get();
        foreach ($exercicios as $exercicio) {
            $comando = $exercicio->id_exercicio;
            $alternativas = Alternativa::where('fk_id_exercicio', $comando)->get();

            // Deleta todas as alternativas associadas a cada exercício
            foreach ($alternativas as $alternativa) {
                $alternativa->delete();
            }
            $exercicio->delete();
        }

        // Exclui os assuntos relacionados à matéria
        $assuntos = Assunto::where('fk_materia', $nome_materia)->get();
        foreach ($assuntos as $assunto) {
            $assunto->delete();
        }

        // Deleta a própria matéria
        $materia->delete();
        
        return redirect()->route('materia.list')
        ->with('success', 'Atualizacao feita com sucesso.');
    } catch (Exception $e) {
        // Em caso de exceção, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
    }
}

public function listar()
{
    // Recupera todas as matérias e as exibe em uma tabela paginada de 10 registros por página
    $rows = Materia::paginate(10);

    return view('admin.tabelas.tabela_materias', ['rows' => $rows]);
}

public function buscar(Request $req)
{
    $busca = $req->input('busca');
    try {
        // Procura matérias com base no termo de busca e retorna em uma tabela paginada de 10 registros por página
        $rows = Materia::whereRaw('LOWER(nome_materia) LIKE ?', ['%' . strtolower($busca) . '%'])
            ->paginate(10)
            ->appends(['busca' => $busca]);

        return view('admin.tabelas.tabela_materias', ['rows' => $rows]);
    } catch (Exception $e) {
        // Em caso de exceção ao buscar, redireciona para a tela de erro
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao buscar.']);
    }
}

public function apresentar()
{
    // Apresenta as matérias disponíveis na visualização de matérias para alunos paginadas em grupos de 10
    $rows = Materia::paginate(10);

    return view('aluno.materias', compact('rows'));
}


 
}
