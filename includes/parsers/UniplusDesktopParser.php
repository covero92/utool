<?php
class UniplusDesktopParser {

    public function parse($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        return $this->parseLines($lines);
    }

    public function parseLines(array $lines) {
        $logEntries = [];
        $currentEntry = null;

        // Expressão regular original, agora que vamos tratar o encoding.
        $pattern = '/^(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})\s+Build:\s+(.*?)\s+Usuário:\s+(.*?)\s+([A-Z]+)\s+(.*)$/u';

        foreach ($lines as $lineNumber => $line) {
            // *** A SOLUÇÃO ESTÁ AQUI ***
            // Converte a linha para UTF-8, detectando o formato original.
            // Isso corrige caracteres como '' e garante que a regex funcione.
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
                    'build'     => trim($matches[2]),
                    'user'      => trim($matches[3]),
                    'level'     => trim($matches[4]),
                    'message'   => trim($matches[5]),
                    'parsed'    => true
                ];
            } else {
                if ($currentEntry !== null && !$this->isNewLogEntry($line)) {
                    $currentEntry['message'] .= "\n" . $line;
                } else {
                    if ($currentEntry !== null) {
                        $logEntries[] = $currentEntry;
                    }
                    $currentEntry = [
                        'timestamp' => 'N/A',
                        'build'     => 'N/A',
                        'user'      => 'N/A',
                        'level'     => 'UNPARSED',
                        'message'   => '[Linha ' . ($lineNumber + 1) . ']: ' . $line,
                        'parsed'    => false
                    ];
                }
            }
        }

        if ($currentEntry !== null) {
            $logEntries[] = $currentEntry;
        }

        return $logEntries;
    }

    private function isNewLogEntry($line) {
        return preg_match('/^\d{2}\/\d{2}\/\d{4}/', $line);
    }
}
