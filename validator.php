<?php
// --- PHP: LÓGICA DE PROCESSAMENTO (DO USUÁRIO) ---

// Helper to remove BOM and clean content
function cleanContent($content) {
    // Remove BOM
    $bom = pack('H*','EFBBBF');
    $content = preg_replace("/^$bom/", '', $content);
    
    // Ensure UTF-8
    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
    }
    
    return $content;
}

// Define Validation Rules
$validationRules = [
    // --- EXPORTAÇÃO ---
    'OP' => [
        'name' => 'Operação (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Data", 'type' => 'D', 'maxLength' => 8, 'required' => false],
            ['name' => "Operador", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Ecf", 'type' => 'N', 'maxLength' => 3, 'required' => false],
            ['name' => "COO", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "CCFGNF", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "Hora inicial", 'type' => 'T', 'maxLength' => 14, 'required' => false],
            ['name' => "Hora final", 'type' => 'T', 'maxLength' => 14, 'required' => false],
            ['name' => "Tipo", 'type' => 'N', 'maxLength' => 2, 'required' => false],
            ['name' => "Histórico", 'type' => 'C', 'maxLength' => 60, 'required' => false],
            ['name' => "Cancelado", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Supervisor cancelamento", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Hora cancelamento", 'type' => 'T', 'maxLength' => 14, 'required' => false],
            ['name' => "Valor bruto da operação", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor arredondamento da operação", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor desconto subtotal", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor líquido", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Motivo do desconto subtotal", 'type' => 'C', 'maxLength' => 5, 'required' => false],
            ['name' => "Supervisor desconto subtotal", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Ordem da venda manual", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Série da venda manual", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Subsérie da venda manual", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "ECF Série", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "ECF Letra adicional", 'type' => 'C', 'maxLength' => 1, 'required' => false],
            ['name' => "ECF Modelo", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "ECF Proprietário", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "ECF Marca", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "IP", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Cartão fidelidade", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Cartão fidelidade lido", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Cliente", 'type' => 'C', 'maxLength' => 14, 'required' => false],
            ['name' => "CPFCNPJ consumidor", 'type' => 'C', 'maxLength' => 18, 'required' => false],
            ['name' => "Nome consumidor", 'type' => 'C', 'maxLength' => 40, 'required' => false],
        ]
    ],
    'IT' => [
        'name' => 'Itens vendidos (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Sequência", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "Produto", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Código de barras lido", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Quantidade", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "UN", 'type' => 'C', 'maxLength' => 3, 'required' => false],
            ['name' => "Preço unitário", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor do desconto", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor do arredondamento", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor líquido", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Vendedor", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Cancelado", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Número de série", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Tributação", 'type' => 'C', 'maxLength' => 5, 'required' => false],
            ['name' => "Motivo do desconto item", 'type' => 'C', 'maxLength' => 5, 'required' => false],
            ['name' => "Supervisor desconto item", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Item digitado", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "DAV", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Grade", 'type' => 'C', 'maxLength' => 256, 'required' => false],
            ['name' => "Descrição do produto", 'type' => 'C', 'maxLength' => 256, 'required' => false],
            ['name' => "CFOP", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Valor do Frete", 'type' => 'N', 'maxLength' => 12, 'required' => false],
        ]
    ],
    'PG' => [
        'name' => 'Meios de pagamento (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Sequência", 'type' => 'N', 'maxLength' => 2, 'required' => false],
            ['name' => "Meio de pagamento", 'type' => 'N', 'maxLength' => 2, 'required' => false],
            ['name' => "Valor total", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Parcela", 'type' => 'N', 'maxLength' => 2, 'required' => false],
            ['name' => "Parcelas", 'type' => 'N', 'maxLength' => 2, 'required' => false],
            ['name' => "Valor da parcela", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Vencimento", 'type' => 'D', 'maxLength' => 8, 'required' => false],
            ['name' => "Documento", 'type' => 'C', 'maxLength' => 30, 'required' => false],
            ['name' => "Código rede tef", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Nome rede tef", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "GNF Vinculado", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "Forma de pagamento TEF", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Bandeira", 'type' => 'C', 'maxLength' => 50, 'required' => false],
        ]
    ],
    // RC Exportação (Recebimento) ignorado em favor do RC Importação (Financeiro)
    'CO' => [
        'name' => 'Cancelamento de operação (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Ecf", 'type' => 'N', 'maxLength' => 3, 'required' => false],
            ['name' => "COO", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "Data e hora do cancelamento", 'type' => 'T', 'maxLength' => 14, 'required' => false],
            ['name' => "Usuário cancelamento", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Modelo Nota", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Série Nota", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Número Nota", 'type' => 'C', 'maxLength' => 11, 'required' => false],
            ['name' => "Série Equipamento SAT", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Série Equipamento ECF", 'type' => 'C', 'maxLength' => 20, 'required' => false],
        ]
    ],
    'XL' => [
        'name' => 'XML (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "XML", 'type' => 'C', 'maxLength' => 999999, 'required' => false], // Ilimitado
        ]
    ],
    'INT' => [
        'name' => 'Inutilização (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 3, 'required' => true],
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Modelo Nota", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Série Nota", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Número Nota", 'type' => 'C', 'maxLength' => 11, 'required' => false],
        ]
    ],
    'RZ' => [
        'name' => 'Redução Z (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Reservado", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Ecf", 'type' => 'N', 'maxLength' => 3, 'required' => false],
            ['name' => "Modelo", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Número de série", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Data", 'type' => 'D', 'maxLength' => 8, 'required' => false],
            ['name' => "CRO", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "CRZ", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "GT Final", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Venda bruta", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Coo inicial", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "Coo final", 'type' => 'N', 'maxLength' => 6, 'required' => false],
        ]
    ],
    'TP' => [
        'name' => 'Totalizador da redução Z (Exportação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Reservado", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Código", 'type' => 'C', 'maxLength' => 5, 'required' => false],
            ['name' => "Valor", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Reservado", 'type' => 'N', 'maxLength' => 1, 'required' => false],
        ]
    ],

    // --- IMPORTAÇÃO ---
    'CL' => [
        'name' => 'Cliente (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Nome", 'type' => 'C', 'maxLength' => 50, 'required' => true], // Asterisco
            ['name' => "Razão Social", 'type' => 'C', 'maxLength' => 50, 'required' => true], // Asterisco
            ['name' => "CNPJ CPF", 'type' => 'C', 'maxLength' => 18, 'required' => false],
            ['name' => "Insc. Estadual", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "RG", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Endereço", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Número", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "Complemento", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Bairro", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "CEP", 'type' => 'C', 'maxLength' => 9, 'required' => false],
            ['name' => "Telefone", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Celular", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Fax", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Email", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Data nascimento", 'type' => 'D', 'maxLength' => 10, 'required' => false],
            ['name' => "Limite crédito", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Nome Contato", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Estado Civil", 'type' => 'N', 'maxLength' => 2, 'required' => false],
            ['name' => "Conjuge", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Pai", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Mãe", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Profissão", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Renda", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Tipo", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Observação", 'type' => 'C', 'maxLength' => 9999, 'required' => false],
            ['name' => "Cidade", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Estado", 'type' => 'C', 'maxLength' => 2, 'required' => true], // Asterisco
            ['name' => "Contato de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "CEP de entrega", 'type' => 'C', 'maxLength' => 9, 'required' => false],
            ['name' => "Estado de entrega", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Cidade de entrega", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Endereço de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Número de entrega", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Complemento do endereço de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Bairro de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Telefone entrega", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Celular entrega", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Fax de entrega", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Email de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Inativo", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Nome do contato de cobrança", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Cep de cobrança", 'type' => 'C', 'maxLength' => 9, 'required' => false],
            ['name' => "Estado de cobrança", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Cidade de cobrança", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Endereço de cobrança", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Número do endereço de cobrança", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Complemento de cobrança", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Bairro de cobrança", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo extra 1", 'type' => 'C', 'maxLength' => 512, 'required' => false],
            ['name' => "Campo extra 2", 'type' => 'C', 'maxLength' => 512, 'required' => false],
            ['name' => "Campo extra 3", 'type' => 'C', 'maxLength' => 512, 'required' => false],
            ['name' => "Campo extra 4", 'type' => 'C', 'maxLength' => 512, 'required' => false],
            ['name' => "Campo extra 5", 'type' => 'C', 'maxLength' => 512, 'required' => false],
            ['name' => "Campo extra 6", 'type' => 'C', 'maxLength' => 512, 'required' => false],
            ['name' => "Usuário", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Cliente", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Fornecedor", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Informação se a entidade está Bloqueada", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Id pauta de preço preferencial", 'type' => 'N', 'maxLength' => 14, 'required' => false],
            ['name' => "Código condição de pagamento preferencial", 'type' => 'C', 'maxLength' => 14, 'required' => false],
            ['name' => "Campo em branco", 'type' => 'C', 'maxLength' => 0, 'required' => false],
            ['name' => "Tipo pessoa", 'type' => 'N', 'maxLength' => 1, 'required' => false],
        ]
    ],
    'PR' => [
        'name' => 'Produto (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Referencia", 'type' => 'C', 'maxLength' => 60, 'required' => false],
            ['name' => "Código EAN", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Inativo", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Nome", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Tipo", 'type' => 'C', 'maxLength' => 1, 'required' => false],
            ['name' => "Código do fornecedor", 'type' => 'C', 'maxLength' => 14, 'required' => false],
            ['name' => "Unidade de medida", 'type' => 'C', 'maxLength' => 3, 'required' => true], // Asterisco
            ['name' => "% Lucro", 'type' => 'N', 'maxLength' => 6, 'required' => false],
            ['name' => "Preço", 'type' => 'N', 'maxLength' => 12, 'required' => true], // Asterisco
            ['name' => "Peso", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Numero de série", 'type' => 'N', 'maxLength' => 2, 'required' => false],
            ['name' => "Tributação ICMS", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "IPI", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "Situação tributaria", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Custo", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "IAT", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "IPPT", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Origem", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Grupo", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Fornecedor", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Caminho da imagem", 'type' => 'C', 'maxLength' => 200, 'required' => false],
            ['name' => "ICMS", 'type' => 'N', 'maxLength' => 4, 'required' => false],
            ['name' => "Tributação especial", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Casas decimais da unidade de medida", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Código do grupo", 'type' => 'C', 'maxLength' => 30, 'required' => false],
            ['name' => "Pesavel", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Tipo produto", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "OBS", 'type' => 'C', 'maxLength' => 9999, 'required' => false],
            ['name' => "Pauta preco1", 'type' => 'N', 'maxLength' => 15, 'required' => false],
            ['name' => "Pauta preco2", 'type' => 'N', 'maxLength' => 15, 'required' => false],
            ['name' => "Pauta preco3", 'type' => 'N', 'maxLength' => 15, 'required' => false],
            ['name' => "Pauta preco4", 'type' => 'N', 'maxLength' => 15, 'required' => false],
            ['name' => "NCM", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Tributação do Simples Nacional NF-e", 'type' => 'N', 'maxLength' => 3, 'required' => false],
            ['name' => "CST Pis/Cofins saída", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Alíquota Pis saída", 'type' => 'C', 'maxLength' => 5, 'required' => false],
            ['name' => "Alíquota Cofins saída", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "CST Pis/Cofins entrada", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Alíquota Pis entrada", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "Alíquota Cofins entrada", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "Permite informar dimensões", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "CFOP interna de entrada", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP interna de saida", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP externa de entrada", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP externa de saida", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP interna de entrada devolucao", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP interna de saida devolucao", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP externa de entrada devolução", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP externa de saida devolução", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP interna de entrada transferência", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP interna de saida transferência", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP externa de entrada transferência", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP externa de saida transferência", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "CFOP Externa de saída para não contribuinte", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Informação extra 1", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Informação extra 2", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Informação extra 3", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Informação extra 4", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Informação extra 5", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Informação extra 6", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "CEST", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Informação adicional", 'type' => 'C', 'maxLength' => 500, 'required' => false],
            ['name' => "Tributação do Simples Nacional – NFC-e ou SAT", 'type' => 'N', 'maxLength' => 3, 'required' => false],
            ['name' => "Custo médio inicial", 'type' => 'N', 'maxLength' => 15, 'required' => false],
            ['name' => "Código da Lei complementar", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Indicador da exigibilidade do ISS", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Código da Receita sem contribuição", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Situação tributária especial para NFC-e/SAT", 'type' => 'C', 'maxLength' => 3, 'required' => false],
            ['name' => "Enviar produto ao E-Commerce", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Nome PDV", 'type' => 'C', 'maxLength' => 120, 'required' => false],
            ['name' => "Descrição Uniplus Shop", 'type' => 'C', 'maxLength' => 4096, 'required' => false],
            ['name' => "Informações no Uniplus Shop", 'type' => 'C', 'maxLength' => 4096, 'required' => false],
            ['name' => "Código do fabricante", 'type' => 'C', 'maxLength' => 10, 'required' => false],
            ['name' => "Peso shop", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Altura shop", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Largura shop", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Comprimento shop", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Tipo embalagem shop", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Informação Extra Balança 1", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 2", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 3", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 4", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 5", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 6", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 7", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 8", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 9", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 10", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 11", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Informação Extra Balança 12", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Códigos Empresas", 'type' => 'C', 'maxLength' => 10, 'required' => false],
        ]
    ],
    'VR' => [
        'name' => 'Variação (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código do produto", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
            ['name' => "Descrição", 'type' => 'C', 'maxLength' => 30, 'required' => true], // Asterisco
            ['name' => "Tipo registro", 'type' => 'C', 'maxLength' => 1, 'required' => true], // Asterisco
            ['name' => "Ordem", 'type' => 'C', 'maxLength' => 5, 'required' => true], // Asterisco
            ['name' => "Código do cadastro de grade", 'type' => 'C', 'maxLength' => 100, 'required' => true], // Asterisco
        ]
    ],
    'PB' => [
        'name' => 'Código de barras do produto (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código do produto", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
            ['name' => "EAN", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
            ['name' => "Variação", 'type' => 'C', 'maxLength' => 5, 'required' => false],
        ]
    ],
    'PS' => [
        'name' => 'Produtos similares (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código do produto", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
            ['name' => "Codigo do produto similar", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
        ]
    ],
    'NCM' => [
        'name' => 'NCM (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 3, 'required' => true],
            ['name' => "Código do NCM", 'type' => 'C', 'maxLength' => 10, 'required' => true], // Asterisco
            ['name' => "Código de exceção", 'type' => 'C', 'maxLength' => 3, 'required' => true], // Asterisco
            ['name' => "Descrição", 'type' => 'C', 'maxLength' => 200, 'required' => false],
            ['name' => "Tipo", 'type' => 'C', 'maxLength' => 1, 'required' => false],
            ['name' => "Percentual do MVA", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "Percentual de Redução do MVA", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "Percentual imposto aproximado", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "Percentual imposto aprox. importação", 'type' => 'N', 'maxLength' => 5, 'required' => false],
        ]
    ],
    'EM' => [
        'name' => 'Embalagens (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código do produto", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
            ['name' => "Unidade de medida", 'type' => 'C', 'maxLength' => 3, 'required' => true], // Asterisco
            ['name' => "Fator de conversão", 'type' => 'N', 'maxLength' => 15, 'required' => true], // Asterisco
            ['name' => "Tipo da embalagem", 'type' => 'C', 'maxLength' => 1, 'required' => true], // Asterisco
            ['name' => "Preço", 'type' => 'N', 'maxLength' => 15, 'required' => true], // Asterisco
            ['name' => "EAN", 'type' => 'C', 'maxLength' => 20, 'required' => false],
        ]
    ],
    'ES' => [
        'name' => 'Saldo em estoque (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Produto", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Quantidade", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Variação", 'type' => 'C', 'maxLength' => 5, 'required' => false],
            ['name' => "Preço de custo", 'type' => 'N', 'maxLength' => 15, 'required' => false],
            ['name' => "Custo médio", 'type' => 'N', 'maxLength' => 15, 'required' => false],
            ['name' => "Local de estoque", 'type' => 'C', 'maxLength' => 5, 'required' => false],
        ]
    ],
    'DV' => [
        'name' => 'DAV (Importação)',
        'fields' => [
            ['name' => "Identificação do registro", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código", 'type' => 'C', 'maxLength' => 14, 'required' => true], // Asterisco
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Tipo DAV", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Valor", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Data", 'type' => 'D', 'maxLength' => 8, 'required' => true], // Asterisco
            ['name' => "Cliente", 'type' => 'C', 'maxLength' => 14, 'required' => false],
            ['name' => "Vendedor", 'type' => 'C', 'maxLength' => 14, 'required' => false],
            ['name' => "Código da Condição de pagamento preferencial", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Desconto sub-total", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Código de identificação", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Observação", 'type' => 'C', 'maxLength' => 9999, 'required' => false],
            ['name' => "Código da pauta", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Código do tipo de frete", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Código da transportadora", 'type' => 'C', 'maxLength' => 14, 'required' => false],
            ['name' => "Cep de entrega", 'type' => 'C', 'maxLength' => 8, 'required' => false],
            ['name' => "Estado de entrega", 'type' => 'C', 'maxLength' => 2, 'required' => false],
            ['name' => "Cidade de entrega", 'type' => 'C', 'maxLength' => 40, 'required' => false],
            ['name' => "Endereço de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Número da entrega", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Complemento do endereço de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Bairro de entrega", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Status da DAV", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Valor do frete", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor desconto dos itens", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Percentual desconto subtotal", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Id do tipo de documento financeiro", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Campo extra 1", 'type' => 'C', 'maxLength' => 512, 'required' => false],
            ['name' => "Campo extra 2", 'type' => 'C', 'maxLength' => 512, 'required' => false],
        ]
    ],
    'ID' => [
        'name' => 'Item DAV (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Produto", 'type' => 'C', 'maxLength' => 20, 'required' => true], // Asterisco
            ['name' => "Quantidade", 'type' => 'N', 'maxLength' => 12, 'required' => true], // Asterisco
            ['name' => "Preço da unidade", 'type' => 'N', 'maxLength' => 12, 'required' => true], // Asterisco
            ['name' => "Desconto", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Valor total", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Número de série", 'type' => 'C', 'maxLength' => 20, 'required' => false],
            ['name' => "Código do DAV", 'type' => 'C', 'maxLength' => 14, 'required' => true], // Asterisco
            ['name' => "Número do item", 'type' => 'N', 'maxLength' => 3, 'required' => false],
            ['name' => "Brinde", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Tipo de desconto", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "Unidade de medida", 'type' => 'C', 'maxLength' => 6, 'required' => false],
            ['name' => "Variações", 'type' => 'C', 'maxLength' => 500, 'required' => false],
            ['name' => "Informações adicionais", 'type' => 'C', 'maxLength' => 500, 'required' => false],
        ]
    ],
    'US' => [
        'name' => 'Usuário PAFECF (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código", 'type' => 'C', 'maxLength' => 10, 'required' => true], // Asterisco
            ['name' => "Nome", 'type' => 'C', 'maxLength' => 40, 'required' => true], // Asterisco
            ['name' => "Senha", 'type' => 'C', 'maxLength' => 40, 'required' => true], // Asterisco
            ['name' => "Supervisor", 'type' => 'N', 'maxLength' => 1, 'required' => false],
            ['name' => "%Desconto máximo", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "ID do perfil", 'type' => 'N', 'maxLength' => 3, 'required' => true], // Asterisco
        ]
    ],
    'RC' => [
        'name' => 'Financeiro (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Tipo", 'type' => 'C', 'maxLength' => 1, 'required' => true], // Asterisco
            ['name' => "Tipo documento financeiro", 'type' => 'N', 'maxLength' => 5, 'required' => true], // Asterisco
            ['name' => "Numero documento", 'type' => 'C', 'maxLength' => 30, 'required' => true], // Asterisco
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => false],
            ['name' => "Número de Parcelas", 'type' => 'N', 'maxLength' => 5, 'required' => false],
            ['name' => "Data emissão", 'type' => 'D', 'maxLength' => 8, 'required' => true], // Asterisco
            ['name' => "Data vencimento", 'type' => 'D', 'maxLength' => 8, 'required' => true], // Asterisco
            ['name' => "Valor", 'type' => 'N', 'maxLength' => 12, 'required' => true], // Asterisco
            ['name' => "Desconto", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Código Cliente", 'type' => 'C', 'maxLength' => 10, 'required' => true], // Asterisco
            ['name' => "Historio", 'type' => 'C', 'maxLength' => 100, 'required' => false],
            ['name' => "Saldo", 'type' => 'N', 'maxLength' => 12, 'required' => false],
            ['name' => "Data Pagamento", 'type' => 'D', 'maxLength' => 8, 'required' => false],
            ['name' => "Status do Documento", 'type' => 'C', 'maxLength' => 1, 'required' => false],
        ]
    ],
    'CP' => [
        'name' => 'Condição de pagamento (Importação)',
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Codigo", 'type' => 'C', 'maxLength' => 6, 'required' => true], // Asterisco
            ['name' => "Descricao", 'type' => 'C', 'maxLength' => 50, 'required' => true], // Asterisco
            ['name' => "Prazos", 'type' => 'C', 'maxLength' => 128, 'required' => true], // Asterisco
            ['name' => "Multiplicar por", 'type' => 'N', 'maxLength' => 5, 'required' => false],
        ]
    ],
];

$initialData = [
    'lines' => [],
    'stats' => ['total' => 0, 'valid' => 0, 'errors' => 0, 'corrected' => 0],
    'recordTypes' => [],
    'filename' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $content = file_get_contents($_FILES['file']['tmp_name']);
        $initialData['filename'] = $_FILES['file']['name'];
    } elseif (isset($_POST['content'])) {
        $content = $_POST['content'];
        $initialData['filename'] = 'conteudo_colado.txt';
    }

    if ($content) {
        $content = cleanContent($content);
        
        // Check if it's XML (Simple check)
        $isXml = (strpos($content, '<?xml') !== false || strpos($content, '<DPS') !== false);

        if ($isXml) {
            $initialData['recordTypes'][] = 'XML-DPS';
            
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadXML($content);
            $errors = libxml_get_errors();
            
            if ($errors) {
                 $initialData['lines'][] = [
                    'id' => 1,
                    'content' => 'Erro ao ler XML',
                    'originalContent' => $content,
                    'recordType' => 'ERO',
                    'errors' => ["XML Malformado: " . $errors[0]->message],
                    'status' => 'error',
                    'fields' => []
                ];
                $initialData['stats']['total'] = 1;
                $initialData['stats']['errors'] = 1;
            } else {
                $dpsNodes = $dom->getElementsByTagName('infDPS');
                $initialData['stats']['total'] = $dpsNodes->length;
                
                if ($dpsNodes->length === 0) {
                     // Try to see if it is just a signature or something else, but if valid XML and no DPS...
                     // Maybe it's a different XML? treat as generic.
                     $dpsNodes = $dom->getElementsByTagName('DPS'); // Fallback
                }

                $i = 0;
                foreach ($dpsNodes as $dps) {
                    $i++;
                    $lineErrors = [];
                    $dpsId = $dps->getAttribute('Id') ?: "DPS #$i";
                    
                    // Extract Values using XPath or getElementsByTagName
                    $xpath = new DOMXPath($dom);
                    // Context is the current $dps node
                    
                    // PIS COFINS Extraction
                    // Path: valores -> trib -> tribFed -> piscofins
                    // Note: Namespaces might be tricky, usually local-name() helps or just getElementsByTagName within context
                    
                    $pisCofinsNodes = $dps->getElementsByTagName('piscofins');
                    if ($pisCofinsNodes->length > 0) {
                        $pc = $pisCofinsNodes->item(0);
                        
                        $vBC = (float) $pc->getElementsByTagName('vBCPisCofins')->item(0)?->nodeValue;
                        $pPis = (float) $pc->getElementsByTagName('pAliqPis')->item(0)?->nodeValue;
                        $pCofins = (float) $pc->getElementsByTagName('pAliqCofins')->item(0)?->nodeValue;
                        $vPis = (float) $pc->getElementsByTagName('vPis')->item(0)?->nodeValue;
                        $vCofins = (float) $pc->getElementsByTagName('vCofins')->item(0)?->nodeValue;
                        
                        // CALCULATION VALIDATION
                        // Tolerance of 0.02 for rounding
                        $calcPis = round($vBC * ($pPis / 100), 2);
                        $calcCofins = round($vBC * ($pCofins / 100), 2);
                        
                        // Detect mismatch PIS
                        if (abs($calcPis - $vPis) > 0.02) {
                             $lineErrors[] = "O valor do PIS informado ($vPis) não corresponde ao cálculo da BC ($vBC) x Alíquota ($pPis%). Valor esperado: $calcPis.";
                        }
                        
                        // Detect mismatch COFINS
                        if (abs($calcCofins - $vCofins) > 0.02) {
                             $lineErrors[] = "O valor do COFINS informado ($vCofins) não corresponde ao cálculo da BC ($vBC) x Alíquota ($pCofins%). Valor esperado: $calcCofins.";
                        }
                    } 
                    
                    // Add result
                    if (!empty($lineErrors)) $initialData['stats']['errors']++;
                    else $initialData['stats']['valid']++;

                    $initialData['lines'][] = [
                        'id' => $i,
                        'content' => "DPS ID: $dpsId", // simplified view
                        'originalContent' => $dom->saveXML($dps), // Store full XML snippet
                        'recordType' => 'DPS',
                        'errors' => $lineErrors,
                        'status' => empty($lineErrors) ? 'valid' : 'error',
                        'fields' => [
                            'ID' => $dpsId,
                            'BC PIS/COFINS' => $vBC ?? 0,
                            'PIS %' => $pPis ?? 0,
                            'COFINS %' => $pCofins ?? 0,
                            'V. PIS' => $vPis ?? 0,
                            'V. COFINS' => $vCofins ?? 0
                        ]
                    ];
                }
            }
            libxml_clear_errors();
            
        } else {
            // TXT PROCESSING (LEGACY)
            $lines = explode("\n", $content);
            $initialData['stats']['total'] = count($lines);
            
            foreach ($lines as $index => $lineRaw) {
                $lineRaw = trim($lineRaw);
                if (empty($lineRaw)) {
                    $initialData['stats']['total']--; 
                    continue;
                }

                $lineNumber = $index + 1;
                $recordType = mb_substr($lineRaw, 0, 2);
                $recordType = preg_replace('/[^A-Z0-9]/', '', $recordType);
                
                $fields = explode(";", $lineRaw); 
                $lineErrors = [];

                if (!in_array($recordType, $initialData['recordTypes'])) {
                    $initialData['recordTypes'][] = $recordType;
                }

                if (!isset($validationRules[$recordType])) {
                    $lineErrors[] = "Tipo de registro desconhecido '$recordType'.";
                } else {
                    $rule = $validationRules[$recordType];
                    foreach ($rule['fields'] as $i => $fieldRule) {
                        if (isset($fields[$i])) {
                            $value = trim($fields[$i]);
                            if ($fieldRule['required'] && $value === '') $lineErrors[] = "Campo '{$fieldRule['name']}' (Col. " . ($i + 1) . ") é obrigatório.";
                            if (mb_strlen($value) > $fieldRule['maxLength']) $lineErrors[] = "Campo '{$fieldRule['name']}' (Col. " . ($i + 1) . ") excede tamanho {$fieldRule['maxLength']}.";
                            if ($fieldRule['type'] === 'N' && !is_numeric(str_replace(',', '.', $value)) && $value !== '') $lineErrors[] = "Campo '{$fieldRule['name']}' (Col. " . ($i + 1) . ") deve ser numérico. Valor: '$value'";
                        } elseif ($fieldRule['required']) {
                            $lineErrors[] = "Campo '{$fieldRule['name']}' é obrigatório e está ausente.";
                        }
                    }
                }

                if (!empty($lineErrors)) $initialData['stats']['errors']++;
                else $initialData['stats']['valid']++;

                $initialData['lines'][] = [
                    'id' => $lineNumber,
                    'content' => $lineRaw,
                    'originalContent' => $lineRaw,
                    'recordType' => $recordType,
                    'errors' => $lineErrors,
                    'status' => empty($lineErrors) ? 'valid' : 'error',
                    'fields' => $fields
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validador R2D2</title>
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        /* CONFIGURAÇÕES GERAIS E TELA CHEIA (MANTENDO O VISUAL ATUAL) */
        body, html { height: 100%; overflow: hidden; background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
        .main-container { height: 100vh; display: flex; flex-direction: column; padding: 1rem; color: var(--bs-body-color); }

        /* CARDS DE ESTATÍSTICA */
        .val-stats-card {
            background: white; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.8rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: center;
        }
        .val-stats-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .val-stats-value { font-size: 1.35rem; font-weight: 700; color: #1e293b; line-height: 1.2; margin-top: 0.2rem; text-shadow: none; }
        .val-stats-value.error { color: #dc2626; }
        .val-stats-value.valid { color: #16a34a; }
        .val-stats-value.corrected { color: #2563eb; }

        /* LISTA DE ITENS */
        .val-list-item {
            display: block;
            background: white;
            border: 1px solid #e2e8f0;
            border-left-width: 4px; 
            border-radius: 4px;
            margin-bottom: 8px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            color: var(--bs-body-color);
        }
        .val-list-item:hover { transform: translateX(2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-color: #cbd5e1; background: white; }
        .val-list-item.active { background-color: #eff6ff; border-color: #3b82f6; border-left-color: #3b82f6; }
        
        .val-list-item.error { border-left-color: #ef4444; }
        .val-list-item.valid { border-left-color: #22c55e; }
        .val-list-item.corrected { border-left-color: #3b82f6; }

        .item-content { font-family: 'Consolas', monospace; font-size: 0.85rem; color: #475569; word-break: break-all; }
        .item-error-msg { font-size: 0.75rem; color: #dc2626; margin-top: 4px; display: flex; align-items: center; font-weight: 600; }

        /* EDITOR DIREITO */
        .val-editor-container {
            background: white; border: 1px solid #e2e8f0; border-radius: 6px; 
            height: 100%; display: flex; flex-direction: column; backdrop-filter: none;
        }
        .editor-header { padding: 10px 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 600; display: flex; justify-content: space-between; align-items: center; border-radius: 6px 6px 0 0; }
        .editor-body { flex-grow: 1; overflow-y: auto; padding: 15px; }
        .editor-footer { padding: 10px 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 6px 6px; }

        /* EDITOR FIELDS (ADAPTADO PARA O FEEDBACK VERDE) */
        .editor-field-row { margin-bottom: 12px; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0; transition: all 0.2s; background: white; }
        .editor-field-row input.form-control { background: white; border: 1px solid #ced4da; color: var(--bs-body-color); }
        .editor-field-row input.form-control:focus { background: white; border-color: #86b7fe; color: var(--bs-body-color); box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25); }
        
        /* Estados de Validação Visual */
        .editor-field-row.is-valid { background-color: #f0fdf4; border-color: #bbf7d0; }

        /* QUICK EDITOR HIGHLIGHTER */
        .editor-highlighter-container { position: relative; height: 400px; }
        .editor-backdrop {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;
            overflow: auto; padding: 0.375rem 0.75rem;
            font-family: var(--bs-font-monospace); font-size: 1rem; line-height: 1.5;
            white-space: pre-wrap; word-wrap: break-word;
            color: transparent; background-color: white;
            border: 1px solid #ced4da; border-radius: 0.375rem; pointer-events: none;
        }
        .editor-textarea {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 2;
            background-color: transparent !important; color: inherit; resize: none; border-color: #ced4da;
        }
        .sep-high {
            background-color: #ffeb3b; color: transparent;
            border-radius: 2px; box-shadow: 0 0 0 1px #ffeb3b;
        }
        .editor-field-row.has-error { background-color: #fef2f2; border-color: #fecaca; }

        .editor-field-label { font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px; display: block; }
        .editor-field-row.is-valid .editor-field-label { color: #166534; }
        .editor-field-row.has-error .editor-field-label { color: #dc2626; }

        .char-count { font-size: 0.7rem; font-weight: 600; }
        .char-count.valid { color: #16a34a; }
        .char-count.error { color: #dc2626; }
    </style>
</head>
<body>

<?php if (empty($initialData['lines'])): ?>
    <!-- TELA DE UPLOAD -->
    <!-- TELA DE UPLOAD -->
    <!-- TELA DE UPLOAD (FULL SCREEN) -->
    <form method="POST" enctype="multipart/form-data" class="d-flex flex-column h-100" onsubmit="document.getElementById('loadingOverlay').style.display = 'flex'">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light shadow-sm z-1">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <div class="d-flex align-items-center text-secondary">
                <i class="bi bi-hdd-network fs-4 me-2"></i>
                <h5 class="m-0 fw-bold">Validador R2D2</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="https://kb.beemore.com/dc/pt-br/domains/suporte/documents/r2d2-importacao-e-exportacao-de-dados" target="_blank" class="btn btn-outline-secondary fw-bold px-3 shadow-sm" title="Documentação">
                    <i class="bi bi-book me-2"></i>Manual
                </a>
                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                <i class="bi bi-play-circle-fill me-2"></i> Iniciar Validação
            </button>
        </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid flex-grow-1 p-4 d-flex flex-column overflow-hidden">
            
            <!-- File Input Section -->
            <div class="row justify-content-center mb-4 mt-2">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase mb-3">Importar Arquivo</label>
                            <input class="form-control form-control-lg bg-light" type="file" name="file">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="d-flex align-items-center justify-content-center mb-3">
                <hr class="w-25 border-secondary-subtle">
                <span class="px-3 text-muted small fw-semibold">OU COLE O CONTEÚDO</span>
                <hr class="w-25 border-secondary-subtle">
            </div>

            <!-- Textarea Section (Maximized) -->
            <div class="flex-grow-1 d-flex flex-column" style="min-height: 0;">
                <textarea class="form-control flex-grow-1 border shadow-sm font-monospace p-3" 
                          name="content" 
                          style="resize: none; font-size: 0.9rem; border-radius: 8px;" 
                          placeholder="Cole o conteúdo do arquivo aqui..."></textarea>
            </div>
        </div>
    </form>
    <div id="loadingOverlay" style="display:none; position: fixed; inset: 0; background: rgba(255,255,255,0.9); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <h5 class="mt-3">Processando Arquivo...</h5>
    </div>
<?php else: ?>

    <!-- INTERFACE PRINCIPAL -->
    <div class="main-container">
        
        <!-- TOPO: Header e Stats -->
        <div class="flex-shrink-0 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <div class="vr"></div>
                    <div>
                        <h5 class="mb-0 fw-bold">Resultado da Validação</h5>
                        <small class="text-muted">Arquivo: <?php echo htmlspecialchars($initialData['filename']); ?></small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="https://kb.beemore.com/dc/pt-br/domains/suporte/documents/r2d2-importacao-e-exportacao-de-dados" target="_blank" class="btn btn-outline-secondary btn-sm" title="Documentação"><i class="bi bi-book"></i> Manual</a>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg"></i> Nova Validação</a>
                    <button class="btn btn-success btn-sm text-white" onclick="openExportModal('csv')"><i class="bi bi-filetype-csv"></i> CSV</button>
                    <button class="btn btn-danger btn-sm text-white" onclick="openExportModal('pdf')"><i class="bi bi-file-pdf"></i> PDF</button>
                    <button class="btn btn-primary btn-sm" onclick="downloadCorrected()"><i class="bi bi-download"></i> TXT Corrigido</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="openMassEdit()"><i class="bi bi-table"></i> Alteração em Massa</button>
                    <button class="btn btn-outline-primary btn-sm" id="btnAutoFix" onclick="autoCorrectAll()"><i class="bi bi-magic"></i> Corrigir Tudo</button>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-2"><div class="val-stats-card"><div class="val-stats-label">Total de Linhas</div><div class="val-stats-value" id="statTotal"><?php echo $initialData['stats']['total']; ?></div></div></div>
                <div class="col-md-2"><div class="val-stats-card"><div class="val-stats-label">Válidas</div><div class="val-stats-value valid" id="statValid"><?php echo $initialData['stats']['valid']; ?></div></div></div>
                <div class="col-md-2"><div class="val-stats-card"><div class="val-stats-label">Erros Encontrados</div><div class="val-stats-value error" id="statErrors"><?php echo $initialData['stats']['errors']; ?></div></div></div>
                <div class="col-md-2"><div class="val-stats-card"><div class="val-stats-label">Linhas Corrigidas</div><div class="val-stats-value corrected" id="statCorrected">0</div></div></div>
                <div class="col-md-4"><div class="val-stats-card"><div class="val-stats-label">Tipos de Registro</div><div class="small mt-2"><?php echo implode(", ", $initialData['recordTypes']); ?></div></div></div>
            </div>
        </div>

        <!-- CONTEÚDO: Lista e Editor -->
        <div class="row g-3 flex-grow-1" style="min-height: 0;">
            
            <!-- PAINEL ESQUERDO: FILTROS E LISTA -->
            <div id="listPanel" class="col-md-6 d-flex flex-column h-100">
                <!-- Barra de Ferramentas -->
                <div class="bg-white border rounded p-2 mb-2 d-flex gap-2 align-items-center shadow-sm">
                    <div class="d-flex flex-grow-1 gap-2">
                        <div class="input-group input-group-sm flex-grow-1">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Buscar..." onkeyup="debounceSearch()">
                        </div>
                        <select class="form-select form-select-sm" style="width: auto;" id="typeFilter" onchange="setTypeFilter(this.value)">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="vr"></div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" id="btnViewList" onclick="setViewMode('list')" title="Lista Simples"><i class="bi bi-list"></i></button>
                        <button class="btn btn-outline-secondary" id="btnViewGroup" onclick="setViewMode('group')" title="Agrupar por Erro"><i class="bi bi-collection"></i></button>
                    </div>
                    <div class="vr"></div>
                    <div class="btn-group btn-group-sm w-100">
                        <button class="btn btn-outline-secondary active" onclick="setTab('errors', this)">Erros (<span id="tabErrorCount"><?php echo $initialData['stats']['errors']; ?></span>)</button>
                        <button class="btn btn-outline-secondary" onclick="setTab('valid', this)">Válidas (<span id="tabValidCount"><?php echo $initialData['stats']['valid']; ?></span>)</button>
                        <button class="btn btn-outline-secondary" onclick="setTab('corrected', this)">Corrigidas (<span id="tabCorrectedCount">0</span>)</button>
                        <button class="btn btn-outline-secondary" onclick="setTab('original', this)">Original</button>
                    </div>
                </div>

                <!-- Container da Lista (Infinite Scroll) -->
                <div class="flex-grow-1 overflow-auto border rounded bg-white p-2 shadow-sm" id="linesContainer" style="background-color: #f8fafc !important;">
                    <!-- Itens renderizados via JS -->
                </div>
            </div>

            <!-- PAINEL DIREITO: EDITOR -->
            <div id="editorPanel" class="col-md-6 h-100">
                <div class="val-editor-container shadow-sm">
                    <div class="editor-header">
                        <span id="editorTitle" class="text-primary"><i class="bi bi-pencil-square me-2"></i>Editor de Linha</span>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" id="btnDetailed" onclick="setEditMode('detailed')">Campos</button>
                            <button class="btn btn-outline-secondary" id="btnQuick" onclick="setEditMode('quick')">Texto</button>
                        </div>
                    </div>

                    <!-- Estado Vazio -->
                    <div id="editorEmptyState" class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                        <i class="bi bi-cursor display-4 mb-3 opacity-25"></i>
                        <p>Selecione uma linha na lista para editar</p>
                    </div>

                    <!-- Conteúdo do Editor -->
                    <div id="editorContent" class="d-none flex-column h-100" style="min-height: 0;">
                        <div class="editor-body" id="editorFields"></div>
                        <div class="editor-footer d-flex justify-content-between">
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteLine()"><i class="bi bi-trash"></i> Excluir</button>
                            <div class="d-flex gap-2">
                                <button class="btn btn-secondary btn-sm" onclick="autoCorrectCurrent()"><i class="bi bi-magic"></i> Auto Ajuste</button>
                                <button class="btn btn-primary btn-sm" onclick="saveChanges()"><i class="bi bi-check-lg"></i> Salvar Alterações</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CORREÇÃO (DO USUÁRIO) -->
    <div class="modal fade" id="correctionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resumo da Correção Automática</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="correctionSummaryText"></span>
                    </div>
                    <div class="list-group list-group-flush small overflow-auto" style="max-height: 400px;" id="correctionLog">
                        <!-- Logs go here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE ALTERAÇÃO EM MASSA -->
    <!-- Mass Edit Modal -->
    <div class="modal fade" id="massEditModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-table me-2"></i>Ferramentas em Massa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="massEditTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="values-tab" data-bs-toggle="tab" data-bs-target="#values-tab-pane" type="button" role="tab">Edição de Valores</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="transfer-tab" data-bs-toggle="tab" data-bs-target="#transfer-tab-pane" type="button" role="tab">Transferir Colunas</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Tab 1: Edição de Valores -->
                        <div class="tab-pane fade show active" id="values-tab-pane" role="tabpanel">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Tipo de Registro</label>
                                    <select class="form-select form-select-sm" id="massEditType" onchange="updateMassEditFields()">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Campo</label>
                                    <select class="form-select form-select-sm" id="massEditField" onchange="loadMassEditValues()" disabled>
                                        <option value="">Selecione o tipo primeiro...</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="alert alert-light border w-100 mb-0 py-1 px-2 small text-muted">
                                        <i class="bi bi-info-circle me-1"></i> Selecione um campo para ver os valores agrupados.
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive border rounded" style="max-height: 500px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Valor Original</th>
                                            <th class="text-center" style="width: 100px;">Ocorrências</th>
                                            <th>Novo Valor (Sugestão)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="massEditTableBody">
                                        <tr><td colspan="3" class="text-center text-muted py-4">Aguardando seleção...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-primary" onclick="applyMassEdit()">Aplicar Alterações</button>
                            </div>
                        </div>

                        <!-- Tab 2: Transferência -->
                        <div class="tab-pane fade" id="transfer-tab-pane" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Copie ou mova valores de uma coluna para outra em todos os registros do tipo selecionado.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Tipo de Registro</label>
                                    <select class="form-select" id="transferType" onchange="updateTransferFields()">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">De (Origem)</label>
                                    <select class="form-select" id="transferSource" disabled>
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end justify-content-center pb-2">
                                    <i class="bi bi-arrow-right fs-3 text-muted"></i>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Para (Destino)</label>
                                    <select class="form-select" id="transferDest" disabled>
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="transferOverwrite" checked>
                                        <label class="form-check-label" for="transferOverwrite">Sobrescrever destino mesmo se já tiver valor</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="transferClear" checked>
                                        <label class="form-check-label" for="transferClear">Limpar campo de origem após mover</label>
                                    </div>
                                </div>
                                <div class="col-12 text-end mt-4">
                                    <button class="btn btn-success" onclick="applyTransfer()">
                                        <i class="bi bi-arrow-left-right me-1"></i> Executar Transferência
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE EXPORTAÇÃO -->
    <div class="modal fade" id="exportOptionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalTitle"><i class="bi bi-download me-2"></i>Exportar Relatório</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Selecione as opções para o arquivo gerado:</p>
                    
                    <div class="d-flex flex-column gap-2 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expOptErrors" checked>
                            <label class="form-check-label" for="expOptErrors">Incluir linhas com <strong>erros</strong></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expOptValid" checked>
                            <label class="form-check-label" for="expOptValid">Incluir linhas <strong>válidas</strong></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expOptSummary" checked>
                            <label class="form-check-label" for="expOptSummary">Incluir <strong>resumo/cabeçalho</strong> no topo do arquivo</label>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="expOptFormatInfo">O arquivo será gerado em formato CSV.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="proceedExport()">Baixar Arquivo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Ação realizada com sucesso!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" style="z-index: 1070;">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header border-0 pb-0 justify-content-center">
            <h5 class="modal-title fs-6 text-uppercase text-secondary fw-bold">Confirmação</h5>
          </div>
          <div class="modal-body py-4">
            <p id="confirmMessage" class="mb-0 text-center fw-medium text-muted"></p>
          </div>
          <div class="modal-footer border-0 pt-0 justify-content-center bg-light rounded-bottom">
            <button type="button" class="btn btn-sm btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary px-4 rounded-pill" id="btnConfirmAction">Sim, Confirmar</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const validationData = <?php echo json_encode($initialData, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR); ?>;
        const validationRules = <?php echo json_encode($validationRules, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR); ?>;
        
        let currentTab = 'errors';
        let viewMode = 'list';
        let editMode = 'detailed';
        let selectedLineId = null;
        let filteredLines = [];
        let renderedCount = 0;
        let currentTypeFilter = '';
        const BATCH_SIZE = 40;
        let searchTimeout;

        // Inicializa o filtro de tipos
        document.addEventListener('DOMContentLoaded', () => {
            const typeFilter = document.getElementById('typeFilter');
            const types = [...new Set(validationData.lines.map(l => l.recordType))].sort();
            types.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.text = t;
                typeFilter.appendChild(opt);
            });
        });

        function setTypeFilter(type) {
            currentTypeFilter = type;
            renderLines(true);
        }

        // Quick View Mode Toggle
        window.currentQuickView = 'split';
        function setQuickView(mode) {
            window.currentQuickView = mode;
            const splitView = document.getElementById('quickSplitView');
            const rawView = document.getElementById('quickRawView');
            const btnSplit = document.getElementById('btnSplitView');
            const btnRaw = document.getElementById('btnRawView');

            if (mode === 'split') {
                splitView.classList.remove('d-none');
                rawView.classList.add('d-none');
                btnSplit.classList.add('active');
                btnRaw.classList.remove('active');
                
                // Sync content from raw to split if needed (optional, but good for consistency)
                const rawVal = document.getElementById('quickEditInput').value;
                // Re-render split view based on raw value? 
                // Simplest is to just re-render the editor for the current line
                const line = validationData.lines.find(l => l.id === selectedLineId);
                if (line) {
                     // Update line content temporarily to re-render
                     line.content = rawVal; 
                     renderEditor(line);
                }
            } else {
                splitView.classList.add('d-none');
                rawView.classList.remove('d-none');
                btnSplit.classList.remove('active');
                btnRaw.classList.add('active');
                
                // Sync content from split to raw
                const inputs = document.querySelectorAll('.quick-split-input');
                const values = Array.from(inputs).map(input => input.value);
                const joined = values.join(';');
                const textarea = document.getElementById('quickEditInput');
                textarea.value = joined;
                updateBackdrop();
            }
        }

        function escapeHtml(text) {
            if (text == null) return '';
            return text.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        // --- RENDERIZAÇÃO DA LISTA (INFINITE SCROLL) ---
        function renderLines(reset = true) {
            const container = document.getElementById('linesContainer');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            if (currentTab === 'original') {
                const content = validationData.lines.map(l => l.originalContent).join('\n');
                const lineNumbers = validationData.lines.map(l => l.id).join('\n');
                container.innerHTML = `
                    <div class="h-100 d-flex border rounded bg-light overflow-hidden">
                        <textarea class="bg-light border-0 text-end text-muted pe-2 py-2" style="width: 50px; resize: none; font-family: monospace; font-size: 0.8rem; line-height: 1.5; outline: none;" readonly>${lineNumbers}</textarea>
                        <div class="vr"></div>
                        <textarea class="form-control border-0 bg-light ps-2 py-2" style="resize: none; font-family: monospace; font-size: 0.8rem; line-height: 1.5; outline: none;" readonly onscroll="this.previousElementSibling.scrollTop = this.scrollTop">${escapeHtml(content)}</textarea>
                    </div>`;
                return;
            }

            filteredLines = validationData.lines.filter(l => {
                if (currentTab === 'errors' && l.status !== 'error') return false;
                if (currentTab === 'valid' && l.status !== 'valid') return false;
                if (currentTab === 'corrected' && l.status !== 'corrected') return false;
                if (currentTypeFilter && l.recordType !== currentTypeFilter) return false;
                if (searchTerm) return l.content.toLowerCase().includes(searchTerm) || l.id.toString().includes(searchTerm);
                return true;
            });

            if (reset) {
                container.innerHTML = '';
                renderedCount = 0;
                container.scrollTop = 0;
            }

            if (filteredLines.length === 0) {
                container.innerHTML = '<div class="text-center py-5 text-muted">Nenhum registro encontrado.</div>';
                return;
            }

            if (viewMode === 'group' && currentTab === 'errors') {
                renderGroupedLines(container);
            } else {
                renderNextBatch();
            }
        }

        function renderGroupedLines(container) {
            const groups = {};
            filteredLines.forEach(l => {
                const key = l.errors.length > 0 ? l.errors[0] : 'Outros';
                if (!groups[key]) groups[key] = [];
                groups[key].push(l);
            });

            let html = '';
            Object.keys(groups).forEach((errorMsg, idx) => {
                const count = groups[errorMsg].length;
                html += `
                    <div class="card mb-2 border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center" 
                             style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#group-${idx}">
                            <div class="d-flex align-items-center text-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <span class="fw-bold small">${escapeHtml(errorMsg)}</span>
                            </div>
                            <span class="badge bg-danger rounded-pill">${count}</span>
                        </div>
                        <div id="group-${idx}" class="collapse">
                            <div class="card-body p-0">
                                ${groups[errorMsg].map(line => createLineHTML(line)).join('')}
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function renderNextBatch() {
            if (renderedCount >= filteredLines.length) return;
            const container = document.getElementById('linesContainer');
            const batch = filteredLines.slice(renderedCount, renderedCount + BATCH_SIZE);
            let html = '';
            batch.forEach(line => { html += createLineHTML(line); });
            container.insertAdjacentHTML('beforeend', html);
            renderedCount += batch.length;
        }

        function createLineHTML(line) {
            const activeClass = line.id === selectedLineId ? 'active' : '';
            let errorBadge = '';
            let errorMsgDiv = '';
            
            if (line.status === 'error' && line.errors.length > 0) {
                errorBadge = `<span class="badge bg-danger ms-auto">${line.errors.length}</span>`;
                errorMsgDiv = `<div class="item-error-msg"><i class="bi bi-exclamation-circle-fill me-1"></i> ${escapeHtml(line.errors[0])}</div>`;
            } else if (line.status === 'corrected') {
                errorBadge = `<i class="bi bi-check-circle-fill text-primary ms-auto"></i>`;
            }

            return `
                <div id="line-item-${line.id}" class="val-list-item ${line.status} ${activeClass}" onclick="selectLine(${line.id})">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-secondary me-2" style="font-size: 0.7rem;">#${line.id}</span>
                        <span class="fw-bold text-dark small me-2">${line.recordType}</span>
                        ${errorBadge}
                    </div>
                    <div class="item-content">${escapeHtml(line.content)}</div>
                    ${errorMsgDiv}
                </div>
            `;
        }

        document.getElementById('linesContainer').addEventListener('scroll', function(e) {
            if (viewMode === 'list' && e.target.scrollTop + e.target.clientHeight >= e.target.scrollHeight - 100) {
                renderNextBatch();
            }
        });

        function debounceSearch() { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => renderLines(true), 300); }

        // --- SELEÇÃO E EDITOR ---
        function selectLine(id) {
            const prev = document.querySelector('.val-list-item.active');
            if (prev) prev.classList.remove('active');
            
            const items = document.querySelectorAll(`#line-item-${id}`);
            items.forEach(item => item.classList.add('active'));

            selectedLineId = id;
            const line = validationData.lines.find(l => l.id === id);
            
            document.getElementById('editorEmptyState').classList.remove('d-flex');
            document.getElementById('editorEmptyState').classList.add('d-none');
            document.getElementById('editorContent').classList.remove('d-none');
            document.getElementById('editorContent').classList.add('d-flex');
            
            document.getElementById('editorTitle').innerHTML = `<i class="bi bi-pencil-square me-2"></i>Editor de Linha #${id}`;

            renderEditor(line);
        }

        function renderEditor(line) {
            const container = document.getElementById('editorFields');
            container.innerHTML = '';

            if (editMode === 'quick') {
                const fields = line.content.split(';');
                
                // Toggle Container
                const toggleHtml = `
                    <div class="d-flex justify-content-end mb-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" id="btnSplitView" onclick="setQuickView('split')">Estrutura</button>
                            <button class="btn btn-outline-secondary" id="btnRawView" onclick="setQuickView('raw')">Texto Puro</button>
                        </div>
                    </div>
                `;

                // Split View (Default)
                let splitHtml = `<div id="quickSplitView" class="d-flex flex-column gap-2" style="height: 400px; overflow-y: auto;">`;
                fields.forEach((val, idx) => {
                    splitHtml += `
                        <div class="input-group input-group-sm">
                            <span class="input-group-text font-monospace bg-light text-secondary" style="width: 45px; justify-content: center;">${idx + 1}</span>
                            <input type="text" class="form-control font-monospace quick-split-input" value="${escapeHtml(val)}" data-index="${idx}">
                        </div>
                    `;
                });
                splitHtml += `</div>`;

                // Raw View (Hidden by default)
                const rawHtml = `
                    <div id="quickRawView" class="d-none">
                        <div class="alert alert-info py-1 small mb-2">Edição de texto cru. Separadores (;) destacados.</div>
                        <div class="editor-highlighter-container">
                            <div class="editor-backdrop" id="editorBackdrop"></div>
                            <textarea class="form-control font-monospace editor-textarea" id="quickEditInput" 
                                oninput="updateBackdrop()" onscroll="syncScroll()">${escapeHtml(line.content)}</textarea>
                        </div>
                    </div>
                `;

                container.innerHTML = toggleHtml + splitHtml + rawHtml;
                
                // Initialize state
                if (window.currentQuickView === 'raw') {
                    setQuickView('raw');
                    setTimeout(updateBackdrop, 0);
                }
                return;
            }

            const rule = validationRules[line.recordType];
            if (!rule) {
                container.innerHTML = '<div class="alert alert-warning">Tipo de registro desconhecido. Edite no modo Texto.</div>';
                return;
            }

            // Identificar índices com erro para destaque inicial
            const invalidIndices = [];
            rule.fields.forEach((r, i) => {
                const val = (line.fields[i] || '').trim();
                if (r.required && val === '') invalidIndices.push(i);
                else if (val.length > r.maxLength) invalidIndices.push(i);
                else if (r.type === 'N' && val !== '' && isNaN(Number(val.replace(',', '.')))) invalidIndices.push(i);
            });

            rule.fields.forEach((f, idx) => {
                const val = line.fields[idx] || '';
                const isErr = invalidIndices.includes(idx);
                const typeLabel = f.type === 'N' ? 'Numérico' : 'Texto';
                
                const div = document.createElement('div');
                div.className = `editor-field-row ${isErr ? 'has-error' : ''}`;
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-end mb-1">
                        <span class="editor-field-label">
                            <span class="text-muted me-1" style="font-weight:normal">#${idx + 1}</span>
                            ${escapeHtml(f.name)} 
                            ${f.required ? '<span class="text-danger" title="Obrigatório">*</span>' : ''}
                        </span>
                        <span class="badge bg-light text-secondary border" style="font-size:0.65rem">${typeLabel}</span>
                    </div>
                    <div class="input-group input-group-sm">
                        <input type="text" 
                               class="form-control ${isErr ? 'is-invalid' : ''}" 
                               value="${escapeHtml(val)}" 
                               id="field_${idx}"
                               maxlength="${f.maxLength}"
                               oninput="updateFieldStatus(this, ${f.maxLength}, ${f.required}, '${f.type}')">
                        <span class="input-group-text text-muted font-monospace" style="min-width:60px; justify-content:center;">
                            <span id="count_${idx}">${val.length}</span>/${f.maxLength}
                        </span>
                    </div>
                    <div class="text-danger small mt-1 error-msg" style="display: ${isErr ? 'block' : 'none'}">
                        ${isErr ? 'Verifique este campo' : ''}
                    </div>
                `;
                container.appendChild(div);
                
                // Trigger update to set green/red states correctly on load
                const input = div.querySelector('input');
                updateFieldStatus(input, f.maxLength, f.required, f.type);
            });
        }

        function updateBackdrop() {
            const input = document.getElementById('quickEditInput');
            const backdrop = document.getElementById('editorBackdrop');
            if (!input || !backdrop) return;
            
            const text = input.value;
            const escaped = text.replace(/&/g, "&amp;")
                                .replace(/</g, "&lt;")
                                .replace(/>/g, "&gt;")
                                .replace(/"/g, "&quot;")
                                .replace(/'/g, "&#039;");
            
            const highlighted = escaped.replace(/;/g, '<span class="sep-high">;</span>');
            const finalHtml = highlighted + (text.endsWith('\n') ? '<br>&nbsp;' : '');
            
            backdrop.innerHTML = finalHtml;
        }

        function syncScroll() {
            const input = document.getElementById('quickEditInput');
            const backdrop = document.getElementById('editorBackdrop');
            if (input && backdrop) {
                backdrop.scrollTop = input.scrollTop;
                backdrop.scrollLeft = input.scrollLeft;
            }
        }

        function updateFieldStatus(input, max, required, type) {
            const val = input.value;
            const countSpan = input.nextElementSibling.firstElementChild;
            const errorDiv = input.parentElement.nextElementSibling;
            const row = input.closest('.editor-field-row');
            
            // Optimize DOM updates
            if (countSpan.innerText !== String(val.length)) {
                countSpan.innerText = val.length;
            }
            
            let error = false;
            let errorMsg = '';

            if (required && val.trim() === '') { error = true; errorMsg = 'Campo obrigatório.'; }
            else if (val.length > max) { error = true; errorMsg = `Excede limite de ${max} caracteres.`; }
            else if (type === 'N' && val !== '' && isNaN(Number(val.replace(',', '.')))) { error = true; errorMsg = 'Deve ser numérico.'; }

            if (error) {
                if (!input.classList.contains('is-invalid')) input.classList.add('is-invalid');
                if (input.classList.contains('is-valid')) input.classList.remove('is-valid');
                
                if (!row.classList.contains('has-error')) row.classList.add('has-error');
                if (row.classList.contains('is-valid')) row.classList.remove('is-valid');
                
                if (!countSpan.classList.contains('text-danger')) countSpan.classList.add('text-danger');
                if (countSpan.classList.contains('text-success')) countSpan.classList.remove('text-success');
                
                if (errorDiv.style.display !== 'block') errorDiv.style.display = 'block';
                if (errorDiv.innerText !== errorMsg) errorDiv.innerText = errorMsg;
            } else {
                if (input.classList.contains('is-invalid')) input.classList.remove('is-invalid');
                if (!input.classList.contains('is-valid')) input.classList.add('is-valid');
                
                if (row.classList.contains('has-error')) row.classList.remove('has-error');
                if (!row.classList.contains('is-valid')) row.classList.add('is-valid');
                
                if (countSpan.classList.contains('text-danger')) countSpan.classList.remove('text-danger');
                if (!countSpan.classList.contains('text-success')) countSpan.classList.add('text-success');
                
                if (errorDiv.style.display !== 'none') errorDiv.style.display = 'none';
            }
        }

        // --- AÇÕES ---
        function saveChanges(isAuto = false) {
            const line = validationData.lines.find(l => l.id === selectedLineId);
            if (!line) return;

            if (editMode === 'quick') {
                if (window.currentQuickView === 'raw') {
                    line.content = document.getElementById('quickEditInput').value;
                } else {
                    const inputs = document.querySelectorAll('.quick-split-input');
                    const values = Array.from(inputs).map(input => input.value);
                    line.content = values.join(';');
                }
                line.fields = line.content.split(';');
            } else {
                const rule = validationRules[line.recordType];
                if (rule) {
                    line.fields = rule.fields.map((_, i) => {
                        const el = document.getElementById(`field_${i}`);
                        return el ? el.value : (line.fields[i] || '');
                    });
                    line.content = line.fields.join(';');
                }
            }

            // Revalidação
            const rule = validationRules[line.recordType];
            line.errors = [];
            if (rule) {
                rule.fields.forEach((r, i) => {
                    const val = (line.fields[i] || '').trim();
                    if (r.required && val === '') line.errors.push(`Campo '${r.name}' (Col. ${i + 1}) vazio`);
                    else if (val.length > r.maxLength) line.errors.push(`Campo '${r.name}' (Col. ${i + 1}) excede tamanho`);
                    else if (r.type === 'N' && val !== '' && isNaN(Number(val.replace(',', '.')))) line.errors.push(`Campo '${r.name}' (Col. ${i + 1}) não é numérico. Valor: '${val}'`);
                });
            }

            line.status = line.errors.length === 0 ? 'corrected' : 'error';
            recalcStats();
            
            // Se estiver na aba de erros e a linha foi corrigida, remove da lista
            if (currentTab === 'errors' && line.status !== 'error') {
                const items = document.querySelectorAll(`#line-item-${line.id}`);
                items.forEach(el => el.remove());
                
                // Se estiver no modo grupo, recarrega para atualizar contadores dos grupos
                if (viewMode === 'group') {
                    renderLines();
                }

                // O editor permanece aberto para visualização da linha corrigida
            } else {
                // Atualiza visualmente na lista
                const items = document.querySelectorAll(`#line-item-${line.id}`);
                items.forEach(listItem => {
                    listItem.className = `val-list-item ${line.status} active`;
                    listItem.querySelector('.item-content').innerText = line.content;
                    const msgDiv = listItem.querySelector('.item-error-msg');
                    if (line.status !== 'error' && msgDiv) msgDiv.remove();
                    else if (line.status === 'error' && !msgDiv) {
                         // Adiciona msg de erro se voltou a ter erro
                         const div = document.createElement('div');
                         div.className = 'item-error-msg';
                         div.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> ${escapeHtml(line.errors[0])}`;
                         listItem.appendChild(div);
                    } else if (line.status === 'error' && msgDiv) {
                        msgDiv.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> ${escapeHtml(line.errors[0])}`;
                    }
                });
            }
            
            if (!isAuto) showToast('Alterações salvas!', 'success');
        }
        function autoCorrectCurrent() {
            const line = validationData.lines.find(l => l.id === selectedLineId);
            if (!line) return;
            const rule = validationRules[line.recordType];
            if (!rule) return;

            // Update fields in DOM
            rule.fields.forEach((r, i) => {
                const el = document.getElementById(`field_${i}`);
                if (el) {
                    let val = el.value.trim();
                    
                    // 1. Fix Numeric
                    if (r.type === 'N') {
                        if (val.toUpperCase() === 'S/N') val = '0';
                        else val = val.replace(/[^0-9,.-]/g, ''); // Keep digits, comma, dot, minus
                    }

                    // 2. Truncate
                    if (val.length > r.maxLength) val = val.substring(0, r.maxLength);
                    
                    el.value = val;
                    updateFieldStatus(el, r.maxLength, r.required, r.type);
                }
            });
            saveChanges(true);
            saveChanges(true);
            showToast('Linha corrigida e salva!');
        }

        // --- ALTERAÇÃO EM MASSA ---
        const sefazUnits = {
            'AMPOLA': 'AMP', 'BALDE': 'BAL', 'BANDEJ': 'BAN', 'BARRA': 'BAR', 'BISNAG': 'BIS', 'BLOCO': 'BLO', 
            'BOBINA': 'BOB', 'BOMB': 'BOM', 'CAPS': 'CAP', 'CART': 'CAR', 'CENTO': 'CEN', 'CJ': 'CJ', 
            'CM': 'CM', 'CM2': 'CM2', 'CX': 'CX', 'CX2': 'CX2', 'CX3': 'CX3', 'CX5': 'CX5', 'CX10': 'CX10', 
            'CX15': 'CX15', 'CX20': 'CX20', 'CX25': 'CX25', 'CX50': 'CX50', 'CX100': 'CX100', 'DISP': 'DIS', 
            'DUZIA': 'DUZ', 'EMBAL': 'EMB', 'FARDO': 'FAR', 'FOLHA': 'FOL', 'FRASCO': 'FRA', 'GALAO': 'GAL', 
            'GF': 'GF', 'GRAMAS': 'GR', 'JOGO': 'JOG', 'KG': 'KG', 'KIT': 'KIT', 'LATA': 'LAT', 'LITRO': 'LIT', 
            'M': 'M', 'M2': 'M2', 'M3': 'M3', 'MILHEI': 'MIL', 'ML': 'ML', 'MWH': 'MWH', 'PACOTE': 'PAC', 
            'PALETE': 'PAL', 'PARES': 'PAR', 'PC': 'PC', 'POTE': 'POT', 'K': 'K', 'RESMA': 'RES', 'ROLO': 'ROL', 
            'SACO': 'SAC', 'SACOLA': 'SCO', 'TAMBOR': 'TAM', 'TANQUE': 'TAN', 'TON': 'TON', 'TUBO': 'TUB', 
            'UNID': 'UN', 'VASIL': 'VAS', 'VIDRO': 'VID'
        };

        let massEditModal;

        function openMassEdit() {
            if (!massEditModal) massEditModal = new bootstrap.Modal(document.getElementById('massEditModal'));
            
            const typeSelect = document.getElementById('massEditType');
            typeSelect.innerHTML = '<option value="">Selecione...</option>';
            
            const transferTypeSelect = document.getElementById('transferType');
            if (transferTypeSelect) {
                transferTypeSelect.innerHTML = '<option value="">Selecione...</option>';
            }

            // Popula tipos de registro
            const types = [...new Set(validationData.lines.map(l => l.recordType))].sort();
            types.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.text = t;
                typeSelect.appendChild(opt);

                if (transferTypeSelect) {
                    const opt2 = document.createElement('option');
                    opt2.value = t;
                    opt2.text = t;
                    transferTypeSelect.appendChild(opt2);
                }
            });

            document.getElementById('massEditField').innerHTML = '<option value="">Selecione o tipo primeiro...</option>';
            document.getElementById('massEditField').disabled = true;
            document.getElementById('massEditTableBody').innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Aguardando seleção...</td></tr>';
            
            // Reset transfer fields
            if (document.getElementById('transferSource')) {
                document.getElementById('transferSource').innerHTML = '<option value="">Selecione...</option>';
                document.getElementById('transferSource').disabled = true;
                document.getElementById('transferDest').innerHTML = '<option value="">Selecione...</option>';
                document.getElementById('transferDest').disabled = true;
            }

            massEditModal.show();
        }

        function updateMassEditFields() {
            const type = document.getElementById('massEditType').value;
            const fieldSelect = document.getElementById('massEditField');
            fieldSelect.innerHTML = '<option value="">Selecione...</option>';
            
            if (!type) {
                fieldSelect.disabled = true;
                return;
            }

            const rule = validationRules[type];
            if (rule) {
                // Adiciona opção explícita para o Tipo de Registro (Coluna 1) se não estiver na regra ou para reforçar
                // Verifica se o índice 0 já está nos campos. Se estiver, usa o nome da regra, mas garante que seja editável.
                // Mas o usuário pediu "independente do qual ele seja". 
                // Vamos listar todos os campos da regra.
                
                rule.fields.forEach((f, i) => {
                    const opt = document.createElement('option');
                    opt.value = i;
                    // Se for o índice 0, destaca que é o Tipo de Registro
                    opt.text = (i === 0) ? `[Tipo de Registro] ${f.name}` : f.name;
                    fieldSelect.appendChild(opt);
                });
                fieldSelect.disabled = false;
            } else {
                // FALLBACK PARA TIPOS DESCONHECIDOS (0, 3, etc)
                const sampleLine = validationData.lines.find(l => l.recordType === type);
                if (sampleLine && sampleLine.fields && sampleLine.fields.length > 0) {
                    sampleLine.fields.forEach((_, i) => {
                        const opt = document.createElement('option');
                        opt.value = i;
                        opt.text = (i === 0) ? `[Tipo de Registro] Coluna 1` : `Coluna ${i + 1}`;
                        fieldSelect.appendChild(opt);
                    });
                    fieldSelect.disabled = false;
                } else {
                     fieldSelect.innerHTML = '<option value="">Sem campos detectados</option>';
                     fieldSelect.disabled = true;
                }
            }
        }

        function loadMassEditValues() {
            const type = document.getElementById('massEditType').value;
            const fieldIndex = parseInt(document.getElementById('massEditField').value);
            const tbody = document.getElementById('massEditTableBody');
            
            if (!type || isNaN(fieldIndex)) {
                tbody.innerHTML = '';
                return;
            }

            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

            // Processamento assíncrono para não travar a UI
            setTimeout(() => {
                const values = {};
                // Use rule if exists, otherwise create dummy rule
                const rule = (validationRules[type] && validationRules[type].fields[fieldIndex]) 
                             ? validationRules[type].fields[fieldIndex] 
                             : { name: `Coluna ${fieldIndex + 1}`, maxLength: 255 };

                validationData.lines.forEach(line => {
                    if (line.recordType === type) {
                        const val = line.fields[fieldIndex] || '';
                        values[val] = (values[val] || 0) + 1;
                    }
                });

                tbody.innerHTML = '';
                
                Object.entries(values).sort((a, b) => b[1] - a[1]).forEach(([val, count]) => {
                    const tr = document.createElement('tr');
                    
                    // Lógica de sugestão
                    let suggestion = val;
                    
                    // 1. Sanitize Numeric Fields (BR format -> US/Clean format)
                    if (rule.type === 'N' && val.trim() !== '') {
                        // Remove thousand separators (dots) first
                        let cleaned = val.replace(/\./g, '');
                        
                        // Handle multiple commas (e.g. 1,000,00 -> 1000.00)
                        if (cleaned.includes(',')) {
                            const parts = cleaned.split(',');
                            if (parts.length > 1) {
                                // Last part is always decimals, verify if it looks like decimal?
                                // Assuming BR format, last part is decimal
                                const decimalPart = parts.pop();
                                const integerPart = parts.join(''); // Remove all other commas
                                cleaned = integerPart + '.' + decimalPart;
                            }
                        }
                        
                        if (!isNaN(Number(cleaned))) {
                            suggestion = Number(cleaned).toString();
                        }
                    }

                    if (rule.name === 'Unidade') {
                        const upperVal = val.toUpperCase().trim();
                        if (sefazUnits[upperVal]) {
                            suggestion = sefazUnits[upperVal];
                        } else if (val.length > 3) {
                            suggestion = val.substring(0, 3).toUpperCase();
                        }
                    }

                    let inputHtml = '';
                    if (fieldIndex === 0) {
                        // Se for Tipo de Registro, usa Select com opções permitidas
                        const validTypes = Object.keys(validationRules).sort();
                        inputHtml = `<select class="form-select form-select-sm mass-edit-input" data-original="${escapeHtml(val)}">`;
                        // Adiciona o valor atual se não for um tipo conhecido (para não quebrar)
                        if (!validationRules[val] && val !== '') {
                             inputHtml += `<option value="${escapeHtml(val)}" selected>${escapeHtml(val)} (Atual)</option>`;
                        }
                        
                        validTypes.forEach(t => {
                            const selected = (t === suggestion) ? 'selected' : '';
                            const desc = validationRules[t].name || '';
                            inputHtml += `<option value="${t}" ${selected}>${t} - ${desc}</option>`;
                        });
                        inputHtml += `</select>`;
                    } else {
                        // Input padrão
                        inputHtml = `<input type="text" class="form-control form-control-sm mass-edit-input" 
                                data-original="${escapeHtml(val)}" 
                                value="${escapeHtml(suggestion)}" 
                                maxlength="${rule.maxLength}">`;
                    }

                    tr.innerHTML = `
                        <td class="align-middle font-monospace text-break">${escapeHtml(val) || '<em class="text-muted">Vazio</em>'}</td>
                        <td class="align-middle text-center"><span class="badge bg-secondary rounded-pill">${count}</span></td>
                        <td>${inputHtml}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }, 100);
        }

        function applyMassEdit() {
            const type = document.getElementById('massEditType').value;
            const fieldIndex = parseInt(document.getElementById('massEditField').value);
            
            if (!type || isNaN(fieldIndex)) return;

            const inputs = document.querySelectorAll('.mass-edit-input');
            const changes = {};
            inputs.forEach(input => {
                const original = input.getAttribute('data-original'); // Recupera o valor original (pode ser string vazia)
                const newValue = input.value;
                if (original !== newValue) {
                    changes[original] = newValue;
                }
            });

            if (Object.keys(changes).length === 0) {
                showToast('Nenhuma alteração definida.', 'warning');
                return;
            }

            showConfirm(`Confirmar alteração em massa para ${Object.keys(changes).length} valores distintos?`, () => {
                let count = 0;
                validationData.lines.forEach(line => {
                    if (line.recordType === type) {
                    const currentVal = line.fields[fieldIndex] || '';
                    if (changes.hasOwnProperty(currentVal)) {
                        line.fields[fieldIndex] = changes[currentVal];
                        
                        // SE ALTEROU A COLUNA 0 (TIPO), ATUALIZA O recordType
                        if (fieldIndex === 0) {
                            line.recordType = changes[currentVal];
                        }

                        line.content = line.fields.join(';'); // Reconstrói a linha
                        
                        // Revalida a linha com a regra do NOVO tipo (se mudou) ou do tipo atual
                        line.errors = [];
                        const currentRule = validationRules[line.recordType]; // Pega a regra atualizada
                        
                        if (currentRule) {
                            currentRule.fields.forEach((r, i) => {
                                const val = (line.fields[i] || '').trim();
                                if (r.required && val === '') line.errors.push(`Campo '${r.name}' vazio`);
                                else if (val.length > r.maxLength) line.errors.push(`Campo '${r.name}' excede tamanho`);
                                else if (r.type === 'N' && val !== '' && isNaN(Number(val.replace(',', '.')))) line.errors.push(`Campo '${r.name}' não é numérico`);
                            });
                        }
                        line.status = line.errors.length === 0 ? 'corrected' : 'error';
                        count++;
                    }
                }
            });

            // Atualiza filtro de tipos pois podem ter mudado
            const typeFilter = document.getElementById('typeFilter');
            const massEditTypeSelect = document.getElementById('massEditType');
            const currentFilterVal = typeFilter.value;
            const currentMassEditType = massEditTypeSelect.value;
            
            const uniqueTypes = [...new Set(validationData.lines.map(l => l.recordType))].sort();
            
            // Atualiza Filtro Principal
            typeFilter.innerHTML = '<option value="">Todos</option>';
            uniqueTypes.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.text = t;
                if(t === currentFilterVal) opt.selected = true;
                typeFilter.appendChild(opt);
            });

            // Atualiza Dropdown do Modal (Mass Edit)
            massEditTypeSelect.innerHTML = '<option value="">Selecione...</option>';
            uniqueTypes.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.text = t;
                if(t === currentMassEditType) opt.selected = true;
                massEditTypeSelect.appendChild(opt);
            });

            recalcStats();
            renderLines();
            // Mantém modal aberto para novos ajustes
            showToast(`${count} linhas foram atualizadas!`, 'success');
            
            // Se o tipo selecionado ainda existe, recarrega valores. Se sumiu, reseta campos.
            if (massEditTypeSelect.value) {
                loadMassEditValues();
            } else {
                 // Tipo sumiu (todos convertidos)
                 document.getElementById('massEditField').innerHTML = '<option value="">Selecione...</option>';
                 document.getElementById('massEditField').disabled = true;
                 document.getElementById('massEditTableBody').innerHTML = ''; // Limpa tabela
                 showToast('Todos os registros deste tipo foram migrados/alterados.', 'info');
            }
            
            // Se o editor estiver aberto, atualiza ou fecha
            if (selectedLineId) {
                    const line = validationData.lines.find(l => l.id === selectedLineId);
                    if (line) {
                        renderEditor(line);
                    }
                }
            });
        }

        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('liveToast');
            const toastBody = document.getElementById('toastMessage');
            
            toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
            toastBody.innerText = message;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        let pendingMassEditAction = null;
        let confirmModalInstance = null;

        function showConfirm(msg, action) {
            document.getElementById('confirmMessage').innerText = msg;
            pendingMassEditAction = action;
            const el = document.getElementById('confirmModal');
            if (!confirmModalInstance) {
                confirmModalInstance = new bootstrap.Modal(el);
            }
            confirmModalInstance.show();
        }

        document.getElementById('btnConfirmAction').addEventListener('click', () => {
            try {
                if (pendingMassEditAction) pendingMassEditAction();
            } catch (e) {
                showToast('Erro ao executar ação: ' + e.message, 'danger');
                console.error(e);
            } finally {
                if (confirmModalInstance) confirmModalInstance.hide();
            }
        });

        function updateTransferFields() {
            const type = document.getElementById('transferType').value;
            const sourceSelect = document.getElementById('transferSource');
            const destSelect = document.getElementById('transferDest');
            
            sourceSelect.innerHTML = '<option value="">Selecione...</option>';
            destSelect.innerHTML = '<option value="">Selecione...</option>';
            
            if (!type) {
                sourceSelect.disabled = true;
                destSelect.disabled = true;
                return;
            }

            const rule = validationRules[type];
            if (rule) {
                rule.fields.forEach((f, i) => {
                    const optSource = document.createElement('option');
                    optSource.value = i;
                    optSource.text = f.name;
                    sourceSelect.appendChild(optSource);

                    const optDest = document.createElement('option');
                    optDest.value = i;
                    optDest.text = f.name;
                    destSelect.appendChild(optDest);
                });
                sourceSelect.disabled = false;
                destSelect.disabled = false;
            }
        }

        function applyTransfer() {
            const type = document.getElementById('transferType').value;
            const sourceIdx = parseInt(document.getElementById('transferSource').value);
            const destIdx = parseInt(document.getElementById('transferDest').value);
            const overwrite = document.getElementById('transferOverwrite').checked;
            const clearSource = document.getElementById('transferClear').checked;

            if (!type || isNaN(sourceIdx) || isNaN(destIdx)) {
                showToast('Selecione todos os campos.', 'danger');
                return;
            }

            if (sourceIdx === destIdx) {
                showToast('Origem e destino devem ser diferentes.', 'warning');
                return;
            }

            showConfirm('Confirmar transferência de valores? Esta ação afetará todas as linhas do tipo selecionado.', () => {
                let count = 0;
                validationData.lines.forEach(line => {
                    if (line.recordType === type) {
                        const sourceVal = line.fields[sourceIdx] || '';
                        const destVal = line.fields[destIdx] || '';

                        // Se origem tem valor e (destino vazio OU sobrescrever)
                        if (sourceVal.trim() !== '' && (destVal.trim() === '' || overwrite)) {
                            line.fields[destIdx] = sourceVal;
                            if (clearSource) {
                                line.fields[sourceIdx] = '';
                            }
                            
                            line.content = line.fields.join(';');
                            
                            // Revalida
                            line.errors = [];
                            const rule = validationRules[type];
                            if (rule) {
                                rule.fields.forEach((r, i) => {
                                    const val = (line.fields[i] || '').trim();
                                    if (r.required && val === '') line.errors.push(`Campo '${r.name}' vazio`);
                                    else if (val.length > r.maxLength) line.errors.push(`Campo '${r.name}' excede tamanho`);
                                    else if (r.type === 'N' && val !== '' && isNaN(Number(val.replace(',', '.')))) line.errors.push(`Campo '${r.name}' não é numérico`);
                                });
                            }
                            line.status = line.errors.length === 0 ? 'corrected' : 'error';
                            count++;
                        }
                    }
                });

                recalcStats();
                renderLines();
                // Mantém modal aberto
                // massEditModal.hide();
                showToast(`${count} linhas atualizadas via transferência!`, 'success');
            });
        }

        function autoCorrectAll() {
            showConfirm('Isto tentará corrigir automaticamente erros estruturais (tamanho, campos extras, espaços). \n\nATENÇÃO: Campos obrigatórios vazios NÃO serão preenchidos automaticamente.\n\nContinuar?', () => {
                _executeAutoCorrectAll();
            });
        }

        function _executeAutoCorrectAll() {
            let correctedCount = 0;
            let log = [];

            validationData.lines.forEach(line => {
                if (line.status === 'error') {
                    let lineChanges = [];
                    const rule = validationRules[line.recordType];
                    let originalContent = line.content;

                    // 1. Trim
                    line.fields = line.fields.map(f => f.trim());

                    if (rule) {
                        // 2. Remove Extra Fields
                        if (line.fields.length > rule.fields.length) {
                            const diff = line.fields.length - rule.fields.length;
                            line.fields = line.fields.slice(0, rule.fields.length);
                            lineChanges.push(`Removidos ${diff} campos excedentes`);
                        }

                        // 3. Fix Numeric
                        line.fields = line.fields.map((val, idx) => {
                            const fieldRule = rule.fields[idx];
                            if (fieldRule && fieldRule.type === 'N') {
                                let newVal = val;
                                if (newVal.toUpperCase() === 'S/N') {
                                    newVal = '0';
                                    if (newVal !== val) lineChanges.push(`Campo '${fieldRule.name}' (S/N) ajustado para 0`);
                                } else {
                                    const cleaned = newVal.replace(/[^0-9,.-]/g, '');
                                    if (cleaned !== newVal) {
                                        newVal = cleaned;
                                        lineChanges.push(`Campo '${fieldRule.name}' limpo de caracteres não numéricos`);
                                    }
                                }
                                return newVal;
                            }
                            return val;
                        });

                        // 4. Truncate Fields
                        line.fields = line.fields.map((val, idx) => {
                            const fieldRule = rule.fields[idx];
                            if (fieldRule && val.length > fieldRule.maxLength) {
                                lineChanges.push(`Campo '${fieldRule.name}' truncado`);
                                return val.substring(0, fieldRule.maxLength);
                            }
                            return val;
                        });
                    }

                    line.content = line.fields.join(';');
                    
                    // Re-validate
                    const errors = [];
                    if (rule) {
                        rule.fields.forEach((r, i) => {
                            const val = (line.fields[i] || '').trim();
                            if (r.required && val === '') errors.push(`Campo '${r.name}' vazio`);
                            else if (val.length > r.maxLength) errors.push(`Campo '${r.name}' excede tamanho`);
                            else if (r.type === 'N' && val !== '' && isNaN(Number(val.replace(',', '.')))) errors.push(`Campo '${r.name}' não é numérico`);
                        });
                    }

                    if (errors.length === 0) {
                        line.status = 'corrected';
                        line.errors = [];
                        correctedCount++;
                        log.push({ id: line.id, status: 'success', changes: lineChanges });
                    } else {
                        line.status = 'error';
                        line.errors = errors;
                        if (lineChanges.length > 0 || originalContent !== line.content) {
                             log.push({ id: line.id, status: 'partial', changes: lineChanges, remaining: errors });
                        }
                    }
                }
            });

            recalcStats();
            renderLines(true);
            recalcStats();
            renderLines(true);
            showCorrectionModal(correctedCount, log);
        }

        function showCorrectionModal(count, log) {
            const summary = document.getElementById('correctionSummaryText');
            const container = document.getElementById('correctionLog');
            
            summary.textContent = `${count} linhas foram totalmente corrigidas.`;
            container.innerHTML = '';
            
            if (log.length === 0 && count === 0) {
                container.innerHTML = '<div class="list-group-item text-muted">Nenhuma alteração realizada. As linhas com erro possuem problemas que requerem intervenção manual.</div>';
            } else if (log.length === 0) {
                 container.innerHTML = '<div class="list-group-item text-muted">Apenas espaços em branco foram removidos.</div>';
            } else {
                log.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'list-group-item';
                    
                    let content = `<strong>Linha ${item.id}</strong>: `;
                    if (item.status === 'success') {
                        content += `<span class="text-success">Corrigida</span>. `;
                        content += item.changes.length ? item.changes.join(', ') : 'Espaços removidos.';
                    } else {
                        content += `<span class="text-warning">Parcialmente ajustada</span>. `;
                        content += item.changes.length ? item.changes.join(', ') + '. ' : '';
                        content += `<br><small class="text-danger">Erros restantes: ${item.remaining.join(', ')}</small>`;
                    }
                    
                    div.innerHTML = content;
                    container.appendChild(div);
                });
            }

            const modalEl = document.getElementById('correctionModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        function deleteLine() {
            showConfirm('Excluir linha?', () => {
                validationData.lines = validationData.lines.filter(l => l.id !== selectedLineId);
                recalcStats();
                renderLines();
                document.getElementById('editorContent').classList.remove('d-flex');
                document.getElementById('editorContent').classList.add('d-none');
                document.getElementById('editorEmptyState').classList.remove('d-none');
                document.getElementById('editorEmptyState').classList.add('d-flex');
                document.getElementById('editorTitle').innerHTML = `<i class="bi bi-pencil-square me-2"></i>Editor de Linha`;
                showToast('Linha excluída.', 'success');
            });
        }

        function recalcStats() {
            const s = validationData.stats;
            s.total = validationData.lines.length;
            s.errors = validationData.lines.filter(l => l.status === 'error').length;
            s.valid = validationData.lines.filter(l => l.status === 'valid').length;
            s.corrected = validationData.lines.filter(l => l.status === 'corrected').length;

            document.getElementById('statTotal').innerText = s.total;
            document.getElementById('statErrors').innerText = s.errors;
            document.getElementById('statValid').innerText = s.valid;
            document.getElementById('statCorrected').innerText = s.corrected;
            
            document.getElementById('tabErrorCount').innerText = s.errors;
            document.getElementById('tabValidCount').innerText = s.valid;
            document.getElementById('tabCorrectedCount').innerText = s.corrected;
        }

        function setTab(tab, btn) {
            currentTab = tab;
            btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const listPanel = document.getElementById('listPanel');
            const editorPanel = document.getElementById('editorPanel');

            if (tab === 'original') {
                listPanel.classList.remove('col-md-6');
                listPanel.classList.add('col-12');
                editorPanel.classList.add('d-none');
            } else {
                listPanel.classList.remove('col-12');
                listPanel.classList.add('col-md-6');
                editorPanel.classList.remove('d-none');
            }
            
            renderLines();
        }

        function setViewMode(mode) {
            viewMode = mode;
            document.getElementById('btnViewList').classList.toggle('active', mode === 'list');
            document.getElementById('btnViewGroup').classList.toggle('active', mode === 'group');
            renderLines();
        }

        function setEditMode(mode) {
            editMode = mode;
            document.getElementById('btnDetailed').classList.toggle('active', mode === 'detailed');
            document.getElementById('btnQuick').classList.toggle('active', mode === 'quick');
            if (selectedLineId) renderEditor(validationData.lines.find(l => l.id === selectedLineId));
        }

        function downloadCSV() {
            let csv = "ID;Conteudo;Erros\n";
            validationData.lines.forEach(l => csv += `${l.id};${l.content};${l.errors.join('|')}\n`);
            const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob(["\ufeff"+csv], {type:'text/csv'})); a.download = 'report.csv'; a.click();
        }
        function downloadCorrected() {
            const txt = validationData.lines.map(l => l.content).join('\n');
            const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([txt], {type:'text/plain'})); a.download = 'corrigido.txt'; a.click();
        }
        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text(`Relatório: ${validationData.filename}`, 10, 10);
            doc.setFontSize(10);
            let y = 20;
            validationData.lines.filter(l => l.status === 'error').forEach(l => {
                if(y > 280) { doc.addPage(); y=10; }
                doc.setTextColor(200,0,0);
                // Errors already have column info from PHP? No, PHP generates strings.
                // We need to regenerate errors with column info OR update PHP to include it.
                // Let's rely on the errors array which we will update in PHP.
                // But wait, the PHP update is for *new* uploads. Existing loaded data won't change unless we reload.
                // However, the user is likely to re-validate or re-upload.
                // Actually, let's just print what's in l.errors.
                // If we want column numbers in PDF for *current* session without reload, we might need to re-generate errors here?
                // The PHP change handles the initial load. 
                // The JS saveChanges handles updates.
                // Let's just print the error message.
                l.errors.forEach(err => {
                     if(y > 280) { doc.addPage(); y=10; }
                     doc.text(`Linha ${l.id}: ${err}`, 10, y);
                     y += 6;
                });
            });
            doc.save("erros.pdf");
        }
        
        // --- EXPORTAÇÃO ---
        let currentExportType = 'csv';
        let exportModal;

        function openExportModal(type) {
            currentExportType = type;
            if (!exportModal) exportModal = new bootstrap.Modal(document.getElementById('exportOptionsModal'));
            
            document.getElementById('exportModalTitle').innerHTML = `<i class="bi bi-file-${type === 'csv' ? 'type-csv' : 'pdf'} me-2"></i>Exportar ${type.toUpperCase()}`;
            document.getElementById('expOptFormatInfo').innerText = `O arquivo será gerado em formato ${type.toUpperCase()}.`;
            
            // Defaults
            document.getElementById('expOptErrors').checked = true;
            document.getElementById('expOptValid').checked = (type === 'csv'); // Default valid only for CSV usually? Or all? Let's say CSV all, PDF errors.
            // Actually user asked for options. Let's default to ALL for CSV, and ERRORS for PDF as sensible defaults but user can change.
            if (type === 'pdf') {
                 document.getElementById('expOptValid').checked = false;
                 document.getElementById('expOptSummary').checked = true;
            } else {
                 document.getElementById('expOptValid').checked = true;
                 document.getElementById('expOptSummary').checked = false; // CSV usually raw
            }

            exportModal.show();
        }

        function proceedExport() {
            const includeErrors = document.getElementById('expOptErrors').checked;
            const includeValid = document.getElementById('expOptValid').checked;
            const includeSummary = document.getElementById('expOptSummary').checked;
            
            if (!includeErrors && !includeValid) {
                showToast('Selecione pelo menos um tipo de linha (erros ou válidas).', 'warning');
                return;
            }

            const linesToExport = validationData.lines.filter(l => {
                if (l.status === 'error' && includeErrors) return true;
                if (l.status !== 'error' && includeValid) return true;
                return false;
            });

            if (linesToExport.length === 0) {
                showToast('Nenhuma linha encontrada com os filtros selecionados.', 'warning');
                return;
            }

            const filename = validationData.filename.split('.')[0];
            const timestamp = new Date().toISOString().slice(0,19).replace(/T/g, ' ').replace(/:/g, '-');

            if (currentExportType === 'csv') {
                let csvContent = "";
                
                if (includeSummary) {
                    csvContent += `Relatório de Validação;${validationData.filename}\n`;
                    csvContent += `Data;${new Date().toLocaleString()}\n`;
                    csvContent += `Total Linhas;${linesToExport.length}\n\n`;
                }

                csvContent += "ID;Tipo;Conteudo;Erros\n";
                linesToExport.forEach(l => {
                    // Escape semicolons in content if needed, though they are separators.
                    // Assuming content is already formatted safe enough or is raw. 
                    // Let's quote content if it contains ; ? The validator uses ; as separator internally for display often? 
                    // The user data format likely relies on position.
                    // Let's just dump content.
                    csvContent += `${l.id};${l.recordType};${l.content};${l.errors.join('|')}\n`;
                });
                
                const blob = new Blob(["\ufeff"+csvContent], {type: 'text/csv;charset=utf-8;'});
                const link = document.createElement("a");
                if (link.download !== undefined) {
                    const url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", `${filename}_${currentExportType}_${timestamp}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            } else {
                // PDF Export
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                doc.setFont("Courier");
                
                let y = 15;
                const pageHeight = doc.internal.pageSize.height;
                const margin = 10;
                
                doc.setFontSize(16);
                doc.text("Relatório de Validação R2D2", margin, y);
                y += 10;
                
                doc.setFontSize(10);
                if (includeSummary) {
                    doc.text(`Arquivo: ${validationData.filename}`, margin, y); y += 6;
                    doc.text(`Data: ${new Date().toLocaleString()}`, margin, y); y += 6;
                    doc.text(`Total Exportado: ${linesToExport.length}`, margin, y); y += 6;
                    
                    // Stats of export
                    const errCount = linesToExport.filter(l => l.status === 'error').length;
                    const valCount = linesToExport.length - errCount;
                    doc.text(`Válidas: ${valCount} | Erros: ${errCount}`, margin, y); y += 10;
                    
                    doc.setLineWidth(0.5);
                    doc.line(margin, y, 200, y);
                    y += 10;
                }

                doc.setFontSize(9);
                
                linesToExport.forEach((l, index) => {
                    if (y > pageHeight - 20) {
                        doc.addPage();
                        y = 15;
                    }
                    
                    const prefix = l.status === 'error' ? '[ERRO]' : '[OK]';
                    const errorIndices = [];
                    
                    if (l.status === 'error') {
                        // Parse column indices from error messages: "Campo 'X' (Col. 9) ..."
                        l.errors.forEach(err => {
                            const match = err.match(/\(Col\.\s*(\d+)\)/);
                            if (match && match[1]) {
                                errorIndices.push(parseInt(match[1]) - 1); // 0-based
                            }
                        });
                    }

                    // Render Line Content Field by Field
                    doc.setFontSize(9);
                    const prefixWidth = doc.getTextWidth(`L.${l.id} (${l.recordType}): `);
                    let currentX = margin;
                    
                    doc.setTextColor(l.status === 'error' ? 200 : 0, 0, 0); 
                    doc.text(`L.${l.id} (${l.recordType}): `, currentX, y);
                    currentX += prefixWidth;
                    
                    const fields = l.content.split(';');
                    const separatorWidth = doc.getTextWidth(';');
                    
                    fields.forEach((field, fIdx) => {
                        const isErrorField = errorIndices.includes(fIdx);
                        const fieldText = field;
                        const fieldWidth = doc.getTextWidth(fieldText);
                        
                        // Check wrap
                        if (currentX + fieldWidth + separatorWidth > 190) { // arbitrary right margin limit
                             y += 4; // New line
                             currentX = margin + prefixWidth; // Indent
                             if (y > pageHeight - 15) { doc.addPage(); y = 15; }
                        }
                        
                        if (isErrorField) {
                            doc.setTextColor(255, 0, 0); // Red
                            doc.setFont(undefined, 'bold');
                        } else {
                            doc.setTextColor(80, 80, 80); // Dark Gray
                            doc.setFont(undefined, 'normal');
                        }
                        
                        doc.text(fieldText, currentX, y);
                        currentX += fieldWidth;
                        
                        // Separator
                        doc.setTextColor(150, 150, 150);
                        doc.setFont(undefined, 'normal');
                        // Don't print separator after last field? strictly spec says raw content has separators.
                        if (fIdx < fields.length - 1) {
                            doc.text(';', currentX, y);
                            currentX += separatorWidth;
                        }
                    });
                    
                    // Reset Font/Color
                    doc.setTextColor(0,0,0);
                    
                    y += 4; // Spacing after line content

                     if (l.status === 'error' && l.errors.length > 0) {
                        doc.setTextColor(180, 0, 0);
                        doc.setFontSize(8);
                        l.errors.forEach(err => {
                             if (y > pageHeight - 15) { doc.addPage(); y = 15; }
                             doc.text(`  -> ${err}`, margin + 5, y);
                             y += 4;
                        });
                        doc.setFontSize(9);
                        y += 2; // Spacing
                    }
                    y += 2;
                });
                
                doc.save(`${filename}_report_${timestamp}.pdf`);
            }
            
            exportModal.hide();
            showToast('Arquivo gerado com sucesso!');
        }

        setTimeout(() => renderLines(), 100);
    </script>

<?php endif; ?>
</body>
</html>
