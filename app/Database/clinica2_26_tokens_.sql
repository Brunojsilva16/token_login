-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 07/01/2026 às 17:59
-- Versão do servidor: 8.0.44-cll-lve
-- Versão do PHP: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `clinica2_26_tokens`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cpf` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `genero` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_responsavel` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `responsavel_financeiro` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cep` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `complemento` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bairro` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `origem` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tags` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_general_ci,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `profissionais`
--

CREATE TABLE `profissionais` (
  `id_prof` int UNSIGNED NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `especialidade` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registro` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `porcentagem` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `profissionais`
--

INSERT INTO `profissionais` (`id_prof`, `nome`, `especialidade`, `registro`, `ativo`, `porcentagem`) VALUES
(102, 'Daniella Paes Barreto Bezerra', NULL, NULL, 1, 70.00),
(104, 'Talita Pacheco dos Santos', NULL, NULL, 1, 70.00),
(106, 'Karla Rodrigues', NULL, NULL, 1, 70.00),
(107, 'Dayane Souza Silva', NULL, NULL, 1, 80.00),
(108, 'Thaís Leão', NULL, NULL, 1, 80.00),
(109, 'Luan Henrique da Silva Arruda', NULL, NULL, 1, 70.00),
(114, 'Tatiane Silva de Moura', NULL, NULL, 1, 70.00),
(115, 'Jocelma Maria Andrade Marins', NULL, NULL, 1, 70.00),
(116, 'Giselle Mendonça de Medeiros', NULL, NULL, 1, 70.00),
(117, 'Iara Cysneiros Silva', NULL, NULL, 1, 80.00),
(118, 'Andreza Patricia Machado Pontes', NULL, 'CRFa4-11099', 1, 80.00),
(119, 'Paulo de Tarso Melo', NULL, '0213928', 1, 80.00),
(120, 'Beatriz Costa Praxedes', NULL, 'CREFITO 1937-6 TO', 1, 70.00),
(121, 'Ana Cristina Cavalcante Belfort', NULL, 'CRP/02-27328', 1, 70.00),
(122, 'Adriana Bezerra', NULL, '02/27302', 1, 75.00),
(124, 'Gabriela Agra', NULL, '02/26670', 1, 70.00),
(125, 'Stephanny Tavares Ferreira', NULL, '02/25667', 1, 70.00),
(126, 'Gabriela Grangeiro Dias', NULL, '02/20360', 1, 75.00),
(127, 'Dalila Dos Reis Gomes', NULL, '02/26111', 1, 70.00),
(128, 'Vanessa Rodrigues Barbosa', NULL, '02/26563', 1, 70.00),
(129, 'Rochanne Sonely de Lima Farias', NULL, '02/26032', 1, 80.00),
(130, 'Monike Maciel Barros Pontes', NULL, '0228888', 1, 75.00),
(132, 'Pedro Cerqueira Russo', NULL, 'CRM 22086', 1, 92.00),
(135, 'Raissa Guerra de Magalhães Melo', NULL, '02/30050', 1, 70.00),
(136, 'Augusto César Cordeiro Galindo', NULL, '02/22179', 1, 70.00),
(137, 'Nathália Karla Souza Cavalcanti', NULL, '02/23103', 1, 70.00),
(138, 'Rodolfo Cunha', NULL, NULL, 1, 75.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessoes`
--

CREATE TABLE `sessoes` (
  `id_sessao` int NOT NULL,
  `id_token` int UNSIGNED NOT NULL,
  `data_sessao` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tokens`
--

CREATE TABLE `tokens` (
  `id_token` int UNSIGNED NOT NULL,
  `token` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `id_prof` int UNSIGNED NOT NULL,
  `id_paciente` int DEFAULT NULL,
  `paciente` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cpf` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_resp` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `responsavel_f` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `origem` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_banco` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT '0.00',
  `formapag` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modalidade` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vencimento` date DEFAULT NULL,
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `porcentagem` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_a`
--

CREATE TABLE `usuarios_a` (
  `id_user` int UNSIGNED NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nivel` int DEFAULT '1',
  `status` int DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_a`
--

INSERT INTO `usuarios_a` (`id_user`, `nome`, `email`, `senha`, `nivel`, `status`, `data_cadastro`) VALUES
(1, 'Assista', 'assistacentro@gmail.com', '$2y$10$AVzSTZyQSVuIVgQG5E9/muCuhvQKB8BCfdM5SlPST56YZjIVpZPhO', 2, 1, '2022-10-25 00:00:00'),
(3, 'Bruno Silva', 'brunojsilvasuporte@gmail.com', '$2y$10$yCmqRlvApJKWOysAldubW.KqyEB8HnrkRji9Vb17XvltZbomcRpgC', 3, 1, '2022-11-09 00:00:00'),
(6, 'Cecilia Campos', 'ceciduda1999@gmail.com', '$2y$10$yhsbkh0DW0ba.AgDie9xfe/eLdhBRhy02KoCB6ir4mi0QsWN1kFMe', 2, 1, '2023-08-15 00:00:00'),
(7, 'Paulo de Tarso', 'paulopsimelo@gmail.com', '$2y$10$iCqD6v1sWFs66VPcOldLG./1hOaqaAx0KTdCogReTh3O3W5b0GW4u', 2, 1, '2024-04-12 00:00:00'),
(8, 'Karen Araújo', 'andradekaren94@gmail.com', '$2y$10$rzhq.wjn0L6Kxu4WSeJ4/.mjK6oYV4aP7DTF09kvBOcEhl/sz1Su6', 2, 1, '2024-05-13 00:00:00'),
(9, 'Alexciane Beatriz Vieira Paixão', 'alexcianebeatriz@hotmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(10, 'Daniella Paes Barreto Bezerra', 'daniellapaesbarreto1011@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(11, 'Talita Pacheco dos Santos', 'talitapacheco.psi@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(12, 'Karla Rodrigues', 'karlinharo08@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(13, 'Dayane Souza Silva', 'dayane.souzapsi@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(14, 'Thaís Leão', 'thaispatricia_2008@hotmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(15, 'Luan Henrique da Silva Arruda', 'luanhenriquepe@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(16, 'Tatiane Silva de Moura', 'mouratatiane11@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(17, 'Jocelma Maria Andrade Marins', 'psicojocelmamarins@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(18, 'Giselle Mendonça de Medeiros', 'gisellemdias.psi@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(19, 'Iara Cysneiros Silva', 'iaracysneirospsi@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(20, 'Andreza Patricia Machado Pontes', 'andrezapontesfono@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(22, 'Beatriz Costa Praxedes', 'beatrizpraxedes@hotmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(23, 'Ana Cristina Cavalcante Belfort', 'cristinabelfort.psi@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(24, 'Adriana Bezerra', 'psi.adriana.bezerra@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(25, 'Gabriela Agra', 'gabrielaagra@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(26, 'Stephanny Tavares Ferreira', 'psi.stephannytavares@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(27, 'Gabriela Grangeiro Dias', 'psi.gabrielagrangeiro@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(28, 'Dalila Dos Reis Gomes', 'psicologadalilareis@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(29, 'Vanessa Rodrigues Barbosa', 'vanessa.rbarbosa@outlook.com.br', '', 1, 1, '2025-12-11 18:21:16'),
(30, 'Rochanne Sonely de Lima Farias', 'psirochannesonely@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(31, 'Monike Maciel Barros Pontes', 'psimonikepontes@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(32, 'Pedro Cerqueira Russo', 'pedro.crusso@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(33, 'Amanda Morais Rodrigues', 'amandamoraisnutricionista@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(34, 'Raissa Guerra de Magalhães Melo', 'psi.raissaguerra@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(35, 'Augusto César Cordeiro Galindo', 'psiaugustocordeiro@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(36, 'Nathália Karla Souza Cavalcanti', 'nathaliacavpsicologia@gmail.com', '', 1, 1, '2025-12-11 18:21:16'),
(37, 'Rodolfo Cunha', 'psirodolfocunha@gmail.com', '', 1, 1, '2025-12-11 18:21:16');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`);

--
-- Índices de tabela `profissionais`
--
ALTER TABLE `profissionais`
  ADD PRIMARY KEY (`id_prof`);

--
-- Índices de tabela `sessoes`
--
ALTER TABLE `sessoes`
  ADD PRIMARY KEY (`id_sessao`),
  ADD KEY `fk_sessao_token` (`id_token`);

--
-- Índices de tabela `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id_token`),
  ADD KEY `idx_token_user` (`id_user`),
  ADD KEY `idx_token_prof` (`id_prof`),
  ADD KEY `fk_tokens_paciente` (`id_paciente`);

--
-- Índices de tabela `usuarios_a`
--
ALTER TABLE `usuarios_a`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `profissionais`
--
ALTER TABLE `profissionais`
  MODIFY `id_prof` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT de tabela `sessoes`
--
ALTER TABLE `sessoes`
  MODIFY `id_sessao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id_token` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios_a`
--
ALTER TABLE `usuarios_a`
  MODIFY `id_user` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `sessoes`
--
ALTER TABLE `sessoes`
  ADD CONSTRAINT `fk_sessoes_token` FOREIGN KEY (`id_token`) REFERENCES `tokens` (`id_token`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `fk_tokens_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tokens_prof` FOREIGN KEY (`id_prof`) REFERENCES `profissionais` (`id_prof`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tokens_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios_a` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
