<?php

class Database {
    private $dbPath;
    private $pdo;

    public function __construct() {
        // Define o caminho do banco de dados na pasta 'data' do projeto
        $this->dbPath = __DIR__ . '/../../data/manifestador.db';
        $this->connect();
        $this->initDb();
    }

    private function connect() {
        try {
            // Cria a pasta data se não existir
            $dir = dirname($this->dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $this->pdo = new PDO('sqlite:' . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados SQLite: " . $e->getMessage());
        }
    }

    private function initDb() {
        // Tabela de Empresas
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS empresas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                razao_social TEXT NOT NULL,
                cnpj TEXT NOT NULL UNIQUE,
                uf TEXT NOT NULL,
                db_host TEXT,
                db_port TEXT,
                db_name TEXT,
                db_user TEXT,
                db_pass TEXT
            )
        ");

        // Tabela de Consultas
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS consultas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                empresa_id INTEGER NOT NULL,
                timestamp TEXT NOT NULL,
                nsu_enviado TEXT NOT NULL,
                nsu_recebido TEXT,
                max_nsu TEXT,
                status_code TEXT,
                motivo TEXT,
                FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
            )
        ");

        // Tabela de Documentos
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS documentos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                empresa_id INTEGER NOT NULL,
                nsu TEXT UNIQUE NOT NULL,
                chave TEXT,
                tipo TEXT,
                status TEXT,
                data_emissao TEXT,
                valor REAL,
                emitente_nome TEXT,
                emitente_cnpj TEXT,
                xml TEXT,
                FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
            )
        ");
        
        // Verifica e adiciona coluna 'status' se não existir (para compatibilidade)
        $this->checkAndAddColumn('documentos', 'status', 'TEXT');
    }

    private function checkAndAddColumn($table, $column, $type) {
        $stmt = $this->pdo->query("PRAGMA table_info($table)");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array($column, $columns)) {
            $this->pdo->exec("ALTER TABLE $table ADD COLUMN $column $type");
        }
    }

    public function getPdo() {
        return $this->pdo;
    }

    // --- Métodos de Empresa ---

    public function getEmpresas() {
        $stmt = $this->pdo->query("SELECT * FROM empresas ORDER BY razao_social");
        return $stmt->fetchAll();
    }

    public function getEmpresa($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM empresas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addEmpresa($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO empresas (razao_social, cnpj, uf, db_host, db_port, db_name, db_user, db_pass)
                VALUES (:razao_social, :cnpj, :uf, :db_host, :db_port, :db_name, :db_user, :db_pass)
            ");
            $stmt->execute([
                ':razao_social' => $data['razao_social'],
                ':cnpj' => $data['cnpj'],
                ':uf' => $data['uf'],
                ':db_host' => $data['db_host'] ?? null,
                ':db_port' => $data['db_port'] ?? null,
                ':db_name' => $data['db_name'] ?? null,
                ':db_user' => $data['db_user'] ?? null,
                ':db_pass' => $data['db_pass'] ?? null
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                throw new Exception("O CNPJ {$data['cnpj']} já está cadastrado.");
            }
            throw $e;
        }
    }

    public function updateEmpresa($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE empresas SET 
                razao_social = :razao_social, cnpj = :cnpj, uf = :uf,
                db_host = :db_host, db_port = :db_port, db_name = :db_name,
                db_user = :db_user, db_pass = :db_pass
            WHERE id = :id
        ");
        $stmt->execute([
            ':id' => $id,
            ':razao_social' => $data['razao_social'],
            ':cnpj' => $data['cnpj'],
            ':uf' => $data['uf'],
            ':db_host' => $data['db_host'] ?? null,
            ':db_port' => $data['db_port'] ?? null,
            ':db_name' => $data['db_name'] ?? null,
            ':db_user' => $data['db_user'] ?? null,
            ':db_pass' => $data['db_pass'] ?? null
        ]);
    }

    public function deleteEmpresa($id) {
        $stmt = $this->pdo->prepare("DELETE FROM empresas WHERE id = ?");
        $stmt->execute([$id]);
    }

    // --- Métodos de Consulta ---

    public function saveConsulta($empresaId, $nsuEnviado, $resultado) {
        $stmt = $this->pdo->prepare("
            INSERT INTO consultas (empresa_id, timestamp, nsu_enviado, nsu_recebido, max_nsu, status_code, motivo)
            VALUES (?, datetime('now', 'localtime'), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $empresaId,
            $nsuEnviado,
            $resultado['ultNSU'] ?? null,
            $resultado['maxNSU'] ?? null,
            $resultado['status'] ?? null,
            $resultado['motivo'] ?? null
        ]);
    }

    public function getUltimaConsulta($empresaId) {
        $stmt = $this->pdo->prepare("SELECT * FROM consultas WHERE empresa_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$empresaId]);
        return $stmt->fetch();
    }

    // --- Métodos de Documentos ---

    public function saveDocumentos($empresaId, $documentos) {
        $stmt = $this->pdo->prepare("
            INSERT OR REPLACE INTO documentos (
                empresa_id, nsu, chave, tipo, status, data_emissao, 
                valor, emitente_nome, emitente_cnpj, xml
            )
            VALUES (:empresa_id, :nsu, :chave, :tipo, :status, :data_emissao, 
                    :valor, :emitente_nome, :emitente_cnpj, :xml)
        ");

        $count = 0;
        foreach ($documentos as $doc) {
            $stmt->execute([
                ':empresa_id' => $empresaId,
                ':nsu' => $doc['nsu'],
                ':chave' => $doc['chave'] ?? null,
                ':tipo' => $doc['tipo'] ?? null,
                ':status' => $doc['status'] ?? null,
                ':data_emissao' => $doc['data_emissao'] ?? null,
                ':valor' => $doc['valor'] ?? null,
                ':emitente_nome' => $doc['emitente_nome'] ?? null,
                ':emitente_cnpj' => $doc['emitente_cnpj'] ?? null,
                ':xml' => $doc['xml'] ?? null
            ]);
            $count++;
        }
        return $count;
    }

    public function getDocumentos($empresaId) {
        $stmt = $this->pdo->prepare("SELECT * FROM documentos WHERE empresa_id = ? ORDER BY CAST(nsu AS INTEGER) DESC");
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll();
    }

    public function deleteDocumento($empresaId, $nsu) {
        $stmt = $this->pdo->prepare("DELETE FROM documentos WHERE empresa_id = ? AND nsu = ?");
        $stmt->execute([$empresaId, $nsu]);
    }
}
