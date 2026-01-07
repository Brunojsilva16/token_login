<?php
// app/Controllers/TokenController.php

namespace App\Controllers;

use App\Models\ProfessionalModel;
use App\Models\TokenModel;
use App\Models\PatientModel;


class TokenController extends BaseController
{
    // ... (create e store mantidos) ...
    public function create()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $profModel = new ProfessionalModel();
        $profissionais = $profModel->getAll();
        $this->view('pages/gerar_token', [
            'profissionais' => $profissionais,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null,
            'pageStyles' => ['css/gerartoken.css']
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function store()
    {

        //         echo "<h3>Debug do POST (Dados recebidos do formulário):</h3>";
        // echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc;'>";
        // var_dump($_POST);
        // echo "</pre>";
        // echo "<hr>";
        // echo "<p>Se você vê o array 'sessoes' acima com os dados corretos, o formulário está funcionando.</p>";
        // echo "<p>Remova ou comente este bloco no arquivo TokenController.php para prosseguir com o salvamento.</p>";
        // exit;

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        $paciente = $_POST['nome_paciente'] ?? '';
        $profissionalId = $_POST['profissional_id'] ?? '';
        $id_paciente = $_POST['hiddenInputId'] ?? null;
        if (empty($paciente) || empty($profissionalId)) {
            $_SESSION['flash_error'] = "Preencha todos os campos obrigatórios.";
            $this->redirect('/gerar-token');
            return;
        }
        $profModel = new ProfessionalModel();
        $profData = $profModel->getById($profissionalId);
        $porcentagem = $profData ? $profData['porcentagem'] : 0.00;
        $valorRaw = $_POST['valor'] ?? '0';
        $valor = str_replace(['R$', '.', ','], ['', '', '.'], $valorRaw);
        $dados = [
            'id_prof' => $profissionalId,
            'id_paciente' => $id_paciente,
            'id_user' => $_SESSION['user_id'],
            'paciente' => $paciente,
            'cpf' => $_POST['cpf_paciente'] ?? null,
            'telefone' => $_POST['telefone_paciente'] ?? null,
            'nome_resp' => $_POST['nome_responsavel'] ?? null,
            'responsavel_f' => $_POST['responsavel_financeiro'] ?? null,
            'nome_banco' => $_POST['nome_banco'] ?? null,
            'formapag' => $_POST['formapag'] ?? null,
            'modalidade' => $_POST['modalidade'] ?? null,
            'vencimento' => $_POST['vencimento'] ?? null,
            'origem' => $_POST['origem'] ?? 'Sistema',
            'valor' => $valor,
            'porcentagem' => $porcentagem
        ];
        $tokenModel = new TokenModel();

        try {
            $tokenId = $tokenModel->create($dados);
            if ($tokenId) {
                // Atualiza sessões na criação também
                $sessoes = $_POST['sessoes'] ?? [];
                if (method_exists($tokenModel, 'saveSessoes')) {
                    $tokenModel->saveSessoes($tokenId, $sessoes);
                }

                $_SESSION['flash_success'] = "Token gerado com sucesso!";
                $this->redirect('/home');
            } else {
                $_SESSION['flash_error'] = "Erro ao criar Token.";
                $this->redirect('/gerar-token');
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erro: " . $e->getMessage();
            $this->redirect('/gerar-token');
        }
    }
    // --- ATUALIZADO: Histórico com Filtros ---
    // public function history()
    // {
    //     if (!isset($_SESSION['user_id'])) {
    //         $this->redirect('/login');
    //         return;
    //     }

    //     $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    //     $search = $_GET['search'] ?? '';

    //     $tokenModel = new TokenModel();

    //     // Passa filtros para o Model
    //     $meusTokens = $tokenModel->getByUserId($_SESSION['user_id'], $limit, $search);

    //     $this->view('pages/meus_tokens', [
    //         'tokens' => $meusTokens,
    //         'limit' => $limit,
    //         'search' => $search
    //     ]);
    // }

    public function print()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        $id = $_GET['id'] ?? null;
        $tokenModel = new TokenModel();
        $token = $tokenModel->getById($id);

        if ($token) {
            $sessoes = $tokenModel->getSessoes($id);
            $viewPath = __DIR__ . "/../Views/pages/print_token.phtml";
            if (file_exists($viewPath)) require $viewPath;
        } else {
            echo "Token não encontrado";
        }
    }

    public function edit($id = null)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        $id = $id ?? filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            $this->redirect('/meus-tokens');
            return;
        }
        $tokenModel = new TokenModel();
        $token = $tokenModel->getById($id);
        if (!$token) {
            $_SESSION['flash_error'] = "Token não encontrado.";
            $this->redirect('/meus-tokens');
            return;
        }
        $profModel = new ProfessionalModel();
        $sessoes = method_exists($tokenModel, 'getSessoes') ? $tokenModel->getSessoes($id) : [];
        $this->view('pages/editar_token', [
            'token' => $token,
            'profissionais' => $profModel->getAll(),
            'sessoes' => $sessoes
        ]);
    }

    // ... (Métodos edit e update mantidos como estavam) ...
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = filter_input(INPUT_POST, 'id_token', FILTER_SANITIZE_NUMBER_INT);

        if ($id) {
            $profModel = new ProfessionalModel();
            $profissionalId = $_POST['id_prof'] ?? null;

            if (empty($profissionalId)) {
                $_SESSION['flash_error'] = "Erro: Profissional é obrigatório.";
                $this->redirect('/token/editar?id=' . $id);
                return;
            }

            $profData = $profModel->getById($profissionalId);
            $porcentagem = $profData ? $profData['porcentagem'] : 0.00;

            $dados = [
                'id_prof' => $profissionalId,
                'id_paciente' => !empty($_POST['hiddenInputId']) ? $_POST['hiddenInputId'] : null,
                'paciente' => $_POST['nome_paciente'] ?? '',
                'cpf' => $_POST['cpf_paciente'] ?? null,
                'telefone' => $_POST['telefone_paciente'] ?? null,
                'nome_resp' => $_POST['nome_responsavel'] ?? null,
                'responsavel_f' => $_POST['responsavel_financeiro'] ?? null,
                'nome_banco' => $_POST['nome_banco'] ?? null,
                'formapag' => $_POST['formapag'] ?? null,
                'modalidade' => $_POST['modalidade'] ?? null,
                'vencimento' => $_POST['vencimento'] ?? null,
                'origem' => $_POST['origem'] ?? 'Sistema',
                'porcentagem' => $porcentagem
            ];

            $valorRaw = $_POST['valor'] ?? '0';
            $dados['valor'] = str_replace(['R$', '.', ','], ['', '', '.'], $valorRaw);

            $tokenModel = new TokenModel();
            try {
                if ($tokenModel->update($id, $dados)) {

                    // CORREÇÃO AQUI:
                    // Pegamos o array de sessões ou um array vazio se não houver inputs
                    $sessoes = $_POST['sessoes'] ?? [];

                    // Chamamos o método incondicionalmente para que ele possa apagar as sessões se o array for vazio
                    if (method_exists($tokenModel, 'saveSessoes')) {
                        $tokenModel->saveSessoes($id, $sessoes);
                    }

                    $_SESSION['flash_success'] = "Token e sessões atualizados com sucesso!";
                } else {
                    $_SESSION['flash_error'] = "Erro ao atualizar dados principais.";
                }
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erro de sistema: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "ID inválido.";
        }

        $this->redirect('/home');
    }

    public function delete()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $id = filter_input(INPUT_POST, 'id_token', FILTER_SANITIZE_NUMBER_INT);

        if ($id) {
            $tokenModel = new TokenModel();
            try {
                if ($tokenModel->delete($id)) {
                    $_SESSION['flash_success'] = "Token excluído com sucesso!";
                } else {
                    $_SESSION['flash_error'] = "Erro ao excluir o Token.";
                }
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erro de sistema: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "ID inválido para exclusão.";
        }

        $this->redirect('/home');
    }
}
