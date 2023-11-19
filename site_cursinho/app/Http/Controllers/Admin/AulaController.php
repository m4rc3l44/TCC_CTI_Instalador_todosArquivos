<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aula;
use App\Models\Assunto;
use App\Services\FTPService; 
use App\Models\Aluno;
use Exception; 


class AulaController extends Controller
{
    public function salvar(Request $req)
    {
        try{
            $dados = $req->all();
            // Obtém o ID da matéria associada ao assunto
            $materia = Assunto::where('nome_assunto', $dados['fk_assunto'])->pluck('fk_materia');    
    
            if ($req->hasFile('nome_arquivo')) { 
                // Verifica se um arquivo foi enviado e processa para salvar no diretório de uploads
                $arquivo = $req->file('nome_arquivo');
                $num = rand(1111, 9999);
                $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
                $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
                $nomeArquivo = "arquivo_" . $num . "." . $ex;
                $arquivo->move($dir, $nomeArquivo);
                $dados['nome_arquivo'] = $dir . "/" . $nomeArquivo;
            }
    
            // Array com os dados da aula a ser cadastrada
            $cadAula = [
                'nome_aula' => $dados['nome_aula'],
                'nome_arquivo' => $dados['nome_arquivo'],
                'fk_assunto' => $dados['fk_assunto'],
                'fk_materia' => $materia['0'],
            ];
                
            // Cria uma nova aula no banco de dados
            Aula::create($cadAula);
            return redirect()->route('aula.list')
            ->with('success', 'Cadastro feito com sucesso.');
        }catch(Exception $e){
            // Em caso de erro, exibe uma tela de erro com a mensagem adequada
            return view('tela_erro.tela_erro', ['erro' => 'Aula já cadastrada.']);
        }
    }
    
    public function edit($nome_aula)
    {
        try {
            // Obtém as informações da aula para edição e todas as linhas de assunto
            $linhas = Aula::where('nome_aula', $nome_aula)->get();
            $rows = Assunto::all();
            return view('admin.editar.editar_aula', compact('linhas', 'rows'));
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações.']);
        }
        // return view('admin.editar.editar_aula', ['aula' => $aula]);
    }
    


    public function update(Request $request, $nome_aula) // Função para atualizar informações de uma aula
    {
        try{
            $dados = $request->all();
            $aulaAntiga = Aula::find($nome_aula);
            // Busca o ID da matéria associada ao assunto selecionado
            $materia = Assunto::where('nome_assunto', $dados['fk_assunto'])->pluck('fk_materia');
    
            if($request->hasFile('nome_arquivo'))
            {
                //Apaga a imagem antiga do servidor
                if(!is_null($aulaAntiga->nome_arquivo)) {
                    $imagePath = $aulaAntiga->nome_arquivo;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }
    
                $arquivo = $request->file('nome_arquivo');
                $num = rand(1111, 9999);
                $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
                $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
                $nomeArquivo = "arquivo_" . $num . "." . $ex;
                $arquivo->move($dir, $nomeArquivo);
                $dados['nome_arquivo'] = $dir . "/" . $nomeArquivo;
                
                $cadAula = [
                    'nome_aula' => $dados['nome_aula'],
                    'nome_arquivo' => $dados['nome_arquivo'],
                    'fk_assunto' => $dados['fk_assunto'],
                    'fk_materia' => $materia['0'],
                ];
            }
            else 
            {
                $cadAula = [
                    'nome_aula' => $dados['nome_aula'],
                    'fk_assunto' => $dados['fk_assunto'],
                    'fk_materia' => $materia['0'],
                ];
            }
            
            // Atualiza os dados da aula no banco de dados
            $aulaAntiga->update($cadAula);
            return redirect()->route('aula.list')
            ->with('success', 'Atualizacao feita com sucesso.');
        } catch(Exception $e){
            dd($e);
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao alterar informações.']); // Em caso de erro, exibe uma tela de erro
        }
    }
    
    public function destroy_aula($nome_aula) // Função para excluir uma aula
    {
        // try{
            $aulas = Aula::where('nome_aula', $nome_aula)->get(); 
           
            foreach ($aulas as $aula) {
                $caminhoImg = $aula->nome_arquivo;
                if(!is_null($aula->nome_arquivo)) {
                    $imagePath = $aula->nome_arquivo;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }
                $aula->delete(); // Exclui a aula do banco de dados
    
                if (file_exists($caminhoImg)) {
                    unlink($caminhoImg); // Remove o arquivo associado à aula
                }
            }

            return redirect()->route('aula.list')
            ->with('success', 'Aula deletada com sucesso.');
        // } catch(Exception $e){
        //     dd($e);
        //     return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']); // Em caso de erro, exibe uma tela de erro
        // }
    }

    public function listar(){
        // Obtém as aulas registradas paginadas para exibir na tabela de aulas da área administrativa
        $rows = Aula::paginate(10);
        return view('admin.tabelas.tabela_aulas', compact('rows')); 
    }
    
    public function listarAssuntosMaterias(){
        // Obtém os assuntos registrados para serem exibidos no cadastro de aulas na área administrativa
        $rows = Assunto::all();
        return view('admin.cadastros.cadAula', compact('rows'));
    }
    
    public function apresentar(){
        // Apresenta a lista de aulas paginadas para os alunos na seção de assuntos
        $rows = Aula::paginate(10);
        return view('aluno.assunto', compact('rows')); 
    }
    
    public function buscar(Request $req){
        // Busca por nome de aulas na área administrativa e retorna o resultado paginado
        $busca = $req->input('busca');
        try{
            $rows = Aula::whereRaw('LOWER(nome_aula) LIKE ?', ['%' . strtolower($busca) . '%'])
                ->paginate(10)
                ->appends(['busca' => $busca]);
            return view('admin.tabelas.tabela_aulas', compact('rows'));
        } catch (Exception $e){
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao filtrar aulas.']);
        }
    }
    
    public function apresentar_assuntos($nome_materia){
        // Apresenta a lista de aulas por matéria, ordenadas por assunto, para os alunos
        $rows = Aula::where('fk_materia', $nome_materia)
            ->orderBy('fk_assunto') 
            ->paginate(10);
        return view('aluno.assunto', compact('rows', 'nome_materia'));
    }
    
    public function PesquisaExercicio(Request $req){ 
        // Realiza uma pesquisa de aulas por matéria, assunto ou nome de aula para os alunos
        $dadosBusca = $req->all();
        try {
            $query = Aula::where('fk_materia', $dadosBusca['materia']);
            
            if (isset($dadosBusca['filtro'])) {
                if ($dadosBusca['filtro'] == 'assunto') {
                    $query->whereRaw('LOWER(fk_assunto) LIKE ?', ['%' . strtolower($dadosBusca['busca']) . '%']);
                } else if ($dadosBusca['filtro'] == 'aula') {
                    $query->whereRaw('LOWER(nome_aula) LIKE ?', ['%' . strtolower($dadosBusca['busca']) . '%']);
                }
            }
            
            $rows = $query->paginate(10)->appends($dadosBusca);
            
            $nome_materia = $dadosBusca['materia'];
            return view('aluno.assunto', compact('rows', 'nome_materia'));
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pesquisar aulas.']);
        }
    }
    

    
}
