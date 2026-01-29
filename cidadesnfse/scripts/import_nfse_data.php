<?php
/**
 * Script de Importação de Dados da NFS-e Nacional
 * 
 * Este script processa um arquivo CSV exportado do PowerBI (ou construído manualmente)
 * e atualiza as informações de adesão ao ambiente nacional e emissor nacional.
 * 
 * Formato esperado do CSV (separado por ponto e vírgula ';'):
 * - Coluna 0: Nome do Município (ex: "São Paulo")
 * - Coluna 1: UF (ex: "SP")
 * - Coluna 2: Aderente Ambiente Nacional (ex: "Sim"/"Não")
 * - Coluna 3: Aderente Emissor Nacional (ex: "Sim"/"Não")
 * 
 * Uso: Coloque o arquivo em data/import_nfse.csv e execute este script via navegador ou CLI.
 */

require_once '../config.php';

header('Content-Type: text/plain; charset=utf-8');

$csvFile = __DIR__ . '/../data/import_nfse.csv';

if (!file_exists($csvFile)) {
    die("Erro: Arquivo CSV não encontrado em: $csvFile\nPor favor, faça o upload do arquivo com o nome 'import_nfse.csv' na pasta 'data/' do projeto cidadesnfse.");
}

try {
    $pdo = getDbConnection();
    
    // Ler o arquivo CSV
    $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Remove cabeçalho se existir (assumindo que a primeira linha é cabeçalho)
    // Se não tiver cabeçalho, comente a linha abaixo.
    array_shift($lines);
    
    $updatedCount = 0;
    $notFoundCount = 0;
    $errors = [];
    
    echo "Iniciando importação...\n";
    echo "Total de linhas: " . count($lines) . "\n\n";
    
    $stmtFind = $pdo->prepare("SELECT codigomunicipio FROM public.cidades WHERE nomemunicipio ILIKE ? AND uf = ?");
    $stmtUpdate = $pdo->prepare("UPDATE public.cidades SET aderenteambientenacional = ?, aderenteemissornacional = ?, reforma_tributaria = ? WHERE codigomunicipio = ?");
    
    foreach ($lines as $line) {
        $data = str_getcsv($line, ';');
        
        // Ajuste conforme as colunas do seu CSV
        if (count($data) < 3) continue;
        
        $cidadeName = trim($data[0]);
        $uf = trim($data[1]);
        $ambNacional = trim($data[2]); // Sim/Não
        $emisNacional = isset($data[3]) ? trim($data[3]) : 'Não';
        
        // Exemplo de lógica para reforma tributária, se houver dado no CSV
        // $reforma = isset($data[4]) ? (trim($data[4]) === 'Sim') : false; 
        // Por padrão false se não tiver info
        $reforma = false; 

        // Normalização básica de valores
        $ambNacional = (stripos($ambNacional, 'Sim') !== false) ? 'Sim' : 'Não';
        $emisNacional = (stripos($emisNacional, 'Sim') !== false) ? 'Sim' : 'Não';

        // Tentar encontrar a cidade no banco
        $stmtFind->execute([$cidadeName, $uf]);
        $cityId = $stmtFind->fetchColumn();
        
        if ($cityId) {
            $stmtUpdate->execute([$ambNacional, $emisNacional, $reforma, $cityId]);
            $updatedCount++;
        } else {
            $notFoundCount++;
            $errors[] = "Cidade não encontrada: $cidadeName - $uf";
        }
    }
    
    echo "Importação concluída!\n";
    echo "Cidades atualizadas: $updatedCount\n";
    echo "Cidades não encontradas: $notFoundCount\n";
    
    if (!empty($errors)) {
        echo "\nErros:\n";
        foreach ($errors as $err) {
            echo "- $err\n";
        }
    }

} catch (Exception $e) {
    die("Erro fatal: " . $e->getMessage());
}
?>
