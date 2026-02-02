<?php
class PdvParser {

    public function parse($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $logEntries = [];
        $currentEntry = null;

        // Expressão Regular para o formato do log do PDV
        // Captura: 1:Timestamp, 2:Thread Info, 3:Nível, 4:Mensagem
        $pattern = '/^(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2}\.\d{3})\s+\[(.*?)\]\s+([A-Z]+)\s+(.*)$/u';

        foreach ($lines as $line) {
            // Converte a linha para UTF-8 para garantir a leitura correta
            $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');

            if (empty(trim($line))) {
                continue;
            }

            if (preg_match($pattern, $line, $matches)) {
                if ($currentEntry !== null) {
                    $logEntries[] = $currentEntry;
                }
                
                $currentEntry = [
                    'timestamp' => trim($matches[1]),
                    // Para manter a UI consistente, vamos mapear a "Thread Info" para o campo 'user'
                    'user'      => trim($matches[2]),
                    'level'     => trim($matches[3]),
                    'message'   => trim($matches[4]),
                    'build'     => 'PDV' // Identificador para o módulo
                ];
            } else {
                // Se a linha não corresponde, é uma continuação (stack trace ou mensagem de múltiplas linhas)
                if ($currentEntry !== null) {
                    $currentEntry['message'] .= "\n" . $line;
                }
            }
        }

        // Adiciona a última entrada
        if ($currentEntry !== null) {
            $logEntries[] = $currentEntry;
        }

        return $logEntries;
    }
}
