<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\TokenModel;
use App\Core\Paginator; // Importando o Paginator

class HomeController extends BaseController
{
    public function index()
    {
        date_default_timezone_set('America/Sao_Paulo');
        Auth::protect();

        $tokenModel = new TokenModel();

        // --- LÓGICA 1: ESTATÍSTICAS ---
        // Aqui mantemos a busca geral para estatísticas
        $dadosEstatisticas = $tokenModel->getAllWithDetails(2000, ''); 

        $hoje = date('Y-m-d');
        $mesAtual = date('Y-m');

        $atendimentosHoje = 0;
        $faturamentoHoje = 0.00;
        $faturamentoMes = 0.00;
        
        // Total para os cards (baseado na amostra de estatísticas)
        $totalTokensGeral = count($dadosEstatisticas);

        foreach ($dadosEstatisticas as $t) {
            if (empty($t['data_registro'])) continue;

            $dataReg = substr($t['data_registro'], 0, 10);
            $mesReg = substr($t['data_registro'], 0, 7);
            $valor = $t['valor'] ?? 0;

            if ($dataReg == $hoje) {
                $atendimentosHoje++;
                $faturamentoHoje += $valor;
            }

            if ($mesReg == $mesAtual) {
                $faturamentoMes += $valor;
            }
        }

        // --- LÓGICA 2: TABELA PAGINADA ---
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 10;
        $search = $_GET['search'] ?? '';

        // 1. Conta o total REAL de registros compatíveis com a busca
        $totalItems = $tokenModel->countAll($search);

        // 2. Inicializa o Paginator
        $paginator = new Paginator($totalItems, $limit, $page, URL_BASE . '/home', $search);

        // 3. Busca os dados limitados e com offset correto
        $tabelaAtendimentos = $tokenModel->getAllWithDetails($limit, $search, $paginator->getOffset());

        $data = [
            'view' => 'home',
            
            // Dados calculados
            'atendimentos_hoje' => $atendimentosHoje,
            'faturamento_hoje' => $faturamentoHoje,
            'faturamento_mes' => $faturamentoMes,
            'total_tokens' => $totalItems, // Usando o count real do banco
            
            // Dados da tabela e paginação
            'atendimentos' => $tabelaAtendimentos,
            'paginator' => $paginator, // Passando o objeto paginator
            
            // Filtros
            'limit' => $limit,
            'search' => $search,
            
            'usuario_nome' => Auth::name(),
            'usuario_email' => Auth::email()
        ];

        $this->view('pages/home', $data);
    }
}