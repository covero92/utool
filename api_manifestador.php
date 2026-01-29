<?php
// api_manifestador.php
header('Content-Type: application/json');
require_once 'includes/manifestador/Database.php';
require_once 'includes/manifestador/Certificado.php';
require_once 'includes/manifestador/SefazService.php';

$action = $_GET['action'] ?? '';
$db = new Database();

try {
    switch ($action) {
        case 'list_empresas':
            echo json_encode($db->getEmpresas());
            break;

        case 'save_empresa':
            $data = json_decode(file_get_contents('php://input'), true);
            $db->addEmpresa($data);
            echo json_encode(['success' => true]);
            break;

        case 'list_docs':
            $empresaId = $_GET['empresa_id'];
            echo json_encode($db->getDocumentos($empresaId));
            break;

        case 'process_lote':
            if (!isset($_FILES['cert']) || !isset($_POST['senha']) || !isset($_POST['empresa_id'])) {
                throw new Exception("Dados incompletos.");
            }

            $empresa = $db->getEmpresa($_POST['empresa_id']);
            $certContent = file_get_contents($_FILES['cert']['tmp_name']);
            $senha = $_POST['senha'];
            $nsu = $_POST['nsu'] ?? '0';

            $certificado = new Certificado($certContent, $senha);
            $service = new SefazService($certificado, $empresa['uf'], $empresa['cnpj']);

            $result = $service->consultarDistDFe($nsu);
            
            // Salva documentos se houver
            if (isset($result['docs']) && !empty($result['docs'])) {
                $docsToSave = [];
                foreach ($result['docs'] as $doc) {
                    // Extrai dados básicos do XML (simplificado)
                    $xml = simplexml_load_string($doc['content']);
                    $chave = ''; // Extrair do XML
                    $valor = 0; // Extrair do XML
                    
                    // Lógica de extração básica (pode ser melhorada)
                    if (isset($xml->NFe->infNFe)) {
                        $chave = (string)$xml->NFe->infNFe['Id'];
                        $chave = str_replace('NFe', '', $chave);
                        $valor = (float)$xml->NFe->infNFe->total->ICMSTot->vNF;
                    }

                    $docsToSave[] = [
                        'nsu' => $doc['nsu'],
                        'xml' => $doc['content'],
                        'chave' => $chave,
                        'valor' => $valor,
                        'tipo' => $doc['schema']
                    ];
                }
                $db->saveDocumentos($empresa['id'], $docsToSave);
            }

            echo json_encode($result);
            break;

        default:
            throw new Exception("Ação inválida.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
