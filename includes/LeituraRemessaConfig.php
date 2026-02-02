<?php

/**
 * Configuração de Leitura de Remessa e Retorno Bancário (CNAB)
 * Baseado nas definições de "Cobrança Escritural - Intelidata".
 * 
 * Esta estrutura visa normalizar as configurações necessárias para processar
 * arquivos CNAB 240 e 400 de múltiplos bancos.
 */

class BoletoConfig
{

    /**
     * @var array Configurações Gerais do Arquivo e Conta
     */
    public $arquivoConfig = [
        'layout_arquivo' => [
            'descricao' => 'Define o padrão do arquivo (CNAB400 ou CNAB240). Fundamental para o parser saber o tamanho da linha.',
            'valor_exemplo' => 'CNAB400',
            'validacao' => ['CNAB400', 'CNAB240'],
            'regra_php' => 'Validar strlen($linha): 400 ou 240 bytes.'
        ],
        'sequencia_remessa' => [
            'descricao' => 'Número sequencial do arquivo de remessa (NSA). Incrementado a cada geração.',
            'valor_exemplo' => 150,
            'tipo' => 'integer',
            'regra_php' => 'Armazenar no banco e incrementar antes de gerar o header do arquivo.'
        ],
        'codigo_convenio' => [
            'descricao' => 'Código do convênio fornecido pelo banco.',
            'valor_exemplo' => '1234567',
            'tipo' => 'string',
            'regra_php' => 'Preencher com str_pad() conforme tamanho exigido pelo layout específico do banco.'
        ],
        'versao_layout' => [
            'descricao' => 'Versão do layout do arquivo (ex: v087).',
            'valor_exemplo' => '087',
            'tipo' => 'string',
            'regra_php' => 'Informado no Header do Arquivo.'
        ],
        'agencia' => [
            'descricao' => 'Número da agência mantenedora da conta.',
            'valor_exemplo' => '1234',
            'tipo' => 'string',
            'regra_php' => 'Remover dígito verificador se o layout pedir separado.'
        ],
        'conta_corrente' => [
            'descricao' => 'Número da conta corrente.',
            'valor_exemplo' => '56789',
            'tipo' => 'string',
            'regra_php' => 'Geralmente requer dígito verificador em campo separado ou junto.'
        ]
    ];

    /**
     * @var array Definições da Carteira e Boleto
     */
    public $carteiraConfig = [
        'carteira' => [
            'descricao' => 'Código da carteira de cobrança contratada.',
            'valor_exemplo' => '109',
            'tipo' => 'string',
            'regra_php' => 'Campo obrigatório em vários segmentos do detalhe.'
        ],
        'variacao_carteira' => [
            'descricao' => 'Variação da carteira (comum no Banco do Brasil).',
            'valor_exemplo' => '019',
            'tipo' => 'string',
            'regra_php' => 'Se não houver, deixar vazio ou zero conforme banco.'
        ],
        'especie_titulo' => [
            'descricao' => 'Tipo do documento de cobrança (Duplicata, Nota Promissória, etc).',
            'valor_exemplo' => '01', // 01 costuma ser Duplicata Mercantil
            'tipo' => 'string',
            'regra_php' => 'Mapear de-para interno (ex: "DM" -> "01").'
        ],
        'instrucao_1' => [
            'descricao' => 'Primeira instrução de cobrança (ex: Protestar, Devolver).',
            'valor_exemplo' => '06', // Protestar
            'tipo' => 'string'
        ],
        'instrucao_2' => [
            'descricao' => 'Segunda instrução de cobrança ou complemento.',
            'valor_exemplo' => '05', // Dias para protesto
            'tipo' => 'string'
        ]
    ];

    /**
     * @var array Mapeamento de Códigos de Ocorrência (Retorno)
     */
    public $ocorrenciasConfig = [
        // Padrão Geral (pode variar por banco, ideal ser sobrescrito nas child classes dos bancos)
        '02' => [
            'acao' => 'entrada_confirmada',
            'descricao' => 'Entrada Confirmada. O banco aceitou o boleto.',
            'regra_php' => 'Atualizar status do título para "Registrado".'
        ],
        '03' => [
            'acao' => 'entrada_rejeitada',
            'descricao' => 'Entrada Rejeitada. O banco recusou o boleto (erro de dados).',
            'regra_php' => 'Marcar título com erro e salvar motivo da rejeição.'
        ],
        '06' => [
            'acao' => 'liquidacao',
            'descricao' => 'Liquidação. Título pago pelo sacado.',
            'regra_php' => 'Baixar o título (recebimento), gerar movimentação financeira.'
        ],
        '09' => [
            'acao' => 'baixa_simples',
            'descricao' => 'Baixa Simples. Título baixado por solicitação ou decurso de prazo.',
            'regra_php' => 'Cancelar/Baixar o título sem gerar recebimento financeiro.'
        ],
        '10' => [
            'acao' => 'baixa_protestada',
            'descricao' => 'Baixado e enviado para protesto.',
            'regra_php' => 'Atualizar status para "Em Protesto".'
        ]
    ];

    /**
     * @var array Identificadores e PIX
     */
    public $identificadoresConfig = [
        'nosso_numero' => [
            'descricao' => 'Identificador único do título no banco (Chave Primária Externa).',
            'valor_exemplo' => '1234567890',
            'tipo' => 'string',
            'regra_php' => 'Fundamental para encontrar o título no retorno. Se não bater, logar erro "Título não encontrado".'
        ],
        'seu_numero' => [
            'descricao' => 'Número do documento no sistema interno (Chave Primária Interna).',
            'valor_exemplo' => 'VENDA-1001',
            'tipo' => 'string',
            'regra_php' => 'Usado como backup para localizar o título se o Nosso Número falhar/estiver zerado.'
        ],
        'chave_pix' => [
            'descricao' => 'Chave PIX vinculada à conta para recebimento híbrido (Boleto + QR Code).',
            'valor_exemplo' => '12.345.678/0001-90', // CNPJ
            'tipo' => 'string',
            'regra_php' => 'Se presente, gerar segmento PIX (ex: Segmento Y no CNAB240) ou info complementar no CNAB400.'
        ]
    ];
}
?>