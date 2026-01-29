<?php
$csts = array (
  '000' => 
  array (
    'codigo' => '000',
    'descricao' => 'Tributação integral',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '000001',
        'descricao' => 'Situações tributadas integralmente pelo IBS e CBS.',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETA',
          2 => 'BPETM',
          3 => 'CTE',
          4 => 'CTEOS',
          5 => 'NF3E',
          6 => 'NFAG',
          7 => 'NFCE',
          8 => 'NFCOM',
          9 => 'NFE',
          10 => 'NFSE',
          11 => 'NFGAS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art4',
      ),
      1 => 
      array (
        'codigo' => '000002',
        'descricao' => 'Exploração de via',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSVIA',
          1 => 'NFGAS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art11',
      ),
      2 => 
      array (
        'codigo' => '000003',
        'descricao' => 'Regime automotivo - projetos incentivados (art. 311)',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => true,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art311',
      ),
      3 => 
      array (
        'codigo' => '000004',
        'descricao' => 'Regime automotivo - projetos incentivados (art. 312)',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => true,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art312',
      ),
    ),
  ),
  '010' => 
  array (
    'codigo' => '010',
    'descricao' => 'Tributação com alíquotas uniformes',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '010001',
        'descricao' => 'Operações do FGTS não realizadas pela Caixa Econômica Federal',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '5 - Uniforme Setorial',
        'dfes' => 
        array (
          0 => 'NFSE',
          1 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art212',
      ),
      1 => 
      array (
        'codigo' => '010002',
        'descricao' => 'Operações do serviço financeiro',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '5 - Uniforme Setorial',
        'dfes' => 
        array (
          0 => 'NFSE',
          1 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
    ),
  ),
  '011' => 
  array (
    'codigo' => '011',
    'descricao' => 'Tributação com alíquotas uniformes reduzidas',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '011001',
        'descricao' => 'Planos de assistência funerária.',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '4 - Uniforme Nacional',
        'dfes' => 
        array (
          0 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art236',
      ),
      1 => 
      array (
        'codigo' => '011002',
        'descricao' => 'Planos de assistência à saúde',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '4 - Uniforme Nacional',
        'dfes' => 
        array (
          0 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art237',
      ),
      2 => 
      array (
        'codigo' => '011003',
        'descricao' => 'Intermediação de planos de assistência à saúde',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '4 - Uniforme Nacional',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art240',
      ),
      3 => 
      array (
        'codigo' => '011004',
        'descricao' => 'Concursos e prognósticos',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '4 - Uniforme Nacional',
        'dfes' => 
        array (
          0 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art246',
      ),
      4 => 
      array (
        'codigo' => '011005',
        'descricao' => 'Planos de assistência à saúde de animais domésticos',
        'reducao_ibs' => '30.00',
        'reducao_cbs' => '30.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '4 - Uniforme Nacional',
        'dfes' => 
        array (
          0 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art243',
      ),
    ),
  ),
  200 => 
  array (
    'codigo' => '200',
    'descricao' => 'Alíquota reduzida',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => false,
      'reducao_aliquota' => true,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '200001',
        'descricao' => 'Aquisições realizadas entre empresas autorizadas a operar em zonas de processamento de exportação',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'CTE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art103',
      ),
      1 => 
      array (
        'codigo' => '200002',
        'descricao' => 'Fornecimento ou importação para produtor rural não contribuinte ou TAC',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art110',
      ),
      2 => 
      array (
        'codigo' => '200003',
        'descricao' => 'Vendas de produtos destinados à alimentação humana (Anexo I)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '1',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art125',
      ),
      3 => 
      array (
        'codigo' => '200004',
        'descricao' => 'Venda de dispositivos médicos (Anexo XII)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '12',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art144',
      ),
      4 => 
      array (
        'codigo' => '200005',
        'descricao' => 'Venda de dispositivos médicos adquiridos por órgãos da administração pública (Anexo IV)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '4',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art144',
      ),
      5 => 
      array (
        'codigo' => '200006',
        'descricao' => 'Situação de emergência de saúde pública reconhecida pelo Poder público (Anexo XII)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art144',
      ),
      6 => 
      array (
        'codigo' => '200007',
        'descricao' => 'Fornecimento dos dispositivos de acessibilidade próprios para pessoas com deficiência (Anexo XIII)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '13',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art145',
      ),
      7 => 
      array (
        'codigo' => '200008',
        'descricao' => 'Fornecimento dos dispositivos de acessibilidade próprios para pessoas com deficiência adquiridos por órgãos da administração pública (Anexo V)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '5',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art145',
      ),
      8 => 
      array (
        'codigo' => '200009',
        'descricao' => 'Fornecimento de medicamentos (Anexo XIV)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '14',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art146',
      ),
      9 => 
      array (
        'codigo' => '200010',
        'descricao' => 'Fornecimento dos medicamentos registrados na Anvisa, adquiridos por órgãos da administração pública',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art146',
      ),
      10 => 
      array (
        'codigo' => '200011',
        'descricao' => 'Fornecimento das composições para nutrição enteral e parenteral quando adquiridas por órgãos da administração pública (Anexo VI)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '6',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art146',
      ),
      11 => 
      array (
        'codigo' => '200012',
        'descricao' => 'Situação de emergência de saúde pública reconhecida pelo Poder público (Anexo XIV)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art146',
      ),
      12 => 
      array (
        'codigo' => '200013',
        'descricao' => 'Fornecimento de tampões higiênicos, absorventes higiênicos internos ou externos',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art147',
      ),
      13 => 
      array (
        'codigo' => '200014',
        'descricao' => 'Fornecimento dos produtos hortícolas, frutas e ovos (Anexo XV)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '15',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art148',
      ),
      14 => 
      array (
        'codigo' => '200015',
        'descricao' => 'Venda de automóveis de passageiros de fabricação nacional adquiridos por motoristas profissionais ou pessoas com deficiência',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art149',
      ),
      15 => 
      array (
        'codigo' => '200016',
        'descricao' => 'Prestação de serviços de pesquisa e desenvolvimento por Instituição Científica, Tecnológica e de Inovação (ICT)',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art156',
      ),
      16 => 
      array (
        'codigo' => '200017',
        'descricao' => 'Operações relacionadas ao FGTS',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
          1 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art212',
      ),
      17 => 
      array (
        'codigo' => '200018',
        'descricao' => 'Operações de resseguro e retrocessão',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art223',
      ),
      18 => 
      array (
        'codigo' => '200019',
        'descricao' => 'Importador dos serviços financeiros contribuinte',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art231',
      ),
      19 => 
      array (
        'codigo' => '200020',
        'descricao' => 'Operação praticada por sociedades cooperativas optantes por regime específico do IBS e CBS',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art271',
      ),
      20 => 
      array (
        'codigo' => '200021',
        'descricao' => 'Serviços de transporte público coletivo de passageiros ferroviário e hidroviário',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'BPETM',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art285',
      ),
      21 => 
      array (
        'codigo' => '200022',
        'descricao' => 'Operação originada fora da ZFM que destine bem material industrializado a contribuinte estabelecido na ZFM',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art445',
      ),
      22 => 
      array (
        'codigo' => '200023',
        'descricao' => 'Operação realizada por indústria incentivada que destine bem material intermediário para outra indústria incentivada na ZFM',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art448',
      ),
      23 => 
      array (
        'codigo' => '200024',
        'descricao' => 'Operação originada fora das Áreas de Livre Comércio destinadas a contribuinte estabelecido nas Áreas de Livre Comércio',
        'reducao_ibs' => '100.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art463',
      ),
      24 => 
      array (
        'codigo' => '200025',
        'descricao' => 'Fornecimento dos serviços de educação relacionados ao Programa Universidade para Todos (Prouni)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '100.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art308',
      ),
      25 => 
      array (
        'codigo' => '200026',
        'descricao' => 'Locação de imóveis localizados nas zonas reabilitadas',
        'reducao_ibs' => '80.00',
        'reducao_cbs' => '80.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art158',
      ),
      26 => 
      array (
        'codigo' => '200027',
        'descricao' => 'Operações de locação, cessão onerosa e arrendamento de bens imóveis',
        'reducao_ibs' => '70.00',
        'reducao_cbs' => '70.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFABI',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art261',
      ),
      27 => 
      array (
        'codigo' => '200028',
        'descricao' => 'Fornecimento dos serviços de educação (Anexo II)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '2',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art129',
      ),
      28 => 
      array (
        'codigo' => '200029',
        'descricao' => 'Fornecimento dos serviços de saúde humana (Anexo III)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '3',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art130',
      ),
      29 => 
      array (
        'codigo' => '200030',
        'descricao' => 'Venda dos dispositivos médicos (Anexo IV)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '4',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art131',
      ),
      30 => 
      array (
        'codigo' => '200031',
        'descricao' => 'Fornecimento dos dispositivos de acessibilidade próprios para pessoas com deficiência (Anexo V)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '5',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art132',
      ),
      31 => 
      array (
        'codigo' => '200032',
        'descricao' => 'Fornecimento dos medicamentos registrados na Anvisa ou produzidos por farmácias de manipulação, ressalvados os medicamentos sujeitos à alíquota zero',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art133',
      ),
      32 => 
      array (
        'codigo' => '200033',
        'descricao' => 'Fornecimento das composições para nutrição enteral e parenteral (Anexo VI)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '6',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art133',
      ),
      33 => 
      array (
        'codigo' => '200034',
        'descricao' => 'Fornecimento dos alimentos destinados ao consumo humano (Anexo VII)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '7',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art135',
      ),
      34 => 
      array (
        'codigo' => '200035',
        'descricao' => 'Fornecimento dos produtos de higiene pessoal e limpeza (Anexo VIII)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '8',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art136',
      ),
      35 => 
      array (
        'codigo' => '200036',
        'descricao' => 'Fornecimento de produtos agropecuários, aquícolas, pesqueiros, florestais e extrativistas vegetais in natura',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art137',
      ),
      36 => 
      array (
        'codigo' => '200037',
        'descricao' => 'Fornecimento de serviços ambientais de conservação ou recuperação da vegetação nativa',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art137',
      ),
      37 => 
      array (
        'codigo' => '200038',
        'descricao' => 'Fornecimento dos insumos agropecuários e aquícolas (Anexo IX)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '9',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art138',
      ),
      38 => 
      array (
        'codigo' => '200039',
        'descricao' => 'Fornecimento dos serviços e o licenciamento ou cessão dos direitos destinados às produções nacionais artísticas (Anexo X)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '10',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art139',
      ),
      39 => 
      array (
        'codigo' => '200040',
        'descricao' => 'Fornecimento de serviços de comunicação institucional à administração pública',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art140',
      ),
      40 => 
      array (
        'codigo' => '200041',
        'descricao' => 'Fornecimento de serviço de educação desportiva (art. 141. I)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art141',
      ),
      41 => 
      array (
        'codigo' => '200042',
        'descricao' => 'Fornecimento de serviço de educação desportiva (art. 141. II)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art141',
      ),
      42 => 
      array (
        'codigo' => '200043',
        'descricao' => 'Fornecimento à administração pública dos serviços e dos bens relativos à soberania (Anexo XI)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '11',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art142',
      ),
      43 => 
      array (
        'codigo' => '200044',
        'descricao' => 'Operações e prestações de serviços de segurança da informação e segurança cibernética desenv por sociedade que tenha sócio brasileiro (Anexo XI)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '11',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art142',
      ),
      44 => 
      array (
        'codigo' => '200045',
        'descricao' => 'Operações relacionadas a projetos de reabilitação urbana de zonas históricas e de áreas críticas de recuperação e reconversão urbanística',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFABI',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art158',
      ),
      45 => 
      array (
        'codigo' => '200046',
        'descricao' => 'Operações com bens imóveis',
        'reducao_ibs' => '50.00',
        'reducao_cbs' => '50.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFABI',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art261',
      ),
      46 => 
      array (
        'codigo' => '200047',
        'descricao' => 'Bares e Restaurantes',
        'reducao_ibs' => '40.00',
        'reducao_cbs' => '40.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art275',
      ),
      47 => 
      array (
        'codigo' => '200048',
        'descricao' => 'Hotelaria, Parques de Diversão e Parques Temáticos',
        'reducao_ibs' => '40.00',
        'reducao_cbs' => '40.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art281',
      ),
      48 => 
      array (
        'codigo' => '200049',
        'descricao' => 'Transporte coletivo de passageiros rodoviário, ferroviário e hidroviário',
        'reducao_ibs' => '40.00',
        'reducao_cbs' => '40.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'BPE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art286',
      ),
      49 => 
      array (
        'codigo' => '200050',
        'descricao' => 'Serviços de transporte aéreo regional coletivo de passageiros ou de carga',
        'reducao_ibs' => '40.00',
        'reducao_cbs' => '40.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'BPETA',
          1 => 'CTE',
          2 => 'CTEOS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art287',
      ),
      50 => 
      array (
        'codigo' => '200051',
        'descricao' => 'Agências de Turismo',
        'reducao_ibs' => '40.00',
        'reducao_cbs' => '40.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art289',
      ),
      51 => 
      array (
        'codigo' => '200052',
        'descricao' => 'Prestação de serviços de profissões intelectuais',
        'reducao_ibs' => '30.00',
        'reducao_cbs' => '30.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art127',
      ),
    ),
  ),
  220 => 
  array (
    'codigo' => '220',
    'descricao' => 'Alíquota fixa',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '220001',
        'descricao' => 'Incorporação imobiliária submetida ao regime especial de tributação',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '1 - Fixa',
        'dfes' => 
        array (
          0 => 'NFABI',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art485',
      ),
      1 => 
      array (
        'codigo' => '220002',
        'descricao' => 'Incorporação imobiliária submetida ao regime especial de tributação',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '1 - Fixa',
        'dfes' => 
        array (
          0 => 'NFABI',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art485',
      ),
      2 => 
      array (
        'codigo' => '220003',
        'descricao' => 'Alienação de imóvel decorrente de parcelamento do solo',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '1 - Fixa',
        'dfes' => 
        array (
          0 => 'NFABI',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art486',
      ),
    ),
  ),
  221 => 
  array (
    'codigo' => '221',
    'descricao' => 'Alíquota fixa proporcional',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '221001',
        'descricao' => 'Locação, cessão onerosa ou arrendamento de bem imóvel com alíquota sobre a receita bruta',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '1 - Fixa',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
    ),
  ),
  222 => 
  array (
    'codigo' => '222',
    'descricao' => 'Redução de Base de Cálculo',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => true,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '222001',
        'descricao' => 'Transporte internacional de passageiros, caso os trechos de ida e volta sejam vendidos em conjunto',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETA',
          2 => 'CTEOS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art12',
      ),
    ),
  ),
  400 => 
  array (
    'codigo' => '400',
    'descricao' => 'Isenção',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '400001',
        'descricao' => 'Fornecimento de serviços de transporte público coletivo de passageiros rodoviário e metroviário',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETM',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art157',
      ),
    ),
  ),
  410 => 
  array (
    'codigo' => '410',
    'descricao' => 'Imunidade e não incidência',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '410001',
        'descricao' => 'Fornecimento de bonificações quando constem no documento fiscal e que não dependam de evento posterior',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETA',
          2 => 'BPETM',
          3 => 'CTE',
          4 => 'CTEOS',
          5 => 'NF3E',
          6 => 'NFABI',
          7 => 'NFAG',
          8 => 'NFCE',
          9 => 'NFCOM',
          10 => 'NFE',
          11 => 'NFSE',
          12 => 'NFGAS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art5',
      ),
      1 => 
      array (
        'codigo' => '410002',
        'descricao' => 'Transferências entre estabelecimentos pertencentes ao mesmo contribuinte',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art6',
      ),
      2 => 
      array (
        'codigo' => '410003',
        'descricao' => 'Doações sem contraprestação em benefício do doador',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETA',
          2 => 'BPETM',
          3 => 'CTE',
          4 => 'CTEOS',
          5 => 'NF3E',
          6 => 'NFABI',
          7 => 'NFAG',
          8 => 'NFCE',
          9 => 'NFCOM',
          10 => 'NFE',
          11 => 'NFSE',
          12 => 'NFSVIA',
          13 => 'NFGAS',
          14 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art6',
      ),
      3 => 
      array (
        'codigo' => '410004',
        'descricao' => 'Exportações de bens e serviços',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETA',
          2 => 'CTE',
          3 => 'CTEOS',
          4 => 'NF3E',
          5 => 'NFCOM',
          6 => 'NFE',
          7 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art8',
      ),
      4 => 
      array (
        'codigo' => '410005',
        'descricao' => 'Fornecimentos realizados pela União, pelos Estados, pelo Distrito Federal e pelos Municípios',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFAG',
          1 => 'NFCE',
          2 => 'NFE',
          3 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art9',
      ),
      5 => 
      array (
        'codigo' => '410006',
        'descricao' => 'Fornecimentos realizados por entidades religiosas e templos de qualquer culto',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art9',
      ),
      6 => 
      array (
        'codigo' => '410007',
        'descricao' => 'Fornecimentos realizados por partidos políticos',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art9',
      ),
      7 => 
      array (
        'codigo' => '410008',
        'descricao' => 'Fornecimentos de livros, jornais, periódicos e do papel destinado a sua impressão',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFCOM',
          2 => 'NFE',
          3 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art9',
      ),
      8 => 
      array (
        'codigo' => '410009',
        'descricao' => 'Fornecimentos de fonogramas e videofonogramas musicais produzidos no Brasil',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFCOM',
          2 => 'NFE',
          3 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art9',
      ),
      9 => 
      array (
        'codigo' => '410010',
        'descricao' => 'Fornecimentos de serviço de comunicação nas modalidades de radiodifusão sonora e de sons e imagens de recepção livre e gratuita',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCOM',
          1 => 'NFSE',
          2 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art9',
      ),
      10 => 
      array (
        'codigo' => '410011',
        'descricao' => 'Fornecimentos de ouro, quando definido em lei como ativo financeiro ou instrumento cambial',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art9',
      ),
      11 => 
      array (
        'codigo' => '410012',
        'descricao' => 'Fornecimento de condomínio edilício não optante pelo regime regular',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art26',
      ),
      12 => 
      array (
        'codigo' => '410013',
        'descricao' => 'Exportações de combustíveis',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art98',
      ),
      13 => 
      array (
        'codigo' => '410014',
        'descricao' => 'Fornecimento de produtor rural não contribuinte',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => true,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art164',
      ),
      14 => 
      array (
        'codigo' => '410015',
        'descricao' => 'Fornecimento por transportador autônomo não contribuinte',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'CTE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art169',
      ),
      15 => 
      array (
        'codigo' => '410016',
        'descricao' => 'Fornecimento ou aquisição de resíduos sólidos',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => true,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art170',
      ),
      16 => 
      array (
        'codigo' => '410017',
        'descricao' => 'Aquisição de bem móvel com crédito presumido sob condição de revenda realizada',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art171',
      ),
      17 => 
      array (
        'codigo' => '410018',
        'descricao' => 'Operações relacionadas aos fundos garantidores e executores de políticas públicas',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFABI',
          1 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art213',
      ),
      18 => 
      array (
        'codigo' => '410019',
        'descricao' => 'Exclusão da gorjeta na base de cálculo no fornecimento de alimentação',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art274',
      ),
      19 => 
      array (
        'codigo' => '410020',
        'descricao' => 'Exclusão do valor de intermediação na base de cálculo no fornecimento de alimentação',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art274',
      ),
      20 => 
      array (
        'codigo' => '410021',
        'descricao' => 'Contribuição de que trata o art. 149-A da Constituição Federal',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NF3E',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art12',
      ),
      21 => 
      array (
        'codigo' => '410022',
        'descricao' => 'Consolidação da propriedade do bem pelo credor',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFABI',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      22 => 
      array (
        'codigo' => '410023',
        'descricao' => 'Alienação de bens móveis ou imóveis que tenham sido objeto de garantia em que o prestador da garantia não seja contribuinte',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFABI',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      23 => 
      array (
        'codigo' => '410024',
        'descricao' => 'Consolidação da propriedade do bem pelo grupo de consórcio',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFABI',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      24 => 
      array (
        'codigo' => '410025',
        'descricao' => 'Alienação de bem que tenha sido objeto de garantia em que o prestador da garantia não seja contribuinte',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFABI',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      25 => 
      array (
        'codigo' => '410026',
        'descricao' => 'Doação com anulação de crédito',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => true,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETA',
          2 => 'BPETM',
          3 => 'CTE',
          4 => 'CTEOS',
          5 => 'NF3E',
          6 => 'NFABI',
          7 => 'NFAG',
          8 => 'NFCE',
          9 => 'NFCOM',
          10 => 'NFE',
          11 => 'NFSE',
          12 => 'NFSVIA',
          13 => 'NFGAS',
          14 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      26 => 
      array (
        'codigo' => '410027',
        'descricao' => 'Exportação de serviço ou de bem imaterial',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'CTE',
          1 => 'CTEOS',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art80',
      ),
      27 => 
      array (
        'codigo' => '410028',
        'descricao' => 'Operações com bens imóveis realizadas por pessoas físicas não consideradas contribuintes',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
          2 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art251',
      ),
      28 => 
      array (
        'codigo' => '410029',
        'descricao' => 'Operações acobertadas somente pelo ICMS',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art4',
      ),
      29 => 
      array (
        'codigo' => '410030',
        'descricao' => 'Estorno de crédito por perecimento, deteriorização, roubo, furto ou extravio.',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => true,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art47',
      ),
      30 => 
      array (
        'codigo' => '410031',
        'descricao' => 'Fornecimento em período anterior ao início de vigência de incidências de CBS e IBS',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NF3E',
          1 => 'NFAG',
          2 => 'NFCOM',
          3 => 'NFGAS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art544',
      ),
      31 => 
      array (
        'codigo' => '410999',
        'descricao' => 'Operações não onerosas sem previsão de tributação, não especificadas anteriormente',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'BPE',
          1 => 'BPETA',
          2 => 'BPETM',
          3 => 'CTE',
          4 => 'CTEOS',
          5 => 'NF3E',
          6 => 'NFABI',
          7 => 'NFAG',
          8 => 'NFCE',
          9 => 'NFCOM',
          10 => 'NFE',
          11 => 'NFSE',
          12 => 'NFGAS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art4',
      ),
    ),
  ),
  510 => 
  array (
    'codigo' => '510',
    'descricao' => 'Diferimento',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => true,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '510001',
        'descricao' => 'Operações, sujeitas a diferimento, com energia elétrica, relativas à geração, comercialização, distribuição e transmissão',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NF3E',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art28',
      ),
    ),
  ),
  515 => 
  array (
    'codigo' => '515',
    'descricao' => 'Diferimento com redução de alíquota',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => false,
      'reducao_aliquota' => true,
      'transferencia_credito' => false,
      'diferimento' => true,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '515001',
        'descricao' => 'Operações, sujeitas a diferimento, com insumos agropecuários e aquícolas (Anexo IX)',
        'reducao_ibs' => '60.00',
        'reducao_cbs' => '60.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
    ),
  ),
  550 => 
  array (
    'codigo' => '550',
    'descricao' => 'Suspensão',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '550001',
        'descricao' => 'Exportações de bens materiais',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art82',
      ),
      1 => 
      array (
        'codigo' => '550002',
        'descricao' => 'Regime de Trânsito',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art84',
      ),
      2 => 
      array (
        'codigo' => '550003',
        'descricao' => 'Regimes de Depósito (art. 85)',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art85',
      ),
      3 => 
      array (
        'codigo' => '550004',
        'descricao' => 'Regimes de Depósito (art. 87)',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art87',
      ),
      4 => 
      array (
        'codigo' => '550005',
        'descricao' => 'Regimes de Depósito (art. 87, Parágrafo único)',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art87',
      ),
      5 => 
      array (
        'codigo' => '550006',
        'descricao' => 'Regimes de Permanência Temporária',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art88',
      ),
      6 => 
      array (
        'codigo' => '550007',
        'descricao' => 'Regimes de Aperfeiçoamento',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art90',
      ),
      7 => 
      array (
        'codigo' => '550008',
        'descricao' => 'Importação de bens para o Regime de Repetro-Temporário',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art93',
      ),
      8 => 
      array (
        'codigo' => '550009',
        'descricao' => 'GNL-Temporário',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art93',
      ),
      9 => 
      array (
        'codigo' => '550010',
        'descricao' => 'Repetro-Permanente',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art93',
      ),
      10 => 
      array (
        'codigo' => '550011',
        'descricao' => 'Repetro-Industrialização',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art93',
      ),
      11 => 
      array (
        'codigo' => '550012',
        'descricao' => 'Repetro-Nacional',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art93',
      ),
      12 => 
      array (
        'codigo' => '550013',
        'descricao' => 'Repetro-Entreposto',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art93',
      ),
      13 => 
      array (
        'codigo' => '550014',
        'descricao' => 'Zona de Processamento de Exportação',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art99',
      ),
      14 => 
      array (
        'codigo' => '550015',
        'descricao' => 'Regime Tributário para Incentivo à Modernização e à Ampliação da Estrutura Portuária',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art105',
      ),
      15 => 
      array (
        'codigo' => '550016',
        'descricao' => 'Regime Especial de Incentivos para o Desenvolvimento da Infraestrutura',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art106',
      ),
      16 => 
      array (
        'codigo' => '550017',
        'descricao' => 'Regime Tributário para Incentivo à Atividade Econômica Naval',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art107',
      ),
      17 => 
      array (
        'codigo' => '550018',
        'descricao' => 'Desoneração da aquisição de bens de capital',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art109',
      ),
      18 => 
      array (
        'codigo' => '550019',
        'descricao' => 'Importação de bem material por indústria incentivada para utilização na ZFM',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art443',
      ),
      19 => 
      array (
        'codigo' => '550020',
        'descricao' => 'Áreas de livre comércio',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art461',
      ),
      20 => 
      array (
        'codigo' => '550021',
        'descricao' => 'Industrialização destinada a exportações',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => true,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
    ),
  ),
  620 => 
  array (
    'codigo' => '620',
    'descricao' => 'Tributação Monofásica',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => true,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '620001',
        'descricao' => 'Tributação monofásica sobre combustíveis',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art172',
      ),
      1 => 
      array (
        'codigo' => '620002',
        'descricao' => 'Tributação monofásica com responsabilidade pela retenção sobre combustíveis',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art178',
      ),
      2 => 
      array (
        'codigo' => '620003',
        'descricao' => 'Tributação monofásica com tributos retidos por responsabilidade sobre combustíveis',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art178',
      ),
      3 => 
      array (
        'codigo' => '620004',
        'descricao' => 'Tributação monofásica sobre mistura de EAC com gasolina A em percentual superior ao obrigatório',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art179',
      ),
      4 => 
      array (
        'codigo' => '620005',
        'descricao' => 'Tributação monofásica sobre mistura de EAC com gasolina A em percentual inferior ao obrigatório',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art179',
      ),
      5 => 
      array (
        'codigo' => '620006',
        'descricao' => 'Tributação monofásica sobre combustíveis cobrada anteriormente',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NFCE',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art180',
      ),
    ),
  ),
  800 => 
  array (
    'codigo' => '800',
    'descricao' => 'Transferência de crédito',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => true,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '800001',
        'descricao' => 'Fusão, cisão ou incorporação',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art55',
      ),
      1 => 
      array (
        'codigo' => '800002',
        'descricao' => 'Transferência de crédito do associado, inclusive as cooperativas singulares',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art272',
      ),
    ),
  ),
  810 => 
  array (
    'codigo' => '810',
    'descricao' => 'Ajuste de IBS na ZFM',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => true,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '810001',
        'descricao' => 'Crédito presumido sobre o valor apurado nos fornecimentos a partir da ZFM',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art450',
      ),
    ),
  ),
  811 => 
  array (
    'codigo' => '811',
    'descricao' => 'Ajustes',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => true,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '811001',
        'descricao' => 'Anulação de Crédito por Saídas Imunes/Isentas',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      1 => 
      array (
        'codigo' => '811002',
        'descricao' => 'Débitos de notas fiscais não processadas na apuração',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      2 => 
      array (
        'codigo' => '811003',
        'descricao' => 'Desenquadramento do Simples Nacional',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFE',
          1 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
    ),
  ),
  820 => 
  array (
    'codigo' => '820',
    'descricao' => 'Tributação em declaração de regime específico',
    'indicadores' => 
    array (
      'exige_tributacao' => false,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '820001',
        'descricao' => 'Documento com informações de fornecimento de serviços de planos de assinstência à saúde',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art235',
      ),
      1 => 
      array (
        'codigo' => '820002',
        'descricao' => 'Documento com informações de fornecimento de serviços de planos de assinstência funerária',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art236',
      ),
      2 => 
      array (
        'codigo' => '820003',
        'descricao' => 'Documento com informações de fornecimento de serviços de planos de assinstência à saúde de animais domésticos',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art243',
      ),
      3 => 
      array (
        'codigo' => '820004',
        'descricao' => 'Documento com informações de prestação de serviços de consursos de prognósticos',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art248',
      ),
      4 => 
      array (
        'codigo' => '820005',
        'descricao' => 'Documento com informações de alienação de bens imóveis',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFABI',
          1 => 'DERE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art254',
      ),
      5 => 
      array (
        'codigo' => '820006',
        'descricao' => 'Documento com informações de fornecimento de serviços de exploração de via',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => '',
      ),
      6 => 
      array (
        'codigo' => '820007',
        'descricao' => 'Documento com informações de fornecimento de serviços financeiros',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NFSE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art181',
      ),
      7 => 
      array (
        'codigo' => '820008',
        'descricao' => 'Documento com informações de fornecimento, mas com tributação realizada em fatura anterior',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '3 - Sem aliquota',
        'dfes' => 
        array (
          0 => 'NF3E',
          1 => 'NFAG',
          2 => 'NFCOM',
          3 => 'NFGAS',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art10',
      ),
    ),
  ),
  830 => 
  array (
    'codigo' => '830',
    'descricao' => 'Exclusão da Base de Cálculo',
    'indicadores' => 
    array (
      'exige_tributacao' => true,
      'reducao_bc' => false,
      'reducao_aliquota' => false,
      'transferencia_credito' => false,
      'diferimento' => false,
      'monofasica' => false,
      'credito_presumido_ibs_zfm' => false,
      'ajuste_competencia' => false,
    ),
    'classificacoes' => 
    array (
      0 => 
      array (
        'codigo' => '830001',
        'descricao' => 'Documento com exclusão da BC da CBS e do IBS de energia elétrica fornecida pela distribuidora à UC',
        'reducao_ibs' => '0.00',
        'reducao_cbs' => '0.00',
        'indicadores' => 
        array (
          'tributacao_regular' => false,
          'credito_presumido' => false,
          'estorno_credito' => false,
        ),
        'tipo_aliquota' => '2 - Padrão',
        'dfes' => 
        array (
          0 => 'NF3E',
          1 => 'NFE',
        ),
        'anexo' => '',
        'legislacao' => 'https://www.planalto.gov.br/ccivil_03/leis/lcp/lcp214.htm#art28',
      ),
    ),
  ),
);
