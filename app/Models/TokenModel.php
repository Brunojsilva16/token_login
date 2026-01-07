<?php
// app/Models/TokenModel.php

namespace App\Models;

use App\Database\Connection;
use PDO;
use Exception;

class TokenModel
{
    protected $conn;
    private $table = 'tokens';
    private $colunaData = 'data_cadastro';
    private $tableSessoes = 'sessoes';

    public function __construct()
    {
        $this->conn = Connection::getInstance();
    }

    public function find($id)
    {
        return $this->getById($id);
    } // Alias
    // Método auxiliar útil para o Edit
    public function getById($id)
    {
        $sql = "SELECT t.*,
                    p.nome as nome_profissional,
                    u.nome as nome_usuario,
                    pac.data_cadastro as data_cadastro_pac
        FROM {$this->table} t 
        LEFT JOIN profissionais p ON t.id_prof = p.id_prof 
        LEFT JOIN usuarios_a u ON t.id_user = u.id_user 
        LEFT JOIN pacientes pac ON t.id_paciente = pac.id_paciente
        WHERE t.id_token = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Conta o total de registros para paginação (Contexto Geral)
     */
    public function countAll($search = '')
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} t 
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof 
                LEFT JOIN usuarios_a u ON t.id_user = u.id_user";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE (t.paciente LIKE :search OR t.token LIKE :search OR t.cpf LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Conta o total de registros por usuário (Contexto Meus Tokens)
     */
    public function countByUserId($userId, $search = '')
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} t WHERE t.id_user = :id_user";
        $params = [':id_user' => $userId];

        if (!empty($search)) {
            $sql .= " AND (t.paciente LIKE :search OR t.token LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getAllWithDetails($limit = 25, $search = '', $offset = 0)
    {
        $sql = "SELECT t.id_token, t.token, t.paciente, t.cpf, t.valor, t.formapag, t.nome_banco, t.vencimento, t.origem, t.nome_resp, t.{$this->colunaData} as data_registro, p.nome as nome_profissional, u.nome as nome_usuario 
                FROM {$this->table} t 
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof 
                LEFT JOIN usuarios_a u ON t.id_user = u.id_user";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE (t.paciente LIKE :search OR t.token LIKE :search OR t.cpf LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Adicionado OFFSET
        $sql .= " ORDER BY t.{$this->colunaData} DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        if (!empty($search)) {
            $stmt->bindValue(':search', $params[':search']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT); // Bind do Offset

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserId($userId, $limit = 25, $search = '', $offset = 0)
    {
        $sql = "SELECT t.id_token as id, t.token, t.paciente as nome_paciente, t.vencimento, t.valor, t.origem, t.nome_resp, t.{$this->colunaData} as data_registro, p.nome as nome_profissional 
                FROM {$this->table} t 
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof 
                WHERE t.id_user = :id_user";

        $params = [':id_user' => $userId];

        if (!empty($search)) {
            $sql .= " AND (t.paciente LIKE :search OR t.token LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Adicionado OFFSET
        $sql .= " ORDER BY t.{$this->colunaData} DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_user', $userId);

        if (!empty($search)) {
            $stmt->bindValue(':search', $params[':search']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT); // Bind do Offset

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ... (Métodos create, getReportData, getById, getSessoes, etc. mantidos iguais) ...

    public function getReportData($startDate, $endDate, $profId = null, $filters = [])
    {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        $sql = "SELECT t.id_token, 
                       t.token, 
                       t.paciente, 
                       t.responsavel_f,
                       t.modalidade,
                       t.origem,
                       t.vencimento,
                       t.nome_banco,
                       t.valor, 
                       t.formapag, 
                       t.{$this->colunaData} as data_registro,
                       p.nome as nome_profissional, 
                       t.porcentagem
                FROM {$this->table} t
                LEFT JOIN profissionais p ON t.id_prof = p.id_prof
                WHERE t.{$this->colunaData} BETWEEN :start AND :end";

        $params = [
            ':start' => $start,
            ':end' => $end
        ];

        if (!empty($profId)) {
            $sql .= " AND t.id_prof = :profId";
            $params[':profId'] = $profId;
        }

        if (!empty($filters['responsavel_f'])) {
            $sql .= " AND t.responsavel_f LIKE :resp";
            $params[':resp'] = "%" . $filters['responsavel_f'] . "%";
        }

        if (!empty($filters['formapag'])) {
            $sql .= " AND t.formapag = :formapag";
            $params[':formapag'] = $filters['formapag'];
        }

        if (!empty($filters['nome_banco'])) {
            $sql .= " AND t.nome_banco = :banco";
            $params[':banco'] = $filters['nome_banco'];
        }

        if (!empty($filters['origem'])) {
            $sql .= " AND t.origem = :origem";
            $params[':origem'] = $filters['origem'];
        }

        $sql .= " ORDER BY t.{$this->colunaData} ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function create($data)
    {
        // Gera um token aleatório de 6 dígitos
        $tokenCode = $this->generateUniqueCode();

        $sql = "INSERT INTO {$this->table} (id_prof, id_paciente, id_user, paciente, cpf, telefone, nome_resp, responsavel_f, nome_banco, valor, porcentagem, formapag, modalidade, vencimento, origem, token) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['id_prof'],
            $data['id_paciente'] ?? null,
            $data['id_user'],
            $data['paciente'],
            $data['cpf'],
            $data['telefone'],
            $data['nome_resp'],
            $data['responsavel_f'],
            $data['nome_banco'],
            $data['valor'],
            $data['porcentagem'],
            $data['formapag'],
            $data['modalidade'],
            $data['vencimento'],
            $data['origem'],
            $tokenCode
        ]);
        return $this->conn->lastInsertId();
    }

    // public function getSessoes($idToken)
    // {
    //     $sql = "SELECT data_sessao FROM sessoes WHERE id_token = :id ORDER BY data_sessao ASC";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute([':id' => $idToken]);
    //     return $stmt->fetchAll(PDO::FETCH_COLUMN);
    // }

    public function generateUniqueCode()
    {
        $limit = 0;
        do {
            $part1 = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $part2 = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $code = $part1 . $part2;
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE token = :token");
            $stmt->execute([':token' => $code]);
            $exists = $stmt->fetchColumn() > 0;
            $limit++;
        } while ($exists && $limit < 10);
        return $code;
    }


    public function update($id, $data)
    {
        // ... (Mesma lógica do anterior, mantendo id_paciente e porcentagem) ...
        try {
            $sql = "UPDATE {$this->table} SET 
                id_prof = ?, id_paciente = ?, paciente = ?, cpf = ?, telefone = ?,
                nome_resp = ?, responsavel_f = ?, nome_banco = ?, valor = ?, porcentagem = ?,
                formapag = ?, modalidade = ?, vencimento = ?, origem = ? 
                WHERE id_token = ?";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['id_prof'],
                $data['id_paciente'] ?? null,
                $data['paciente'],
                $data['cpf'],
                $data['telefone'],
                $data['nome_resp'],
                $data['responsavel_f'],
                $data['nome_banco'],
                $data['valor'],
                $data['porcentagem'],
                $data['formapag'],
                $data['modalidade'],
                $data['vencimento'],
                $data['origem'],
                $id
            ]);
        } catch (Exception $e) {
            throw $e; // Lança o erro para saber o que aconteceu
        }
    }


    /**
     * Busca todas as sessões vinculadas a um token
     */
    public function getSessoes($idToken)
    {
        try {
            // JOIN com tokens para pegar o id_user, depois JOIN com usuarios_a para pegar o nome
            $sql = "SELECT s.*, u.nome as nome_usuario 
                    FROM {$this->tableSessoes} s
                    INNER JOIN {$this->table} t ON s.id_token = t.id_token
                    LEFT JOIN usuarios_a u ON t.id_user = u.id_user
                    WHERE s.id_token = :id 
                    ORDER BY s.data_sessao ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $idToken);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Salva (Sincroniza) as sessões
     */
    public function saveSessoes($idToken, $sessoes)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Remove TODAS as sessões antigas deste token
            $delSql = "DELETE FROM {$this->tableSessoes} WHERE id_token = :id";
            $delStmt = $this->conn->prepare($delSql);
            $delStmt->bindValue(':id', $idToken);
            $delStmt->execute();

            // 2. Insere as novas sessões
            if (!empty($sessoes) && is_array($sessoes)) {
                // AQUI: Removi 'hora_sessao' e o bind :hora
                $insSql = "INSERT INTO {$this->tableSessoes} (id_token, data_sessao) VALUES (:id, :data)";
                $insStmt = $this->conn->prepare($insSql);

                foreach ($sessoes as $sessao) {
                    $dataSessao = null;

                    // Verifica o formato do dado recebido
                    if (is_array($sessao)) {
                        // Se vier array (do editar_token), pega a chave 'data'
                        $dataSessao = $sessao['data'] ?? null;
                    } else {
                        // Se vier string direta (do gerar_token ou debug), usa o valor direto
                        $dataSessao = $sessao;
                    }

                    // Só insere se a data for válida
                    if (!empty($dataSessao)) {
                        $insStmt->bindValue(':id', $idToken);
                        $insStmt->bindValue(':data', $dataSessao);
                        $insStmt->execute();
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_token = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

 public function getRelatorioQuantitativo($dtInicio, $dtFim, $idProf = '', $origem = '')
    {
        // 1. Construção da Query Base
        // Fazemos JOIN com pacientes para obter CPF e Nome atualizados se possível
        // COALESCE: Se não tiver nome na tabela pacientes, tenta pegar da tabela tokens (legado)
        $sql = "SELECT 
                    t.id_token,
                    t.data_cadastro,
                    t.origem,
                    p.cpf,
                    COALESCE(p.nome, t.paciente) as paciente_nome
                FROM {$this->table} t
                LEFT JOIN pacientes p ON t.id_paciente = p.id_paciente
                WHERE t.data_cadastro BETWEEN :dt_inicio AND :dt_fim";

        $params = [
            ':dt_inicio' => $dtInicio . ' 00:00:00',
            ':dt_fim'    => $dtFim . ' 23:59:59'
        ];

        // 2. Filtros Dinâmicos
        if (!empty($idProf)) {
            $sql .= " AND t.id_prof = :id_prof";
            $params[':id_prof'] = $idProf;
        }

        if (!empty($origem)) {
            // Usa LIKE para ser mais flexível (ex: 'Google' encontra 'Google Ads')
            $sql .= " AND t.origem LIKE :origem";
            $params[':origem'] = "%{$origem}%";
        }

        // Ordena por data para processar os mais antigos primeiro (ou recentes, conforme preferência)
        $sql .= " ORDER BY t.data_cadastro ASC";

        // Executa a consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $todosTokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Lógica de Desduplicação (Pacientes Únicos)
        $relatorioUnico = [];
        $cpfsProcessados = [];
        $nomesProcessados = [];

        foreach ($todosTokens as $token) {
            $cpf = $token['cpf'];
            $nome = trim($token['paciente_nome']); // Remove espaços extras
            
            // Validação básica: se não tem nome, ignora
            if (empty($nome)) continue;

            $jaProcessado = false;

            // Verificação por CPF (Prioritária)
            if (!empty($cpf)) {
                if (in_array($cpf, $cpfsProcessados)) {
                    $jaProcessado = true;
                } else {
                    $cpfsProcessados[] = $cpf;
                }
            } 
            // Verificação por Nome (Fallback caso não tenha CPF)
            else {
                // Normaliza o nome para evitar duplicatas por Maiúsculas/Minúsculas
                $nomeNormalizado = mb_strtolower($nome);
                if (in_array($nomeNormalizado, $nomesProcessados)) {
                    $jaProcessado = true;
                } else {
                    $nomesProcessados[] = $nomeNormalizado;
                }
            }

            // Se não foi processado ainda, adiciona ao relatório final
            if (!$jaProcessado) {
                $relatorioUnico[] = [
                    'paciente' => $nome, // Nome formatado original
                    'origem'   => $token['origem'] ?: 'Não Informado',
                    'data'     => $token['data_cadastro']
                ];
            }
        }

        // 4. Ordenação Final por Nome do Paciente (A-Z) para exibição na tabela
        usort($relatorioUnico, function($a, $b) {
            return strcasecmp($a['paciente'], $b['paciente']);
        });

        return $relatorioUnico;
    }
}
