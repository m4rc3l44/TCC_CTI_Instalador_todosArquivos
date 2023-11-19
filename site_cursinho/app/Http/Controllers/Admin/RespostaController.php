<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RespostaAluno;
use App\Models\Exercicio;
use App\Models\Alternativa;
use App\Models\Materia;
use App\Models\Assunto;
use Exception; 


class RespostaController extends Controller
{
    private $allExercises; // Variável de classe para armazenar todos os exercícios
 
    public function __construct()
    {
        // Carregue todos os exercícios no construtor
        $this->allExercises = Exercicio::with('alternativas')->get();
    }

    public function salvar_resposta(Request $req)
{
    try {
        // Obtém todos os exercícios, matérias e assuntos
        $exs = $this->allExercises;
        $materias = Materia::all();  
        $assuntos = Assunto::all();
        $dados = $req->all();

        // Inicializa a sessão 'exercicios' se ainda não existir
        if (!session()->has('exercicios')) {
            session(['exercicios' => []]);
        }

        $rows = $exs;

        // Verifica se há filtros de busca na sessão
        if (session()->has('busca')) { 
            $query = Exercicio::with('alternativas');
            $busca = session('busca');

            // Aplica os filtros de busca à query dinamicamente
            if (isset($busca[0]['Materia']) && $busca[0]['Materia'] != "todos") {
                $query->where('fk_materia', $busca[0]['Materia']);
            }
            // Adicione mais condições para outros campos conforme necessário

            $rows = $query->get();
        }

        // Obtém a alternativa correta para o exercício fornecido
        $alternativaCorreta = Alternativa::where('fk_id_exercicio', $dados['fk_id_exercicio'])
            ->where('correta', true) 
            ->value('letra'); 

        // Verifica se uma alternativa foi selecionada
        if (!isset($dados['letra_respondida']) || empty($dados['letra_respondida'])) {
            // Define mensagem de erro e prepara dados do exercício
            session(['resposta_certa_errada' => 'Selecione uma resposta antes de enviar.']); 
            $exercicio = [
                'resposta_certa_errada' => 'Selecione uma resposta antes de enviar.',
                'id_exercicio' => $dados['fk_id_exercicio'],
                'alternativa_respondida' => null,
                'letra_correta' => $alternativaCorreta
            ];
            // Adiciona o exercício ao array na sessão
            session()->push('exercicios', $exercicio);
            
            return view('aluno.exercicios', compact('rows', 'materias', 'assuntos', 'exs')); 
        }
        
        // Cria uma nova resposta de aluno com os dados fornecidos
        RespostaAluno::create($dados);

        // Verifica se a alternativa selecionada é correta
        if ($dados['letra_respondida'] === $alternativaCorreta) {
            // Define a resposta como correta e prepara dados do exercício
            $exercicio = [
                'resposta_certa_errada' => 'correta',
                'id_exercicio' => $dados['fk_id_exercicio'],
                'alternativa_respondida' => $dados['letra_respondida'],
                'letra_correta' => $alternativaCorreta
            ];
            // Adiciona o exercício ao array na sessão
            session()->push('exercicios', $exercicio);
        } else {
            // Define a resposta como errada e prepara dados do exercício
            $exercicio = [
                'resposta_certa_errada' => 'errada',
                'id_exercicio' => $dados['fk_id_exercicio'],
                'alternativa_respondida' => $dados['letra_respondida'],
                'letra_correta' => $alternativaCorreta
            ];
            // Adiciona o exercício ao array na sessão
            session()->push('exercicios', $exercicio);
        }

        return view('aluno.exercicios', compact('rows', 'materias', 'assuntos', 'exs')); 
    } catch (Exception $e) {
        // Em caso de exceção, exibe uma view de erro com uma mensagem específica
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao responder: ' . $e]);
    }
}
}


 