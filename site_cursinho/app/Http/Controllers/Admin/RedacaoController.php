<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Redacao;
use App\Models\RedacaoAluno;
use App\Models\RedacaoCorrigida;
use App\Services\FTPService; 
use Exception; 
use Auth;
use Illuminate\Support\Facades\Crypt;



class RedacaoController extends Controller
{
    public function __construct()
    {
        // Carrega todos os exercícios no construtor
        if (Auth::check()) {
            $this->cpf = Auth::user()->cpf;
        } else {
            // O usuário não está autenticado, faça o tratamento apropriado aqui
            $this->cpf = null; // Ou defina um valor padrão, se necessário
        }
    }

    public function salvar(Request $req)
    {
        // Comentado bloco try-catch, você pode descomentá-lo quando necessário
        // try {
            $dados = $req->all();
            // dd($dados);
            // $dados['senha_aluno'] = Hash::make($dados['senha_aluno']);
            if ($req->hasFile('nome_imagem')) {
                $imagem = $req->file('nome_imagem');
                $num = rand(1111, 9999);
                $dir = "img/redacaos/";
                $ex = $imagem->guessClientExtension();
                $nomeImagem = "imagem_" . $num . "." . $ex;
                $imagem->move($dir, $nomeImagem);
                $dados['nome_imagem'] = $dir . "/" . $nomeImagem;
            }
            if ($req->hasFile('proposta_arquivo')) {
                $arquivo = $req->file('proposta_arquivo');
                $num = rand(1111, 9999);
                $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
                $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
                $nomeArquivo = "arquivo_" . $num . "." . $ex;
                $arquivo->move($dir, $nomeArquivo);
                $dados['proposta_arquivo'] = $dir . "/" . $nomeArquivo;
            }
            // dd($dados);

            Redacao::create($dados);
            Redacao::where('titulo', $dados['titulo'])->update(['proposta_arquivo' => $dados['proposta_arquivo']]);
            return redirect()->route('redacao.list')
                ->with('success', 'Cadastro feito com sucesso');
        // } catch (Exception $e) {
        //     return view('tela_erro.tela_erro', ['erro' => 'Erro ao cadastrar. Informações repetidas.']);
        // }
    }

    // Outros métodos edit, update e destroy foram comentados e podem ser descomentados conforme necessário.

    public function listar()
    {
        $rows = Redacao::paginate(10);
        return view('admin.tabelas.tabela_redacoes', compact('rows'));
    }

    public function buscar(Request $req) // Busca na tabela que os professores e adms têm acesso
    {
        $busca = $req->input('busca');
        // dd($busca);
        try {
            $rows = Redacao::whereRaw('LOWER(titulo) LIKE ?', ['%' . strtolower($busca) . '%'])->paginate(10)
                ->appends(['busca' => $busca]);

            return view('admin.tabelas.tabela_redacoes', compact('rows'));
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Falha ao pegar informações.']);
        }
    }

    public function alunobuscar(Request $req) // Busca do aluno por redações
    {
        $busca = $req->input('busca');
        // dd($busca);
        try {
            $rows = Redacao::whereRaw('LOWER(titulo) LIKE ?', ['%' . strtolower($busca) . '%'])->paginate(9)
                ->appends(['busca' => $busca]);

            return view('aluno.redacao', compact('rows'));
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao filtrar redações.']);
        }
    }

    public function apresentar()
    {
        $rows = Redacao::paginate(10);
        return view('aluno.redacao', compact('rows'));
    }

    public function edit_redacao_aluno($titulo)
    {
        // $rows = Redacao::where('titulo', $titulo)->get();
        // $url = url('editar/redacao_aluno/'.$titulo. urlencode('?w/edit'));

        // $chave = 'chave';

        // $tituloCriptografado = Crypt::encryptString($titulo, $chave);
        // dd($tituloCriptografado);
        // $temaDecodificado = urldecode($titulo);
        $tituloDecodificado = urldecode($titulo);
        return view('aluno.enviar_redacao', ['titulo' => $tituloDecodificado]);
        // $rows = Redacao::where('titulo', $titulo)->with('aluno')->get();
        // return view('admin.editar.editar_redacao', compact('rows'));
    }
    //Cria a redação. O nome está errado
    public function update_redacao_aluno(Request $req, $titulo, $fk_cpf_aluno)
    {
        $dados =  $req->all();

        $redacaoAntiga = RedacaoAluno::find([$titulo, $fk_cpf_aluno]);

        if ($req->hasFile('nome_arquivo')) {

            $arquivo = $req->file('nome_arquivo');
            $num = rand(1111, 9999);
            $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
            $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
            $nomeArquivo = "arquivo_" . $num . "." . $ex;
            $arquivo->move($dir, $nomeArquivo);
            $dados['nome_arquivo'] = $dir . "/" . $nomeArquivo;
        }
        $tituloDecodificado = urldecode($titulo);
        $dados['fk_tema'] = "" . $tituloDecodificado;
        $dados['fk_cpf_aluno'] = "" . $fk_cpf_aluno;
        RedacaoAluno::create($dados);
        return redirect()->route('redacao.list')
            ->with('success', 'Atualização feita com sucesso');
    }

    public function listar_redacao_aluno()
    {
        $rows = RedacaoAluno::with('aluno')->orderBy('corrigida', 'asc')->paginate(9);
        return view('admin.tabelas.tabela_redacao_aluno', compact('rows'));
    }

    public function edit($titulo)
    {
        try {
            $tituloDecodificado = urldecode($titulo);
            $rows = Redacao::where('titulo', $tituloDecodificado)->get();
            return view('admin.editar.editar_redacao', compact('rows'));
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao pegar informações: ' + $e]);
        }
    }
    public function update(Request $req, $titulo)
    {
        try {
            // Decodifique o título da redação
            $tituloDecodificado = urldecode($titulo);
    
            // Encontre a redação antiga pelo título
            //$redacaoAntigo = Redacao::where('titulo', $tituloDecodificado)->first();
            $redacaoAntigo = Redacao::find($tituloDecodificado);
            // Verifique se a redação antiga foi encontrada
            if (!$redacaoAntigo) {
                // Redação antiga não encontrada, talvez você queira tratar isso de alguma forma
                return redirect()->route('redacao.list')->with('error', 'Redação não encontrada');
            }
            $dados=$req->all();
            // Verifique se o título da redação foi alterado
            if ($req->titulo != $titulo) {
                // Crie uma nova redação

                if ($req->hasFile('nome_imagem')) {
                        if(!is_null($redacaoAntigo->nome_imagem))
                        {
                            $imagePath = $redacaoAntigo->nome_imagem;
                            $ftpService = new FTPService();
                            $ftpService->deleta($imagePath); 
                        }
                    $imagem = $req->file('nome_imagem');
                    $num = rand(1111, 9999);
                    $dir = "img/redacaos/";
                    $ex = $imagem->guessClientExtension();
                    $nomeImagem = "imagem_" . $num . "." . $ex;
                    $imagem->move($dir, $nomeImagem);
                    $dados['nome_imagem'] = $dir . "/" . $nomeImagem;
                }
                if ($req->hasFile('proposta_arquivo')) {

                    if(!is_null($redacaoAntigo->proposta_arquivo))
                    {
                        $imagePath = $redacaoAntigo->proposta_arquivo;
                        $ftpService = new FTPService();
                        $ftpService->deleta($imagePath); 
                    }

                    $arquivo = $req->file('proposta_arquivo');
                    $num = rand(1111, 9999);
                    $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
                    $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
                    $nomeArquivo = "arquivo_" . $num . "." . $ex;
                    $arquivo->move($dir, $nomeArquivo);
                    $dados['proposta_arquivo'] = $dir . "/" . $nomeArquivo;
                }
                $novoredacao = Redacao::create($dados);
    
                // Atualize as redações de alunos com o novo título
                $redacoesalunosAntigas = RedacaoAluno::where('fk_tema', $titulo)->get();
                foreach ($redacoesalunosAntigas as $redacaoaluno) {
                    $redacaoaluno->fk_tema = $novoredacao->titulo;
                    $redacaoaluno->save();
                }
    
                $redacoescorrigidasAntigas = RedacaoCorrigida::where('fk_tema', $titulo)->get();
                foreach ($redacoescorrigidasAntigas as $redacaoaluno) {
                    $redacaoaluno->fk_tema = $novoredacao->titulo;
                    $redacaoaluno->save();
                }
    
                // Exclua a redação antiga
                $redacaoAntigo->delete();
            } else {
                // Atualize a redação antiga com os dados do request
                if ($req->hasFile('nome_imagem')) {

                    if(!is_null($redacaoAntigo->nome_imagem))
                    {
                        $imagePath = $redacaoAntigo->nome_imagem;
                        $ftpService = new FTPService();
                        $ftpService->deleta($imagePath); 
                    }
                        $imagem = $req->file('nome_imagem');
                        $num = rand(1111, 9999);
                        $dir = "img/redacaos/";
                        $ex = $imagem->guessClientExtension();
                        $nomeImagem = "imagem_" . $num . "." . $ex;
                        $imagem->move($dir, $nomeImagem);
                        $dados['nome_imagem'] = $dir . "/" . $nomeImagem;
                    }
                    if ($req->hasFile('proposta_arquivo')) {

                        if(!is_null($redacaoAntigo->proposta_arquivo))
                        {
                            $imagePath = $redacaoAntigo->proposta_arquivo;
                            $ftpService = new FTPService();
                            $ftpService->deleta($imagePath); 
                        }

                        $arquivo = $req->file('proposta_arquivo');
                        $num = rand(1111, 9999);
                        $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
                        $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
                        $nomeArquivo = "arquivo_" . $num . "." . $ex;
                        $arquivo->move($dir, $nomeArquivo);
                        $dados['proposta_arquivo'] = $dir . "/" . $nomeArquivo;
                    }
                $redacaoAntigo->update($dados);
            }
    
            return redirect()->route('redacao.list')->with('success', 'Redação atualizada com sucesso');
        } catch (Exception $e) {
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao atualizar redação.']);
        }
    }
    
    public function destroy($titulo)
    {
        // try {
            // Encontre a redação pelo título
               $tituloDecodificado = urldecode($titulo);
    
            // Encontre a redação antiga pelo título

            
            $redacao = Redacao::find($tituloDecodificado);

            if(!is_null($redacao->nome_imagem)) {
                $imagePath = $redacao->nome_imagem;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath);
            }
            if(!is_null($redacao->proposta_arquivo)) {
                $imagePath = $redacao->proposta_arquivo;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath);
            }
            
        

            
    
            // Verifique se a redação foi encontrada
            if (!$redacao) {
                // Redação não encontrada, talvez você queira tratar isso de alguma forma
                return redirect()->route('redacao.list')->with('error', 'Redação não encontrada');
            }
    
            // Exclua as redações de alunos relacionadas ao tema
            $redacoesalunosAntigas = RedacaoAluno::where('fk_tema', $titulo)->get();
            foreach ($redacoesalunosAntigas as $redacaoaluno) {
                $redacaoaluno->delete();
            }
    
            $redacoescorrigidasAntigas = RedacaoCorrigida::where('fk_tema', $titulo)->get();
            foreach ($redacoescorrigidasAntigas as $redacaocorrigida) {
                $redacaocorrigida->delete();
            }
    
            // Exclua a redação
            $redacao->delete();
    
            return redirect()->route('redacao.list')->with('success', 'Redação excluída com sucesso');
        // } catch (Exception $e) {
        //     return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
        // }
    }
    public function listar_redacao_aluno_para_aluno(Request $req, $cpf)
{
    // Use o parâmetro $cpf para buscar os registros no modelo RedacaoCorrigida
    $rows = RedacaoAluno::where('fk_cpf_aluno', $cpf)->get();
    // dd($rows);

    // Envie os registros para a view
    return view('aluno.tabela_redacao_aluno', compact('rows'));
}

public function edit_redacao_enviada($id_redacao)
{
    
    // dd($cpf);

    $rows = RedacaoAluno::where('id_redacao', $id_redacao)->get();
    return view('aluno.editar_redacao_enviada', compact('rows'));
    // return view('admin.editar.editar_aula', ['aula' => $aula]);
}

public function update_redacao_enviada(Request $req, $id_redacao, $fk_cpf_aluno)
{
    try {
        $cpf = Auth::user()->cpf;
        if ($cpf != $fk_cpf_aluno) {
            // dd($cpf);
            return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
        }
        $redacao = RedacaoAluno::find($id_redacao);

        $dados =  $req->all();

        if ($req->hasFile('nome_arquivo')) {
            
            if(!is_null($redacaoAntigo->proposta_arquivo))
            {
                $imagePath = $redacaoAntigo->proposta_arquivo;
                $ftpService = new FTPService();
                $ftpService->deleta($imagePath); 
            }

            $arquivo = $req->file('nome_arquivo');
            $num = rand(1111, 9999);
            $dir = "uploads/"; // Diretório onde os arquivos serão armazenados
            $ex = $arquivo->getClientOriginalExtension(); // Obtém a extensão original do arquivo
            $nomeArquivo = "arquivo_" . $num . "." . $ex;
            $arquivo->move($dir, $nomeArquivo);
            $dados['nome_arquivo'] = $dir . "/" . $nomeArquivo;
        }
        // $dados['fk_tema']="".$titulo; 
        $dados['fk_cpf_aluno'] = "" . $fk_cpf_aluno; 
        $redacao->update($dados);
        return redirect()->route('redacao.list_aluno_para_aluno', ['cpf' => $fk_cpf_aluno])
            ->with('success', 'Atualizacao feita com sucesso');
    } catch (Exception $e) {
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao alterar informações da redação.']);
    }
}

public function destroy_redacao_enviada($id_redacao, $fk_cpf_aluno)
{
    $cpf = Auth::user()->cpf;
    if ($cpf != $fk_cpf_aluno) {
        // dd($cpf);
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
    }
    try {
        $redacaoenviada = RedacaoAluno::where('id_redacao', $id_redacao)->get();
        if(!is_null($redacaoenviada->nome_arquivo)) {
            $imagePath = $redacaoenviada->nome_arquivo;
            $ftpService = new FTPService();
            $ftpService->deleta($imagePath);
        }
        foreach ($redacaoenviada as $redacao) {
            $redacao->delete();
        }

        return redirect()->route('redacao.list_aluno_para_aluno', ['cpf' => $fk_cpf_aluno])
            ->with('success', 'Atualizacao feita com sucesso');
    } catch (Exception $e) {
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao excluir.']);
    }
}

public function buscar_redaluno(Request $req)
{
    $busca = $req->input('busca');
    // dd($busca);
    try {
        $rows = RedacaoAluno::whereRaw('LOWER(fk_tema) LIKE ?', ['%' . strtolower($busca) . '%'])->paginate(10)
            ->appends(['busca' => $busca]);

        return view('admin.tabelas.tabela_redacao_aluno', compact('rows'));
    } catch (Exception $e) {
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao filtrar redações.']);
    }
}

public function buscar_redacao_aluno_aluno(Request $req, $cpf)
{
    try {
        $busca = $req->input('busca');
        // $rows = RedacaoAluno::where('fk_tema', 'LIKE', '%' . $busca . '%')->get();
        $rows = RedacaoAluno::where('fk_cpf_aluno', $cpf)
            ->whereRaw('LOWER(fk_tema) LIKE ?', ['%' . strtolower($busca) . '%'])
            ->paginate(10)->appends(['busca' => $busca]);

        // $rows = RedacaoAluno::whereRaw('LOWER(fk_tema) LIKE ?', ['%' . strtolower($busca) . '%'])
        // ->get();
        return view('aluno.tabela_redacao_aluno', compact('rows'));
    } catch (Exception $e) {
        return view('tela_erro.tela_erro', ['erro' => 'Erro ao filtar redações.']);
    }
}

}
    