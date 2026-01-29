<?php

require_once 'Certificado.php';

class SefazService {
    private $certificado;
    private $uf;
    private $cnpj;
    private $ambiente;
    private $lastResponse;

    // URLs de Produção (Simplificado para o exemplo, idealmente viria de um arquivo de config/constantes)
    const URLS_SEFAZ = [
        'RS' => [
            'NFeDistribuicaoDFe' => 'https://www1.nfe.fazenda.gov.br/NFeDistribuicaoDFe/NFeDistribuicaoDFe.asmx',
            'NfeConsultaCadastro' => 'https://cad.sefazrs.rs.gov.br/ws/cadconsultacadastro/cadconsultacadastro4.asmx',
            'NfeConsultaProtocolo' => 'https://nfe.sefazrs.rs.gov.br/ws/NfeConsulta/NfeConsulta4.asmx'
        ],
        'AN' => [
             'NFeDistribuicaoDFe' => 'https://www1.nfe.fazenda.gov.br/NFeDistribuicaoDFe/NFeDistribuicaoDFe.asmx'
        ]
        // Adicionar outras UFs conforme necessário ou usar a tabela completa do Python
    ];

    public function __construct(Certificado $certificado, $uf, $cnpj, $ambiente = '1') {
        $this->certificado = $certificado;
        $this->uf = strtoupper($uf);
        $this->cnpj = $cnpj;
        $this->ambiente = $ambiente;
    }

    private function getUrl($servico) {
        // Lógica simplificada de URL. O serviço de Distribuição é Nacional (AN)
        if ($servico === 'NFeDistribuicaoDFe') {
            return self::URLS_SEFAZ['AN']['NFeDistribuicaoDFe'];
        }
        
        // Para outros serviços, tenta pegar da UF, se não tiver, usa RS como fallback ou erro
        if (isset(self::URLS_SEFAZ[$this->uf][$servico])) {
            return self::URLS_SEFAZ[$this->uf][$servico];
        }
        
        // Fallback genérico ou erro (aqui usando RS como exemplo de URL padrão SVRS)
        return self::URLS_SEFAZ['RS'][$servico] ?? '';
    }

    public function consultarDistDFe($nsu = '0', $ultNSU = '0') {
        $url = $this->getUrl('NFeDistribuicaoDFe');
        $action = 'http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe/nfeDistDFeInteresse';
        
        // Monta o XML da requisição
        $xmlBody = <<<XML
<nfeDistDFeInteresse xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe">
    <nfeDadosMsg>
        <distDFeInt xmlns="http://www.portalfiscal.inf.br/nfe" versao="1.01">
            <tpAmb>{$this->ambiente}</tpAmb>
            <cUFAutor>{$this->getCodigoUF($this->uf)}</cUFAutor>
            <CNPJ>{$this->cnpj}</CNPJ>
            <distNSU>
                <ultNSU>{$nsu}</ultNSU>
            </distNSU>
        </distDFeInt>
    </nfeDadosMsg>
</nfeDistDFeInteresse>
XML;

        $soapEnvelope = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <soap:Body>{$xmlBody}</soap:Body>
</soap:Envelope>
SOAP;

        return $this->sendSoapRequest($url, $soapEnvelope, $action);
    }

    private function sendSoapRequest($url, $soapEnvelope, $action) {
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: ' . $action,
            'Content-Length: ' . strlen($soapEnvelope)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soapEnvelope);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Configura certificado e chave
        curl_setopt($ch, CURLOPT_SSLCERT, $this->certificado->getCertPath());
        curl_setopt($ch, CURLOPT_SSLKEY, $this->certificado->getKeyPath());
        
        $response = curl_exec($ch);
        $this->lastResponse = $response;
        
        if (curl_errno($ch)) {
            throw new Exception('Erro cURL: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        return $this->parseResponse($response);
    }

    private function parseResponse($xmlResponse) {
        $dom = new DOMDocument();
        $dom->loadXML($xmlResponse);
        
        // Remove namespaces para facilitar o parsing
        $xmlString = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$3', $xmlResponse);
        $simpleXml = simplexml_load_string($xmlString);
        
        // Tenta encontrar o retorno (pode variar dependendo do serviço)
        // Para DistDFe:
        $retDistDFeInt = $simpleXml->xpath('//retDistDFeInt')[0] ?? null;
        
        if ($retDistDFeInt) {
            $result = [
                'status' => (string)$retDistDFeInt->cStat,
                'motivo' => (string)$retDistDFeInt->xMotivo,
                'ultNSU' => (string)$retDistDFeInt->ultNSU,
                'maxNSU' => (string)$retDistDFeInt->maxNSU,
                'docs' => []
            ];

            if (isset($retDistDFeInt->loteDistDFeInt->docZip)) {
                foreach ($retDistDFeInt->loteDistDFeInt->docZip as $docZip) {
                    $schema = (string)$docZip['schema'];
                    $nsu = (string)$docZip['NSU'];
                    $content = gzdecode(base64_decode((string)$docZip));
                    
                    $result['docs'][] = [
                        'nsu' => $nsu,
                        'schema' => $schema,
                        'content' => $content
                    ];
                }
            }
            return $result;
        }

        return ['error' => 'Resposta não reconhecida', 'raw' => $xmlResponse];
    }

    private function getCodigoUF($uf) {
        $codigos = [
            'RO' => '11', 'AC' => '12', 'AM' => '13', 'RR' => '14', 'PA' => '15', 'AP' => '16', 'TO' => '17',
            'MA' => '21', 'PI' => '22', 'CE' => '23', 'RN' => '24', 'PB' => '25', 'PE' => '26', 'AL' => '27',
            'SE' => '28', 'BA' => '29', 'MG' => '31', 'ES' => '32', 'RJ' => '33', 'SP' => '35', 'PR' => '41',
            'SC' => '42', 'RS' => '43', 'MS' => '50', 'MT' => '51', 'GO' => '52', 'DF' => '53'
        ];
        return $codigos[$uf] ?? '91'; // 91 = AN
    }
}
