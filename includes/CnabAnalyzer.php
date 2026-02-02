<?php

class CnabAnalyzer
{

    public function analyzeFile($filePath, $originalName, $bankCode = null)
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if (empty($lines)) {
            return ['error' => 'Arquivo vazio'];
        }

        // Detect Layout (240 or 400) based on first line length
        $firstLine = $lines[0];
        $length = strlen(rtrim($firstLine));

        if ($length == 240) {
            $type = 'CNAB240';
            $fileBankCode = substr($firstLine, 0, 3);
        } elseif ($length == 400) {
            $type = 'CNAB400';
            $fileBankCode = substr($firstLine, 76, 3);
        } else {
            // Attempt pattern matching for files with trimmed trailing spaces or encoding issues
            // Pattern CNAB 240: BBB LLLL T (Banco, Lote, Tipo)
            // Lote 0000 and Tipo 0 = Header Arquivo
            $possibleBank = substr($firstLine, 0, 3);
            $possibleLote = substr($firstLine, 3, 4);
            $possibleTipo = substr($firstLine, 7, 1);

            if (is_numeric($possibleBank) && $possibleLote === '0000' && $possibleTipo === '0') {
                $type = 'CNAB240';
                $fileBankCode = $possibleBank;
            }

            // Pattern CNAB 400 Header: 0 (ID) 1 (Op) REMESSA (Literal)
            // Starts with "01REMESSA" or "02RETORNO"
            $cnab400Start = substr($firstLine, 0, 9); // 0 + 1 + 7
            // Check if pos 0 is '0' and matches literal pattern or just specific positions
            if (substr($firstLine, 0, 1) === '0' && (strpos($firstLine, 'REMESSA') !== false || strpos($firstLine, 'RETORNO') !== false)) {
                $type = 'CNAB400';
                // CNAB 400 bank code is usually at 76-79
                if (strlen($firstLine) >= 79) {
                    $fileBankCode = substr($firstLine, 76, 3);
                }
            }
        }

        // Warning: Some CNAB400 might be slightly shifted or different standards, 
        // but 76-79 is standard for BB, Bradesco, etc.

        // Validate Bank Compatibility only if specific bank is required
        if ($bankCode !== null && $type != 'DESCONHECIDO') {
            // Trim codes to ensure string comparison works (e.g. "001" vs "1")
            if ($fileBankCode !== $bankCode) {
                return ['error' => "Banco selecionado ($bankCode) incompatível com o arquivo (Banco: $fileBankCode)"];
            }
        }

        $parsedLines = [];
        $lineCount = 0;

        foreach ($lines as $index => $line) {
            $lineCount++;
            // Limit preview to first 50 lines + last 5 lines to avoid memory issues on huge files
            if ($lineCount > 50 && $lineCount < count($lines) - 5)
                continue;

            $line = rtrim($line); // Remove newline chars
            $parsedLines[] = $this->parseLine($line, $type, $index + 1);
        }

        return [
            'filename' => $originalName,
            'type' => $type,
            'bank_in_file' => $fileBankCode,
            'total_lines' => count($lines),
            'preview_lines' => $parsedLines
        ];
    }

    private function parseLine($line, $type, $lineNumber)
    {
        $data = [
            'number' => $lineNumber,
            'content' => $line,
            'fields' => []
        ];

        if ($type == 'CNAB400') {
            $data['fields'] = $this->parseCNAB400($line, $lineNumber);
        } elseif ($type == 'CNAB240') {
            $data['fields'] = $this->parseCNAB240($line, $lineNumber);
        } else {
            $data['fields'][] = ['pos' => '1-' . strlen($line), 'name' => 'Conteúdo Não Reconhecido', 'value' => $line];
        }

        return $data;
    }


    // Helper to format field array with start/end indices and validation
    private function makeField($start, $end, $name, $line, $type = 'string')
    {
        // CNAB positions are 1-based, substr is 0-based.
        $startIndex = $start - 1;
        $length = $end - $start + 1;

        $value = substr($line, $startIndex, $length);
        $isValid = true;
        $validationMessage = '';

        // Validation Logic
        if ($type === 'numeric') {
            if (!is_numeric($value)) {
                $isValid = false;
                $validationMessage = "Conforme layout precisa ser Numérico ({$length} dígitos). Recebido: '{$value}'";
            }
        } elseif ($type === 'date') {
            // Basic date check (length 6 or 8 and numeric)
            // Could add valid date logic (checkdate) later
            if (!ctype_digit($value) || (strlen($value) !== 8 && strlen($value) !== 6)) {
                $isValid = false;
                $validationMessage = "Conforme layout precisa ser Data (DDMMAAAA). Recebido: '{$value}'";
            } elseif (strlen($value) === 8) {
                $d = substr($value, 0, 2);
                $m = substr($value, 2, 2);
                $y = substr($value, 4, 4);
                if (!checkdate($m, $d, $y)) {
                    $isValid = false;
                    $validationMessage = "Data inexistente (DDMMAAAA). Recebido: '{$value}'";
                }
            }
        }

        return [
            'pos' => str_pad($start, 3, '0', STR_PAD_LEFT) . '-' . str_pad($end, 3, '0', STR_PAD_LEFT),
            'start' => $start,
            'end' => $end,
            'name' => $name,
            'value' => $value,
            'type' => $type,
            'valid' => $isValid,
            'error' => $validationMessage
        ];
    }

    private function parseCNAB400($line, $num)
    {
        $id = substr($line, 0, 1);
        $fields = [];

        if ($id == '0') { // Header
            $fields[] = $this->makeField(1, 1, 'Identificação do Registro', $line);
            $fields[] = $this->makeField(2, 2, 'Tipo de Operação', $line);
            $fields[] = $this->makeField(3, 9, 'Literal Remessa', $line);
            $fields[] = $this->makeField(10, 11, 'Código do Serviço', $line);
            $fields[] = $this->makeField(12, 26, 'Literal Serviço', $line);
            $fields[] = $this->makeField(27, 46, 'Código da Empresa', $line);
            $fields[] = $this->makeField(47, 76, 'Nome da Empresa', $line);
            $fields[] = $this->makeField(77, 79, 'Número do Banco', $line);
            $fields[] = $this->makeField(95, 100, 'Data de Gravação', $line);
            $fields[] = $this->makeField(395, 400, 'Sequencial do Registro', $line);
        } elseif ($id == '1') { // Detalhe
            $fields[] = $this->makeField(1, 1, 'Identificação do Registro', $line);
            $fields[] = $this->makeField(2, 3, 'Tipo de Inscrição (Cedente)', $line);
            $fields[] = $this->makeField(4, 17, 'CNPJ/CPF (Cedente)', $line);
            $fields[] = $this->makeField(38, 62, 'Nr. Controle do Participante', $line);
            $fields[] = $this->makeField(71, 81, 'Nosso Número', $line);
            $fields[] = $this->makeField(108, 108, 'Carteira', $line);
            $fields[] = $this->makeField(109, 110, 'Ocorrência', $line);
            $fields[] = $this->makeField(111, 120, 'Nr. do Documento', $line);
            $fields[] = $this->makeField(121, 126, 'Vencimento', $line);
            $fields[] = $this->makeField(127, 139, 'Valor do Título', $line);
            $fields[] = $this->makeField(395, 400, 'Sequencial do Registro', $line);
        } elseif ($id == '9') { // Trailer
            $fields[] = $this->makeField(1, 1, 'Identificação do Registro', $line);
            $fields[] = $this->makeField(395, 400, 'Sequencial do Registro', $line);
        } else {
            $fields[] = $this->makeField(1, 1, 'Identificação', $line);
            $fields[] = $this->makeField(2, 400, 'Conteúdo não mapeado', $line);
        }

        return $fields;
    }

    private function parseCNAB240($line, $num)
    {
        $tipoRegistro = substr($line, 7, 1);
        $fields = [];

        // Common Header for all lines (Pos 1-8)
        $fields[] = $this->makeField(1, 3, 'Banco', $line);
        $fields[] = $this->makeField(4, 7, 'Lote', $line);
        $fields[] = $this->makeField(8, 8, 'Tipo Registro', $line);

        if ($tipoRegistro == '0') { // Header Arquivo
            $fields[] = $this->makeField(9, 17, 'Reservado', $line);
            $fields[] = $this->makeField(18, 18, 'Tipo Inscrição', $line, 'numeric');
            $fields[] = $this->makeField(19, 32, 'Nr. Inscrição', $line, 'numeric');
            $fields[] = $this->makeField(33, 52, 'Convênio', $line);
            // Sicoob: Agência/Conta info (53-72)
            $fields[] = $this->makeField(53, 57, 'Agência', $line, 'numeric');
            $fields[] = $this->makeField(58, 58, 'DV Agência', $line);
            $fields[] = $this->makeField(59, 70, 'Conta Corrente', $line, 'numeric');
            $fields[] = $this->makeField(71, 71, 'DV Conta', $line, 'numeric');
            $fields[] = $this->makeField(72, 72, 'DV Ag/Conta', $line);

            $fields[] = $this->makeField(73, 102, 'Nome da Empresa', $line);
            $fields[] = $this->makeField(103, 132, 'Nome do Banco', $line);
            $fields[] = $this->makeField(133, 142, 'Reservado', $line);
            $fields[] = $this->makeField(143, 143, 'Código Rem/Ret', $line, 'numeric');
            $fields[] = $this->makeField(144, 151, 'Data Geração', $line, 'date');
            $fields[] = $this->makeField(152, 157, 'Hora Geração', $line, 'numeric');
            $fields[] = $this->makeField(158, 163, 'Nr. Sequencial Arquivo', $line, 'numeric');
            $fields[] = $this->makeField(164, 166, 'Versão Layout', $line, 'numeric');
            $fields[] = $this->makeField(167, 171, 'Densidade', $line, 'numeric');
            $fields[] = $this->makeField(172, 240, 'Reservado', $line);

        } elseif ($tipoRegistro == '1') { // Header Lote
            $fields[] = $this->makeField(9, 9, 'Tipo Operação', $line);
            $fields[] = $this->makeField(10, 11, 'Tipo Serviço', $line, 'numeric');
            $fields[] = $this->makeField(12, 13, 'Forma Lançamento / Versão', $line, 'numeric'); // Sicoob usually 00 or Versão
            $fields[] = $this->makeField(14, 16, 'Versão Layout Lote', $line, 'numeric');
            $fields[] = $this->makeField(17, 17, 'Reservado', $line);
            $fields[] = $this->makeField(18, 18, 'Tipo Inscrição', $line, 'numeric');
            $fields[] = $this->makeField(19, 33, 'Inscrição Empresa', $line, 'numeric');
            $fields[] = $this->makeField(34, 53, 'Convênio', $line);
            // Sicoob Agência/Conta (54-73)
            $fields[] = $this->makeField(54, 58, 'Agência', $line, 'numeric');
            $fields[] = $this->makeField(59, 59, 'DV Agência', $line);
            $fields[] = $this->makeField(60, 71, 'Conta Corrente', $line, 'numeric');
            $fields[] = $this->makeField(72, 72, 'DV Conta', $line, 'numeric');
            $fields[] = $this->makeField(73, 73, 'DV Ag/Conta', $line);

            $fields[] = $this->makeField(74, 103, 'Nome Empresa', $line);
            $fields[] = $this->makeField(104, 143, 'Mensagem 1', $line);
            $fields[] = $this->makeField(144, 183, 'Mensagem 2', $line);
            $fields[] = $this->makeField(184, 191, 'Nr. Remessa/Retorno', $line, 'numeric');
            $fields[] = $this->makeField(192, 199, 'Data Gravação', $line, 'date');
            $fields[] = $this->makeField(200, 207, 'Data Crédito', $line, 'date');
            $fields[] = $this->makeField(208, 240, 'Reservado', $line);

        } elseif ($tipoRegistro == '3') { // Detalhe
            $segmento = substr($line, 13, 1);
            $fields[] = $this->makeField(9, 13, 'Nr. Sequencial Reg. Lote', $line, 'numeric');
            $fields[] = $this->makeField(14, 14, 'Segmento', $line);

            if ($segmento == 'P') {
                $fields[] = $this->makeField(15, 15, 'Reservado', $line);
                $fields[] = $this->makeField(16, 17, 'Código Movimento', $line, 'numeric');
                $fields[] = $this->makeField(18, 22, 'Agência', $line, 'numeric');
                $fields[] = $this->makeField(23, 23, 'DV Agência', $line);
                $fields[] = $this->makeField(24, 35, 'Conta Corrente', $line, 'numeric');
                $fields[] = $this->makeField(36, 36, 'DV Conta', $line, 'numeric');
                $fields[] = $this->makeField(37, 37, 'DV Ag/Conta', $line);
                $fields[] = $this->makeField(38, 57, 'Nosso Número', $line);
                $fields[] = $this->makeField(58, 58, 'Carteira', $line, 'numeric');
                $fields[] = $this->makeField(59, 59, 'Cadastramento', $line, 'numeric');
                $fields[] = $this->makeField(60, 60, 'Documento', $line);
                $fields[] = $this->makeField(61, 61, 'Emissão Boleto', $line);
                $fields[] = $this->makeField(62, 62, 'Distribuição', $line);
                $fields[] = $this->makeField(63, 77, 'Número do Documento', $line);
                $fields[] = $this->makeField(78, 85, 'Vencimento', $line, 'date');
                $fields[] = $this->makeField(86, 100, 'Valor do Título', $line, 'numeric');
                $fields[] = $this->makeField(101, 105, 'Agência Cobradora', $line, 'numeric');
                $fields[] = $this->makeField(106, 106, 'DV Agência Cobradora', $line);
                $fields[] = $this->makeField(107, 108, 'Espécie Título', $line, 'numeric');
                $fields[] = $this->makeField(109, 109, 'Aceite', $line);
                $fields[] = $this->makeField(110, 117, 'Data Emissão', $line, 'date');
                $fields[] = $this->makeField(118, 118, 'Cód. Juros', $line, 'numeric');
                $fields[] = $this->makeField(119, 126, 'Data Juros', $line, 'date');
                $fields[] = $this->makeField(127, 141, 'Valor Juros/Dia', $line, 'numeric');
                $fields[] = $this->makeField(142, 142, 'Cód. Desc. 1', $line, 'numeric');
                $fields[] = $this->makeField(143, 150, 'Data Desc. 1', $line, 'date');
                $fields[] = $this->makeField(151, 165, 'Valor/Perc Desc. 1', $line, 'numeric');
                $fields[] = $this->makeField(166, 180, 'Valor IOF', $line, 'numeric');
                $fields[] = $this->makeField(181, 195, 'Valor Abatimento', $line, 'numeric');
                $fields[] = $this->makeField(196, 220, 'Identificação Título Empresa', $line);
                $fields[] = $this->makeField(221, 221, 'Cód. Protesto', $line, 'numeric');
                $fields[] = $this->makeField(222, 223, 'Prazo Protesto', $line, 'numeric');
                $fields[] = $this->makeField(224, 224, 'Cód. Baixa/Devolução', $line, 'numeric');
                $fields[] = $this->makeField(225, 227, 'Prazo Baixa/Dev', $line, 'numeric');
                $fields[] = $this->makeField(228, 229, 'Cód. Moeda', $line, 'numeric');
                $fields[] = $this->makeField(230, 239, 'Contrato Banco', $line, 'numeric');
                $fields[] = $this->makeField(240, 240, 'Reservado', $line);

            } elseif ($segmento == 'Q') {
                $fields[] = $this->makeField(15, 15, 'Reservado', $line);
                $fields[] = $this->makeField(16, 17, 'Código Movimento', $line, 'numeric');
                $fields[] = $this->makeField(18, 18, 'Tipo Inscrição Sacado', $line, 'numeric');
                $fields[] = $this->makeField(19, 33, 'Inscrição (CPF/CNPJ)', $line, 'numeric');
                $fields[] = $this->makeField(34, 73, 'Nome do Sacado', $line);
                $fields[] = $this->makeField(74, 113, 'Endereço', $line);
                $fields[] = $this->makeField(114, 128, 'Bairro', $line);
                $fields[] = $this->makeField(129, 133, 'CEP', $line, 'numeric');
                $fields[] = $this->makeField(134, 136, 'Sufixo CEP', $line, 'numeric');
                $fields[] = $this->makeField(137, 151, 'Cidade', $line);
                $fields[] = $this->makeField(152, 153, 'UF', $line);
                $fields[] = $this->makeField(154, 154, 'Tipo Inscrição Avalista', $line, 'numeric');
                $fields[] = $this->makeField(155, 169, 'Inscrição Avalista', $line, 'numeric');
                $fields[] = $this->makeField(170, 209, 'Nome Avalista', $line);
                $fields[] = $this->makeField(210, 212, 'Cód. Banco Corresp.', $line, 'numeric');
                $fields[] = $this->makeField(213, 232, 'Nosso Nº Banco Corresp.', $line, 'numeric');
                $fields[] = $this->makeField(233, 240, 'Reservado', $line);

            } elseif ($segmento == 'R') {
                $fields[] = $this->makeField(15, 15, 'Reservado', $line);
                $fields[] = $this->makeField(16, 17, 'Código Movimento', $line, 'numeric');
                $fields[] = $this->makeField(18, 18, 'Código Desconto 2', $line, 'numeric');
                $fields[] = $this->makeField(19, 26, 'Data Desconto 2', $line, 'date');
                $fields[] = $this->makeField(27, 41, 'Valor Desconto 2', $line, 'numeric');
                $fields[] = $this->makeField(42, 42, 'Código Desconto 3', $line, 'numeric');
                $fields[] = $this->makeField(43, 50, 'Data Desconto 3', $line, 'date');
                $fields[] = $this->makeField(51, 65, 'Valor Desconto 3', $line, 'numeric');
                $fields[] = $this->makeField(66, 66, 'Cód. Multa', $line, 'numeric');
                $fields[] = $this->makeField(67, 74, 'Data Multa', $line, 'date');
                $fields[] = $this->makeField(75, 89, 'Valor/Perc Multa', $line, 'numeric');
                $fields[] = $this->makeField(90, 99, 'Informação ao Sacado', $line);
                $fields[] = $this->makeField(100, 139, 'Mensagem 3', $line);
                $fields[] = $this->makeField(140, 179, 'Mensagem 4', $line);
                $fields[] = $this->makeField(180, 240, 'Reservado', $line);
            } else {
                $fields[] = $this->makeField(15, 240, 'Dados do Segmento ' . $segmento, $line);
            }

        } elseif ($tipoRegistro == '5') { // Trailer Lote
            $fields[] = $this->makeField(9, 17, 'Reservado', $line);
            $fields[] = $this->makeField(18, 23, 'Qtd Registros Lote', $line, 'numeric');
            $fields[] = $this->makeField(24, 41, 'Total Cobrança (Simples/Caucionada)', $line, 'numeric');
            $fields[] = $this->makeField(42, 59, 'Qtd Cobrança (Vinculada)', $line, 'numeric');
            $fields[] = $this->makeField(60, 65, 'Qtd Cobrança (Caucionada)', $line, 'numeric');
            $fields[] = $this->makeField(66, 83, 'Total Cobrança (Caucionada)', $line, 'numeric');
            $fields[] = $this->makeField(84, 98, 'Reservado', $line);
            $fields[] = $this->makeField(99, 240, 'Reservado', $line);

        } elseif ($tipoRegistro == '9') { // Trailer Arquivo
            $fields[] = $this->makeField(9, 17, 'Reservado', $line);
            $fields[] = $this->makeField(18, 23, 'Qtd Lotes', $line, 'numeric');
            $fields[] = $this->makeField(24, 29, 'Qtd Registros Arquivo', $line, 'numeric');
            $fields[] = $this->makeField(30, 35, 'Qtd Contas Conciliadas', $line, 'numeric');
            $fields[] = $this->makeField(36, 240, 'Reservado', $line);
        }

        return $fields;
    }
}
