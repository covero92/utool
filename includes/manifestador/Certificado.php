<?php

class Certificado {
    private $pfxContent;
    private $password;
    private $tempDir;
    private $certPath;
    private $keyPath;

    public function __construct($pfxContent, $password) {
        $this->pfxContent = $pfxContent;
        $this->password = $password;
        $this->tempDir = __DIR__ . '/../../data/temp_certs/';
        
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
        
        $this->extractKeys();
    }

    private function extractKeys() {
        $certs = [];
        if (!openssl_pkcs12_read($this->pfxContent, $certs, $this->password)) {
            throw new Exception("Falha ao ler o arquivo PFX. Verifique a senha.");
        }

        // Gera nomes únicos para os arquivos temporários
        $uniqueId = uniqid('cert_', true);
        $this->certPath = $this->tempDir . $uniqueId . '_cert.pem';
        $this->keyPath = $this->tempDir . $uniqueId . '_key.pem';

        // Salva o certificado e a chave privada
        if (!file_put_contents($this->certPath, $certs['cert'])) {
            throw new Exception("Erro ao salvar certificado temporário.");
        }
        
        if (!file_put_contents($this->keyPath, $certs['pkey'])) {
            throw new Exception("Erro ao salvar chave privada temporária.");
        }
    }

    public function getCertPath() {
        return $this->certPath;
    }

    public function getKeyPath() {
        return $this->keyPath;
    }

    public function getDadosEmpresa() {
        $certData = openssl_x509_parse(file_get_contents($this->certPath));
        if (!$certData) {
            throw new Exception("Erro ao ler dados do certificado.");
        }

        $subject = $certData['subject'];
        
        // Extrai CNPJ do Common Name (CN) ou Description
        // Formato comum: Nome da Empresa:CNPJ
        $cn = $subject['CN'] ?? '';
        $cnpj = '';
        
        if (preg_match('/[:\s](\d{14})/', $cn, $matches)) {
            $cnpj = $matches[1];
        } elseif (isset($subject['description']) && preg_match('/(\d{14})/', $subject['description'], $matches)) {
            $cnpj = $matches[1];
        }

        return [
            'razao_social' => explode(':', $cn)[0],
            'cnpj' => $cnpj,
            'valid_to' => date('Y-m-d H:i:s', $certData['validTo_time_t']),
            'issuer' => $certData['issuer']['CN'] ?? 'Desconhecido'
        ];
    }

    public function __destruct() {
        // Limpa arquivos temporários ao destruir o objeto
        if (file_exists($this->certPath)) unlink($this->certPath);
        if (file_exists($this->keyPath)) unlink($this->keyPath);
    }
}
