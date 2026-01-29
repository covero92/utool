<?php
class UniplusWebParser {

    public function parse($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $logEntries = [];
        $currentEntry = null;

        $pattern = '/^(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2},\d{3})\s+([\w.-]+)\s+([\w.-]+)\s+(.*)$/u';

        foreach ($lines as $line) {
            $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');

            if (empty(trim($line))) {
                continue;
            }

            if (preg_match($pattern, $line, $matches)) {
                if ($currentEntry !== null) {
                    $logEntries[] = $currentEntry;
                }
                
                $timestamp = trim($matches[1]);
                $thread = trim($matches[2]);
                $logger = trim($matches[3]);
                $rest = trim($matches[4]);
                $tenant = 'N/A';
                $message = $rest;

                if (strpos($rest, 'tenant:') !== false && strpos($rest, ' - ') !== false) {
                    preg_match('/tenant:\s*([\w-]*)/', $rest, $tenantMatches);
                    if (isset($tenantMatches[1]) && !empty($tenantMatches[1])) {
                        $tenant = $tenantMatches[1];
                    }
                    $messageParts = explode(' - ', $rest, 2);
                    $message = $messageParts[1] ?? $rest;
                }

                $currentEntry = [
                    'timestamp' => $timestamp,
                    'user'      => $tenant,
                    'level'     => $logger,
                    'message'   => $message,
                    'build'     => $thread
                ];
            } else {
                // *** A LÓGICA DE CORREÇÃO ESTÁ AQUI ***
                if ($currentEntry !== null) {
                    // Se a mensagem atual for apenas um "Error" genérico,
                    // substitua-a por esta linha, que é a exceção real.
                    if (trim($currentEntry['message']) === 'Error') {
                        $currentEntry['message'] = trim($line);
                    } else {
                        // Caso contrário, é um stack trace normal, então apenas anexe.
                        $currentEntry['message'] .= "\n" . $line;
                    }
                }
            }
        }

        if ($currentEntry !== null) {
            $logEntries[] = $currentEntry;
        }

        return $logEntries;
    }
}
