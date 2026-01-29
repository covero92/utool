<?php

class FileValidator {

    public static $validationRules = [
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

    private $initialData;

    public function __construct() {
        $this->initialData = [
            'lines' => [],
            'stats' => ['total' => 0, 'valid' => 0, 'errors' => 0, 'corrected' => 0],
            'recordTypes' => [],
            'filename' => ''
        ];
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = '';
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $content = file_get_contents($_FILES['file']['tmp_name']);
                $this->initialData['filename'] = $_FILES['file']['name'];
            } elseif (isset($_POST['content'])) {
                $content = $_POST['content'];
                $this->initialData['filename'] = 'conteudo_colado.txt';
            }

            if ($content) {
                $content = $this->cleanContent($content);
                
                // Check if it's XML (Simple check)
                $isXml = (strpos($content, '<?xml') !== false || strpos($content, '<DPS') !== false);

                if ($isXml) {
                    $this->processXML($content);
                } else {
                    $this->processTXT($content);
                }
            }
        }
        return $this->initialData;
    }

    private function cleanContent($content) {
        // Remove BOM
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        
        // Ensure UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }
        
        return $content;
    }

    private function processXML($content) {
        $this->initialData['recordTypes'][] = 'XML-DPS';
        
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadXML($content);
        $errors = libxml_get_errors();
        
        if ($errors) {
             $this->initialData['lines'][] = [
                'id' => 1,
                'content' => 'Erro ao ler XML',
                'originalContent' => $content,
                'recordType' => 'ERO',
                'errors' => ["XML Malformado: " . $errors[0]->message],
                'status' => 'error',
                'fields' => []
            ];
            $this->initialData['stats']['total'] = 1;
            $this->initialData['stats']['errors'] = 1;
        } else {
            $dpsNodes = $dom->getElementsByTagName('infDPS');
            $this->initialData['stats']['total'] = $dpsNodes->length;
            
            if ($dpsNodes->length === 0) {
                 $dpsNodes = $dom->getElementsByTagName('DPS'); // Fallback
            }

            $i = 0;
            foreach ($dpsNodes as $dps) {
                $i++;
                $lineErrors = [];
                $dpsId = $dps->getAttribute('Id') ?: "DPS #$i";
                
                $pisCofinsNodes = $dps->getElementsByTagName('piscofins');
                if ($pisCofinsNodes->length > 0) {
                    $pc = $pisCofinsNodes->item(0);
                    
                    $vBC = (float) $pc->getElementsByTagName('vBCPisCofins')->item(0)?->nodeValue;
                    $pPis = (float) $pc->getElementsByTagName('pAliqPis')->item(0)?->nodeValue;
                    $pCofins = (float) $pc->getElementsByTagName('pAliqCofins')->item(0)?->nodeValue;
                    $vPis = (float) $pc->getElementsByTagName('vPis')->item(0)?->nodeValue;
                    $vCofins = (float) $pc->getElementsByTagName('vCofins')->item(0)?->nodeValue;
                    
                    // CALCULATION VALIDATION
                    $calcPis = round($vBC * ($pPis / 100), 2);
                    $calcCofins = round($vBC * ($pCofins / 100), 2);
                    
                    if (abs($calcPis - $vPis) > 0.02) {
                         $lineErrors[] = "O valor do PIS informado ($vPis) não corresponde ao cálculo da BC ($vBC) x Alíquota ($pPis%). Valor esperado: $calcPis.";
                    }
                    
                    if (abs($calcCofins - $vCofins) > 0.02) {
                         $lineErrors[] = "O valor do COFINS informado ($vCofins) não corresponde ao cálculo da BC ($vBC) x Alíquota ($pCofins%). Valor esperado: $calcCofins.";
                    }
                } 
                
                if (!empty($lineErrors)) $this->initialData['stats']['errors']++;
                else $this->initialData['stats']['valid']++;

                $this->initialData['lines'][] = [
                    'id' => $i,
                    'content' => "DPS ID: $dpsId", 
                    'originalContent' => $dom->saveXML($dps), 
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
    }

    private function processTXT($content) {
        $lines = explode("\n", $content);
        $this->initialData['stats']['total'] = count($lines);
        
        foreach ($lines as $index => $lineRaw) {
            $lineRaw = trim($lineRaw);
            if (empty($lineRaw)) {
                $this->initialData['stats']['total']--; 
                continue;
            }

            $lineNumber = $index + 1;
            $recordType = mb_substr($lineRaw, 0, 2);
            $recordType = preg_replace('/[^A-Z0-9]/', '', $recordType);
            
            $fields = explode(";", $lineRaw); 
            $fields = array_map('trim', $fields);
            
            // CORREÇÃO PONTO FLUTUANTE DE PREÇO NO PR
            if ($recordType === 'PR' && isset($fields[10])) {
                $val = $fields[10];
                if (strpos($val, ',') !== false) {
                    $val = str_replace('.', '', $val);
                    $val = str_replace(',', '.', $val);
                } 
                elseif (substr_count($val, '.') > 1) {
                    $lastDot = strrpos($val, '.');
                    $val = str_replace('.', '', substr($val, 0, $lastDot)) . substr($val, $lastDot);
                }
                $fields[10] = $val;
            }

            $lineErrors = [];

            if (!in_array($recordType, $this->initialData['recordTypes'])) {
                $this->initialData['recordTypes'][] = $recordType;
            }

            if (!isset(self::$validationRules[$recordType])) {
                $lineErrors[] = "Tipo de registro desconhecido '$recordType'.";
            } else {
                $rule = self::$validationRules[$recordType];
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

            if (!empty($lineErrors)) $this->initialData['stats']['errors']++;
            else $this->initialData['stats']['valid']++;

            $this->initialData['lines'][] = [
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
