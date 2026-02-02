<?php
require_once '../config.php';
session_start();
header('Content-Type: application/json');

$pdo = getDbConnection();

// A lista de campos booleanos continua sendo a nossa referência
$booleanFields = [
    'permite_nfse_sem_consumidor_final', 'envio_em_lote', 'usa_certificado',
    'possui_inutilizacao', 'possui_impressao_propria', 'impressao_com_certificado',
    'possui_arquivo_aidf', 'possui_classe_stub', 'homologado'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT p.*, EXISTS(SELECT 1 FROM public.cidades c WHERE c.provedor = p.provedor) as is_in_use FROM public.provedores_uniplus p ORDER BY p.descricao');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

// Rota POST: Criar um novo provedor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nenhum dado fornecido.']);
        exit;
    }

    $columns = array_keys($data);
    $placeholders = array_map(fn($c) => ":$c", $columns);

    try {
        $sql = "INSERT INTO public.provedores_uniplus (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);

        // **CORREÇÃO DEFINITIVA**: Vincula cada valor com seu tipo explícito
        foreach ($data as $key => &$value) {
            if (in_array($key, $booleanFields)) {
                $stmt->bindValue(":$key", (bool)$value, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['error' => 'Erro ao criar o provedor: ' . $e->getMessage()]);
    }
    exit;
}

// Rota PUT: Atualizar um provedor
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do provedor não fornecido.']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nenhum dado para atualizar.']);
        exit;
    }

    $fields = array_map(fn($c) => "$c = :$c", array_keys($data));

    try {
        $sql = "UPDATE public.provedores_uniplus SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        // **CORREÇÃO DEFINITIVA**: Vincula cada valor de DADO com seu tipo explícito
        foreach ($data as $key => &$value) {
            if (in_array($key, $booleanFields)) {
                // Força o tipo para PDO::PARAM_BOOL
                $stmt->bindValue(":$key", (bool)$value, PDO::PARAM_BOOL);
            } else {
                // Trata o resto como string (PDO lida bem com null aqui)
                $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }
        // Vincula o ID separadamente
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['error' => 'Erro ao atualizar o provedor: ' . $e->getMessage()]);
    }
    exit;
}

// Rota DELETE (sem alterações, mas padronizada)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do provedor não fornecido.']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM public.provedores_uniplus WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => $stmt->rowCount() > 0]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['error' => 'Erro ao excluir o provedor: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método não permitido.']);