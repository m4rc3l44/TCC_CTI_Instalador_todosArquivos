<?php

namespace App\Http\Controllers;
use App\Models\RedacaoCorrigida;
use App\Models\RedacaoAluno;
use App\Services\FTPService; 
use Illuminate\Http\Request;

class RedacaoCorrigidaController extends Controller
{
    public function salvar(Request $req)
    {
        try {
            $dados = $req->all();
            
            // Verifica se há um arquivo de correção enviado
            if ($req->hasFile('arquivo_correcao')) {
                $arquivo = $req->file('arquivo_correcao');
                $num = rand(1111, 9999);
                $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
                $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
                $nomeArquivo = "arquivocorrecao_" . $num . "." . $ex;
                $arquivo->move($dir, $nomeArquivo);
                $dados['arquivo_correcao'] = $dir . "/" . $nomeArquivo;
            }
    
            // Cria uma nova redação corrigida
            $redacaoCorrigida = RedacaoCorrigida::create($dados);
    
            // Atualiza o status da redação do aluno para 'corrigida'
            RedacaoAluno::where('fk_cpf_aluno', $dados['fk_cpf_aluno']) 
                ->where('fk_tema', $dados['fk_tema'])
                ->update(['corrigida' => true]);
    
            // Redireciona para a lista de redações do aluno
            return redirect()->route('redacao.list_aluno');
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao salvar a correção da redação.']);
        }
    }
    
    public function redirecionar_redacao(Request $request)
    {
        // Obtém o CPF do aluno e o tema da redação passados como parâmetros na query
        $cpfAluno = $request->query('cpf_aluno');
        $temaRedacao = $request->query('tema_redacao');
    
        // Os parâmetros são transformados em arrays para serem utilizados na view
        $cpfAluno = [$cpfAluno];
        $temaRedacao = [$temaRedacao];
    
        // Exibe o formulário de cadastro para a correção da redação
        return view('admin.cadastros.cadRedacaoCorrigida', compact('cpfAluno', 'temaRedacao'));
    }
    
      


    public function edit($id_redacao_corrigida) 
    {
        try {
            // Busca as informações da redação corrigida com o ID específico
            $rows = RedacaoCorrigida::where('id_redacao_corrigida', $id_redacao_corrigida)->get(); 
            return view('admin.editar.editar_redacao_corrigida', compact('rows'));
        } catch (Exception $e) {
            // Redireciona para a listagem com uma mensagem de erro em caso de exceção
            return redirect()->route('redacao_corrigida.list')->with('error', 'Erro ao pegar infos de assuntos.');
        }
    }
    
    public function update(Request $req, $id_redacao_corrigida) 
    {
        try {
            $redacao = RedacaoCorrigida::find($id_redacao_corrigida);
            $dados = $req->all();
            if ($req->hasFile('arquivo_correcao')) {
                if(!is_null($redacao->arquivo_correcao))  
                {
                    $imagePath = $redacao->arquivo_correcao;
                    $ftpService = new FTPService();
                    $ftpService->deleta($imagePath); 
                }

                // Lida com a atualização do arquivo de correção, se houver um novo arquivo
                $arquivo = $req->file('arquivo_correcao');
                $num = rand(1111, 9999);
                $dir = "uploads/"; // Diretório onde os arquivos são armazenados
                $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
                $nomeArquivo = "arquivo_correcao" . $num . "." . $ex;
                $arquivo->move($dir, $nomeArquivo);
                $dados['arquivo_correcao'] = $dir . "/" . $nomeArquivo;
            }
            $redacao->update($dados); // Atualiza os dados da redação corrigida
            return redirect()->route('redacao_corrigida.list'); // Redireciona para a listagem
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => ' ']); // Exibe tela de erro em caso de exceção
        }
    }
    
    public function destroy($id_redacao_corrigida)
    {
        try {
            $redacao = RedacaoCorrigida::find($id_redacao_corrigida);
            if(!is_null($redacao->arquivo_correcao)) {
                $imagePath = $redacao->arquivo_correcao;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath);
            }
            $redacao->delete(); // Deleta a redação corrigida com base no ID
            return redirect()->route('redacao_corrigida.list'); // Redireciona para a listagem
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => ' ']); // Exibe tela de erro em caso de exceção
        }
    }
    

        public function listar_redacoes_corrigidas($cpf)
        {
            // Lista as redações corrigidas associadas a um aluno específico
            $rows = RedacaoCorrigida::where('fk_cpf_aluno', $cpf)->paginate(10);
        
            // Envia os registros para a view 'aluno.correcao_redacao'
            return view('aluno.correcao_redacao', compact('rows'));
        }
        
        public function listar_redacoes_corrigidas_para_professor()
        {
            // Lista todas as redações corrigidas com informações dos alunos associados
            $rows = RedacaoCorrigida::with('aluno')->paginate(10);
        
            // Envia os registros para a view 'admin.tabelas.tabela_redacoes_corrigidas'
            return view('admin.tabelas.tabela_redacoes_corrigidas', compact('rows'));
        }
        
        public function buscar(Request $req)
        {
            $busca = $req->input('busca');
            
            try {
                // Procura por redações com base no tema
                $rows = RedacaoCorrigida::whereRaw('LOWER(fk_tema) LIKE ?', ['%' . strtolower($busca) . '%'])
                    ->paginate(10)
                    ->appends(['busca' => $busca]);
        
                // Exibe os resultados na view 'admin.tabelas.tabela_redacoes_corrigidas'
                return view('admin.tabelas.tabela_redacoes_corrigidas', compact('rows'));
            } catch (Exception $e) {
                dd($e); // Retorna uma página de erro caso ocorra uma exceção
            }
        }
        


}
