<?php
// app/Controllers/ReportController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\TokenModel;
use App\Models\ProfessionalModel;

class ReportController extends BaseController
{
    // Tela de filtro (Mantém layout padrão pois é parte do sistema)
    public function index()
    {
        Auth::protect();
        $profModel = new ProfessionalModel();
        $profissionais = $profModel->getAll();
        $this->view('pages/reports/filter', ['profissionais' => $profissionais]);
    }

    // Processa o relatório
    public function generate()
    {
        Auth::protect();

        $tipo = $_GET['tipo'] ?? 'completo';
        $format = $_GET['format'] ?? 'html';
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
        $profId = $_GET['profissional_id'] ?? null;

        $filters = [
            'responsavel_f' => $_GET['responsavel_f'] ?? null,
            'formapag' => $_GET['formapag'] ?? null,
            'nome_banco' => $_GET['nome_banco'] ?? null,
            'origem' => $_GET['origem'] ?? null
        ];

        $tokenModel = new TokenModel();

        if (method_exists($tokenModel, 'getReportData')) {
            $dados = $tokenModel->getReportData($dataInicio, $dataFim, $profId, $filters);
        } else {
            $dados = [];
        }

        // Variáveis para a View
        $viewData = [
            'dados' => $dados,
            'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim],
            'filtro_prof' => $profId,
            'isExcel' => ($format === 'excel')
        ];

        // Define qual arquivo procurar
        $fileName = ($tipo === 'completo') ? 'complete.phtml' : 'summary.phtml';

        // --- CORREÇÃO DE CAMINHO ---
        // Lista de locais possíveis onde o arquivo pode estar (prioridade para pasta reports)
        // Usa 'views' minúsculo para compatibilidade com Linux
        $possiblePaths = [
            __DIR__ . '/../Views/pages/reports/' . $fileName,  // Caminho Ideal
            __DIR__ . '/../Views/pages/' . $fileName           // Caminho Alternativo (raiz de pages)
        ];

        $viewPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $viewPath = $path;
                break;
            }
        }

        // Se não achou em lugar nenhum, mostra erro detalhado
        if (!$viewPath) {
            echo "<h3>Erro: Arquivo de visualização não encontrado.</h3>";
            echo "<p>O sistema procurou nos seguintes locais e não encontrou:</p>";
            echo "<ul>";
            foreach ($possiblePaths as $path) {
                echo "<li>" . htmlspecialchars($path) . "</li>";
            }
            echo "</ul>";
            echo "<p>Verifique se o arquivo <strong>$fileName</strong> foi enviado para o servidor e se o nome da pasta <strong>Views</strong> está em minúsculo.</p>";
            exit;
        }

        // --- MODO EXCEL ---
        if ($format === 'excel') {
            $filename = 'relatorio_' . $tipo . '_' . date('Y-m-d_Hi') . '.xls';

            if (ob_get_level()) ob_end_clean(); // Limpa buffers anteriores

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            extract($viewData);
            require $viewPath;
            exit;
        }

        // --- MODO TELA (HTML) ---
        // Require direto para evitar Sidebar/Header do BaseController
        extract($viewData);
        require $viewPath;
        exit;
    }

 public function quantitativo()
    {
        // 1. Protege a rota (apenas utilizadores logados)
        Auth::protect();

        // 2. Instancia Models
        $profModel = new ProfessionalModel();
        $tokenModel = new TokenModel();

        // 3. Carrega lista de profissionais para o Select do filtro
        $profissionais = $profModel->getAll();

        // 4. Configura filtros iniciais (Padrão: Mês atual)
        $filtros = [
            'dt_inicio' => $_GET['dt_inicio'] ?? date('Y-m-01'),
            'dt_fim'    => $_GET['dt_fim'] ?? date('Y-m-t'),
            'id_prof'   => $_GET['id_prof'] ?? '',
            'origem'    => $_GET['origem'] ?? ''
        ];

        $dadosRelatorio = [];

        // 5. Se for uma submissão de formulário (verificamos se dt_inicio está no GET)
        // ou se queremos carregar os dados padrão ao entrar na página
        try {
            $dadosRelatorio = $tokenModel->getRelatorioQuantitativo(
                $filtros['dt_inicio'],
                $filtros['dt_fim'],
                $filtros['id_prof'],
                $filtros['origem']
            );
        } catch (\Exception $e) {
            // Em caso de erro no banco, podemos passar um array vazio ou tratar erro
            $dadosRelatorio = [];
            // Opcional: Adicionar mensagem de erro flash se tiver sistema de notificações
        }

        // 6. Carrega a View
        // Ajuste o caminho 'pages/relatorio_quantitativo' conforme a estrutura das suas pastas de view
        // Se o ficheiro estiver na raiz das views, use apenas 'relatorio_quantitativo'
        $this->view('pages/relatorio_quantitativo', [
            'profissionais' => $profissionais,
            'dadosRelatorio' => $dadosRelatorio,
            'filtros' => $filtros
        ]);
    }
}
