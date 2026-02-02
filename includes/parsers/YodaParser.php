<?php
class YodaParser {

    public function parse($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $logEntries = [];
        $currentEntry = null;

        // Expressão Regular para o formato do log do Yoda
        // Captura: 1:Timestamp, 2:Thread, 3:Logger/Classe, 4:Mensagem
        $pattern = '/^(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2},\d{3})\s+\[(.*?)\]\s+([\w.]+)\s+-\s(.*)$/u';

        foreach ($lines as $line) {
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
                    'user'      => 'Yoda', // Como não há tenant, usamos um identificador fixo
                    'level'     => trim($matches[3]), // A Classe/Logger será nosso "Nível"
                    'message'   => trim($matches[4]),
                    'build'     => trim($matches[2])  // A Thread será nosso "Build"
                ];
            } else {
                if ($currentEntry !== null) {
                    $currentEntry['message'] .= "\n" . $line;
                }
            }
        }

        if ($currentEntry !== null) {
            $logEntries[] = $currentEntry;
        }

        return $logEntries;
    }
}
