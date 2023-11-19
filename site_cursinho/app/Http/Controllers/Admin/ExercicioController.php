<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exercicio;
use App\Models\Alternativa;
use App\Models\Materia;
use App\Models\Assunto;
use App\Services\FTPService; 
use Exception; 



class ExercicioController extends Controller
{
    private $allExercises; // Variável de classe para armazenar todos os exercícios

    public function __construct()
    {
        // Carrega todos os exercícios no construtor
        $this->allExercises = Exercicio::with('alternativas')->get();
    }

    public function salvar(Request $req)
    {
    try {
        // Recebe todos os dados da view de cadastro de exercícios
        $dados = $req->all();
      
        // Obtém o ID da matéria baseado no nome do assunto
        $materia = Assunto::where('nome_assunto', $dados['fk_assunto'])->pluck('fk_materia');

        // Verifica se há um arquivo de imagem para o exercício
        if ($req->hasFile('imagem_exercicio')) {
            // Manipulação do arquivo de imagem do exercício
            // Gera um número aleatório para o nome do arquivo
            $num = rand(1111, 9999);
            $dir = "img/exercicios/";
            $imagem = $req->file('imagem_exercicio');
            $ex = $imagem->guessClientExtension();
            $nomeImagem = "imagem_".$num.".".$ex;
            $imagem->move($dir, $nomeImagem);
            // Atualiza o caminho da imagem no array de dados
            $dados['imagem_exercicio'] = $dir . "/" . $nomeImagem;
        }

        // Verifica se há um arquivo de imagem para a correção do exercício
        if ($req->hasFile('imagem_correcao_exercicio')) {
            // Manipulação do arquivo de imagem de correção do exercício
            $num = rand(1111, 9999);
            $dir = "img/exercicios/";
            $imagem = $req->file('imagem_correcao_exercicio');
            $ex = $imagem->guessClientExtension();
            $nomeImagem = "imagem_".$num.".".$ex;
            $imagem->move($dir, $nomeImagem);
            // Atualiza o caminho da imagem de correção no array de dados
            $dados['imagem_correcao_exercicio'] = $dir . "/" . $nomeImagem;
        }

        // Cria um array com os dados do exercício
        $cadEx = [
            'id_exercicio' => $dados['id_exercicio'],
            'descricao_exercicio' => $dados['descricao_exercicio'],
            'ano_exercicio' => $dados['ano_exercicio'],
            'vestibular' => $dados['vestibular'],
            'fk_assunto' => $dados['fk_assunto'],
            'correcao_exercicio' => $dados['correcao_exercicio'],
            'fk_materia' => $materia['0'], // Usa o ID da matéria obtido anteriormente
        ];

        // Se houver um arquivo de imagem de exercício, atualiza o caminho no array de dados
        if ($req->hasFile('imagem_exercicio')) { 
            $cadEx['imagem_exercicio'] = $dados['imagem_exercicio'];
        }

        // Cria um novo registro na tabela 'Exercicio' com os dados fornecidos
        Exercicio::create($cadEx);

        // Atualiza o campo 'correcao_exercicio' na tabela 'Exercicio'
        Exercicio::where('id_exercicio', $dados['id_exercicio'])->update(['correcao_exercicio' => $dados['correcao_exercicio']]);

        // Se houver um arquivo de imagem de correção do exercício, atualiza o caminho no array de dados e na tabela 'Exercicio'
        if ($req->hasFile('imagem_correcao_exercicio')) {
            $cadEx['imagem_correcao_exercicio'] = $dados['imagem_correcao_exercicio'];
            Exercicio::where('id_exercicio', $dados['id_exercicio'])->update(['imagem_correcao_exercicio' => $dados['imagem_correcao_exercicio']]);
        }

        // Redireciona para a mesma página de Cadastro de Exercício
        return redirect()->route('exercicio.list')
        ->with('success', 'Cadastro feito com sucesso');
    } catch(Exception $e) {
        // Em caso de erro, exibe uma view de erro com uma mensagem específica
        return view('tela_erro.tela_erro', ['erro' => 'Erro no cadastro. Assunto/matéria não existentes. Verifique as informações']);
    }
}


    public function ListarAssuntos() 
    {
        // Obtém o maior ID de exercício na tabela 'Exercicio'
        $maiorIdExercicio = Exercicio::max('id_exercicio');

        // Recupera todos os registros da tabela 'Assunto'
        $rows = Assunto::all();

        // Retorna a view 'cadExercicio', passando os dados recuperados como parâmetros
        return view('admin.cadastros.cadExercicio', compact('rows', 'maiorIdExercicio'));
    }

    public function edit($id_exercicio) //Apresenta a tela de ediçãio de exercícios com todos os dados
    {
        try {
            // Obtém o maior ID de exercício na tabela 'Exercicio'
            $maiorIdExercicio = Exercicio::max('id_exercicio');

            // Recupera os dados do exercício com o ID fornecido
            $rows = Exercicio::where('id_exercicio', $id_exercicio)->get();

            // Recupera todos os registros da tabela 'Assunto'
            $subject = Assunto::all();

            // Retorna a view 'editar_exercicio', passando os dados recuperados como parâmetros
            return view('admin.editar.editar_exercicio', compact('rows', 'subject', 'maiorIdExercicio'));
        } 
        catch (Exception $e) {
            // Em caso de exceção, exibe um erro detalhado
            dd($e);
            // Retorna a view de erro com uma mensagem genérica
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações.']);
        }
    }



    public function update(Request $req, $id_exercicio) // Atualiza um exercício existente
    {
        try {
            // Encontra o exercício antigo com o ID fornecido
            $exercicio = Exercicio::find($id_exercicio);
    
            // Obtém todos os dados da requisição
            $dados = $req->all();
            
            // Obtém o ID da matéria baseado no nome do assunto
            $materia = Assunto::where('nome_assunto', $dados['fk_assunto'])->pluck('fk_materia'); 
    
            // Verifica se há um arquivo de imagem para o exercício
            if ($req->hasFile('imagem_exercicio')) {

                //Apaga a imagem antiga do servidor
                if(!is_null($exercicio->imagem_exercicio)) {
                    $imagePath = $exercicio->imagem_exercicio;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }

                // Manipulação do arquivo de imagem do exercício
                // Gera um número aleatório para o nome do arquivo
                $imagem = $req->file('imagem_exercicio');
                $num = rand(1111, 9999);
                $dir = "img/exercicios/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_" . $num . "." . $ex;
                $imagem->move($dir, $nomeImagem);
                // Atualiza o caminho da imagem no array de dados
                $dados['imagem_exercicio'] = $dir . "/" . $nomeImagem;
            }
    
            // Verifica se há um arquivo de imagem para a correção do exercício
            if ($req->hasFile('imagem_correcao_exercicio')) {
                
                //Apaga a imagem antiga do servidor
                if(!is_null($exercicio->imagem_correcao_exercicio)) {
                    $imagePath = $exercicio->imagem_correcao_exercicio;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }

                // Manipulação do arquivo de imagem de correção do exercício
                $imagem = $req->file('imagem_correcao_exercicio');
                $num = rand(1111, 9999);
                $dir = "img/exercicios/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_" . $num . "." . $ex;
                $imagem->move($dir, $nomeImagem);
                // Atualiza o caminho da imagem de correção no array de dados
                $dados['imagem_correcao_exercicio'] = $dir . "/" . $nomeImagem;
            }
    
            // Cria um array com os dados atualizados do exercício
            $cadEx = [
                'id_exercicio' => $dados['id_exercicio'],
                'descricao_exercicio' => $dados['descricao_exercicio'],
                'ano_exercicio' => $dados['ano_exercicio'],
                'vestibular' => $dados['vestibular'],
                'fk_assunto' => $dados['fk_assunto'],
                'fk_materia' => $materia['0'],
                'correcao_exercicio' => $dados['correcao_exercicio'],
            ];
    
            // Se houver um arquivo de imagem de exercício, atualiza o caminho no array de dados
            if ($req->hasFile('imagem_exercicio')) {
                $cadEx['imagem_exercicio'] = $dados['imagem_exercicio'];
            }
    
            // Atualiza os dados do exercício na tabela 'Exercicio'
            $exercicio->update($cadEx);
    
            // Atualiza o campo 'correcao_exercicio' na tabela 'Exercicio' com o dado fornecido
            Exercicio::where('id_exercicio', $dados['id_exercicio'])->update(['correcao_exercicio' => $dados['correcao_exercicio']]);
    
            // Se houver um arquivo de imagem de correção do exercício, atualiza o caminho na tabela 'Exercicio'
            if ($req->hasFile('imagem_correcao_exercicio')) {
                $cadEx['imagem_correcao_exercicio'] = $dados['imagem_correcao_exercicio'];
                Exercicio::where('id_exercicio', $dados['id_exercicio'])->update(['imagem_correcao_exercicio' => $dados['imagem_correcao_exercicio']]);
            }
    
            // Redireciona para a rota 'exercicio.list'
            return redirect()->route('exercicio.list')
            ->with('success', 'Atualizacao feita com sucesso');
        } catch (Exception $e) {
            // Em caso de exceção, exibe uma view de erro com uma mensagem específica
            return view('tela_erro.tela_erro', ['erro' => 'Erro no cadastro. Assunto/matéria não existentes ou Id já cadastrado. Verifique as informações']);
        }
    }
    
    public function destroy($id_exercicio) // Remove um exercício
    {
        try {
            // Encontra o exercício com o ID fornecido
            $exercicio = Exercicio::find($id_exercicio);
            if(!is_null($exercicio->imagem_exercicio)) {
                $imagePath = $exercicio->imagem_exercicio;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath);
            }

            if(!is_null($exercicio->imagem_correcao_exercicio)) {
                $imagePath = $exercicio->imagem_correcao_exercicio;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath);
            }
    
            // Recupera todas as alternativas associadas ao exercício
            $alternativas = Alternativa::where('fk_id_exercicio', $id_exercicio)->get();
    
            // Remove todas as alternativas associadas ao exercício
            foreach ($alternativas as $alternativa) {
                $alternativa->delete();
            }
    
            // Remove o exercício
            $exercicio->delete();
    
            // Redireciona para a rota 'exercicio.list'
            return redirect()->route('exercicio.list')
            ->with('success', 'Exercicio deletado com sucesso');
        } catch (Exception $e) { 
            // Em caso de exceção, exibe uma view de erro
            return view('tela_erro.tela_erro', ['erro' => ' ']);
        }
    }
    
    public function listar(){
        // Obtém os exercícios paginados (10 por página)
        $rows = Exercicio::paginate(10);
        return view('admin.tabelas.tabela_exercicios', compact('rows'));
    }
    
    public function listar_para_aluno(){
        // Obtém todos os exercícios e suas alternativas associadas
        $rows = Exercicio::with('alternativas')->get();
    
        // Obtém todas as matérias, assuntos e verificações de respostas certas/erradas
        $exs = $this->allExercises; 
        $materias = Materia::all(); 
        $assuntos = Assunto::all(); 
        $resposta_certa_errada = session('resposta_certa_errada', null);
        $id_exercicio = session('id_exercicio', null);
    
        // Limpa as variáveis de sessão
        session()->forget(['resposta_certa_errada']);
        session()->forget(['id_exercicio']);
        
        if (session()->exists('busca')) {
            session()->forget('busca'); 
        }
        
        return view('aluno.exercicios', compact('rows', 'resposta_certa_errada', 'id_exercicio', 'assuntos', 'materias', 'exs'));
    }
    
    public function buscar(Request $req)
    { 
        // Obtém o termo de busca da requisição
        $busca = $req->input('busca');
    
        try {
            // Realiza a busca na descrição do exercício e retorna os resultados paginados
            $rows = Exercicio::whereRaw('LOWER(descricao_exercicio) LIKE ?', ['%' . strtolower($busca) . '%'])->paginate(10);
    
            return view('admin.tabelas.tabela_exercicios', compact('rows'))
                ->appends(['busca' => $busca]);
        } catch (Exception $e) {
            dd($e); 
        }
    }
    
    //Buscar realizada na tela do aluno
    public function buscar_aluno(Request $req)
    {
        try {
            // Obtém os filtros da requisição
            $busca = $req->all();
            $exs = $this->allExercises; 
            $materias = Materia::all(); 
            $assuntos = Assunto::all(); 
            $resposta_certa_errada = session('resposta_certa_errada', null);
            $id_exercicio = session('id_exercicio', null);
    
            // Limpa as variáveis de sessão
            session()->forget(['resposta_certa_errada']);
            session()->forget(['id_exercicio']);
    
            // Cria a query para buscar os exercícios com base nos filtros selecionados
            $query = Exercicio::with('alternativas');
    
            // Constrói a consulta dinamicamente com base nos filtros selecionados
            if ($busca['Materia'] != "todos") {
                $query->where('fk_materia', $busca['Materia']);
            }
    
            if ($busca['Assunto'] != "todos") {
                $query->where('fk_assunto', $busca['Assunto']);
            }
    
            if ($busca['Vestibular'] != "todos") {
                $query->where('vestibular', $busca['Vestibular']);
            }
    
            if ($busca['Ano'] != "todos") {
                $query->where('ano_exercicio', $busca['Ano']);
            }
    
            
            // Adicione mais condições para outros campos conforme necessário
            
            // Executa a consulta
            $rows = $query->get(); 
    
            // Limpa a variável de sessão de busca
            if (session()->exists('busca')) {
                session()->forget('busca'); 
            }
            
            // Adiciona os filtros à variável de sessão
            session()->push('busca', $busca);
    
            return view('aluno.exercicios', compact('rows', 'resposta_certa_errada', 'id_exercicio', 'assuntos', 'materias', 'exs'));
        } catch (Exception $e) {
            dd($e); 
        }
    }
    

   
 
} 