<?php

namespace App\Services;

class FTPService
{
    public function conectarFTP($servidor, $usuario, $senha)
    {
        // Estabeleça a conexão FTP
        $conn_id = ftp_connect($servidor);

        // Faça login no servidor FTP
        if (ftp_login($conn_id, $usuario, $senha)) {
            return $conn_id;
        } else {
            // Falha ao fazer login
            ftp_close($conn_id); // Feche a conexão em caso de falha
            return false;
        }
    }
 
    public function deleta($imagePath)
    {
        $servidor = 'ftp.projetoscti.com.br';
        $usuario = 'projetoscti24';
        $senha = '730494';

        $ftp_conexao = $this->conectarFTP($servidor, $usuario, $senha);

        if ($ftp_conexao !== false) {
            // Use o comando FTP 'delete' para excluir o arquivo
            if (ftp_size($ftp_conexao, $imagePath) !== -1) {
                // O arquivo existe, pode continuar com a exclusão
                if (ftp_delete($ftp_conexao, $imagePath)) {
                    // Arquivo FTP excluído com sucesso
                 
                } else {
                    // Erro ao excluir o arquivo FTP
                }
            } else {
                // O arquivo não existe, faça algo apropriado, como mostrar uma mensagem
            }

            // Feche a conexão FTP quando terminar
            ftp_close($ftp_conexao);
        } else {
            // Falha na conexão FTP
            // Trate a falha na conexão, se necessário
        }
    }
}
