<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\PatientModel;

class PatientController extends BaseController
{
    private $patientModel;

    public function __construct()
    {
        if (!Auth::isLogged()) {
            header('Location: /login');
            exit;
        }
        $this->patientModel = new PatientModel();
    }

    // LISTAR TODOS OS PACIENTES
    public function index()
    {
        $userId = Auth::id();
        $pacientes = $this->patientModel->getByUserId($userId);
        $this->view('pages/pacientes_lista', ['pacientes' => $pacientes]);
    }

    // ABRIR FORMULÁRIO DE CADASTRO (VAZIO)
    public function create()
    {
        $this->view('pages/cadastro_paciente');
    }

    // ABRIR FORMULÁRIO DE EDIÇÃO (PREENCHIDO)
    public function edit($id)
    {
        $paciente = $this->patientModel->find($id);

        // Segurança: Verificar se o paciente pertence ao usuário logado (opcional, mas recomendado)
        // if ($paciente['user_id'] != Auth::user()['id']) { ... redirecionar ... }

        if (!$paciente) {
            $_SESSION['error'] = "Paciente não encontrado.";
            header('Location: /pacientes');
            exit;
        }

        // Carrega a mesma view, mas passa os dados do paciente
        $this->view('pages/cadastro_paciente', ['paciente' => $paciente]);
    }

    // SALVAR (INSERIR OU ATUALIZAR)
    public function store()
    {
        $userId = Auth::id();
        
        // Dados do formulário
        $data = $_POST;
        $data['user_id'] = $userId;
        
        // Se tiver ID no POST, é uma ATUALIZAÇÃO
        if (!empty($data['id_paciente'])) {
            $id = $data['id_paciente'];
            if ($this->patientModel->update($id, $data)) {
                $_SESSION['success'] = "Paciente atualizado com sucesso!";
            } else {
                $_SESSION['error'] = "Erro ao atualizar paciente.";
            }
        } else {
            // Se não tiver ID, é um NOVO cadastro
            if ($this->patientModel->create($data)) {
                $_SESSION['success'] = "Paciente cadastrado com sucesso!";
            } else {
                $_SESSION['error'] = "Erro ao cadastrar paciente.";
            }
        }

        header('Location: /pacientes'); // Redireciona para a lista
        exit;
    }

    // DELETAR
    public function delete($id)
    {
        $userId = Auth::id();
        
        if ($this->patientModel->delete($id, $userId)) {
            $_SESSION['success'] = "Paciente excluído com sucesso.";
        } else {
            $_SESSION['error'] = "Erro ao excluir paciente.";
        }

        header('Location: /pacientes');
        exit;
    }
}