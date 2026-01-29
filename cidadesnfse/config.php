<?php
// Arquivo de Configuração Principal

// **ATENÇÃO:** Altere estas credenciais para as do seu ambiente de produção.
// É uma má prática manter credenciais hard-coded em um ambiente real.
// Para este projeto autocontido, estamos fazendo uma exceção.

// Configurações do Banco de Dados PostgreSQL
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'cidades_nfse');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'postgres');

// Senha para o modo de administração
// Em um sistema real, use um método de hash seguro (ex: password_hash)
define('ADMIN_PASSWORD', 'admin123');

/**
 * Cria e retorna uma nova conexão PDO com o banco de dados PostgreSQL.
 * Configurada para lançar exceções em caso de erro e retornar resultados como arrays associativos.
 *
 * @return PDO A instância do objeto PDO.
 * @throws PDOException Se a conexão falhar.
 */
function getDbConnection(): PDO {
    $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_USER,
        DB_PASSWORD
    );

    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        // Em um ambiente de produção, seria melhor logar o erro do que exibi-lo.
        error_log('Connection failed: ' . $e->getMessage());
        // Retorna uma resposta de erro genérica para o cliente.
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed.']);
        exit;
    }
}