<?php

/**
 * NFeValidator.php
 * 
 * Strict NFe Validator Engine
 * Adheres to User Requirements 1-10
 */

// 4. Rule Interface
interface RuleInterface {
    public function getCode(): string;
    public function validate(DOMDocument $dom, DOMXPath $xpath, stdClass $context): ?array;
}

// 6. Generic Math Handler (BcMath wrapper or fallback)
class TaxMath {
    public static function mul($a, $b, $scale = 4) {
        if (function_exists('bcmul')) {
            return bcmul((string)$a, (string)$b, $scale);
        }
        return (string)round((float)$a * (float)$b, $scale);
    }

    public static function sub($a, $b, $scale = 4) {
        if (function_exists('bcsub')) {
            return bcsub((string)$a, (string)$b, $scale);
        }
        return (string)round((float)$a - (float)$b, $scale);
    }
    
    public static function round($val, $precision = 2) {
        return round((float)$val, $precision, PHP_ROUND_HALF_UP); // Requirement: HALF_UP
    }
    
    public static function comp($a, $b, $scale = 2) {
         if (function_exists('bccomp')) {
            return bccomp((string)$a, (string)$b, $scale);
        }
        $fa = round((float)$a, $scale);
        $fb = round((float)$b, $scale);
        if ($fa > $fb) return 1;
        if ($fa < $fb) return -1;
        return 0;
    }
}

// 3. Validation Context
class ValidationContext {
    public static function extract(DOMXPath $xpath): stdClass {
        $ctx = new stdClass();
        $ctx->modelo = self::val($xpath, '//nfe:ide/nfe:mod');
        $ctx->finalidade = self::val($xpath, '//nfe:ide/nfe:finNFe');
        $ctx->crt = self::val($xpath, '//nfe:emit/nfe:CRT');
        return $ctx;
    }

    private static function val($xpath, $query) {
        $nodes = $xpath->query($query);
        return ($nodes && $nodes->length > 0) ? trim($nodes->item(0)->nodeValue) : null;
    }
}

// Rule 630 Implementation
class Rule630 implements RuleInterface {
    public function getCode(): string {
        return '630';
    }

    public function validate(DOMDocument $dom, DOMXPath $xpath, stdClass $context): ?array {
        // Requirement 7: Filter indTot=0 or missing fields
        // Requirement 2: Use nfe prefix

        $errors = [];
        $dets = $xpath->query('//nfe:infNFe/nfe:det');

        foreach ($dets as $det) {
            $nItem = $det->getAttribute('nItem');

            // 7. Filter items ignored by SEFAZ (indTot = 0)
            // Path: prod/indTot
            $indTotNode = $xpath->query('nfe:prod/nfe:indTot', $det)->item(0);
            if ($indTotNode && trim($indTotNode->nodeValue) == '0') {
                continue;
            }

            // Extract Values
            $qTribNode  = $xpath->query('nfe:prod/nfe:qTrib', $det)->item(0);
            $vUnTribNode = $xpath->query('nfe:prod/nfe:vUnTrib', $det)->item(0);
            $vProdNode     = $xpath->query('nfe:prod/nfe:vProd', $det)->item(0);

            // 7. Ignore if missing mandatory fields (XSD job)
            if (!$qTribNode || !$vUnTribNode || !$vProdNode) {
                continue;
            }

            $qTrib = $qTribNode->nodeValue;
            $vUnTrib = $vUnTribNode->nodeValue;
            $vProd = $vProdNode->nodeValue;

            // 6. Calculation Logic (Strict)
            // Calc = qTrib * vUnTrib
            $mult = TaxMath::mul($qTrib, $vUnTrib, 10); // High precision intermediate
            $calc = TaxMath::round($mult, 2); // Round to 2 decimals HALF_UP
            
            // Diff = abs(calc - vProd)
            $diff = abs($calc - (float)$vProd);

            // Tolerance 0.01
            if ($diff > 0.01) {
                // 5. Standard Error Format
                $errors[] = [
                    'code' => '630',
                    'severity' => 'error',
                    'nItem' => $nItem,
                    'msg' => 'Valor do Produto difere do produto Valor Unitário de Tributação e Quantidade Tributável',
                    'detail' => "Item $nItem: vProd informada ($vProd) != Calculado ($calc). [qTrib: $qTrib * vUnTrib: $vUnTrib]"
                ];
            }
        }
        
        return empty($errors) ? null : $errors;
    }
}

class NFeValidator {
    private $dom;
    private $xpath;
    private $rules = [];
    private $context;

    public function __construct(string $xmlContent) {
        // 1. Normalization & Namespace
        $this->dom = new DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        
        // Load XML suppressing warnings (handled by main app, but safe here too)
        $loaded = @$this->dom->loadXML($xmlContent);
        if (!$loaded) {
            throw new Exception("XML Malformado ou Inválido");
        }

        // 1. Extract real NFe from nfeProc if valid
        $this->normalizeXml();

        // 2. Register Namespace
        $this->xpath = new DOMXPath($this->dom);
        $this->xpath->registerNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

        // 3. Extract Context
        $this->context = ValidationContext::extract($this->xpath);
        
        // Register Rules
        $this->registerRule(new Rule630());
    }

    private function normalizeXml() {
        // Check for nfeProc
        $root = $this->dom->documentElement;
        if ($root->localName === 'nfeProc') {
            $nfe = $root->getElementsByTagNameNS('http://www.portalfiscal.inf.br/nfe', 'NFe')->item(0);
            if (!$nfe) {
                // Try without namespace
                $nfe = $root->getElementsByTagName('NFe')->item(0); 
            }
            
            if ($nfe) {
                // Replace root with NFe
                $newDom = new DOMDocument();
                $newDom->preserveWhiteSpace = false;
                $newDom->appendChild($newDom->importNode($nfe, true));
                $this->dom = $newDom;
            }
        }
    }

    public function registerRule(RuleInterface $rule) {
        $this->rules[] = $rule;
    }

    public function validate() {
        $allErrors = [];
        foreach ($this->rules as $rule) {
            $res = $rule->validate($this->dom, $this->xpath, $this->context);
            if ($res) {
                $allErrors = array_merge($allErrors, $res);
            }
        }
        return $allErrors;
    }
}
