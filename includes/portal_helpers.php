<?php

class SupportPortal {
    private $releaseNotesPath;
    private $configPath;

    public function __construct() {
        $this->releaseNotesPath = __DIR__ . '/../data/release_notes.json';
        $this->configPath = __DIR__ . '/../data/portal_config.json';
    }

    /**
     * Calculates the technical password for the day.
     * Logic: Day * Month * Year(2-digit) * 3
     * Example: 05/12/2025 -> 5 * 12 * 25 * 3 = 4500
     */
    public function getTechnicalPassword() {
        $today = new DateTime();
        $day = (int)$today->format('d');
        $month = (int)$today->format('m');
        $year = (int)$today->format('y');
        
        $password = $day * $month * $year * 3;
        
        return $password;
    }

    public function getLatestBuild() {
        if (!file_exists($this->releaseNotesPath)) {
            return ['version' => 'N/A', 'date' => 'N/A'];
        }

        $json = file_get_contents($this->releaseNotesPath);
        $data = json_decode($json, true);

        if (empty($data) || !isset($data[0])) {
            return ['version' => 'N/A', 'date' => 'N/A'];
        }

        $latest = $data[0];
        return [
            'version' => $latest['version'] ?? 'N/A',
            'date' => isset($latest['date']) ? date('d/m/Y', strtotime($latest['date'])) : 'N/A'
        ];
    }

    public function getNotices() {
        if (!file_exists($this->configPath)) {
            return [];
        }

        $json = file_get_contents($this->configPath);
        $data = json_decode($json, true);

        return $data['notices'] ?? [];
    }

    public function getMeetings() {
        if (!file_exists($this->configPath)) {
            return [];
        }

        $json = file_get_contents($this->configPath);
        $data = json_decode($json, true);

        return $data['meetings'] ?? [];
    }

    // --- Configuration Helper ---
    public function getConfig($key = null) {
        if (!file_exists($this->configPath)) return [];
        
        $json = file_get_contents($this->configPath);
        $data = json_decode($json, true);
        if (!$data) $data = [];

        // Ensure default structure
        if (!isset($data['notices'])) $data['notices'] = [];
        if (!isset($data['meetings'])) $data['meetings'] = [];

        if ($key) {
            return $data[$key] ?? null;
        }
        return $data;
    }

    public function updateConfig($key, $value) {
        // Read FRESH data to avoid race conditions (simple file lock simulation)
        $data = $this->getConfig(); 
        $data[$key] = $value;
        
        // Atomic write approach
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->configPath, $json, LOCK_EX);
    }

    // --- Access Control ---
    public function isBlocked($cardId) {
        $blocked = $this->getConfig('blocked_cards') ?? [];
        return in_array($cardId, $blocked);
    }

    public function toggleBlockToken($cardId) {
        $blocked = $this->getConfig('blocked_cards') ?? [];
        if (in_array($cardId, $blocked)) {
            $blocked = array_values(array_diff($blocked, [$cardId]));
            $isBlocked = false;
        } else {
            $blocked[] = $cardId;
            $isBlocked = true;
        }
        $this->updateConfig('blocked_cards', $blocked);
        return $isBlocked;
    }

    public function getSystemVersion() {
        $gitHead = __DIR__ . '/../.git/HEAD';
        if (file_exists($gitHead)) {
            $headContent = file_get_contents($gitHead);
            if (strpos($headContent, 'ref:') === 0) {
                $refPath = __DIR__ . '/../.git/' . trim(substr($headContent, 5));
                if (file_exists($refPath)) {
                    $hash = trim(file_get_contents($refPath));
                    return substr($hash, 0, 7);
                }
            } else {
                return substr(trim($headContent), 0, 7);
            }
        }
        return 'Dev'; // Fallback
    }
}
