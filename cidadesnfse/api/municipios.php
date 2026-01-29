<?php
require_once '../config.php';
session_start();
header('Content-Type: application/json');

$pdo = getDbConnection();

// **NOVO**: Lista dos campos que são booleanos na tabela 'cidades'
$booleanFields = [
    'homologado_uniplus',
    'reforma_tributaria',
    'cliente_uniplus_ativo'
];

// Rota GET: Listar todos os municípios (sem alterações)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['history_for_id'])) {
    $stmt = $pdo->query(
       'SELECT c.*, p.homologado as provedor_homologado 
        FROM public.cidades c 
        LEFT JOIN public.provedores_uniplus p ON c.provedor = p.provedor
        ORDER BY c.uf, c.nomemunicipio'
    );
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Rota GET: Obter histórico (sem alterações)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['history_for_id'])) {
    $municipioId = (int)$_GET['history_for_id'];
    $stmt = $pdo->prepare('SELECT * FROM public.historico_alteracoes WHERE municipio_codigo = ? ORDER BY "timestamp" DESC');
    $stmt->execute([$municipioId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Rota PUT: Atualizar um município
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
        http_response_code(403);
        echo json_encode(['error' => 'Acesso negado.']);
        exit;
    }

    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do município não fornecido.']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nenhum dado para atualizar.']);
        exit;
    }

    // Pega os dados antigos para registrar no histórico
    $stmtOldData = $pdo->prepare("SELECT * FROM public.cidades WHERE codigomunicipio = ?");
    $stmtOldData->execute([$id]);
    $oldData = $stmtOldData->fetch(PDO::FETCH_ASSOC);

    $fields = array_map(fn($c) => "$c = :$c", array_keys($data));
    $sql = "UPDATE public.cidades SET " . implode(', ', $fields) . " WHERE codigomunicipio = :id";
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare($sql);

        // **CORREÇÃO DEFINITIVA**: Vincula cada valor com seu tipo explícito
        foreach ($data as $key => &$value) {
            if (in_array($key, $booleanFields)) {
                // Força o tipo para PDO::PARAM_BOOL
                $stmt->bindValue(":$key", (bool)$value, PDO::PARAM_BOOL);
            } else {
                // Trata o resto como string (PDO lida bem com null aqui)
                $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }
        // Vincula o ID separadamente (codigomunicipio é BIGINT, PARAM_STR é o mais seguro)
        $stmt->bindValue(":id", $id, PDO::PARAM_STR);
        
        $stmt->execute();

        // Insere no histórico de alterações (lógica inalterada)
        $historyStmt = $pdo->prepare(
            'INSERT INTO public.historico_alteracoes (municipio_codigo, campo_alterado, valor_antigo, valor_novo) VALUES (?, ?, ?, ?)'
        );
        foreach ($data as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;
            if ((string)$oldValue !== (string)$newValue) {
                $historyStmt->execute([$id, $key, (string)$oldValue, (string)$newValue]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        error_log('Erro ao atualizar município: ' . $e->getMessage());
        echo json_encode(['error' => 'Erro ao atualizar o município: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não permitido.']);