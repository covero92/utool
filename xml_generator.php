<?php include 'reforma_tributaria_data.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de XML</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Configuração do Worker do PDF.js
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>
    <style>
        :root {
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-highlight: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: var(--color-body-bg);
            background-attachment: fixed;
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: var(--color-text-main);
            transition: color 0.3s ease;
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        :root.classic {
            /* Professional Light Mode - High Contrast */
            --color-body-bg: #f8fafc; /* Slightly lighter background */

            --color-card-bg: #ffffff;
            --color-card-header-bg: #f1f5f9;
            --color-card-item-header-bg: #e2e8f0;
            --color-card-subtle-bg: #f8fafc;

            --color-text-main: #0f172a;
            --color-text-primary: #020617; /* Darker primary */
            --color-text-secondary: #475569; /* Darker secondary */
            --color-text-muted: #64748b;
            --color-text-placeholder: #64748b; /* Darker placeholder */
            --color-text-inverted: #ffffff;

            --color-border: #cbd5e1; /* visible border */
            --color-border-accent: #7dd3fc;

            --color-input-bg: #ffffff; /* Solid white input */

            --color-header-bg: rgba(255, 255, 255, 0.9);
            --color-header-border: #e2e8f0;
            --color-header-text: #0f172a;

            --color-accent: #0284c7; /* Darker blue accent */
            --color-accent-fg: #0369a1;
            --color-accent-fg-hover: #075985;
            --color-accent-text: #0284c7; /* Darker accent text */
            --color-accent-subtle-bg: #e0f2fe;

            --color-danger-text: #dc2626;
            --color-danger-ring: #fecaca;
            --color-danger-bg-hover: #fef2f2;

            --color-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            --color-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

            --color-glass-border: #e2e8f0;
        }

        :root.plus {
            /* Release Notes Dark Theme - High Contrast */
            --color-body-bg: radial-gradient(at 0% 0%, #1e293b 0px, transparent 50%), radial-gradient(at 100% 0%, #312e81 0px, transparent 50%), linear-gradient(to bottom, #0f172a, #020617);

            /* Increased opacity for better contrast against background */
            --color-card-bg: rgba(30, 41, 59, 0.7);
            --color-card-header-bg: rgba(255, 255, 255, 0.03);
            --color-card-item-header-bg: rgba(15, 23, 42, 0.8);
            --color-card-subtle-bg: rgba(0, 0, 0, 0.2);

            --color-text-main: #f8fafc;
            --color-text-primary: #ffffff;
            --color-text-secondary: #cbd5e1; /* Lighter secondary text */
            --color-text-muted: #94a3b8;
            --color-text-placeholder: #64748b;
            --color-text-inverted: #0f172a;

            /* Stronger borders */
            --color-border: rgba(255, 255, 255, 0.15);
            --color-border-accent: rgba(56, 189, 248, 0.7);

            /* Darker input background for better text legibility */
            --color-input-bg: rgba(2, 6, 23, 0.6);

            --color-header-bg: rgba(15, 23, 42, 0.8);
            --color-header-border: rgba(255, 255, 255, 0.15);
            --color-header-text: #ffffff;

            --color-accent: #3b82f6;
            --color-accent-fg: #2563eb;
            --color-accent-fg-hover: #1d4ed8;
            --color-accent-text: #7dd3fc; /* Brighter accent text */
            --color-accent-subtle-bg: rgba(59, 130, 246, 0.25);

            --color-danger-text: #fca5a5;
            --color-danger-ring: #ef4444;
            --color-danger-bg-hover: rgba(239, 68, 68, 0.2);

            --color-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.15);
            --color-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.4);

            --color-glass-border: rgba(255, 255, 255, 0.12);
        }

        /* Specific body override for Plus theme to match Release Notes */
        html.plus body {
            background-size: 100% 100%;
            animation: none;
            background-attachment: fixed;
        }

        /* Glassmorphism Utilities */
        .glass-panel {
            background: var(--color-card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--color-glass-border);
            box-shadow: var(--color-shadow-lg);
        }

        .glass-header {
            background: var(--color-header-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--color-glass-border);
        }

        .glass-input {
            background: var(--color-input-bg) !important;
            backdrop-filter: blur(4px);
            border: 1px solid var(--color-border) !important;
            transition: all 0.2s ease;
        }

        .glass-input:focus {
            border-color: var(--color-accent) !important;
            box-shadow: 0 0 15px var(--color-accent-subtle-bg) !important;
            transform: translateY(-1px);
        }

        .glass-button {
            background: var(--color-accent-fg);
            background: linear-gradient(135deg, var(--color-accent), var(--color-accent-fg));
            border: none;
            box-shadow: 0 4px 15px var(--color-accent-subtle-bg);
            transition: all 0.3s ease;
        }

        .glass-button:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 20px var(--color-accent-subtle-bg);
            filter: brightness(1.1);
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: revert;
            margin: 0;
        }

        textarea {
            resize: none;
            overflow: hidden;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--color-border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-text-muted);
        }
    </style>
</head>

<body>
    <div id="root"></div>
    <script type="text/babel" data-presets="react">
        // --- DADOS E UTILITARIOS ---
        const stateCodes = {
            'AC': '12', 'AL': '27', 'AP': '16', 'AM': '13', 'BA': '29', 'CE': '23', 'DF': '53', 'ES': '32',
            'GO': '52', 'MA': '21', 'MT': '51', 'MS': '50', 'MG': '31', 'PA': '15', 'PB': '25', 'PR': '41',
            'PE': '26', 'PI': '22', 'RJ': '33', 'RN': '24', 'RS': '43', 'RO': '11', 'RR': '14', 'SC': '42',
            'SP': '35', 'SE': '28', 'TO': '17'
        };

        const countryCodes = [
            { code: '1058', name: 'BRASIL' }, { code: '0230', name: 'ALEMANHA' }, { code: '0400', name: 'ANGOLA' },
            { code: '0474', name: 'ARABIA SAUDITA' }, { code: '0531', name: 'ARGELIA' }, { code: '0590', name: 'ARGENTINA' },
            { code: '0639', name: 'AUSTRALIA' }, { code: '0698', name: 'AUSTRIA' }, { code: '1015', name: 'BELGICA' },
            { code: '1112', name: 'BOLIVIA' }, { code: '1501', name: 'CANADA' }, { code: '1544', name: 'CHILE' },
            { code: '1600', name: 'CHINA' }, { code: '1720', name: 'COLOMBIA' }, { code: '1908', name: 'COREIA DO SUL' },
            { code: '2321', name: 'DINAMARCA' }, { code: '2400', name: 'EGITO' }, { code: '2455', name: 'ESPANHA' },
            { code: '2498', name: 'ESTADOS UNIDOS' }, { code: '2715', name: 'FILIPINAS' }, { code: '2758', name: 'FRANCA' },
            { code: '3379', name: 'INDIA' }, { code: '3557', name: 'INDONESIA' }, { code: '3654', name: 'IRAQUE' },
            { code: '3727', name: 'IRLANDA' }, { code: '3860', name: 'ITALIA' }, { code: '3990', name: 'JAPAO' },
            { code: '4883', name: 'MEXICO' }, { code: '5282', name: 'NORUEGA' }, { code: '5380', name: 'NOVA ZELANDIA' },
            { code: '5738', name: 'HOLANDA (PAISES BAIXOS)' }, { code: '5860', name: 'PAQUISTAO' }, { code: '5908', name: 'PARAGUAI' },
            { code: '5991', name: 'PERU' }, { code: '6033', name: 'POLONIA' }, { code: '6070', name: 'PORTUGAL' },
            { code: '6289', name: 'REINO UNIDO' }, { code: '7286', name: 'SUICA' }, { code: '7375', name: 'SUECIA' },
            { code: '7561', name: 'TAILANDIA' }, { code: '7707', name: 'TURQUIA' }, { code: '8458', name: 'URUGUAI' },
            { code: '8580', name: 'VENEZUELA' }
        ];

        const FIELD_MAX_LENGTHS = {
            'ide.natOp': 60, 'ide.nNF': 9, 'ide.serie': 3, 'emit.CNPJ': 14, 'emit.xNome': 60,
            'emit.enderEmit.xLgr': 60, 'emit.enderEmit.nro': 60, 'emit.enderEmit.xBairro': 60,
            'emit.enderEmit.xMun': 60, 'emit.enderEmit.cMun': 7, 'emit.enderEmit.CEP': 8,
            'dest.idEstrangeiro': 20, 'dest.xNome': 60, 'dest.enderDest.xLgr': 60,
            'dest.enderDest.nro': 60, 'dest.enderDest.xBairro': 60, 'dest.enderDest.xMun': 60,
            'dest.enderDest.xPais': 60, 'dest.enderDest.cPais': 4, 'prod.cProd': 60,
            'prod.xProd': 120, 'prod.NCM': 8, 'prod.CFOP': 4, 'prod.uCom': 6,
            'di.nDI': 12, 'di.xLocDesemb': 60, 'di.UFDesemb': 2, 'di.cExportador': 60,
            'adi.nAdicao': 3, 'adi.nSeqAdic': 3, 'adi.cFabricante': 60, 'adi.nDraw': 11
        };

        const TAX_REFORM_DATA = <?php echo json_encode($csts); ?>;

        const formatAsISOStringWithTimezone = (date) => {
            if (!date || isNaN(date.getTime())) { const now = new Date(); date = now; }
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            const timezoneOffset = -date.getTimezoneOffset();
            const offsetHours = String(Math.floor(Math.abs(timezoneOffset) / 60)).padStart(2, '0');
            const offsetMinutes = String(Math.abs(timezoneOffset) % 60).padStart(2, '0');
            const offsetSign = timezoneOffset >= 0 ? '+' : '-';
            return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}${offsetSign}${offsetHours}:${offsetMinutes}`;
        };

        const INITIAL_NFE_DATA = {
            infNFe: {
                Id: "NFe43240743102945000127550010000000011002031018",
                versao: "4.00",
                ide: { cUF: "43", cNF: "00203101", natOp: "Compra para Comercializacao", mod: "55", serie: "1", nNF: "1", dhEmi: formatAsISOStringWithTimezone(new Date()), dhSaiEnt: formatAsISOStringWithTimezone(new Date()), tpNF: "0", idDest: "3", cMunFG: "4305108", tpImp: "1", tpEmis: "1", cDV: "8", tpAmb: "1", finNFe: "1", indFinal: "0", indPres: "9", procEmi: "3", verProc: "TESTE" },
                emit: { CNPJ: "01212344000127", xNome: "EMPRESA ABC", xFant: "NOME FANTASIA", enderEmit: { xLgr: "RUA TESTE 00", nro: "00", xBairro: "TESTE", cMun: "4305108", xMun: "CAXIAS DO SUL", UF: "RS", CEP: "95012663", cPais: "1058", xPais: "BRASIL", fone: "" }, IE: "", IM: "", CRT: "3" },
                dest: { idEstrangeiro: "EXT-0001", xNome: "TESTE CLIENTE", enderDest: { xLgr: "TESTE 2", nro: "", xBairro: "Burgbrohl", cMun: "9999999", xMun: "BURGOBROHL", UF: "EX", CEP: "00000000", cPais: "0230", xPais: "ALEMANHA" }, indIEDest: "9" },
                det: [
                    { nItem: 1, prod: { cProd: "210706", cEAN: "SEM GTIN", xProd: "PRODUTO TESTE", NCM: "68042219", CFOP: "3102", uCom: "PC", qCom: "3800.0000", vUnCom: "2.912953", vProd: "11069.22", cEANTrib: "SEM GTIN", uTrib: "PC", qTrib: "3800.0000", vUnTrib: "2.912953", vFrete: "276.70", vSeg: "0.00", vOutro: "1345.92", indTot: "1", DI: [{ nDI: "", dDI: "", xLocDesemb: "", UFDesemb: "", dDesemb: "", tpViaTransp: "1", vAFRMM: "0.00", tpIntermedio: "1", cExportador: "", vSISCOMEX: "0.00", adi: [{ nAdicao: "1", nSeqAdic: "1", cFabricante: "00000", nDraw: "123456789" }] }] }, imposto: { ICMS: { ICMS00: { orig: "1", CST: "00", modBC: "3", vBC: "15678.17", pICMS: "17.00", vICMS: "2665.29" } }, IPI: { cEnq: "999", IPITrib: { CST: "01", vBC: "0.00", pIPI: "0.00", vIPI: "0.00" } }, II: { vBC: "11069.22", vDespAdu: "45.29", vII: "597.74", vIOF: "0.00" }, PIS: { PISAliq: { CST: "01", vBC: "11069.22", pPIS: "2.10", vPIS: "232.45" } }, COFINS: { COFINSAliq: { CST: "01", vBC: "11069.22", pCOFINS: "9.65", vCOFINS: "1068.18" } }, IBSCBS: { CST: "01", cClassTrib: "", gIBSCBS: { vBC: "0.00", gIBSUF: { pIBSUF: "0.00", vIBSUF: "0.00" }, gIBSMun: { pIBSMun: "0.00", vIBSMun: "0.00" }, gCBS: { pCBS: "0.00", vCBS: "0.00" }, vIBS: "0.00" } } }, infAdProd: "NF-e Referente a importacao conforme DI 1010" }
                ],
                total: { ICMSTot: { vBC: "15678.17", vICMS: "2665.29", vICMSDeson: "0.00", vFCPUFDest: "0.00", vICMSUFDest: "0.00", vICMSUFRemet: "0.00", vFCP: "0.00", vBCST: "0.00", vST: "0.00", vFCPST: "0.00", vFCPSTRet: "0.00", vProd: "11069.22", vFrete: "276.70", vSeg: "0.00", vDesc: "0.00", vII: "597.74", vDespAdu: "45.29", vIPI: "0.00", vIPIDevol: "0.00", vPIS: "232.45", vCOFINS: "1068.18", vOutro: "1345.92", vNF: "13333.15", vTotTrib: "0.00" }, IBSCBSTot: { vBCIBSCBS: "0.00", gIBS: { vIBS: "0.00" }, gCBS: { vCBS: "0.00" } } },
                transp: { modFrete: "0" },
                pag: { detPag: { tPag: "99", vPag: "13333.15" } },
                infAdic: { infCpl: "NF-e Referente a importacao conforme DI 1010" }
            }
        };

        const generateCNF = () => String(Math.floor(10000000 + Math.random() * 89999999));
        const getEmptyNfeData = () => ({
            infNFe: {
                Id: "", versao: "4.00",
                ide: { cUF: "43", cNF: generateCNF(), natOp: "", mod: "55", serie: "1", nNF: "", dhEmi: formatAsISOStringWithTimezone(new Date()), dhSaiEnt: formatAsISOStringWithTimezone(new Date()), tpNF: "0", idDest: "3", cMunFG: "", tpImp: "1", tpEmis: "1", cDV: "", tpAmb: "1", finNFe: "1", indFinal: "0", indPres: "9", procEmi: "3", verProc: "TESTE" },
                emit: { CNPJ: "", xNome: "", xFant: "", enderEmit: { xLgr: "", nro: "", xBairro: "", cMun: "", xMun: "", UF: "RS", CEP: "", cPais: "1058", xPais: "BRASIL", fone: "" }, IE: "", IM: "", CRT: "3" },
                dest: { idEstrangeiro: "", xNome: "", enderDest: { xLgr: "", nro: "", xBairro: "", cMun: "9999999", xMun: "", UF: "EX", CEP: "", cPais: "", xPais: "" }, indIEDest: "9" },
                det: [],
                total: { ICMSTot: { vBC: "0.00", vICMS: "0.00", vICMSDeson: "0.00", vFCPUFDest: "0.00", vICMSUFDest: "0.00", vICMSUFRemet: "0.00", vFCP: "0.00", vBCST: "0.00", vST: "0.00", vFCPST: "0.00", vFCPSTRet: "0.00", vProd: "0.00", vFrete: "0.00", vSeg: "0.00", vDesc: "0.00", vII: "0.00", vDespAdu: "0.00", vIPI: "0.00", vIPIDevol: "0.00", vPIS: "0.00", vCOFINS: "0.00", vOutro: "0.00", vNF: "0.00", vTotTrib: "0.00" }, IBSCBSTot: { vBCIBSCBS: "0.00", gIBS: { vIBS: "0.00" }, gCBS: { vCBS: "0.00" } } },
                transp: { modFrete: "0" },
                pag: { detPag: { tPag: "99", vPag: "0.00" } },
                infAdic: { infCpl: "" }
            }
        });
        const getEmptyDiInfo = () => ({ nDI: "", dDI: "", xLocDesemb: "", UFDesemb: "", dDesemb: "", cExportador: "", tpViaTransp: "7", tpIntermedio: "1", vAFRMM: "0.00", vSISCOMEX: "0.00" });

        const escapeXml = (unsafe) => {
            if (typeof unsafe !== 'string') return '';
            return unsafe.replace(/[<>&'"]/g, (c) => {
                switch (c) { case '<': return '&lt;'; case '>': return '&gt;'; case '&': return '&amp;'; case '\'': return '&apos;'; case '"': return '&quot;'; default: return c; }
            });
        };

        const generateDIXml = (di) => `
                <DI>
                  <nDI>${escapeXml(di.nDI)}</nDI>
                  <dDI>${escapeXml(di.dDI)}</dDI>
                  <xLocDesemb>${escapeXml(di.xLocDesemb)}</xLocDesemb>
                  <UFDesemb>${escapeXml(di.UFDesemb)}</UFDesemb>
                  <tpViaTransp>${escapeXml(di.tpViaTransp)}</tpViaTransp>
                  ${di.vAFRMM !== undefined && di.vAFRMM !== null && di.vAFRMM !== '' ? `<vAFRMM>${escapeXml(di.vAFRMM)}</vAFRMM>` : ''}
                  ${di.vAFRMM !== undefined && di.vAFRMM !== null && di.vAFRMM !== '' ? `<tpIntermedio>${escapeXml(di.CNPJ ? di.tpIntermedio : '3')}</tpIntermedio>` : ''}
                  ${(di.CNPJ && (di.tpIntermedio == '1' || di.tpIntermedio == '2')) ? `<CNPJ>${escapeXml(di.CNPJ)}</CNPJ>` : ''}
                  ${(di.UFTerceiro && (di.tpIntermedio == '1' || di.tpIntermedio == '2')) ? `<UFTerceiro>${escapeXml(di.UFTerceiro)}</UFTerceiro>` : ''}
                  <cExportador>${escapeXml(di.cExportador)}</cExportador>
                  ${(di.vSISCOMEX !== undefined && di.vSISCOMEX !== null && di.vSISCOMEX !== '') ? `<vSISCOMEX>${escapeXml(di.vSISCOMEX)}</vSISCOMEX>` : ''}
                  ${((di.adi && di.adi.length > 0) ? di.adi : [{ nAdicao: '1', nSeqAdic: '1', cFabricante: '00000' }]).map(a => `<adi>
                    <nAdicao>${escapeXml(a.nAdicao || '1')}</nAdicao>
                    <nSeqAdic>${escapeXml(a.nSeqAdic || '1')}</nSeqAdic>
                    <cFabricante>${escapeXml(a.cFabricante || '00000')}</cFabricante>
                  </adi>`).join('')}
                </DI>`;

        const generateDetalheXml = (det) => `
          <det nItem="${det.nItem}">
            <prod>
              <cProd>${escapeXml(det.prod.cProd)}</cProd>
              <cEAN>${escapeXml(det.prod.cEAN)}</cEAN>
              <xProd>${escapeXml(det.prod.xProd)}</xProd>
              <NCM>${escapeXml(det.prod.NCM)}</NCM>
              <CFOP>${escapeXml(det.prod.CFOP)}</CFOP>
              <uCom>${escapeXml(det.prod.uCom)}</uCom>
              <qCom>${escapeXml(det.prod.qCom)}</qCom>
              <vUnCom>${escapeXml(det.prod.vUnCom)}</vUnCom>
              <vProd>${escapeXml(det.prod.vProd)}</vProd>
              <cEANTrib>${escapeXml(det.prod.cEANTrib)}</cEANTrib>
              <uTrib>${escapeXml(det.prod.uTrib)}</uTrib>
              <qTrib>${escapeXml(det.prod.qTrib)}</qTrib>
              <vUnTrib>${escapeXml(det.prod.vUnTrib)}</vUnTrib>${det.prod.vFrete && parseFloat(det.prod.vFrete) > 0 ? `
              <vFrete>${escapeXml(det.prod.vFrete)}</vFrete>` : ''}${det.prod.vSeg && parseFloat(det.prod.vSeg) > 0 ? `
              <vSeg>${escapeXml(det.prod.vSeg)}</vSeg>` : ''}${det.prod.vOutro && parseFloat(det.prod.vOutro) > 0 ? `
              <vOutro>${escapeXml(det.prod.vOutro)}</vOutro>` : ''}
              <indTot>${escapeXml(det.prod.indTot)}</indTot>
              ${det.prod.DI.map(generateDIXml).join('')}
            </prod>
            <imposto>
              <ICMS>
                <ICMS00>
                  <orig>${escapeXml(det.imposto.ICMS.ICMS00.orig)}</orig>
                  <CST>${escapeXml(det.imposto.ICMS.ICMS00.CST)}</CST>
                  <modBC>${escapeXml(det.imposto.ICMS.ICMS00.modBC)}</modBC>
                  <vBC>${escapeXml(det.imposto.ICMS.ICMS00.vBC || '0.00')}</vBC>
                  <pICMS>${escapeXml(det.imposto.ICMS.ICMS00.pICMS || '0.00')}</pICMS>
                  <vICMS>${escapeXml(det.imposto.ICMS.ICMS00.vICMS || '0.00')}</vICMS>
                </ICMS00>
              </ICMS>
              <IPI>
                <cEnq>${escapeXml(det.imposto.IPI.cEnq)}</cEnq>
                <IPITrib>
                  <CST>${escapeXml(det.imposto.IPI.IPITrib.CST)}</CST>
                  <vBC>${escapeXml(det.imposto.IPI.IPITrib.vBC || '0.00')}</vBC>
                  <pIPI>${escapeXml(det.imposto.IPI.IPITrib.pIPI || '0.00')}</pIPI>
                  <vIPI>${escapeXml(det.imposto.IPI.IPITrib.vIPI || '0.00')}</vIPI>
                </IPITrib>
              </IPI>
              <II>
                <vBC>${escapeXml(det.imposto.II.vBC || '0.00')}</vBC>
                <vDespAdu>${escapeXml(det.imposto.II.vDespAdu || '0.00')}</vDespAdu>
                <vII>${escapeXml(det.imposto.II.vII || '0.00')}</vII>
                <vIOF>${escapeXml(det.imposto.II.vIOF || '0.00')}</vIOF>
              </II>
              <PIS>
                <PISAliq>
                  <CST>${escapeXml(det.imposto.PIS.PISAliq.CST)}</CST>
                  <vBC>${escapeXml(det.imposto.PIS.PISAliq.vBC || '0.00')}</vBC>
                  <pPIS>${escapeXml(det.imposto.PIS.PISAliq.pPIS || '0.00')}</pPIS>
                  <vPIS>${escapeXml(det.imposto.PIS.PISAliq.vPIS || '0.00')}</vPIS>
                </PISAliq>
              </PIS>
              <COFINS>
                <COFINSAliq>
                  <CST>${escapeXml(det.imposto.COFINS.COFINSAliq.CST)}</CST>
                  <vBC>${escapeXml(det.imposto.COFINS.COFINSAliq.vBC || '0.00')}</vBC>
                  <pCOFINS>${escapeXml(det.imposto.COFINS.COFINSAliq.pCOFINS || '0.00')}</pCOFINS>
                  <vCOFINS>${escapeXml(det.imposto.COFINS.COFINSAliq.vCOFINS || '0.00')}</vCOFINS>
                </COFINSAliq>
              </COFINS>
              ${det.imposto.IBSCBS && det.imposto.IBSCBS.gIBSCBS ? `
              <IBSCBS>
                <CST>${escapeXml(det.imposto.IBSCBS.CST)}</CST>
                ${det.imposto.IBSCBS.cClassTrib ? `<cClassTrib>${escapeXml(det.imposto.IBSCBS.cClassTrib)}</cClassTrib>` : ''}
                <gIBSCBS>
                  <vBC>${escapeXml(det.imposto.IBSCBS.gIBSCBS.vBC || '0.00')}</vBC>
                  <gIBSUF>
                    <pIBSUF>${escapeXml(det.imposto.IBSCBS.gIBSCBS.gIBSUF.pIBSUF || '0.00')}</pIBSUF>
                    <vIBSUF>${escapeXml(det.imposto.IBSCBS.gIBSCBS.gIBSUF.vIBSUF || '0.00')}</vIBSUF>
                  </gIBSUF>
                  <gIBSMun>
                    <pIBSMun>${escapeXml(det.imposto.IBSCBS.gIBSCBS.gIBSMun.pIBSMun || '0.00')}</pIBSMun>
                    <vIBSMun>${escapeXml(det.imposto.IBSCBS.gIBSCBS.gIBSMun.vIBSMun || '0.00')}</vIBSMun>
                  </gIBSMun>
                  <gCBS>
                    <pCBS>${escapeXml(det.imposto.IBSCBS.gIBSCBS.gCBS.pCBS || '0.00')}</pCBS>
                    <vCBS>${escapeXml(det.imposto.IBSCBS.gIBSCBS.gCBS.vCBS || '0.00')}</vCBS>
                  </gCBS>
                  <vIBS>${escapeXml(det.imposto.IBSCBS.gIBSCBS.vIBS || '0.00')}</vIBS>
                </gIBSCBS>
              </IBSCBS>` : ''}
            </imposto>
            ${det.infAdProd ? `<infAdProd>${escapeXml(det.infAdProd)}</infAdProd>` : ''}
          </det>`;

        const generateNFeXml = (nfe) => {
            const { infNFe } = nfe;
            const { ide, emit, dest, det, total, transp, pag, infAdic } = infNFe;
            const { ICMSTot } = total;

            const xml = '<' + '?xml version="1.0" encoding="UTF-8"?>' + `
      <NFe xmlns="http://www.portalfiscal.inf.br/nfe">
        <infNFe Id="${escapeXml(infNFe.Id)}" versao="${escapeXml(infNFe.versao)}">
          <ide>
            <cUF>${escapeXml(ide.cUF)}</cUF>
            <cNF>${escapeXml(ide.cNF)}</cNF>
            <natOp>${escapeXml(ide.natOp)}</natOp>
            <mod>${escapeXml(ide.mod)}</mod>
            <serie>${escapeXml(ide.serie)}</serie>
            <nNF>${escapeXml(ide.nNF)}</nNF>
            <dhEmi>${escapeXml(ide.dhEmi)}</dhEmi>${ide.dhSaiEnt ? `
            <dhSaiEnt>${escapeXml(ide.dhSaiEnt)}</dhSaiEnt>` : ''}
            <tpNF>${escapeXml(ide.tpNF)}</tpNF>
            <idDest>${escapeXml(ide.idDest)}</idDest>${ide.cMunFG ? `
            <cMunFG>${escapeXml(ide.cMunFG)}</cMunFG>` : ''}
            <tpImp>${escapeXml(ide.tpImp)}</tpImp>
            <tpEmis>${escapeXml(ide.tpEmis)}</tpEmis>
            <cDV>${escapeXml(ide.cDV)}</cDV>
            <tpAmb>${escapeXml(ide.tpAmb)}</tpAmb>
            <finNFe>${escapeXml(ide.finNFe)}</finNFe>
            <indFinal>${escapeXml(ide.indFinal)}</indFinal>
            <indPres>${escapeXml(ide.indPres)}</indPres>
            <procEmi>${escapeXml(ide.procEmi)}</procEmi>
            <verProc>${escapeXml(ide.verProc)}</verProc>
          </ide>
          <emit>
            <CNPJ>${escapeXml(emit.CNPJ)}</CNPJ>
            <xNome>${escapeXml(emit.xNome)}</xNome>${emit.xFant ? `
            <xFant>${escapeXml(emit.xFant)}</xFant>` : ''}
            <enderEmit>
              <xLgr>${escapeXml(emit.enderEmit.xLgr)}</xLgr>
              <nro>${escapeXml(emit.enderEmit.nro)}</nro>
              <xBairro>${escapeXml(emit.enderEmit.xBairro)}</xBairro>
              <cMun>${escapeXml(emit.enderEmit.cMun)}</cMun>
              <xMun>${escapeXml(emit.enderEmit.xMun)}</xMun>
              <UF>${escapeXml(emit.enderEmit.UF)}</UF>${emit.enderEmit.CEP ? `
              <CEP>${escapeXml(emit.enderEmit.CEP)}</CEP>` : ''}
              <cPais>${escapeXml(emit.enderEmit.cPais)}</cPais>
              <xPais>${escapeXml(emit.enderEmit.xPais)}</xPais>${emit.enderEmit.fone ? `
              <fone>${escapeXml(emit.enderEmit.fone)}</fone>` : ''}
            </enderEmit>
            ${emit.IE ? `<IE>${escapeXml(emit.IE)}</IE>` : ''}${emit.IM ? `
            <IM>${escapeXml(emit.IM)}</IM>` : ''}
            <CRT>${escapeXml(emit.CRT)}</CRT>
          </emit>
          <dest>
            <idEstrangeiro>${dest.idEstrangeiro ? escapeXml(dest.idEstrangeiro) : ''}</idEstrangeiro>
            <xNome>${escapeXml(dest.xNome)}</xNome>
            <enderDest>
              <xLgr>${escapeXml(dest.enderDest.xLgr)}</xLgr>
              <nro>${escapeXml(dest.enderDest.nro)}</nro>
              <xBairro>${escapeXml(dest.enderDest.xBairro)}</xBairro>
              <cMun>${escapeXml(dest.enderDest.cMun)}</cMun>
              <xMun>${escapeXml(dest.enderDest.xMun)}</xMun>
              <UF>${escapeXml(dest.enderDest.UF)}</UF>${dest.enderDest.CEP ? `
              <CEP>${escapeXml(dest.enderDest.CEP)}</CEP>` : ''}
              <cPais>${escapeXml(dest.enderDest.cPais)}</cPais>
              <xPais>${escapeXml(dest.enderDest.xPais)}</xPais>${dest.enderDest.fone ? `
              <fone>${escapeXml(dest.enderDest.fone)}</fone>` : ''}
            </enderDest>
            <indIEDest>${escapeXml(dest.indIEDest)}</indIEDest>
          </dest>
          ${det.map(generateDetalheXml).join('')}
          <total>
            <ICMSTot>
              <vBC>${escapeXml(ICMSTot.vBC)}</vBC>
              <vICMS>${escapeXml(ICMSTot.vICMS)}</vICMS>
              <vICMSDeson>${escapeXml(ICMSTot.vICMSDeson)}</vICMSDeson>
              ${ICMSTot.vFCPUFDest && parseFloat(ICMSTot.vFCPUFDest) > 0 ? `<vFCPUFDest>${escapeXml(ICMSTot.vFCPUFDest)}</vFCPUFDest>` : '<vFCPUFDest>0.00</vFCPUFDest>'}
              ${ICMSTot.vICMSUFDest && parseFloat(ICMSTot.vICMSUFDest) > 0 ? `<vICMSUFDest>${escapeXml(ICMSTot.vICMSUFDest)}</vICMSUFDest>` : '<vICMSUFDest>0.00</vICMSUFDest>'}
              ${ICMSTot.vICMSUFRemet && parseFloat(ICMSTot.vICMSUFRemet) > 0 ? `<vICMSUFRemet>${escapeXml(ICMSTot.vICMSUFRemet)}</vICMSUFRemet>` : '<vICMSUFRemet>0.00</vICMSUFRemet>'}
              <vFCP>${escapeXml(ICMSTot.vFCP)}</vFCP>
              <vBCST>${escapeXml(ICMSTot.vBCST)}</vBCST>
              <vST>${escapeXml(ICMSTot.vST)}</vST>
              <vFCPST>${escapeXml(ICMSTot.vFCPST)}</vFCPST>
              <vFCPSTRet>${escapeXml(ICMSTot.vFCPSTRet)}</vFCPSTRet>
              <vProd>${escapeXml(ICMSTot.vProd)}</vProd>
              <vFrete>${escapeXml(ICMSTot.vFrete)}</vFrete>
              <vSeg>${escapeXml(ICMSTot.vSeg)}</vSeg>
              <vDesc>${escapeXml(ICMSTot.vDesc)}</vDesc>
              <vII>${escapeXml(ICMSTot.vII)}</vII>
              <vDespAdu>${escapeXml(ICMSTot.vDespAdu || '0.00')}</vDespAdu>
              <vIPI>${escapeXml(ICMSTot.vIPI)}</vIPI>
              <vIPIDevol>${escapeXml(ICMSTot.vIPIDevol)}</vIPIDevol>
              <vPIS>${escapeXml(ICMSTot.vPIS)}</vPIS>
              <vCOFINS>${escapeXml(ICMSTot.vCOFINS)}</vCOFINS>
              <vOutro>${escapeXml(ICMSTot.vOutro)}</vOutro>
              <vNF>${escapeXml(ICMSTot.vNF)}</vNF>
              <vTotTrib>${escapeXml(ICMSTot.vTotTrib)}</vTotTrib>
            </ICMSTot>
            ${total.IBSCBSTot ? `
            <IBSCBSTot>
              <vBCIBSCBS>${escapeXml(total.IBSCBSTot.vBCIBSCBS)}</vBCIBSCBS>
              <gIBS><vIBS>${escapeXml(total.IBSCBSTot.gIBS.vIBS)}</vIBS></gIBS>
              <gCBS><vCBS>${escapeXml(total.IBSCBSTot.gCBS.vCBS)}</vCBS></gCBS>
            </IBSCBSTot>` : ''}
          </total>
          <transp>
            <modFrete>${escapeXml(transp.modFrete)}</modFrete>
          </transp>
          <pag>
            <detPag>
              <tPag>${escapeXml(pag.detPag.tPag)}</tPag>
              <vPag>${escapeXml(pag.detPag.vPag)}</vPag>
            </detPag>
          </pag>
          <infAdic>
            ${infAdic.infCpl ? `<infCpl>${escapeXml(infAdic.infCpl)}</infCpl>` : ''}
          </infAdic>
        </infNFe>
      </NFe>
      `;
            return xml.replace(/^\s*[\r\n]/gm, "");
        };

        const mapNfeXmlToState = (xmlDoc) => {
            const get = (element, tag) => element?.querySelector(tag)?.textContent?.trim() || '';
            const getAttr = (element, attribute) => element?.getAttribute(attribute) || '';

            let newNfe = getEmptyNfeData();
            let newDiInfo = getEmptyDiInfo();

            const infNFe = xmlDoc.querySelector('infNFe');
            if (!infNFe) throw new Error("Tag <infNFe> não encontrada no XML.");

            const ide = infNFe.querySelector('ide');
            if (ide) {
                newNfe.infNFe.ide = {
                    ...newNfe.infNFe.ide, cUF: get(ide, 'cUF'), cNF: get(ide, 'cNF'), natOp: get(ide, 'natOp'),
                    mod: get(ide, 'mod'), serie: get(ide, 'serie'), nNF: get(ide, 'nNF'),
                    dhEmi: get(ide, 'dhEmi'), dhSaiEnt: get(ide, 'dhSaiEnt'), tpNF: get(ide, 'tpNF'),
                    idDest: get(ide, 'idDest'), cMunFG: get(ide, 'cMunFG'), finNFe: get(ide, 'finNFe'),
                    indFinal: get(ide, 'indFinal'), indPres: get(ide, 'indPres')
                };
            }

            const emit = infNFe.querySelector('emit');
            if (emit) {
                newNfe.infNFe.emit = { ...newNfe.infNFe.emit, CNPJ: get(emit, 'CNPJ'), xNome: get(emit, 'xNome'), xFant: get(emit, 'xFant'), IE: get(emit, 'IE') };
                const enderEmit = emit.querySelector('enderEmit');
                if (enderEmit) {
                    newNfe.infNFe.emit.enderEmit = { xLgr: get(enderEmit, 'xLgr'), nro: get(enderEmit, 'nro'), xBairro: get(enderEmit, 'xBairro'), cMun: get(enderEmit, 'cMun'), xMun: get(enderEmit, 'xMun'), UF: get(enderEmit, 'UF'), CEP: get(enderEmit, 'CEP'), cPais: get(enderEmit, 'cPais'), xPais: get(enderEmit, 'xPais'), fone: get(enderEmit, 'fone') };
                }
            }

            const dest = infNFe.querySelector('dest');
            if (dest) {
                newNfe.infNFe.dest = { ...newNfe.infNFe.dest, idEstrangeiro: get(dest, 'idEstrangeiro'), xNome: get(dest, 'xNome'), indIEDest: get(dest, 'indIEDest') };
                const enderDest = dest.querySelector('enderDest');
                if (enderDest) {
                    newNfe.infNFe.dest.enderDest = { xLgr: get(enderDest, 'xLgr'), nro: get(enderDest, 'nro'), xBairro: get(enderDest, 'xBairro'), cMun: get(enderDest, 'cMun'), xMun: get(enderDest, 'xMun'), UF: get(enderDest, 'UF'), CEP: get(enderDest, 'CEP'), cPais: get(enderDest, 'cPais'), xPais: get(enderDest, 'xPais'), fone: get(enderDest, 'fone') };
                }
            }

            const dets = infNFe.querySelectorAll('det');
            newNfe.infNFe.det = Array.from(dets).map((det) => {
                const itemTemplate = JSON.parse(JSON.stringify(INITIAL_NFE_DATA.infNFe.det[0]));
                const prod = det.querySelector('prod');
                const imposto = det.querySelector('imposto');

                itemTemplate.nItem = getAttr(det, 'nItem');
                if (prod) {
                    itemTemplate.prod = {
                        ...itemTemplate.prod, cProd: get(prod, 'cProd'), cEAN: get(prod, 'cEAN'), xProd: get(prod, 'xProd'),
                        NCM: get(prod, 'NCM'), CFOP: get(prod, 'CFOP'), uCom: get(prod, 'uCom'), qCom: get(prod, 'qCom'),
                        vUnCom: get(prod, 'vUnCom'), vProd: get(prod, 'vProd'), vFrete: get(prod, 'vFrete'),
                        vSeg: get(prod, 'vSeg'), vOutro: get(prod, 'vOutro'), indTot: get(prod, 'indTot')
                    };
                }
                if (imposto) {
                    const icmsNode = imposto.querySelector('ICMS > *');
                    if (icmsNode) itemTemplate.imposto.ICMS.ICMS00 = { orig: get(icmsNode, 'orig'), CST: get(icmsNode, 'CST'), modBC: get(icmsNode, 'modBC'), vBC: get(icmsNode, 'vBC'), pICMS: get(icmsNode, 'pICMS'), vICMS: get(icmsNode, 'vICMS') };
                    const ipiTrib = imposto.querySelector('IPI > IPITrib');
                    if (ipiTrib) itemTemplate.imposto.IPI.IPITrib = { CST: get(ipiTrib, 'CST'), vBC: get(ipiTrib, 'vBC'), pIPI: get(ipiTrib, 'pIPI'), vIPI: get(ipiTrib, 'vIPI') };
                    const ii = imposto.querySelector('II');
                    if (ii) itemTemplate.imposto.II = { vBC: get(ii, 'vBC'), vDespAdu: get(ii, 'vDespAdu'), vII: get(ii, 'vII'), vIOF: get(ii, 'vIOF') };
                    const pisNode = imposto.querySelector('PIS > *');
                    if (pisNode) itemTemplate.imposto.PIS.PISAliq = { CST: get(pisNode, 'CST'), vBC: get(pisNode, 'vBC'), pPIS: get(pisNode, 'pPIS'), vPIS: get(pisNode, 'vPIS') };
                    const cofinsNode = imposto.querySelector('COFINS > *');
                    if (cofinsNode) itemTemplate.imposto.COFINS.COFINSAliq = { CST: get(cofinsNode, 'CST'), vBC: get(cofinsNode, 'vBC'), pCOFINS: get(cofinsNode, 'pCOFINS'), vCOFINS: get(cofinsNode, 'vCOFINS') };

                    const ibsCbsNode = imposto.querySelector('IBSCBS');
                    if (ibsCbsNode) {
                        const gIBSCBS = ibsCbsNode.querySelector('gIBSCBS');
                        if (gIBSCBS) {
                            itemTemplate.imposto.IBSCBS = {
                                CST: get(ibsCbsNode, 'CST'), cClassTrib: get(ibsCbsNode, 'cClassTrib'),
                                gIBSCBS: {
                                    vBC: get(gIBSCBS, 'vBC'),
                                    gIBSUF: { pIBSUF: get(gIBSCBS, 'gIBSUF pIBSUF'), vIBSUF: get(gIBSCBS, 'gIBSUF vIBSUF') },
                                    gIBSMun: { pIBSMun: get(gIBSCBS, 'gIBSMun pIBSMun'), vIBSMun: get(gIBSCBS, 'gIBSMun vIBSMun') },
                                    gCBS: { pCBS: get(gIBSCBS, 'gCBS pCBS'), vCBS: get(gIBSCBS, 'gCBS vCBS') },
                                    vIBS: get(gIBSCBS, 'vIBS')
                                }
                            };
                        }
                    }
                }

                const diNodes = prod.querySelectorAll('DI');
                itemTemplate.prod.DI = Array.from(diNodes).map(diNode => ({
                    nDI: get(diNode, 'nDI'), dDI: get(diNode, 'dDI'), xLocDesemb: get(diNode, 'xLocDesemb'),
                    UFDesemb: get(diNode, 'UFDesemb'), dDesemb: get(diNode, 'dDesemb'), tpViaTransp: get(diNode, 'tpViaTransp'),
                    vAFRMM: get(diNode, 'vAFRMM'), tpIntermedio: get(diNode, 'tpIntermedio'), cExportador: get(diNode, 'cExportador'),
                    adi: Array.from(diNode.querySelectorAll('adi')).map(adiNode => ({ nAdicao: get(adiNode, 'nAdicao'), nSeqAdic: get(adiNode, 'nSeqAdic'), cFabricante: get(adiNode, 'cFabricante'), nDraw: get(adiNode, 'nDraw') }))
                }));

                itemTemplate.infAdProd = get(det, 'infAdProd');
                return itemTemplate;
            });

            const icmsTot = infNFe.querySelector('ICMSTot');
            if (icmsTot) {
                newNfe.infNFe.total.ICMSTot = {
                    vBC: get(icmsTot, 'vBC'), vICMS: get(icmsTot, 'vICMS'), vICMSDeson: get(icmsTot, 'vICMSDeson'),
                    vFCP: get(icmsTot, 'vFCP'), vBCST: get(icmsTot, 'vBCST'), vST: get(icmsTot, 'vST'),
                    vFCPST: get(icmsTot, 'vFCPST'), vFCPSTRet: get(icmsTot, 'vFCPSTRet'), vProd: get(icmsTot, 'vProd'),
                    vFrete: get(icmsTot, 'vFrete'), vSeg: get(icmsTot, 'vSeg'), vDesc: get(icmsTot, 'vDesc'),
                    vII: get(icmsTot, 'vII'), vIPI: get(icmsTot, 'vIPI'), vIPIDevol: get(icmsTot, 'vIPIDevol'),
                    vPIS: get(icmsTot, 'vPIS'), vCOFINS: get(icmsTot, 'vCOFINS'), vOutro: get(icmsTot, 'vOutro'),
                    vNF: get(icmsTot, 'vNF'), vTotTrib: get(icmsTot, 'vTotTrib'),
                    vFCPUFDest: get(icmsTot, 'vFCPUFDest'), vICMSUFDest: get(icmsTot, 'vICMSUFDest'), vICMSUFRemet: get(icmsTot, 'vICMSUFRemet'), vDespAdu: get(icmsTot, 'vDespAdu'),
                };
            }
            const ibsCbsTot = infNFe.querySelector('total IBSCBSTot');
            if (ibsCbsTot) {
                newNfe.infNFe.total.IBSCBSTot = {
                    vBCIBSCBS: get(ibsCbsTot, 'vBCIBSCBS'),
                    gIBS: { vIBS: get(ibsCbsTot, 'gIBS vIBS') },
                    gCBS: { vCBS: get(ibsCbsTot, 'gCBS vCBS') }
                };
            }
            newNfe.infNFe.transp.modFrete = get(infNFe, 'transp modFrete');
            const detPag = infNFe.querySelector('pag detPag');
            if (detPag) newNfe.infNFe.pag.detPag = { tPag: get(detPag, 'tPag'), vPag: get(detPag, 'vPag') };
            newNfe.infNFe.infAdic.infCpl = get(infNFe, 'infAdic infCpl');

            if (newNfe.infNFe.det.length > 0 && newNfe.infNFe.det[0].prod.DI.length > 0) {
                const firstDI = newNfe.infNFe.det[0].prod.DI[0];
                newDiInfo = {
                    nDI: firstDI.nDI, dDI: firstDI.dDI, xLocDesemb: firstDI.xLocDesemb, UFDesemb: firstDI.UFDesemb,
                    dDesemb: firstDI.dDesemb, cExportador: firstDI.cExportador, tpViaTransp: firstDI.tpViaTransp,
                    tpIntermedio: firstDI.tpIntermedio, vAFRMM: get(icmsTot, 'vAFRMM') || '0.00', vSISCOMEX: '0.00'
                };
            }
            return { nfeData: newNfe, diInfoData: newDiInfo };
        };

        // --- COMPONENTES UI ---
        const Input = ({ label, id, className, helpText, ...props }) => {
            const inputId = id || `input-${label.replace(/\s+/g, '-')}`;
            return (
                <div className={className}>
                    <label htmlFor={inputId} className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm flex items-center gap-1">
                        {label}
                        {helpText && (
                            <div className="group relative ml-1 display-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-blue-400 cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div className="invisible group-hover:visible absolute z-50 bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 text-xs text-white bg-gray-800 rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    {helpText}
                                    <div className="absolute top-100 left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                                </div>
                            </div>
                        )}
                    </label>
                    <input id={inputId} {...props} className={`glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] placeholder-[var(--color-text-placeholder)] p-2.5 transition-all duration-200 ${props.readOnly ? 'bg-gray-100/10 cursor-not-allowed text-gray-400' : ''}`} />
                </div>
            );
        };

        const Section = ({ title, children, onClear, isCollapsible = true, headerContent = null }) => {
            const [isOpen, setIsOpen] = React.useState(true);
            return (
                <div className="glass-panel rounded-xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
                    <div className="w-full flex justify-between items-center px-4 py-3 bg-[var(--color-card-header-bg)] border-b border-[var(--color-border)]">
                        <div className="flex items-center gap-2">
                            <h3 className="text-lg font-semibold text-[var(--color-text-primary)]">{title}</h3>
                            {headerContent}
                            {onClear && (
                                <button type="button" onClick={onClear} title={`Limpar Seção: ${title}`} className="p-1 text-[var(--color-text-muted)] hover:text-[var(--color-danger-text)] hover:bg-[var(--color-danger-bg-hover)] rounded-full transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" /></svg>
                                </button>
                            )}
                        </div>
                        {isCollapsible && (
                            <button type="button" onClick={() => setIsOpen(!isOpen)} className="p-1 rounded-full hover:bg-black/10" aria-expanded={isOpen}>
                                <svg className={`h-6 w-6 transform transition-transform text-[var(--color-text-muted)] ${isOpen ? 'rotate-180' : ''}`} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" /></svg>
                            </button>
                        )}
                    </div>
                    {isOpen && <div className="p-4 sm:p-6 space-y-4">{children}</div>}
                </div>
            );
        };

        const DanfeModal = ({ nfe, isOpen, onClose }) => {
            const [zoom, setZoom] = React.useState(1);
            if (!isOpen) return null;
            return (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 flex justify-center items-center p-4" onClick={onClose}>
                    <div className="glass-panel rounded-xl w-full max-w-[95vw] h-[90vh] relative animate-fadeIn flex flex-col" onClick={e => e.stopPropagation()}>
                        <div className="glass-header p-4 z-10 flex justify-between items-center rounded-t-xl flex-none">
                            <h3 className="text-xl font-bold text-center text-[var(--color-text-primary)]">Pré Visualização DANFE</h3>
                            
                            <div className="flex items-center gap-2">
                                <div className="flex items-center gap-1 bg-[var(--color-card-bg)] rounded-lg p-1 border border-[var(--color-border)] shadow-sm mr-4">
                                    <button type="button" onClick={() => setZoom(prev => Math.max(0.5, prev - 0.1))} className="p-1.5 rounded-md text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-card-subtle-bg)] transition-colors" title="Diminuir Zoom">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" /></svg>
                                    </button>
                                    <span className="text-xs font-mono font-bold text-[var(--color-text-primary)] w-12 text-center select-none">{Math.round(zoom * 100)}%</span>
                                    <button type="button" onClick={() => setZoom(prev => Math.min(3, prev + 0.1))} className="p-1.5 rounded-md text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-card-subtle-bg)] transition-colors" title="Aumentar Zoom">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" /></svg>
                                    </button>
                                </div>
                                <button type="button" onClick={onClose} className="p-2 rounded-full text-[var(--color-text-muted)] hover:bg-[var(--color-danger-bg-hover)] hover:text-[var(--color-danger-text)] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                        <div className="p-4 sm:p-6 bg-white/90 rounded-b-xl flex-1 overflow-hidden flex flex-col">
                            <p className="text-center font-semibold text-sm text-red-600 mb-4 flex-none">(SEM VALOR FISCAL - APENAS PARA CONSULTA)</p>
                            <div className="flex-1 overflow-auto pb-4 custom-scrollbar bg-gray-50/50 rounded-lg border border-gray-200 p-4">
                                <div style={{ transform: `scale(${zoom})`, transformOrigin: 'top center', transition: 'transform 0.2s ease-out', width: '100%', minWidth: '800px', marginBottom: `${(zoom - 1) * 300}px` }}>
                                    <DanfePreview nfe={nfe} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        };

        const DISummaryModal = ({ summary, isOpen, onClose, onConfirm }) => {
            if (!isOpen || !summary) return null;
            const Field = ({ label, children }) => (
                <div className="py-2 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt className="text-sm font-medium text-[var(--color-text-secondary)]">{label}</dt>
                    <dd className="mt-1 text-sm text-[var(--color-text-primary)] sm:mt-0 sm:col-span-2">{children || '-'}</dd>
                </div>
            );
            return (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center items-center p-4" onClick={onClose}>
                    <div className="glass-panel rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all animate-fadeIn" onClick={e => e.stopPropagation()}>
                        <div className="p-6">
                            <div className="flex items-start justify-between">
                                <div><h3 className="text-xl font-bold text-[var(--color-text-primary)]">Resumo da Declaração de Importação</h3><p className="mt-1 text-sm text-[var(--color-text-muted)]">Confirme os dados antes de carregar no formulário.</p></div>
                                <button type="button" onClick={onClose} className="p-2 rounded-full text-[var(--color-text-muted)] hover:bg-[var(--color-danger-bg-hover)] hover:text-[var(--color-danger-text)] transition-colors"><svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>
                            <div className="mt-6 border-t border-[var(--color-border)] pt-4">
                                <dl className="divide-y divide-[var(--color-border)]">
                                    <Field label="Nº da DI">{summary.numeroDI}</Field>
                                    <Field label="Data do Registro">{summary.dataRegistro}</Field>
                                    <Field label="Importador">{summary.importadorNome}</Field>
                                    <Field label="Fornecedor/Exportador">{summary.fornecedorNome}</Field>
                                    <Field label="Total de Adições">{summary.totalAdicoes}</Field>
                                    <Field label="Valor Total (R$)">{summary.valorTotalReais}</Field>
                                </dl>
                            </div>
                        </div>
                        <div className="glass-header px-6 py-4 flex justify-end items-center gap-3 rounded-b-2xl border-t border-[var(--color-border)]">
                            <button type="button" onClick={onClose} className="rounded-lg px-4 py-2 text-sm font-semibold text-[var(--color-text-secondary)] hover:bg-[var(--color-card-subtle-bg)] transition-colors">Cancelar</button>
                            <button type="button" onClick={onConfirm} className="glass-button inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all">Importar Dados</button>
                        </div>
                    </div>
                </div>
            );
        };

        const HelpModal = ({ isOpen, onClose }) => {
            if (!isOpen) return null;
            const HelpSection = ({ title, children }) => (
                <div className="mb-6"><h4 className="text-lg font-bold text-[var(--color-text-primary)] border-b-2 border-[var(--color-accent-fg)] pb-1 mb-3">{title}</h4><div className="space-y-2 text-[var(--color-text-secondary)] text-sm leading-relaxed">{children}</div></div>
            );
            return (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center items-center p-4" onClick={onClose}>
                    <div className="glass-panel rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col transform transition-all animate-fadeIn" onClick={e => e.stopPropagation()}>
                        <div className="flex justify-between items-center p-4 border-b border-[var(--color-border)] sticky top-0 glass-header rounded-t-2xl z-10">
                            <h3 className="text-xl font-bold text-[var(--color-text-primary)]">Guia de Uso da Ferramenta</h3>
                            <button type="button" onClick={onClose} className="p-2 rounded-full text-[var(--color-text-muted)] hover:bg-[var(--color-danger-bg-hover)] hover:text-[var(--color-danger-text)] transition-colors"><svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>
                        <div className="p-8 overflow-y-auto">
                            <HelpSection title="Visão Geral"><p>Esta ferramenta foi projetada para simplificar a criação e correção de XML de Notas Fiscais (NF-e) de importação. Você pode preencher todos os dados manualmente, importar informações de uma Declaração de Importação (DI) ou importar uma NF-e (XML) existente para fazer ajustes.</p></HelpSection>
                            <HelpSection title="Como Começar: 3 Maneiras Principais">
                                <p><strong>1. Criar uma NF-e do Zero:</strong></p><ul className="list-disc list-inside pl-4"><li>Navegue pelas abas e preencha os campos.</li><li>Na aba "Itens", adicione os produtos.</li></ul>
                                <p className="mt-4"><strong>2. Importar Dados da DI:</strong></p><ul className="list-disc list-inside pl-4"><li>Clique no botão <strong>"Importar DI"</strong> no cabeçalho (seta no topo).</li><li>Selecione o arquivo da DI (<strong>.xml</strong> ou <strong>.pdf</strong>).</li></ul>
                                <p className="mt-4"><strong>3. Corrigir uma NF-e Existente:</strong></p><ul className="list-disc list-inside pl-4"><li>Clique no botão <strong>"Importar NF-e"</strong>.</li><li>Selecione o arquivo <strong>.xml</strong> da nota para corrigir.</li><li>Faça as correções e clique em "Gerar XML".</li></ul>
                            </HelpSection>
                        </div>
                    </div>
                </div>
            );
        };        const CreditsModal = ({ isOpen, onClose }) => {
            if (!isOpen) return null;
            return (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center items-center p-4" onClick={onClose}>
                    <div className="glass-panel rounded-2xl shadow-xl w-full max-w-md transform transition-all animate-fadeIn" onClick={e => e.stopPropagation()}>
                        <div className="flex justify-between items-center p-6 border-b border-[var(--color-border)] glass-header rounded-t-2xl">
                            <h3 className="text-xl font-bold text-[var(--color-text-primary)]">Créditos</h3>
                            <button type="button" onClick={onClose} className="p-2 rounded-full text-[var(--color-text-muted)] hover:bg-[var(--color-danger-bg-hover)] hover:text-[var(--color-danger-text)] transition-colors"><svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>
                        <div className="p-8 text-center">
                            <div className="mb-6">
                                <div className="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-full flex items-center justify-center shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10 text-white" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" /></svg>
                                </div>
                                <h4 className="text-2xl font-bold text-[var(--color-text-primary)] mb-2">Jose Leonardo Lemos</h4>
                                <p className="text-sm text-[var(--color-text-muted)]">Desenvolvedor</p>
                            </div>
                            <div className="space-y-3 text-sm text-[var(--color-text-secondary)]">
                                <p>Ferramenta desenvolvida para facilitar a criação e correção de XMLs de Notas Fiscais de Importação.</p>
                                <p className="text-xs text-[var(--color-text-muted)] italic">Versão 1.0 - Janeiro 2026</p>
                            </div>
                            <div className="mt-6 pt-6 border-t border-[var(--color-border)]">
                                <p className="text-xs text-[var(--color-text-muted)] mb-3">Gostou da ferramenta?</p>
                                <button disabled className="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-amber-400 to-orange-500 text-white font-bold shadow-md opacity-50 cursor-not-allowed" title="Em breve!">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clipRule="evenodd" /></svg>
                                    Me Pague um Café (Em breve)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            );
        };

        const Totals = ({ icmsTot, onTotalChange }) => {
            return (
                <div className="space-y-6">
                    <div className="bg-blue-50/10 border border-blue-500/30 rounded-lg p-4 mb-4">
                        <p className="text-sm text-blue-300">
                            <strong>Sobre os Totais:</strong> Os valores são calculados automaticamente com base nos itens. Você pode editá-los manualmente se precisar ajustar valores quebrados para importação.
                        </p>
                    </div>

                    <h4 className="font-bold text-[var(--color-text-muted)] border-b border-[var(--color-border)] pb-2">Base Cálculo ICMS</h4>
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <Input label="Base Cálculo ICMS" type="number" step="0.01" value={icmsTot.vBC} onChange={e => onTotalChange('vBC', e.target.value)} helpText="Base de cálculo do ICMS" />
                        <Input label="Valor Total ICMS" type="number" step="0.01" value={icmsTot.vICMS} onChange={e => onTotalChange('vICMS', e.target.value)} helpText="Valor total do ICMS" />
                        <Input label="Base Calc. ST" type="number" step="0.01" value={icmsTot.vBCST} onChange={e => onTotalChange('vBCST', e.target.value)} helpText="Base de cálculo do ICMS ST" />
                        <Input label="Valor ICMS ST" type="number" step="0.01" value={icmsTot.vST} onChange={e => onTotalChange('vST', e.target.value)} helpText="Valor do ICMS ST" />
                    </div>

                    <h4 className="font-bold text-[var(--color-text-muted)] mt-5 border-b border-[var(--color-border)] pb-2">Valor Desc.</h4>
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <Input label="Valor Desc." type="number" step="0.01" value={icmsTot.vDesc} onChange={e => onTotalChange('vDesc', e.target.value)} helpText="Valor total de descontos" />
                        <Input label="Valor Seguro" type="number" step="0.01" value={icmsTot.vSeg} onChange={e => onTotalChange('vSeg', e.target.value)} helpText="Valor do seguro" />
                        <Input label="Valor II" type="number" step="0.01" value={icmsTot.vII} onChange={e => onTotalChange('vII', e.target.value)} helpText="Valor do Imposto de Importação" />
                        <Input label="Valor IPI" type="number" step="0.01" value={icmsTot.vIPI} onChange={e => onTotalChange('vIPI', e.target.value)} helpText="Valor total do IPI" />
                    </div>

                    <h4 className="font-bold text-[var(--color-text-muted)] mt-5 border-b border-[var(--color-border)] pb-2">Valor PIS</h4>
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <Input label="Valor PIS" type="number" step="0.01" value={icmsTot.vPIS} onChange={e => onTotalChange('vPIS', e.target.value)} helpText="Valor total do PIS" />
                        <Input label="Valor COFINS" type="number" step="0.01" value={icmsTot.vCOFINS} onChange={e => onTotalChange('vCOFINS', e.target.value)} helpText="Valor total do COFINS" />
                        <Input label="Valor Frete" type="number" step="0.01" value={icmsTot.vFrete} onChange={e => onTotalChange('vFrete', e.target.value)} helpText="Valor total do frete" />
                        <Input label="Outras Desp." type="number" step="0.01" value={icmsTot.vOutro} onChange={e => onTotalChange('vOutro', e.target.value)} helpText="Outras despesas acessórias" />
                    </div>

                    <h4 className="font-bold text-[var(--color-text-muted)] mt-5 border-b border-[var(--color-border)] pb-2">Valor Prod.</h4>
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <Input label="Valor Prod." type="number" step="0.01" value={icmsTot.vProd} onChange={e => onTotalChange('vProd', e.target.value)} helpText="Valor total dos produtos" />
                        <Input label="Desp. Adu." type="number" step="0.01" value={icmsTot.vDespAdu} onChange={e => onTotalChange('vDespAdu', e.target.value)} helpText="Despesas aduaneiras" />
                        <Input label="Valor TRIB" type="number" step="0.01" value={icmsTot.vTRIB} onChange={e => onTotalChange('vTRIB', e.target.value)} helpText="Valor total de tributos" />
                    </div>

                    <div className="mt-6 p-4 bg-gradient-to-r from-blue-500/10 to-purple-500/10 border-2 border-blue-500/50 rounded-xl">
                        <Input 
                            label="VALOR TOTAL DA NOTA" 
                            type="number" 
                            step="0.01" 
                            value={icmsTot.vNF} 
                            onChange={e => onTotalChange('vNF', e.target.value)} 
                            helpText="Valor total da Nota Fiscal"
                            className="font-bold text-lg"
                        />
                    </div>
                </div>
            );
        };


        // --- DANFE PREVIEW ---
        const formatDanfeCurrency = (value, precision = 2) => { const num = parseFloat(value); if (isNaN(num)) return '-'; return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: precision, maximumFractionDigits: precision }).format(num); };
        const formatDate = (dateString) => { if (!dateString) return ''; try { const date = new Date(dateString); if (isNaN(date.getTime())) return ''; return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`; } catch (e) { return ''; } };
        const formatAccessKey = (key) => { if (!key) return '-'; const numericKey = key.replace(/\D/g, ''); return numericKey.match(/.{1,4}/g)?.join(' ') || numericKey; };
        const Field = ({ label, children, small = true }) => (<div><span className={`block text-gray-500 ${small ? 'text-[7px] leading-tight' : 'text-[10px] leading-tight'} font-sans uppercase`}>{label}</span><span className={`block text-black ${small ? 'text-xs' : 'text-sm'} font-semibold break-words`}>{children || '-'}</span></div>);

        const DanfePreview = ({ nfe }) => {
            const { ide, emit, dest, det, total } = nfe.infNFe;
            return (
                <div className="bg-white text-black font-sans text-[8px] border border-black">
                    <div className="grid grid-cols-10 border-b border-black">
                        <div className="col-span-4 p-1 border-r border-black flex items-center justify-center"><div className="text-center"><h2 className="font-bold text-sm">{emit.xFant || emit.xNome}</h2><p className="text-[7px]">{emit.enderEmit.xLgr}, {emit.enderEmit.nro}</p><p className="text-[7px]">{emit.enderEmit.xBairro} - {emit.enderEmit.xMun} - {emit.enderEmit.UF}</p></div></div>
                        <div className="col-span-2 p-1 border-r border-black flex flex-col items-center justify-center"><h1 className="font-bold text-lg">DANFE</h1><p className="text-center text-[7px] leading-tight">Documento Auxiliar da Nota Fiscal Eletrônica</p><div className="text-left w-full mt-2"><p className="text-[7px]">0 - ENTRADA</p><p className="text-[7px]">1 - SAÍDA</p></div></div>
                        <div className="col-span-4 p-1"><div className="text-center"><p className="font-bold">NF-e</p><p className="text-lg font-bold">Nº {ide.nNF || '---'}</p><p className="font-bold">SÉRIE: {ide.serie || '---'}</p></div><div className="mt-1"><Field small label="CHAVE DE ACESSO"><p className="font-mono tracking-tight text-center text-[9px]">{formatAccessKey(nfe.infNFe.Id)}</p></Field></div></div>
                    </div>
                    <div className="grid grid-cols-10"><div className="col-span-6 p-1 border-r border-black"><Field small label="Natureza da Operação">{ide.natOp}</Field></div><div className="col-span-4 p-1"><Field small label="PROTOCOLO DE AUTORIZAÇÃO DE USO">-</Field></div></div>
                    <div className="grid grid-cols-10 border-t border-black"><div className="col-span-3 p-1 border-r border-black"><Field small label="INSCRIÇÃO ESTADUAL">{emit.IE}</Field></div><div className="col-span-3 p-1 border-r border-black"><Field small label="INSC. EST. DO SUBST. TRIBUT."></Field></div><div className="col-span-4 p-1"><Field small label="CNPJ">{emit.CNPJ}</Field></div></div>
                    <div className="text-center font-bold text-[7px] bg-gray-100 border-y border-black">DESTINATÁRIO / REMETENTE</div>
                    <div className="grid grid-cols-10"><div className="col-span-5 p-1 border-r border-black"><Field small label="NOME / RAZÃO SOCIAL">{dest.xNome}</Field></div><div className="col-span-3 p-1 border-r border-black"><Field small label="CNPJ / CPF / ID Estrangeiro">{dest.idEstrangeiro}</Field></div><div className="col-span-2 p-1"><Field small label="DATA DA EMISSÃO">{formatDate(ide.dhEmi)}</Field></div></div>
                    <div className="grid grid-cols-10 border-t border-black"><div className="col-span-5 p-1 border-r border-black"><Field small label="ENDEREÇO">{`${dest.enderDest.xLgr || ''}, ${dest.enderDest.nro || ''}`}</Field></div><div className="col-span-3 p-1 border-r border-black"><Field small label="BAIRRO / DISTRITO">{dest.enderDest.xBairro}</Field></div><div className="col-span-2 p-1"><Field small label="CEP">{dest.enderDest.CEP}</Field></div></div>
                    <div className="grid grid-cols-10 border-t border-black"><div className="col-span-4 p-1 border-r border-black"><Field small label="MUNICÍPIO">{dest.enderDest.xMun}</Field></div><div className="col-span-1 p-1 border-r border-black"><Field small label="UF">{dest.enderDest.UF}</Field></div><div className="col-span-3 p-1 border-r border-black"><Field small label="FONE / FAX">{dest.enderDest.fone}</Field></div><div className="col-span-2 p-1"><Field small label="DATA DA SAÍDA">{ide.dhSaiEnt ? formatDate(ide.dhSaiEnt) : ''}</Field></div></div>
                    <div className="text-center font-bold text-[7px] bg-gray-100 border-y border-black">CÁLCULO DO IMPOSTO</div>
                    <div className="grid grid-cols-6"><div className="p-1 border-r border-b border-black"><Field small label="BASE DE CÁLCULO DO ICMS">{formatDanfeCurrency(total.ICMSTot.vBC)}</Field></div><div className="p-1 border-r border-b border-black"><Field small label="VALOR DO ICMS">{formatDanfeCurrency(total.ICMSTot.vICMS)}</Field></div><div className="p-1 border-r border-b border-black"><Field small label="BASE DE CÁLC. ICMS S.T.">{formatDanfeCurrency(total.ICMSTot.vBCST)}</Field></div><div className="p-1 border-r border-b border-black"><Field small label="VALOR DO ICMS S.T.">{formatDanfeCurrency(total.ICMSTot.vST)}</Field></div><div className="p-1 border-r border-b border-black"><Field small label="VALOR TOTAL DOS PRODUTOS">{formatDanfeCurrency(total.ICMSTot.vProd)}</Field></div><div className="p-1 border-b border-black"><Field small label="VALOR DO FRETE">{formatDanfeCurrency(total.ICMSTot.vFrete)}</Field></div></div>
                    <div className="grid grid-cols-6"><div className="p-1 border-r border-black"><Field small label="VALOR DO SEGURO">{formatDanfeCurrency(total.ICMSTot.vSeg)}</Field></div><div className="p-1 border-r border-black"><Field small label="DESCONTO">{formatDanfeCurrency(total.ICMSTot.vDesc)}</Field></div><div className="p-1 border-r border-black"><Field small label="OUTRAS DESPESAS">{formatDanfeCurrency(total.ICMSTot.vOutro)}</Field></div><div className="p-1 border-r border-black"><Field small label="VALOR TOTAL DO IPI">{formatDanfeCurrency(total.ICMSTot.vIPI)}</Field></div><div className="p-1 border-r border-black"><Field small label="V. APROX. TRIBUTOS">{formatDanfeCurrency(total.ICMSTot.vTotTrib)}</Field></div><div className="p-1 bg-gray-100 font-bold"><Field small label="VALOR TOTAL DA NOTA">{formatDanfeCurrency(total.ICMSTot.vNF)}</Field></div></div>
                    <div className="text-center font-bold text-[7px] bg-gray-100 border-y border-black">DADOS DOS PRODUTOS / SERVIÇOS</div>
                    <div style={{ display: 'grid', gridTemplateColumns: '70px minmax(0, 1fr) 60px 35px 35px 35px 55px 55px 55px 55px 55px 55px 35px 35px' }}>
                        <div className="p-1 border-b border-r border-black font-bold text-[7px]">CÓD. PRODUTO</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">DESCRIÇÃO</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">NCM</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">CST</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">CFOP</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">UN.</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">QUANT.</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">V. UNIT</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">V. TOTAL</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">BC. ICMS</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">V. ICMS</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">V. IPI</div><div className="p-1 border-b border-r border-black font-bold text-[7px]">%ICMS</div><div className="p-1 border-b border-black font-bold text-[7px]">%IPI</div>
                        {det.map((item, i) => (<React.Fragment key={i}><div className="p-1 border-b border-r border-black break-all text-center">{item.prod.cProd}</div><div className="p-1 border-b border-r border-black text-left break-words">{item.prod.xProd}</div><div className="p-1 border-b border-r border-black break-all text-center">{item.prod.NCM}</div><div className="p-1 border-b border-r border-black break-all text-center">{item.imposto.ICMS.ICMS00.CST}</div><div className="p-1 border-b border-r border-black break-all text-center">{item.prod.CFOP}</div><div className="p-1 border-b border-r border-black break-all text-center">{item.prod.uCom}</div><div className="p-1 border-b border-r border-black break-all text-right">{formatDanfeCurrency(item.prod.qCom, 4)}</div><div className="p-1 border-b border-r border-black break-all text-right">{formatDanfeCurrency(item.prod.vUnCom, 6)}</div><div className="p-1 border-b border-r border-black break-all text-right">{formatDanfeCurrency(item.prod.vProd, 2)}</div><div className="p-1 border-b border-r border-black break-all text-right">{formatDanfeCurrency(item.imposto.ICMS.ICMS00.vBC, 2)}</div><div className="p-1 border-b border-r border-black break-all text-right">{formatDanfeCurrency(item.imposto.ICMS.ICMS00.vICMS, 2)}</div><div className="p-1 border-b border-r border-black break-all text-right">{formatDanfeCurrency(item.imposto.IPI.IPITrib.vIPI, 2)}</div><div className="p-1 border-b border-r border-black break-all text-right">{formatDanfeCurrency(item.imposto.ICMS.ICMS00.pICMS, 2)}</div><div className="p-1 border-b border-black break-all text-right">{formatDanfeCurrency(item.imposto.IPI.IPITrib.pIPI, 2)}</div></React.Fragment>))}
                    </div>
                    <div className="text-center font-bold text-[7px] bg-gray-100 border-y border-black">DADOS ADICIONAIS</div>
                    <div className="grid grid-cols-2"><div className="col-span-1 p-1 border-r border-black min-h-[50px]"><Field small label="INFORMAÇÕES COMPLEMENTARES">{nfe.infNFe.infAdic.infCpl}</Field></div><div className="col-span-1 p-1 min-h-[50px]"><Field small label="RESERVADO AO FISCO"></Field></div></div>
                </div>
            );
        };

        const Footer = () => (
            <footer className="bg-[var(--color-card-bg)] border-t border-[var(--color-border)] mt-12 py-6">
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-[var(--color-text-muted)]">
                    <p> 2026 - Gerador de XML NFe</p>
                </div>
            </footer>
        );

        const ItemRow = ({ item, index, onItemChange, onRemove, onDuplicate, onAdiChange, onAddAdi, onRemoveAdi, isOpen, onToggleOpen, onOpenTaxModal }) => {
            const [activeTab, setActiveTab] = React.useState('produto');
            const [activeTaxTab, setActiveTaxTab] = React.useState('icms');

            const TAX_TABS = [
                { id: 'icms', label: 'ICMS' },
                { id: 'ipi', label: 'IPI' },
                { id: 'pis_cofins', label: 'PIS / COFINS' },
                { id: 'ii', label: 'II (Importação)' },
                { id: 'ibscbs', label: 'IBS / CBS (Reforma)' }
            ];

            return (
                <div className="glass-panel rounded-xl overflow-hidden mb-4 shadow-sm hover:shadow-md transition-shadow">
                    <div className="flex justify-between items-center p-4 bg-[var(--color-header-bg)] border-b border-[var(--color-glass-border)] cursor-pointer hover:bg-opacity-80 transition-colors" onClick={() => onToggleOpen(index)}>
                        <div className="flex items-center gap-3 flex-1">
                            <svg className={`h-5 w-5 transform transition-transform text-[var(--color-text-secondary)] ${isOpen ? 'rotate-180' : ''}`} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" /></svg>
                            <div>
                                <h4 className="text-base font-bold text-[var(--color-text-primary)]">Item {item.nItem}: <span className="font-normal">{item.prod.xProd || "Novo Item"}</span></h4>
                                <p className="text-xs text-[var(--color-text-muted)] mt-0.5">
                                    Cod: {item.prod.cProd || '-'} | NCM: {item.prod.NCM || '-'} | Qtd: {item.prod.qCom} | Total: R$ {item.prod.vProd}
                                </p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2" onClick={e => e.stopPropagation()}>
                            <button type="button" onClick={() => onDuplicate(index)} title="Duplicar Item" className="p-2 text-[var(--color-text-muted)] hover:text-[var(--color-accent-fg)] hover:bg-[var(--color-accent-subtle-bg)] rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2-2H9a2 2 0 01-2-2V9z" /><path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h6a2 2 0 00-2-2H5z" /></svg></button>
                            <button type="button" onClick={() => onRemove(index)} title="Remover Item" className="p-2 text-[var(--color-text-muted)] hover:text-[var(--color-danger-text)] hover:bg-[var(--color-danger-bg-hover)] rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" /></svg></button>
                        </div>
                    </div>

                    {isOpen && (
                        <div className="flex flex-col">
                            {/* Item Tabs */}
                            <div className="flex border-b border-[var(--color-border)] bg-[var(--color-card-subtle-bg)]/30 backdrop-blur-sm">
                                <button type="button" onClick={() => setActiveTab('produto')} className={`px-6 py-3 text-sm font-bold border-b-2 transition-all ${activeTab === 'produto' ? 'border-[var(--color-accent)] text-[var(--color-accent)] bg-[var(--color-accent-subtle-bg)]/50' : 'border-transparent text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)]'}`}>Produto & Valores</button>
                                <button type="button" onClick={() => setActiveTab('tributos')} className={`px-6 py-3 text-sm font-bold border-b-2 transition-all ${activeTab === 'tributos' ? 'border-[var(--color-accent)] text-[var(--color-accent)] bg-[var(--color-accent-subtle-bg)]/50' : 'border-transparent text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)]'}`}>Tributação</button>
                                <button type="button" onClick={() => setActiveTab('importacao')} className={`px-6 py-3 text-sm font-bold border-b-2 transition-all ${activeTab === 'importacao' ? 'border-[var(--color-accent)] text-[var(--color-accent)] bg-[var(--color-accent-subtle-bg)]/50' : 'border-transparent text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)]'}`}>Importação (DI)</button>
                            </div>

                            <div className="p-6">
                                {/* PRODUTO TAB */}
                                {activeTab === 'produto' && (
                                    <div className="space-y-6 animate-fadeIn">
                                        <div className="grid grid-cols-1 md:grid-cols-6 gap-3 mb-2">
                                            <div className="md:col-span-1"><Input label="Código" value={item.prod.cProd} onChange={e => onItemChange(index, 'prod.cProd', e.target.value)} required helpText="Código do produto" /></div>
                                            <div className="md:col-span-3"><Input label="Descrição" value={item.prod.xProd} onChange={e => onItemChange(index, 'prod.xProd', e.target.value)} required helpText="Descrição detalhada do produto" /></div>
                                            <div className="md:col-span-1"><Input label="NCM" value={item.prod.NCM} onChange={e => onItemChange(index, 'prod.NCM', e.target.value)} required helpText="Código NCM (8 dígitos)" /></div>
                                            <div className="md:col-span-1"><Input label="CFOP" value={item.prod.CFOP} onChange={e => onItemChange(index, 'prod.CFOP', e.target.value)} required helpText="Código Fiscal de Operações" /></div>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-5 gap-3">
                                            <Input label="Unidade" value={item.prod.uCom} onChange={e => onItemChange(index, 'prod.uCom', e.target.value)} required helpText="Unidade comercial (UN, KG, etc.)" />
                                            <Input label="Quantidade" type="number" step="0.0001" value={item.prod.qCom} onChange={e => onItemChange(index, 'prod.qCom', e.target.value)} required helpText="Quantidade comercial" />
                                            <Input label="Valor Unit." type="number" step="0.0000000001" value={item.prod.vUnCom} onChange={e => onItemChange(index, 'prod.vUnCom', e.target.value)} required helpText="Valor unitário comercial" />
                                            <Input label="Valor Total" type="number" step="0.01" value={item.prod.vProd} onChange={e => onItemChange(index, 'prod.vProd', e.target.value)} required helpText="Valor total bruto do item" />
                                            <Input label="EAN/GTIN" value={item.prod.cEAN} onChange={e => onItemChange(index, 'prod.cEAN', e.target.value)} required helpText="Código de barras (GTIN)" />
                                        </div>
                                        <div className="mt-2 grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <Input label="Frete (R$)" type="number" step="0.01" value={item.prod.vFrete} onChange={e => onItemChange(index, 'prod.vFrete', e.target.value)} helpText="Valor do frete para este item" />
                                            <Input label="Seguro (R$)" type="number" step="0.01" value={item.prod.vSeg} onChange={e => onItemChange(index, 'prod.vSeg', e.target.value)} helpText="Valor do seguro para este item" />
                                            <Input label="Outras Desp. (R$)" type="number" step="0.01" value={item.prod.vOutro} onChange={e => onItemChange(index, 'prod.vOutro', e.target.value)} helpText="Outras despesas acessórias" />
                                        </div>
                                    </div>
                                )}

                                {/* TRIBUTOS TAB */}
                                {activeTab === 'tributos' && (
                                    <div className="flex flex-col md:flex-row gap-6 animate-fadeIn">
                                        {/* Side/Top Nav for Taxes */}
                                        <div className="w-full md:w-48 flex flex-row md:flex-col gap-1 border-b md:border-b-0 md:border-r border-[var(--color-border)] pr-0 md:pr-4 pb-4 md:pb-0 overflow-x-auto md:overflow-visible">
                                            {TAX_TABS.map(tab => (
                                                <button key={tab.id} type="button" onClick={() => setActiveTaxTab(tab.id)} className={`px-3 py-2 text-sm text-left rounded-md transition-colors whitespace-nowrap ${activeTaxTab === tab.id ? 'bg-[var(--color-accent-subtle-bg)] text-[var(--color-accent-text)] font-semibold' : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-card-subtle-bg)]'}`}>{tab.label}</button>
                                            ))}
                                        </div>

                                        {/* Tax Content Area */}
                                        <div className="flex-1">
                                            {activeTaxTab === 'icms' && (
                                                <div className="space-y-4">
                                                    <div className="grid grid-cols-1 md:grid-cols-6 gap-4 p-4 rounded-lg border border-[var(--color-border)] bg-[var(--color-card-subtle-bg)]">
                                                        <div className="md:col-span-1"><Input label="Origem" value={item.imposto.ICMS.ICMS00.orig} onChange={e => onItemChange(index, 'imposto.ICMS.ICMS00.orig', e.target.value)} helpText="Origem da mercadoria (0, 1, 2...)" /></div>
                                                        <div className="md:col-span-1"><Input label="CST" value={item.imposto.ICMS.ICMS00.CST} onChange={e => onItemChange(index, 'imposto.ICMS.ICMS00.CST', e.target.value)} helpText="Código da Situação Tributária" /></div>
                                                        <Input label="Mod. BC" value={item.imposto.ICMS.ICMS00.modBC} onChange={e => onItemChange(index, 'imposto.ICMS.ICMS00.modBC', e.target.value)} helpText="Modalidade de determinação da BC" />
                                                        <Input label="Base Calc." type="number" step="0.01" value={item.imposto.ICMS.ICMS00.vBC} onChange={e => onItemChange(index, 'imposto.ICMS.ICMS00.vBC', e.target.value)} helpText="Valor da Base de Cálculo do ICMS" />
                                                        <Input label="Alíquota (%)" type="number" step="0.01" value={item.imposto.ICMS.ICMS00.pICMS} onChange={e => onItemChange(index, 'imposto.ICMS.ICMS00.pICMS', e.target.value)} helpText="Alíquota do imposto" />
                                                        <Input label="Valor ICMS" type="number" step="0.01" value={item.imposto.ICMS.ICMS00.vICMS} readOnly className="font-bold" helpText="Valor calculado do ICMS" />
                                                    </div>
                                                </div>
                                            )}

                                            {activeTaxTab === 'ipi' && (
                                                <div className="space-y-4">
                                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <Input label="CST IPI" value={item.imposto.IPI.IPITrib.CST} onChange={e => onItemChange(index, 'imposto.IPI.IPITrib.CST', e.target.value)} helpText="CST do IPI" />
                                                        <Input label="Cód. Enquadramento" value={item.imposto.IPI.cEnq} onChange={e => onItemChange(index, 'imposto.IPI.cEnq', e.target.value)} helpText="Código de Enquadramento Legal" />
                                                    </div>
                                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-lg border border-[var(--color-border)] bg-[var(--color-card-subtle-bg)]">
                                                        <Input label="Base Calc. IPI" type="number" step="0.01" value={item.imposto.IPI.IPITrib.vBC} onChange={e => onItemChange(index, 'imposto.IPI.IPITrib.vBC', e.target.value)} helpText="Valor da Base de Cálculo do IPI" />
                                                        <Input label="Alíquota (%)" type="number" step="0.01" value={item.imposto.IPI.IPITrib.pIPI} onChange={e => onItemChange(index, 'imposto.IPI.IPITrib.pIPI', e.target.value)} helpText="Alíquota do IPI" />
                                                        <Input label="Valor IPI" type="number" step="0.01" value={item.imposto.IPI.IPITrib.vIPI} readOnly className="font-bold" helpText="Valor do IPI" />
                                                    </div>
                                                </div>
                                            )}

                                            {activeTaxTab === 'pis_cofins' && (
                                                <div className="space-y-6">
                                                    <div>
                                                        <h6 className="font-bold text-sm text-[var(--color-text-secondary)] mb-2">PIS</h6>
                                                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 p-3 border border-[var(--color-border)] rounded-md">
                                                            <Input label="CST PIS" value={item.imposto.PIS.PISAliq.CST} onChange={e => onItemChange(index, 'imposto.PIS.PISAliq.CST', e.target.value)} helpText="CST do PIS" />
                                                            <Input label="Base Calc." type="number" step="0.01" value={item.imposto.PIS.PISAliq.vBC} onChange={e => onItemChange(index, 'imposto.PIS.PISAliq.vBC', e.target.value)} helpText="Base de Cálculo do PIS" />
                                                            <Input label="Alíquota (%)" type="number" step="0.01" value={item.imposto.PIS.PISAliq.pPIS} onChange={e => onItemChange(index, 'imposto.PIS.PISAliq.pPIS', e.target.value)} helpText="Alíquota do PIS" />
                                                            <Input label="Valor PIS" type="number" step="0.01" value={item.imposto.PIS.PISAliq.vPIS} readOnly helpText="Valor do PIS" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 className="font-bold text-sm text-[var(--color-text-secondary)] mb-2">COFINS</h6>
                                                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 p-3 border border-[var(--color-border)] rounded-md">
                                                            <Input label="CST COFINS" value={item.imposto.COFINS.COFINSAliq.CST} onChange={e => onItemChange(index, 'imposto.COFINS.COFINSAliq.CST', e.target.value)} helpText="CST da COFINS" />
                                                            <Input label="Base Calc." type="number" step="0.01" value={item.imposto.COFINS.COFINSAliq.vBC} onChange={e => onItemChange(index, 'imposto.COFINS.COFINSAliq.vBC', e.target.value)} helpText="Base de Cálculo da COFINS" />
                                                            <Input label="Alíquota (%)" type="number" step="0.01" value={item.imposto.COFINS.COFINSAliq.pCOFINS} onChange={e => onItemChange(index, 'imposto.COFINS.COFINSAliq.pCOFINS', e.target.value)} helpText="Alíquota da COFINS" />
                                                            <Input label="Valor COFINS" type="number" step="0.01" value={item.imposto.COFINS.COFINSAliq.vCOFINS} readOnly helpText="Valor da COFINS" />
                                                        </div>
                                                    </div>
                                                </div>
                                            )}

                                            {activeTaxTab === 'ii' && (
                                                <div className="space-y-4">
                                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 rounded-lg border border-[var(--color-border)] bg-[var(--color-card-subtle-bg)]">
                                                        <Input label="Base Calc. II" type="number" step="0.01" value={item.imposto.II.vBC} onChange={e => onItemChange(index, 'imposto.II.vBC', e.target.value)} />
                                                        <Input label="Desp. Aduaneiras" type="number" step="0.01" value={item.imposto.II.vDespAdu} onChange={e => onItemChange(index, 'imposto.II.vDespAdu', e.target.value)} />
                                                        <Input label="Valor II" type="number" step="0.01" value={item.imposto.II.vII} onChange={e => onItemChange(index, 'imposto.II.vII', e.target.value)} />
                                                        <Input label="Valor IOF" type="number" step="0.01" value={item.imposto.II.vIOF} onChange={e => onItemChange(index, 'imposto.II.vIOF', e.target.value)} />
                                                    </div>
                                                </div>
                                            )}

                                            {activeTaxTab === 'ibscbs' && (
                                                <div className="space-y-4">
                                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                        <Input label="CST Reforma" value={item.imposto.IBSCBS.CST} onChange={e => onItemChange(index, 'imposto.IBSCBS.CST', e.target.value)} />
                                                        <div className="md:col-span-2">
                                                            <label className="block text-sm font-medium text-[var(--color-text-secondary)] mb-1.5">Classificação Tributária</label>
                                                            <div className="relative">
                                                                <input
                                                                    type="text"
                                                                    value={item.imposto.IBSCBS.cClassTrib}
                                                                    onChange={e => onItemChange(index, 'imposto.IBSCBS.cClassTrib', e.target.value)}
                                                                    className="block w-full border border-[var(--color-border)] rounded-lg shadow-sm focus:border-[var(--color-accent)] focus:ring-[var(--color-accent)] sm:text-sm bg-[var(--color-input-bg)] text-[var(--color-text-primary)] transition-colors p-2.5 pr-10"
                                                                />
                                                                <button
                                                                    type="button"
                                                                    onClick={() => onOpenTaxModal(index)}
                                                                    className="absolute right-2 top-1/2 -translate-y-1/2 text-[var(--color-text-muted)] hover:text-[var(--color-accent)] p-1 rounded-md transition-colors"
                                                                    title="Selecionar Classificação"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clipRule="evenodd" /></svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="p-4 rounded-lg border border-[var(--color-accent-subtle-bg)] bg-[var(--color-card-bg)] shadow-sm">
                                                        <div className="mb-4">
                                                            <Input label="Base de Cálculo Geral IBS/CBS" type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.vBC} onChange={e => onItemChange(index, 'imposto.IBSCBS.gIBSCBS.vBC', e.target.value)} />
                                                        </div>
                                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                            <div className="space-y-3">
                                                                <h6 className="font-bold text-xs uppercase text-[var(--color-text-muted)] border-b pb-1">IBS (Imposto Bens e Serv.)</h6>
                                                                <div className="grid grid-cols-2 gap-3">
                                                                    <Input label="Alíquota Est." type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.gIBSUF.pIBSUF} onChange={e => onItemChange(index, 'imposto.IBSCBS.gIBSCBS.gIBSUF.pIBSUF', e.target.value)} />
                                                                    <Input label="Valor Est." type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.gIBSUF.vIBSUF} readOnly />
                                                                    <Input label="Alíquota Mun." type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.gIBSMun.pIBSMun} onChange={e => onItemChange(index, 'imposto.IBSCBS.gIBSCBS.gIBSMun.pIBSMun', e.target.value)} />
                                                                    <Input label="Valor Mun." type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.gIBSMun.vIBSMun} readOnly />
                                                                </div>
                                                                <div className="pt-2">
                                                                    <Input label="Valor Total IBS" type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.vIBS} readOnly className="font-bold bg-[var(--color-accent-subtle-bg)] rounded-md" />
                                                                </div>
                                                            </div>
                                                            <div className="space-y-3">
                                                                <h6 className="font-bold text-xs uppercase text-[var(--color-text-muted)] border-b pb-1">CBS (Contrib. Bens e Serv.)</h6>
                                                                <div className="grid grid-cols-2 gap-3">
                                                                    <Input label="Alíquota CBS" type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.gCBS.pCBS} onChange={e => onItemChange(index, 'imposto.IBSCBS.gIBSCBS.gCBS.pCBS', e.target.value)} />
                                                                    <Input label="Valor CBS" type="number" step="0.01" value={item.imposto.IBSCBS.gIBSCBS.gCBS.vCBS} readOnly />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {/* IMPORTACAO TAB */}
                                {activeTab === 'importacao' && (
                                    <div className="animate-fadeIn">
                                        <Section title="Declaração de Importação (DI)" isCollapsible={false}>
                                            <div className="space-y-6">
                                                {item.prod.DI.map((di, diIndex) => (
                                                    <div key={diIndex} className="bg-[var(--color-card-subtle-bg)] p-4 rounded-lg border border-[var(--color-border)]">
                                                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                            <Input label="Número DI" value={di.nDI} onChange={e => console.log('Edit DI not impl on item level directly')} readOnly={true} title="Edite nas Info. da DI" />
                                                            <div className="md:col-span-3 flex items-center">
                                                                <span className="text-xs text-[var(--color-text-muted)]">Para editar dados da DI, vá para a aba "Informações da DI"</span>
                                                            </div>
                                                        </div>

                                                        <h6 className="font-bold text-sm text-[var(--color-text-primary)] mb-3">Adições</h6>
                                                        {di.adi.map((adi, adiIndex) => (
                                                            <div key={adiIndex} className="grid grid-cols-1 sm:grid-cols-5 gap-2 items-end mb-2">
                                                                <Input label="Nº Adição" value={adi.nAdicao} onChange={e => onAdiChange(index, diIndex, adiIndex, 'nAdicao', e.target.value)} />
                                                                <Input label="Seq." value={adi.nSeqAdic} onChange={e => onAdiChange(index, diIndex, adiIndex, 'nSeqAdic', e.target.value)} />
                                                                <Input label="Cod. Fab." value={adi.cFabricante} onChange={e => onAdiChange(index, diIndex, adiIndex, 'cFabricante', e.target.value)} />
                                                                <Input label="Nº Drawback" value={adi.nDraw || ''} onChange={e => onAdiChange(index, diIndex, adiIndex, 'nDraw', e.target.value)} />
                                                                <button type="button" onClick={() => onRemoveAdi(index, diIndex, adiIndex)} className="h-10 px-3 text-[var(--color-danger-text)] hover:bg-[var(--color-danger-bg-hover)] rounded-md transition-colors" title="Remover Adição">Remover</button>
                                                            </div>
                                                        ))}
                                                        <button type="button" onClick={() => onAddAdi(index, diIndex)} className="text-sm font-semibold text-[var(--color-accent-text)] hover:text-[var(--color-accent-fg-hover)] mt-2">+ Nova Adição</button>
                                                    </div>
                                                ))}
                                                {item.prod.DI.length === 0 && <p className="text-sm text-[var(--color-text-muted)]">Nenhuma DI vinculada a este item. (Adicione nas Informações da DI)</p>}
                                            </div>
                                        </Section>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            );
        };

        const TextArea = ({ label, id, className, value, ...props }) => {
            const areaId = id || `textarea-${label.replace(/\s+/g, '-')}`;
            const textAreaRef = React.useRef(null);
            React.useEffect(() => { if (textAreaRef.current) { textAreaRef.current.style.height = 'auto'; textAreaRef.current.style.height = `${textAreaRef.current.scrollHeight}px`; } }, [value]);
            return (
                <div className={className}>
                    <label htmlFor={areaId} className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm">{label}</label>
                    <textarea id={areaId} ref={textAreaRef} value={value} {...props} className="glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] placeholder-[var(--color-text-placeholder)] p-2.5" />
                </div>
            );
        };

        const calcularDigitoVerificador = (chave43) => {
            if (!chave43 || chave43.length !== 43) return '';
            let soma = 0, peso = 2;
            for (let i = 42; i >= 0; i--) { soma += parseInt(chave43.charAt(i), 10) * peso; peso++; if (peso > 9) peso = 2; }
            const resto = soma % 11;
            let dv = 11 - resto;
            if (dv >= 10) dv = 0;
            return String(dv);
        };

        const extractSummaryFromXml = (xmlDoc) => {
            const get = (element, tag) => element?.querySelector(tag)?.textContent?.trim() || '';
            const di = xmlDoc.querySelector('declaracaoImportacao');
            if (!di) return null;
            const adicoes = xmlDoc.querySelectorAll('adicao');
            const firstAdicao = adicoes.length > 0 ? adicoes[0] : null;
            const rawDate = get(di, 'dataRegistro');
            const formattedDate = rawDate.replace(/(\d{4})(\d{2})(\d{2})/, '$3/$2/$1');
            const rawValue = get(di, 'localDescargaTotalReais');
            const formattedValue = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseInt(rawValue, 10) / 100);
            return { numeroDI: get(di, 'numeroDI'), dataRegistro: formattedDate, importadorNome: get(di, 'importadorNome'), fornecedorNome: firstAdicao ? get(firstAdicao, 'fornecedorNome') : 'Não encontrado', totalAdicoes: get(di, 'totalAdicoes'), valorTotalReais: formattedValue };
        };

        // --- COMPONENT TAX CLASSIFICATION MODAL ---
        const TaxClassificationModal = ({ isOpen, onClose, onSelect }) => {
            const [searchTerm, setSearchTerm] = React.useState('');
            const [filteredItems, setFilteredItems] = React.useState([]);

            const allItems = React.useMemo(() => {
                if (typeof TAX_REFORM_DATA === 'undefined') return [];
                const list = [];
                Object.entries(TAX_REFORM_DATA).forEach(([cstKey, group]) => {
                    if (group.classificacoes) {
                        Object.values(group.classificacoes).forEach(cls => {
                            list.push({ ...cls, cstParent: group.codigo, cstDesc: group.descricao });
                        });
                    }
                });
                return list;
            }, []);

            React.useEffect(() => {
                if (!searchTerm) {
                    setFilteredItems(allItems);
                } else {
                    const lower = searchTerm.toLowerCase();
                    setFilteredItems(allItems.filter(item =>
                        item.codigo.toLowerCase().includes(lower) ||
                        item.descricao.toLowerCase().includes(lower)
                    ));
                }
            }, [searchTerm, allItems]);

            if (!isOpen) return null;

            return (
                <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn">
                    <div className="glass-panel rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col border border-[var(--color-glass-border)]">
                        <div className="p-4 border-b border-[var(--color-border)] flex justify-between items-center bg-[var(--color-card-header-bg)] rounded-t-xl">
                            <div>
                                <h3 className="font-bold text-lg text-[var(--color-text-primary)]">Selecionar Classificação Tributária</h3>
                                <p className="text-xs text-[var(--color-text-muted)]">Reforma Tributária (IBS/CBS)</p>
                            </div>
                            <button onClick={onClose} className="p-2 hover:bg-[var(--color-danger-bg-hover)] text-[var(--color-text-muted)] hover:text-[var(--color-danger-text)] rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div className="p-4 border-b border-[var(--color-border)] bg-[var(--color-card-subtle-bg)]/50">
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg className="h-5 w-5 text-[var(--color-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <input
                                    autoFocus
                                    type="text"
                                    className="glass-input block w-full pl-10 pr-3 py-2 rounded-md leading-5 placeholder-[var(--color-text-placeholder)] focus:outline-none sm:text-sm transition duration-150 ease-in-out"
                                    placeholder="Busque por código ou descrição da classificação..."
                                    value={searchTerm}
                                    onChange={e => setSearchTerm(e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="flex-1 overflow-y-auto p-0">
                            <table className="min-w-full divide-y divide-[var(--color-border)]">
                                <thead className="bg-[var(--color-card-header-bg)] sticky top-0 z-10 glass-header">
                                    <tr>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-bold text-[var(--color-text-muted)] uppercase tracking-wider">Código</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-bold text-[var(--color-text-muted)] uppercase tracking-wider w-1/2">Descrição</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-bold text-[var(--color-text-muted)] uppercase tracking-wider">CST Relacionado</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-bold text-[var(--color-text-muted)] uppercase tracking-wider">Redução %</th>
                                        <th scope="col" className="px-6 py-3 text-right text-xs font-bold text-[var(--color-text-muted)] uppercase tracking-wider">Ação</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-[var(--color-card-bg)] divide-y divide-[var(--color-border)]">
                                    {filteredItems.map((item) => (
                                        <tr key={item.codigo} className="hover:bg-[var(--color-card-subtle-bg)] transition-colors group">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--color-text-primary)] font-mono">{item.codigo}</td>
                                            <td className="px-6 py-4 text-sm text-[var(--color-text-secondary)]">{item.descricao}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-[var(--color-text-secondary)]">
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                                    {item.cstParent}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-[var(--color-text-secondary)]">
                                                <div className="flex flex-col gap-1">
                                                    <span className="text-xs">IBS: <span className={parseFloat(item.reducao_ibs) > 0 ? "text-green-600 font-bold" : ""}>{item.reducao_ibs}%</span></span>
                                                    <span className="text-xs">CBS: <span className={parseFloat(item.reducao_cbs) > 0 ? "text-green-600 font-bold" : ""}>{item.reducao_cbs}%</span></span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button onClick={() => onSelect(item)} className="text-white font-bold bg-gradient-to-r from-[var(--color-accent)] to-[var(--color-accent-fg)] hover:from-[var(--color-accent-fg)] hover:to-[var(--color-accent-fg-hover)] px-4 py-1.5 rounded-full shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">Selecionar</button>
                                            </td>
                                        </tr>
                                    ))}
                                    {filteredItems.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-12 text-center text-sm text-[var(--color-text-muted)]">
                                                Nenhuma classificação encontrada para "{searchTerm}"
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        <div className="p-4 border-t border-[var(--color-border)] bg-[var(--color-card-subtle-bg)] text-xs text-right text-[var(--color-text-muted)] rounded-b-xl">
                            Mostrando {filteredItems.length} de {allItems.length} classificações
                        </div>
                    </div>
                </div>
            );
        };

        // --- COMPONENT CONFIRM MODAL ---
        const ConfirmModal = ({ isOpen, title, message, onConfirm, onClose }) => {
            if (!isOpen) return null;
            return (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fadeIn">
                    <div className="bg-[var(--color-card-bg)] rounded-xl shadow-2xl max-w-md w-full border border-[var(--color-glass-border)] animate-scaleIn overflow-hidden">
                        <div className="p-6">
                            <h3 className="text-xl font-bold text-[var(--color-header-text)] mb-2">{title}</h3>
                            <p className="text-[var(--color-text-secondary)]">{message}</p>
                        </div>
                        <div className="px-6 py-4 bg-[var(--color-card-subtle-bg)] border-t border-[var(--color-border)] flex justify-end gap-3">
                            <button onClick={onClose} className="px-4 py-2 rounded-lg text-sm font-medium text-[var(--color-text-secondary)] hover:bg-[var(--color-card-bg)] hover:text-[var(--color-text-primary)] transition-colors">Cancelar</button>
                            <button onClick={() => { onConfirm(); onClose(); }} className="px-4 py-2 rounded-lg text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transform transition-transform hover:scale-105">Confirmar</button>
                        </div>
                    </div>
                </div>
            );
        };

        // --- COMPONENT HEADER (CORRIGIDO) ---
        const Header = ({ onDownload, onTogglePreview, onLoadExample, onClearForm, theme, onToggleTheme, onImportDI, onImportNFe, onToggleHelp, onToggleCredits, onApplyCorrections }) => {
            return (
                <header className="sticky top-0 z-30 w-full glass-header shadow-sm transition-all duration-300">
                    <div className="w-full px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <a href="index.php" title="Voltar ao Hub" className="mr-2 text-[var(--color-text-muted)] hover:text-[var(--color-accent)] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            </a>
                            <div className="bg-gradient-to-br from-blue-500 to-cyan-400 text-white p-2 rounded-lg shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-[var(--color-header-text)] tracking-tight">Gerador XML NFe</h1>
                                <p className="text-[10px] text-[var(--color-text-muted)] font-medium uppercase tracking-wider hidden sm:block">Importação & Correção</p>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            {/* Theme, Help & Credits */}
                            <div className="flex items-center bg-[var(--color-card-bg)] rounded-lg p-1 border border-[var(--color-border)] shadow-sm">
                                <button type="button" onClick={onToggleTheme} className="p-2 text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-card-subtle-bg)] rounded-md transition-colors" title={theme === 'classic' ? 'Ativar Modo Escuro' : 'Ativar Modo Claro'}>
                                    {theme === 'classic' ? (<svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>) : (<svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clipRule="evenodd" /></svg>)}
                                </button>
                                <div className="w-px h-4 bg-[var(--color-border)] mx-1"></div>
                                <button type="button" onClick={onToggleHelp} title="Ajuda (Atalhos e Dicas)" className="p-2 text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-card-subtle-bg)] rounded-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clipRule="evenodd" /></svg>
                                </button>
                                <div className="w-px h-4 bg-[var(--color-border)] mx-1"></div>
                                <button type="button" onClick={onToggleCredits} title="Créditos" className="p-2 text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-card-subtle-bg)] rounded-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" /></svg>
                                </button>
                            </div>

                            {/* Actions Group */}
                            <div className="hidden md:flex items-center bg-[var(--color-card-bg)] rounded-lg p-1 border border-[var(--color-border)] shadow-sm gap-1">
                                <label className="cursor-pointer inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-[var(--color-text-secondary)] hover:bg-[var(--color-card-subtle-bg)] hover:text-[var(--color-text-primary)] transition-colors" title="Importar XML NFe">
                                    <input type="file" accept=".xml" onChange={onImportNFe} className="hidden" />
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clipRule="evenodd" /></svg>
                                    <span className="hidden lg:inline">XML</span>
                                </label>
                                <div className="w-px h-4 bg-[var(--color-border)]"></div>
                                <label className="cursor-pointer inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-[var(--color-text-secondary)] hover:bg-[var(--color-card-subtle-bg)] hover:text-[var(--color-text-primary)] transition-colors" title="Importar Declaração de Importação">
                                    <input type="file" accept=".xml,.pdf" onChange={onImportDI} className="hidden" />
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M8.707 7.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414L11 7.586V3a1 1 0 10-2 0v4.586l-.293-.293z" /><path d="M3 5a2 2 0 012-2h1a1 1 0 010 2H5v7h2l1 2h4l1-2h2V5h-1a1 1 0 110-2h1a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" /></svg>
                                    <span className="hidden lg:inline">DI</span>
                                </label>
                            </div>
                            
                            {/* Data Controls */}
                            <div className="hidden md:flex items-center bg-[var(--color-card-bg)] rounded-lg p-1 border border-[var(--color-border)] shadow-sm gap-1">
                                <button type="button" onClick={onLoadExample} className="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-[var(--color-text-secondary)] hover:bg-[var(--color-card-subtle-bg)] hover:text-[var(--color-text-primary)] transition-colors" title="Carregar dados de exemplo">Exemplo</button>
                                <div className="w-px h-4 bg-[var(--color-border)]"></div>
                                <button type="button" onClick={onClearForm} className="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-[var(--color-danger-text)] hover:bg-[var(--color-card-subtle-bg)] transition-colors" title="Limpar formulário">Limpar</button>
                            </div>

                            {/* Main CTA */}
                            <div className="flex items-center gap-2">
                                <button type="button" onClick={onTogglePreview} className="hidden sm:inline-flex items-center gap-2 rounded-lg bg-[var(--color-card-bg)] px-3 py-2 text-sm font-semibold text-[var(--color-text-secondary)] shadow-sm ring-1 ring-inset ring-[var(--color-border)] hover:bg-[var(--color-card-subtle-bg)] transition-colors" title="Visualizar DANFE">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fillRule="evenodd" d="M.458 10C1.73 6.957 5.46 4 10 4s8.27 2.957 9.542 6c-1.272 3.043-5.042 6-9.542 6S1.73 13.043.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clipRule="evenodd" /></svg>
                                    <span className="hidden lg:inline">DANFE</span>
                                </button>

                                <button type="button" onClick={onApplyCorrections} className="hidden sm:inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 px-3 py-2 text-sm font-semibold text-white shadow-md hover:from-emerald-600 hover:to-teal-600 transition-all transform hover:scale-105" title="Corrigir problemas comuns no XML (Tags ADI, etc)">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" /></svg>
                                    <span className="hidden lg:inline">Corrigir XML</span>
                                </button>

                                <button type="submit" form="nfe-form" className="glass-button inline-flex items-center gap-2 rounded-lg px-6 py-2 text-sm font-bold text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-accent)] transform transition-transform hover:scale-105">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                    <span>Gerar XML</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </header>
            );
        };

        const App = () => {
            const [nfe, setNfe] = React.useState(getEmptyNfeData());
            const [diInfo, setDiInfo] = React.useState(getEmptyDiInfo());
            const [parsedDiInfoForImport, setParsedDiInfoForImport] = React.useState(null);
            const [isDanfeModalOpen, setIsDanfeModalOpen] = React.useState(false);
            const [activeMainTab, setActiveMainTab] = React.useState('cabeçalho');
            const [openItems, setOpenItems] = React.useState({});
            const [formKey, setFormKey] = React.useState(Date.now());
            const [theme, setTheme] = React.useState('plus'); // Default to Release Notes theme
            const [isDISummaryOpen, setIsDISummaryOpen] = React.useState(false);
            const [diSummaryData, setDISummaryData] = React.useState(null);
            const [parsedNfeForImport, setParsedNfeForImport] = React.useState(null);
            const [isHelpModalOpen, setIsHelpModalOpen] = React.useState(false);
            const [viewMode, setViewMode] = React.useState('landing');
            const [isCreditsModalOpen, setIsCreditsModalOpen] = React.useState(false); // 'landing', 'editor'

            // Confirm Modal State
            const [confirmModal, setConfirmModal] = React.useState({ isOpen: false, title: '', message: '', onConfirm: null });
            const closeConfirmModal = () => setConfirmModal(prev => ({ ...prev, isOpen: false }));

            // Tax Classification Modal State
            const [isTaxModalOpen, setIsTaxModalOpen] = React.useState(false);
            const [activeTaxItemIndex, setActiveTaxItemIndex] = React.useState(null);

            const openTaxModal = (index) => {
                setActiveTaxItemIndex(index);
                setIsTaxModalOpen(true);
            };

            const handleTaxSelect = (classification) => {
                if (activeTaxItemIndex === null) return;

                const index = activeTaxItemIndex;
                // Deep clone det array to modify
                const newDet = [...nfe.infNFe.det];

                // Ensure object structure exists
                if (!newDet[index].imposto) newDet[index].imposto = {};
                if (!newDet[index].imposto.IBSCBS) {
                    newDet[index].imposto.IBSCBS = { CST: "01", cClassTrib: "", gIBSCBS: { vBC: "0.00", gIBSUF: { pIBSUF: "0.00", vIBSUF: "0.00" }, gIBSMun: { pIBSMun: "0.00", vIBSMun: "0.00" }, gCBS: { pCBS: "0.00", vCBS: "0.00" }, vIBS: "0.00" } };
                }

                const taxObj = newDet[index].imposto.IBSCBS;
                taxObj.cClassTrib = classification.codigo;
                if (classification.cstParent) {
                    taxObj.CST = classification.cstParent;
                }

                // Apply Reduction Logic if 100%
                if (parseFloat(classification.reducao_ibs) === 100) {
                    taxObj.gIBSCBS.gIBSUF.pIBSUF = "0.00";
                    taxObj.gIBSCBS.gIBSMun.pIBSMun = "0.00";
                    taxObj.gIBSCBS.gCBS.pCBS = "0.00";
                }

                setNfe(prev => ({ ...prev, infNFe: { ...prev.infNFe, det: newDet } }));
                setIsTaxModalOpen(false);
            };

            const toggleTheme = React.useCallback(() => setTheme(prev => (prev === 'classic' ? 'plus' : 'classic')), []);
            const togglePreview = React.useCallback(() => setIsDanfeModalOpen(prev => !prev), []);
            const toggleHelpModal = React.useCallback(() => setIsHelpModalOpen(prev => !prev), []);

            React.useEffect(() => { if (window.pdfjsLib) window.pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js`; }, []);
            React.useEffect(() => { document.documentElement.className = theme; }, [theme]);
            // AUTOMATIC TOTALS CALCULATION DISABLED TO ALLOW MANUAL EDITING
            // User requirement: "se eu alterar algo no subtotal, ela deve aceitar"
            // The totals fields are now fully editable and will not be automatically recalculated
            /*
            React.useEffect(() => {
                const calculateTotals = () => {
                    const { det } = nfe.infNFe;
                    const totals = det.reduce((acc, item) => {
                        acc.vProd += parseFloat(item.prod.vProd) || 0; acc.vFrete += parseFloat(item.prod.vFrete) || 0;
                        acc.vSeg += parseFloat(item.prod.vSeg) || 0; acc.vOutro += parseFloat(item.prod.vOutro) || 0;
                        acc.vBC += parseFloat(item.imposto.ICMS.ICMS00.vBC) || 0; acc.vICMS += parseFloat(item.imposto.ICMS.ICMS00.vICMS) || 0;
                        acc.vII += parseFloat(item.imposto.II.vII) || 0; acc.vDespAdu += parseFloat(item.imposto.II.vDespAdu) || 0;
                        acc.vIPI += parseFloat(item.imposto.IPI.IPITrib.vIPI) || 0; acc.vPIS += parseFloat(item.imposto.PIS.PISAliq.vPIS) || 0;
                        acc.vCOFINS += parseFloat(item.imposto.COFINS.COFINSAliq.vCOFINS) || 0;

                        // IBS/CBS Totals
                        const ibsCbs = item.imposto.IBSCBS?.gIBSCBS || {};
                        acc.vBCIBSCBS += parseFloat(ibsCbs.vBC) || 0;
                        acc.vIBS += parseFloat(ibsCbs.vIBS) || 0;
                        acc.vCBS += parseFloat(ibsCbs.gCBS?.vCBS) || 0;

                        return acc;
                    }, { vProd: 0, vFrete: 0, vSeg: 0, vOutro: 0, vBC: 0, vICMS: 0, vII: 0, vDespAdu: 0, vIPI: 0, vPIS: 0, vCOFINS: 0, vBCIBSCBS: 0, vIBS: 0, vCBS: 0 });
                    const vST = parseFloat(nfe.infNFe.total.ICMSTot.vST) || 0;
                    const vDesc = parseFloat(nfe.infNFe.total.ICMSTot.vDesc) || 0;
                    // IBS and CBS are added "outside" like IPI in this model assumption
                    const vNF = totals.vProd + vST + totals.vFrete + totals.vSeg + totals.vOutro + totals.vIPI + totals.vII + totals.vIBS + totals.vCBS + totals.vPIS + totals.vCOFINS + totals.vICMS - vDesc;
                    const newTotal = {
                        ...nfe.infNFe.total.ICMSTot, vProd: totals.vProd.toFixed(2), vFrete: totals.vFrete.toFixed(2), vSeg: totals.vSeg.toFixed(2), vOutro: totals.vOutro.toFixed(2), vBC: totals.vBC.toFixed(2), vICMS: totals.vICMS.toFixed(2), vII: totals.vII.toFixed(2), vDespAdu: totals.vDespAdu.toFixed(2), vIPI: totals.vIPI.toFixed(2), vPIS: totals.vPIS.toFixed(2), vCOFINS: totals.vCOFINS.toFixed(2), vNF: vNF.toFixed(2),
                        parentIBSCBSTot: { vBCIBSCBS: totals.vBCIBSCBS.toFixed(2), gIBS: { vIBS: totals.vIBS.toFixed(2) }, gCBS: { vCBS: totals.vCBS.toFixed(2) } }
                    };
                    const newPag = { ...nfe.infNFe.pag, detPag: { ...nfe.infNFe.pag.detPag, vPag: vNF.toFixed(2) } };
                    // Deep compare to avoid loops (simplified check)
                    if (JSON.stringify(newTotal) !== JSON.stringify(nfe.infNFe.total.ICMSTot) || JSON.stringify(newPag) !== JSON.stringify(nfe.infNFe.pag)) {
                        setNfe(prev => ({ ...prev, infNFe: { ...prev.infNFe, total: { ICMSTot: newTotal, IBSCBSTot: newTotal.parentIBSCBSTot }, pag: newPag } }));
                    }
                };
                calculateTotals();
            }, [nfe.infNFe.det, nfe.infNFe.total.ICMSTot.vST, nfe.infNFe.total.ICMSTot.vDesc]);
            */

            React.useEffect(() => {
                const { ide, emit } = nfe.infNFe;
                if (!ide.cUF || !ide.dhEmi || !emit.CNPJ || !ide.mod || !ide.serie || !ide.nNF || !ide.tpEmis || !ide.cNF) {
                    if (nfe.infNFe.Id !== '') setNfe(prev => ({ ...prev, infNFe: { ...prev.infNFe, Id: '', ide: { ...prev.infNFe.ide, cDV: '' } } })); return;
                }
                try {
                    const dhEmiDate = new Date(ide.dhEmi);
                    if (isNaN(dhEmiDate.getTime())) return;
                    const chaveSemDV = [String(ide.cUF).padStart(2, '0'), String(dhEmiDate.getFullYear()).slice(2) + String(dhEmiDate.getMonth() + 1).padStart(2, '0'), String(emit.CNPJ).replace(/\D/g, '').padStart(14, '0'), String(ide.mod).padStart(2, '0'), String(ide.serie).padStart(3, '0'), String(ide.nNF).padStart(9, '0'), String(ide.tpEmis).padStart(1, '0'), String(ide.cNF).padStart(8, '0')].join('');
                    if (chaveSemDV.length === 43) {
                        const cDV = calcularDigitoVerificador(chaveSemDV);
                        const newId = `NFe${chaveSemDV}${cDV}`;
                        if (newId !== nfe.infNFe.Id) setNfe(prev => ({ ...prev, infNFe: { ...prev.infNFe, Id: newId, ide: { ...prev.infNFe.ide, cDV: cDV } } }));
                    }
                } catch (e) { }
            }, [nfe.infNFe.ide, nfe.infNFe.emit.CNPJ]);

            const startManualEntry = () => {
                setNfe(JSON.parse(JSON.stringify(INITIAL_NFE_DATA)));
                setDiInfo({ nDI: "1010", dDI: "2025-07-10", xLocDesemb: "RIO GRANDE", UFDesemb: "RS", dDesemb: "2025-07-10", cExportador: "TESTE", tpViaTransp: "1", tpIntermedio: "1", vAFRMM: "27.84", vSISCOMEX: "123.45" });
                const initialOpenState = {};
                INITIAL_NFE_DATA.infNFe.det.forEach((_, index) => { initialOpenState[index] = true; });
                setOpenItems(initialOpenState);
                setViewMode('editor');
            };

            const loadExampleData = () => {
                const isDirty = JSON.stringify(nfe) !== JSON.stringify(getEmptyNfeData()) || JSON.stringify(diInfo) !== JSON.stringify(getEmptyDiInfo());
                const performLoad = () => {
                    setNfe(JSON.parse(JSON.stringify(INITIAL_NFE_DATA)));
                    setDiInfo({ nDI: "1010", dDI: "2025-07-10", xLocDesemb: "RIO GRANDE", UFDesemb: "RS", dDesemb: "2025-07-10", cExportador: "TESTE", tpViaTransp: "1", tpIntermedio: "1", vAFRMM: "27.84", vSISCOMEX: "123.45" });
                    const initialOpenState = {};
                    INITIAL_NFE_DATA.infNFe.det.forEach((_, index) => { initialOpenState[index] = true; });
                    setOpenItems(initialOpenState);
                };

                if (isDirty) {
                    setConfirmModal({
                        isOpen: true,
                        title: 'Carregar Exemplo',
                        message: 'Isso irá substituir os dados atuais pelos de EXEMPLO. Deseja continuar?',
                        onConfirm: performLoad
                    });
                } else {
                    performLoad();
                }
            };
            const loadExampleEmitter = () => { setNfe(prev => { const newNfe = JSON.parse(JSON.stringify(prev)); newNfe.infNFe.emit = { ...newNfe.infNFe.emit, CNPJ: "01212344000127", xNome: "Intelidata Informatica LTDA", enderEmit: { ...newNfe.infNFe.emit.enderEmit, xLgr: "Rua 25 de Agosto", nro: "15", xBairro: "Jardim Maluche", cMun: "4202909", xMun: "Brusque", UF: "SC", CEP: "88354361", cPais: "1058", xPais: "BRASIL" } }; newNfe.infNFe.ide.cUF = stateCodes['SC']; return newNfe; }); };
            const clearForm = () => {
                setConfirmModal({
                    isOpen: true,
                    title: 'Limpar Formulário',
                    message: 'Tem certeza que deseja limpar todos os dados?',
                    onConfirm: () => {
                        setFormKey(Date.now());
                        setNfe(getEmptyNfeData());
                        setDiInfo(getEmptyDiInfo());
                        setOpenItems({});
                    }
                });
            };
            const clearSection = (sectionKey) => {
                if (sectionKey === 'diInfo') { setDiInfo(getEmptyDiInfo()); return; }
                setNfe(prev => { const newNfe = JSON.parse(JSON.stringify(prev)); if (sectionKey === 'det') { newNfe.infNFe.det = []; setOpenItems({}); } else if (sectionKey === 'ide') { newNfe.infNFe.ide = { ...getEmptyNfeData().infNFe.ide, cNF: generateCNF() }; } else { newNfe.infNFe[sectionKey] = getEmptyNfeData().infNFe[sectionKey]; } return newNfe; });
            };
            const handleInputChange = (section, field, value) => {
                setNfe(prev => { const newNfe = JSON.parse(JSON.stringify(prev)); const keys = field.split('.'); let current = newNfe.infNFe[section]; for (let i = 0; i < keys.length - 1; i++) current = current[keys[i]]; const finalKey = keys[keys.length - 1]; const fullPath = `${section}.${field}`; current[finalKey] = FIELD_MAX_LENGTHS[fullPath] ? value.slice(0, FIELD_MAX_LENGTHS[fullPath]) : value; if (field === 'dhEmi' && value) try { current[finalKey] = formatAsISOStringWithTimezone(new Date(value)); } catch (e) { } return newNfe; });
            };
            const handleDiInfoChange = (field, value) => setDiInfo(prev => ({ ...prev, [field]: value }));
            const handleUfChange = (uf) => setNfe(prev => ({ ...prev, infNFe: { ...prev.infNFe, ide: { ...prev.infNFe.ide, cUF: stateCodes[uf] || '' }, emit: { ...prev.infNFe.emit, enderEmit: { ...prev.infNFe.emit.enderEmit, UF: uf } } } }));
            const handleCountryChange = (code) => { const country = countryCodes.find(c => c.code === code); setNfe(prev => ({ ...prev, infNFe: { ...prev.infNFe, dest: { ...prev.infNFe.dest, enderDest: { ...prev.infNFe.dest.enderDest, cPais: code, xPais: country ? country.name : '' } } } })); };

            const handleItemChange = (index, field, value) => {
                setNfe(prev => {
                    const newNfe = JSON.parse(JSON.stringify(prev)); const item = newNfe.infNFe.det[index]; const keys = field.split('.'); let current = item; const section = keys[0]; for (let i = 0; i < keys.length - 1; i++) current = current[keys[i]]; const finalKey = keys[keys.length - 1]; const fullPath = section === 'prod' ? `prod.${keys.slice(1).join('.')}` : `other.${field}`; current[finalKey] = FIELD_MAX_LENGTHS[fullPath] ? value.slice(0, FIELD_MAX_LENGTHS[fullPath]) : value;
                    const q = parseFloat(item.prod.qCom) || 0, v = parseFloat(item.prod.vUnCom) || 0; item.prod.vProd = (q * v).toFixed(2);
                    const pICMS = parseFloat(item.imposto.ICMS.ICMS00.pICMS) || 0, vBCICMS = parseFloat(item.imposto.ICMS.ICMS00.vBC) || 0; item.imposto.ICMS.ICMS00.vICMS = (vBCICMS * pICMS / 100).toFixed(2);
                    const pIPI = parseFloat(item.imposto.IPI.IPITrib.pIPI) || 0, vBCIPI = parseFloat(item.imposto.IPI.IPITrib.vBC) || 0; item.imposto.IPI.IPITrib.vIPI = (vBCIPI * pIPI / 100).toFixed(2);
                    const pPIS = parseFloat(item.imposto.PIS.PISAliq.pPIS) || 0, vBCPIS = parseFloat(item.imposto.PIS.PISAliq.vBC) || 0; item.imposto.PIS.PISAliq.vPIS = (vBCPIS * pPIS / 100).toFixed(2);
                    const pCOFINS = parseFloat(item.imposto.COFINS.COFINSAliq.pCOFINS) || 0, vBCCOFINS = parseFloat(item.imposto.COFINS.COFINSAliq.vBC) || 0; item.imposto.COFINS.COFINSAliq.vCOFINS = (vBCCOFINS * pCOFINS / 100).toFixed(2);

                    // IBS/CBS Calcs
                    if (item.imposto.IBSCBS && item.imposto.IBSCBS.gIBSCBS) {
                        const vBCIBSCBS = parseFloat(item.imposto.IBSCBS.gIBSCBS.vBC) || 0;
                        // IBS UF
                        const pIBSUF = parseFloat(item.imposto.IBSCBS.gIBSCBS.gIBSUF.pIBSUF) || 0;
                        item.imposto.IBSCBS.gIBSCBS.gIBSUF.vIBSUF = (vBCIBSCBS * pIBSUF / 100).toFixed(2);
                        // IBS Mun
                        const pIBSMun = parseFloat(item.imposto.IBSCBS.gIBSCBS.gIBSMun.pIBSMun) || 0;
                        item.imposto.IBSCBS.gIBSCBS.gIBSMun.vIBSMun = (vBCIBSCBS * pIBSMun / 100).toFixed(2);
                        // Total IBS
                        item.imposto.IBSCBS.gIBSCBS.vIBS = (parseFloat(item.imposto.IBSCBS.gIBSCBS.gIBSUF.vIBSUF) + parseFloat(item.imposto.IBSCBS.gIBSCBS.gIBSMun.vIBSMun)).toFixed(2);
                        // CBS
                        const pCBS = parseFloat(item.imposto.IBSCBS.gIBSCBS.gCBS.pCBS) || 0;
                        item.imposto.IBSCBS.gIBSCBS.gCBS.vCBS = (vBCIBSCBS * pCBS / 100).toFixed(2);
                    }
                    return newNfe;
                });
            };

            const handleAdiChange = (itemIdx, diIdx, adiIdx, field, val) => setNfe(prev => { const d = JSON.parse(JSON.stringify(prev.infNFe.det)); d[itemIdx].prod.DI[diIdx].adi[adiIdx][field] = FIELD_MAX_LENGTHS[`adi.${field}`] ? val.slice(0, FIELD_MAX_LENGTHS[`adi.${field}`]) : val; return { ...prev, infNFe: { ...prev.infNFe, det: d } }; });
            const addItem = () => setNfe(prev => { const d = [...prev.infNFe.det], newItem = JSON.parse(JSON.stringify(INITIAL_NFE_DATA.infNFe.det[0])); newItem.nItem = d.length + 1; newItem.prod.cProd = ''; newItem.prod.xProd = ''; newItem.prod.DI = [{ adi: [{ nAdicao: "1", nSeqAdic: "1", cFabricante: "" }] }]; d.push(newItem); setOpenItems(p => ({ ...p, [d.length - 1]: true })); return { ...prev, infNFe: { ...prev.infNFe, det: d } }; });
            const duplicateItem = (idx) => setNfe(prev => { const d = [...prev.infNFe.det], item = JSON.parse(JSON.stringify(d[idx])); d.splice(idx + 1, 0, item); const renumbered = d.map((it, i) => ({ ...it, nItem: i + 1 })); const nOpen = {}; renumbered.forEach((_, i) => nOpen[i] = openItems[i] ?? true); setOpenItems(nOpen); return { ...prev, infNFe: { ...prev.infNFe, det: renumbered } }; });
            const removeItem = (idx) => setNfe(prev => { const d = prev.infNFe.det.filter((_, i) => i !== idx).map((it, i) => ({ ...it, nItem: i + 1 })); const nOpen = {}; Object.keys(openItems).forEach(k => { const kI = parseInt(k); if (kI < idx) nOpen[kI] = openItems[kI]; else if (kI > idx) nOpen[kI - 1] = openItems[kI]; }); setOpenItems(nOpen); return { ...prev, infNFe: { ...prev.infNFe, det: d } }; });
            const addAdi = (it, di) => setNfe(prev => { const d = JSON.parse(JSON.stringify(prev.infNFe.det)); d[it].prod.DI[di].adi.push({ nAdicao: String((d[it].prod.DI[di].adi || []).length + 1), nSeqAdic: '1', cFabricante: '' }); return { ...prev, infNFe: { ...prev.infNFe, det: d } }; });
            const removeAdi = (it, di, ad) => setNfe(prev => { const d = JSON.parse(JSON.stringify(prev.infNFe.det)); d[it].prod.DI[di].adi.splice(ad, 1); return { ...prev, infNFe: { ...prev.infNFe, det: d } }; });

            // Sistema de Correções de XML - Versionado
            const XML_CORRECTIONS_VERSION = '1.0.3';
            
            // Correção v1.0: Tags ADI
            const fixAdiTags = (nfeData) => {
                let globalSeq = 1; // Sequencial global único para nSeqAdic
                
                nfeData.infNFe.det.forEach((item) => {
                    item.prod.DI.forEach((di) => {
                        di.adi.forEach((adi, adiIndex) => {
                            // nAdicao: sempre "1" (primeira adição do item)
                            // Cada item tem sua própria adição numerada a partir de 1
                            adi.nAdicao = String(adiIndex + 1);
                            // nSeqAdic: sequencial único global (1, 2, 3, 4...)
                            adi.nSeqAdic = String(globalSeq++);
                        });
                    });
                });
                
                return nfeData;
            };
            
            // Função principal que aplica todas as correções
            const applyXmlCorrections = () => {
                setNfe(prev => {
                    let correctedNfe = JSON.parse(JSON.stringify(prev));
                    
                    // Aplicar correções em sequência
                    correctedNfe = fixAdiTags(correctedNfe);
                    // Futuras correções podem ser adicionadas aqui:
                    // correctedNfe = fixOtherIssue(correctedNfe);
                    
                    return correctedNfe;
                });
                
                alert(`✅ XML Corrigido com Sucesso!\n\nVersão: ${XML_CORRECTIONS_VERSION}\n\nCorreções aplicadas:\n• Tags ADI (nAdicao e nSeqAdic corretos)`);
            };

            const mapDiXmlToNfe = (xmlDoc) => {
                let nfeData = getEmptyNfeData(); let diInfoData = getEmptyDiInfo();
                const get = (e, t) => e?.querySelector(t)?.textContent?.trim() || '';
                const parseVal = (v, d = 2) => (parseInt(v, 10) || 0) / Math.pow(10, d);
                const di = xmlDoc.querySelector('declaracaoImportacao'); if (!di) throw new Error("Tag <declaracaoImportacao> não encontrada.");
                const diDateStr = get(di, 'dataRegistro'); if (diDateStr) { const y = diDateStr.substring(0, 4), m = diDateStr.substring(4, 6), d = diDateStr.substring(6, 8); nfeData.infNFe.ide.dhEmi = formatAsISOStringWithTimezone(new Date(`${y}-${m}-${d}T12:00:00`)); diInfoData.dDI = `${y}-${m}-${d}`; }
                nfeData.infNFe.ide.nNF = get(di, 'numeroDI').slice(0, 9); nfeData.infNFe.ide.natOp = "Importação";
                const imp = di.querySelector('importador');
                if (imp) { nfeData.infNFe.emit = { ...nfeData.infNFe.emit, CNPJ: get(imp, 'numero'), xNome: get(imp, 'nome'), enderEmit: { xLgr: get(imp, 'importadorEnderecoLogradouro'), nro: get(imp, 'importadorEnderecoNumero') || 'S/N', xBairro: get(imp, 'importadorEnderecoBairro'), xMun: get(imp, 'importadorEnderecoMunicipio'), UF: get(imp, 'importadorEnderecoUf'), CEP: get(imp, 'importadorEnderecoCep'), cPais: '1058', xPais: 'BRASIL' } }; nfeData.infNFe.ide.cUF = stateCodes[get(imp, 'importadorEnderecoUf')] || ''; }
                nfeData.infNFe.infAdic.infCpl = get(di, 'informacaoComplementar');
                const ads = xmlDoc.querySelectorAll('adicao'); if (ads.length === 0) throw new Error("Nenhuma <adicao> encontrada.");
                const fAd = ads[0]; if (fAd) { nfeData.infNFe.dest.xNome = get(fAd, 'fornecedorNome'); nfeData.infNFe.dest.enderDest = { ...nfeData.infNFe.dest.enderDest, xLgr: get(fAd, 'fornecedorLogradouro') || get(fAd, 'fornecedorCidade'), nro: get(fAd, 'fornecedorNumero') || 'S/N', xBairro: get(fAd, 'fornecedorCidade'), xMun: get(fAd, 'fornecedorCidade'), UF: "EX", xPais: get(fAd, 'paisOrigemMercadoriaNome'), cPais: get(fAd, 'paisOrigemMercadoriaCodigo') }; diInfoData.cExportador = get(fAd, 'fornecedorNome'); }
                diInfoData = { ...diInfoData, nDI: get(di, 'numeroDI'), xLocDesemb: get(di, 'cargaUrfEntradaNome'), UFDesemb: get(di, 'urfDespachoNome').match(/(\w{2})$/)?.[1] || nfeData.infNFe.emit.enderEmit.UF, dDesemb: get(di, 'cargaDataChegada')?.replace(/(\d{4})(\d{2})(\d{2})/, '$1-$2-$3'), tpViaTransp: get(di, 'viaTransporteCodigo'), vSISCOMEX: get(di, 'informacaoComplementar').match(/TAXA\s+SISCOMEX\s*:\s*([\d.,]+)/i)?.[1].replace(/\./g, '').replace(',', '.') || '0.00', vAFRMM: get(di, 'informacaoComplementar').match(/A\.F\.R\.M\.M\s*:\s*R\$\s*([\d.,]+)/i)?.[1].replace(/\./g, '').replace(',', '.') || '0.00' };
                let ctr = 1;
                ads.forEach(ad => {
                    const mercs = Array.from(ad.querySelectorAll('mercadoria')); if (mercs.length === 0) return;
                    let totVal = mercs.reduce((s, m) => s + (parseVal(get(m, 'quantidade'), 4) * parseVal(get(m, 'valorUnitario'), 8)), 0) || 1;
                    mercs.forEach((m, sa) => {
                        let newItem = JSON.parse(JSON.stringify(INITIAL_NFE_DATA.infNFe.det[0])); newItem.nItem = ctr++;
                        const q = parseVal(get(m, 'quantidade'), 4), v = parseVal(get(m, 'valorUnitario'), 8), vp = q * v;
                        newItem.prod = { ...newItem.prod, cProd: `ITEM-${ctr - 1}`, xProd: get(m, 'descricaoMercadoria').slice(0, 120), NCM: get(ad, 'dadosMercadoriaCodigoNcm'), CFOP: "3101", uCom: get(m, 'unidadeMedida'), qCom: q.toFixed(4), vUnCom: v.toFixed(6), vProd: vp.toFixed(2), DI: [] };
                        const prop = vp / totVal;
                        const vii = parseVal(get(ad, 'iiAliquotaValorRecolher'), 2) * prop, vipi = parseVal(get(ad, 'ipiAliquotaValorRecolher'), 2) * prop, vpis = parseVal(get(ad, 'pisPasepAliquotaValorRecolher'), 2) * prop, vcof = parseVal(get(ad, 'cofinsAliquotaValorRecolher'), 2) * prop;
                        newItem.imposto.II = { vBC: vp.toFixed(2), vII: vii.toFixed(2), vDespAdu: '0.00', vIOF: '0.00' };
                        newItem.imposto.IPI.IPITrib = { CST: "01", pIPI: parseVal(get(ad, 'ipiAliquotaAdValorem'), 2).toFixed(2), vBC: vp.toFixed(2), vIPI: vipi.toFixed(2) };
                        newItem.imposto.PIS.PISAliq = { CST: "01", pPIS: parseVal(get(ad, 'pisPasepAliquotaAdValorem'), 2).toFixed(2), vBC: vp.toFixed(2), vPIS: vpis.toFixed(2) };
                        newItem.imposto.COFINS.COFINSAliq = { CST: "01", pCOFINS: parseVal(get(ad, 'cofinsAliquotaAdValorem'), 2).toFixed(2), vBC: vp.toFixed(2), vCOFINS: vcof.toFixed(2) };
                        newItem.imposto.ICMS.ICMS00 = { orig: "1", CST: "00", modBC: "3", vBC: (vp + vii + vipi + vpis + vcof).toFixed(2), pICMS: "17.00", vICMS: "0.00" };
                        newItem.prod.DI.push({ adi: [{ nAdicao: get(ad, 'numeroAdicao'), nSeqAdic: String(sa + 1), cFabricante: get(ad, 'fornecedorNome'), nDraw: '' }] });
                        newItem.infAdProd = `Referente a DI: ${diInfoData.nDI}, Adição: ${get(ad, 'numeroAdicao')}`;
                        nfeData.infNFe.det.push(newItem);
                    });
                });
                return { nfeData, diInfoData };
            };

            const mapDiPdfToNfe = async (file) => {
                if (!window.pdfjsLib) throw new Error("PDF.js falhou.");
                return new Promise((res, rej) => {
                    const fr = new FileReader();
                    fr.onload = async function () {
                        try {
                            const pdf = await window.pdfjsLib.getDocument(new Uint8Array(this.result)).promise;
                            let txt = ''; for (let i = 1; i <= pdf.numPages; i++) txt += (await (await pdf.getPage(i)).getTextContent()).items.map(s => s.str).join(' ') + '\n';
                            const ext = r => txt.match(r)?.[1]?.trim().replace(/\s+/g, ' ') || '';
                            const nfe = getEmptyNfeData(), di = getEmptyDiInfo();
                            nfe.infNFe.ide.natOp = "Importação"; nfe.infNFe.emit.CNPJ = ext(/Importador CNPJ:\s*([\d.\/-]+)/).replace(/\D/g, ''); nfe.infNFe.emit.xNome = ext(/Importador CNPJ:[\s\d.\/-]+(.*)/).split(/Adquirente|Endereço/)[0].trim(); nfe.infNFe.dest.xNome = ext(/Exportador Nome:\s*(.*)/);
                            di.nDI = ext(/Declaração:\s*(\S+)/); nfe.infNFe.ide.nNF = di.nDI.split('/')[1]?.split('-')[0] || ''; di.dDI = ext(/Data do Registro:\s*(\d{2}\/\d{2}\/\d{4})/).split('/').reverse().join('-');
                            if (di.dDI) nfe.infNFe.ide.dhEmi = formatAsISOStringWithTimezone(new Date(`${di.dDI}T12:00:00`));
                            const rgx = /Qtde:\s*([\d.,]+)\s*[A-Z]+\s*VUCV:\s*([\d.,]+)\s*DOLAR DOS EUA\s*([\s\S]+?)(?=Qtde:|Imposto de Importação|Dados Gerais Pis)/g;
                            let m, ctr = 1;
                            while ((m = rgx.exec(txt))) {
                                let it = JSON.parse(JSON.stringify(INITIAL_NFE_DATA.infNFe.det[0])); it.nItem = ctr++; const q = parseFloat(m[1].replace('.', '').replace(',', '.')), v = parseFloat(m[2].replace('.', '').replace(',', '.'));
                                it.prod = { ...it.prod, qCom: q.toFixed(4), vUnCom: v.toFixed(6), vProd: (q * v).toFixed(2), xProd: m[3].trim(), cProd: `PROD-${ctr - 1}`, NCM: ext(/NCM\s+([\d.]+)/).replace(/\./g, ''), DI: [{ adi: [{ nAdicao: "1", nSeqAdic: String(ctr - 1), cFabricante: ext(/Fabricante\/Produtor Nome:\s*(.*)/) }] }] };
                                nfe.infNFe.det.push(it);
                            }
                            res({ nfeData: nfe, diInfoData: di });
                        } catch (e) { rej(e); }
                    };
                    fr.readAsArrayBuffer(file);
                });
            };

            const handleDIImport = async (e) => {
                const f = e.target.files[0]; if (!f) return;
                try {
                    if (f.name.toLowerCase().endsWith('.xml')) {
                        const xml = new DOMParser().parseFromString(await f.text(), "application/xml");
                        if (xml.querySelector("parsererror")) throw new Error("XML inválido.");
                        const sum = extractSummaryFromXml(xml); const { nfeData, diInfoData } = mapDiXmlToNfe(xml);
                        setDISummaryData(sum); setParsedNfeForImport(nfeData); setParsedDiInfoForImport(diInfoData); setIsDISummaryOpen(true);
                    } else if (f.name.toLowerCase().endsWith('.pdf')) {
                        const { nfeData, diInfoData } = await mapDiPdfToNfe(f); setNfe(nfeData); setDiInfo(diInfoData);
                        const nOpen = {}; nfeData.infNFe.det.forEach((_, i) => nOpen[i] = true); setOpenItems(nOpen); alert("PDF Importado!");
                    } else throw new Error("Use XML ou PDF.");
                } catch (err) { console.error(err); alert("Erro: " + err.message); } finally { e.target.value = null; }
            };

            const handleNFeImport = async (e) => {
                const f = e.target.files[0]; if (!f) return;
                try {
                    if (!f.name.toLowerCase().endsWith('.xml')) throw new Error("Use arquivo .xml");
                    const xml = new DOMParser().parseFromString(await f.text(), "application/xml");
                    if (xml.querySelector("parsererror")) throw new Error("XML inválido.");
                    const { nfeData, diInfoData } = mapNfeXmlToState(xml); setNfe(nfeData); setDiInfo(diInfoData);
                    const nOpen = {}; nfeData.infNFe.det.forEach((_, i) => nOpen[i] = true); setOpenItems(nOpen); alert("NFe Importada!");
                } catch (err) { console.error(err); alert("Erro: " + err.message); } finally { e.target.value = null; }
            };

            const handleConfirmImport = () => { if (parsedNfeForImport) { setNfe(parsedNfeForImport); if (parsedDiInfoForImport) setDiInfo(parsedDiInfoForImport); const nOpen = {}; parsedNfeForImport.infNFe.det.forEach((_, i) => nOpen[i] = true); setOpenItems(nOpen); alert("Importado!"); } setIsDISummaryOpen(false); };

            const downloadXml = () => {
                if (nfe.infNFe.det.length === 0) { alert("Adicione itens."); return; }
                const finalNfe = JSON.parse(JSON.stringify(nfe));
                const totProd = finalNfe.infNFe.det.reduce((s, i) => s + (parseFloat(i.prod.vProd) || 0), 0) || 1;
                finalNfe.infNFe.det.forEach(item => {
                    if (!item.prod.DI || !item.prod.DI.length) item.prod.DI = [{ adi: [] }];
                    const factor = (parseFloat(item.prod.vProd) || 0) / totProd;
                    item.prod.DI.forEach(di => { Object.assign(di, diInfo); di.vAFRMM = ((parseFloat(diInfo.vAFRMM) || 0) * factor).toFixed(2); di.vSISCOMEX = ((parseFloat(diInfo.vSISCOMEX) || 0) * factor).toFixed(2); });
                });
                const blob = new Blob([generateNFeXml(finalNfe)], { type: 'application/xml' });
                const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `NFe-${nfe.infNFe.ide.nNF || 'num'}.xml`;
                document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
            };

            const TABS = ['cabeçalho', 'emitente', 'destinatário', 'declaração de importação', 'itens', 'totais', 'info. adicionais'];

            if (viewMode === 'landing') {
                return (
                    <div className="min-h-screen flex flex-col justify-center items-center p-4">
                        <div className="max-w-2xl w-full text-center space-y-8 animate-fadeIn">
                            <div>
                                <h1 className="text-4xl font-bold text-[var(--color-header-text)] mb-2">Gerador de XML NFe <span className="text-lg font-normal text-[var(--color-text-muted)]">v1.0.3</span></h1>
                                <p className="text-[var(--color-text-secondary)]">IMPORTAÇÃO E CORREÇÃO</p>
                            </div>
                            
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div className="card-hover glass-panel p-6 rounded-xl flex flex-col items-center cursor-pointer transition-all hover:scale-105" onClick={() => document.getElementById('landing-xml-upload').click()}>
                                    <div className="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </div>
                                    <h3 className="font-bold text-lg mb-2">Corrigir XML</h3>
                                    <p className="text-xs text-[var(--color-text-muted)]">Importe um XML existente para validar e corrigir erros.</p>
                                    <input type="file" id="landing-xml-upload" accept=".xml" className="hidden" onChange={(e) => { handleNFeImport(e); setViewMode('editor'); }} />
                                </div>

                                <div className="card-hover glass-panel p-6 rounded-xl flex flex-col items-center cursor-pointer transition-all hover:scale-105" onClick={() => document.getElementById('landing-di-upload').click()}>
                                    <div className="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                    </div>
                                    <h3 className="font-bold text-lg mb-2">Importar DI</h3>
                                    <p className="text-xs text-[var(--color-text-muted)]">Gere a nota importando XML ou PDF da Declaração de Importação.</p>
                                    <input type="file" id="landing-di-upload" accept=".xml,.pdf" className="hidden" onChange={(e) => { handleDIImport(e); setViewMode('editor'); }} />
                                </div>

                                <div className="card-hover glass-panel p-6 rounded-xl flex flex-col items-center cursor-pointer transition-all hover:scale-105" onClick={startManualEntry}>
                                    <div className="w-16 h-16 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" /></svg>
                                    </div>
                                    <h3 className="font-bold text-lg mb-2">Digitar Manual</h3>
                                    <p className="text-xs text-[var(--color-text-muted)]">Preencha os campos manualmente a partir de uma DI impressa.</p>
                                </div>
                            </div>

                            <button onClick={toggleTheme} className="text-sm text-[var(--color-text-muted)] hover:underline mt-8">
                                Alternar Tema
                            </button>
                        </div>
                    </div>
                );
            }

            return (
                <div className="min-h-screen flex flex-col">
                    <Header onDownload={downloadXml} onTogglePreview={togglePreview} onLoadExample={loadExampleData} onClearForm={clearForm} theme={theme} onToggleTheme={toggleTheme} onImportDI={handleDIImport} onImportNFe={handleNFeImport} onToggleHelp={toggleHelpModal} onToggleCredits={() => setIsCreditsModalOpen(true)} onApplyCorrections={applyXmlCorrections} />
                    <DanfeModal isOpen={isDanfeModalOpen} onClose={togglePreview} nfe={nfe} />
                    <TaxClassificationModal isOpen={isTaxModalOpen} onClose={() => setIsTaxModalOpen(false)} onSelect={handleTaxSelect} />
                    <DISummaryModal summary={diSummaryData} isOpen={isDISummaryOpen} onClose={() => setIsDISummaryOpen(false)} onConfirm={handleConfirmImport} />
                    <HelpModal isOpen={isHelpModalOpen} onClose={toggleHelpModal} />
                    <ConfirmModal isOpen={confirmModal.isOpen} title={confirmModal.title} message={confirmModal.message} onConfirm={confirmModal.onConfirm} onClose={closeConfirmModal} />
                    <CreditsModal isOpen={isCreditsModalOpen} onClose={() => setIsCreditsModalOpen(false)} />
                    <main className="flex-grow p-4 sm:p-6 lg:p-8" key={formKey}>
                        <div className="w-full">
                            <form id="nfe-form" onSubmit={(e) => { e.preventDefault(); downloadXml(); }}>
                                <div className="border-b border-[var(--color-border)] mb-6">
                                    <nav className="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto" aria-label="Tabs">
                                        {TABS.map(tab => (
                                            <button key={tab} type="button" onClick={() => setActiveMainTab(tab)} className={`${activeMainTab === tab ? 'border-[var(--color-accent-text)] text-[var(--color-accent-text)] bg-[var(--color-accent-subtle-bg)] rounded-t-lg' : 'border-transparent text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] hover:border-[var(--color-border)]'} whitespace-nowrap py-3 px-4 border-b-2 font-bold text-sm capitalize transition-all duration-200`}>{tab.replace('info. adicionais', 'info. add')}</button>
                                        ))}
                                    </nav>
                                </div>
                                <div className="space-y-6">
                                    {activeMainTab === 'cabeçalho' && (
                                        <Section title="Cabeçalho da Nota Fiscal" onClear={() => clearSection('ide')} isCollapsible={false}>
                                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                                <div><label className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm">Tipo de Operação</label><select value={nfe.infNFe.ide.tpNF} onChange={e => handleInputChange('ide', 'tpNF', e.target.value)} className="glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] p-2.5"><option value="0">0 - Entrada</option><option value="1">1 - Saída</option></select></div>
                                                <Input label="Nº da Nota" value={nfe.infNFe.ide.nNF} onChange={e => handleInputChange('ide', 'nNF', e.target.value)} required helpText="Número sequencial da Nota Fiscal" />
                                                <Input label="Série" value={nfe.infNFe.ide.serie} onChange={e => handleInputChange('ide', 'serie', e.target.value)} required helpText="Série da Nota Fiscal (ex: 1)" />
                                                <Input label="Data de Emissão" type="datetime-local" value={nfe.infNFe.ide.dhEmi ? nfe.infNFe.ide.dhEmi.substring(0, 16) : ''} onChange={e => handleInputChange('ide', 'dhEmi', e.target.value)} required helpText="Data e hora da emissão da nota" />
                                                <Input label="Natureza da Operação" value={nfe.infNFe.ide.natOp} onChange={e => handleInputChange('ide', 'natOp', e.target.value)} required helpText="Descrição da operação (ex: Importação)" />
                                            </div>
                                        </Section>
                                    )}
                                    {activeMainTab === 'emitente' && (
                                        <Section title="Emitente (Quem emite a nota)" onClear={() => clearSection('emit')} isCollapsible={false} headerContent={(<button type="button" onClick={loadExampleEmitter} className="ml-4 px-3 py-1 text-xs font-bold text-[var(--color-accent-text)] border border-[var(--color-accent-text)] rounded-full hover:bg-[var(--color-accent-subtle-bg)] transition-colors shadow-sm">Exemplo</button>)}>
                                            <div className="bg-blue-50/10 border border-blue-500/30 rounded-lg p-4 mb-4">
                                                <p className="text-sm text-blue-300">
                                                    <strong>Sobre o Emitente:</strong> É a empresa ou pessoa que está gerando e enviando a nota fiscal. Na importação própria, é a sua empresa.
                                                </p>
                                            </div>
                                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                                <Input label="CNPJ" value={nfe.infNFe.emit.CNPJ} onChange={e => handleInputChange('emit', 'CNPJ', e.target.value)} required helpText="CNPJ da empresa emitente (apenas números)" maxLength={14} />
                                                <div className="sm:col-span-2"><Input label="Razão Social" value={nfe.infNFe.emit.xNome} onChange={e => handleInputChange('emit', 'xNome', e.target.value)} required helpText="Nome oficial da empresa" /></div>
                                                <Input label="Inscrição Estadual" value={nfe.infNFe.emit.IE} onChange={e => handleInputChange('emit', 'IE', e.target.value)} required helpText="IE na SEFAZ do estado" />
                                                <div><label className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm">Regime Tributário</label><select value={nfe.infNFe.emit.CRT} onChange={e => handleInputChange('emit', 'CRT', e.target.value)} className="glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] p-2.5"><option value="1">1 - Simples Nacional</option><option value="2">2 - Simples Nacional (excesso)</option><option value="3">3 - Regime Normal</option></select></div>
                                            </div>
                                            <h4 className="font-bold text-[var(--color-text-muted)] mt-5 mb-3 border-b border-[var(--color-border)] pb-1">Endereço do Emitente</h4>
                                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                <Input label="Logradouro" value={nfe.infNFe.emit.enderEmit.xLgr} onChange={e => handleInputChange('emit', 'enderEmit.xLgr', e.target.value)} required helpText="Rua, Av, etc." />
                                                <Input label="Número" value={nfe.infNFe.emit.enderEmit.nro} onChange={e => handleInputChange('emit', 'enderEmit.nro', e.target.value)} required helpText="Número do imóvel" />
                                                <Input label="Bairro" value={nfe.infNFe.emit.enderEmit.xBairro} onChange={e => handleInputChange('emit', 'enderEmit.xBairro', e.target.value)} required />
                                                <Input label="Município" value={nfe.infNFe.emit.enderEmit.xMun} onChange={e => handleInputChange('emit', 'enderEmit.xMun', e.target.value)} required />
                                                <Input label="Cód. Mun (IBGE)" value={nfe.infNFe.emit.enderEmit.cMun} onChange={e => handleInputChange('emit', 'enderEmit.cMun', e.target.value)} required helpText="Código IBGE da cidade" />
                                                <div><label className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm">UF</label><select value={nfe.infNFe.emit.enderEmit.UF} onChange={e => handleUfChange(e.target.value)} className="glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] p-2.5"><option value="" disabled>Selecione...</option>{Object.keys(stateCodes).sort().map(uf => (<option key={uf} value={uf}>{uf}</option>))}</select></div>
                                                <Input label="CEP" value={nfe.infNFe.emit.enderEmit.CEP} onChange={e => handleInputChange('emit', 'enderEmit.CEP', e.target.value)} required helpText="Apenas números" />
                                            </div>
                                        </Section>
                                    )}{activeMainTab === 'destinatário' && (
                                        <Section title="Destinatário (Quem recebe a nota)" onClear={() => clearSection('dest')} isCollapsible={false}>
                                            <div className="bg-green-50/10 border border-green-500/30 rounded-lg p-4 mb-4">
                                                <p className="text-sm text-green-300">
                                                    <strong>Sobre o Destinatário:</strong> É a empresa ou pessoa que receberá a mercadoria ou serviço. Na importação, é o fornecedor estrangeiro.
                                                </p>
                                            </div>
                                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <Input label="ID Estrangeiro" value={nfe.infNFe.dest.idEstrangeiro} onChange={e => handleInputChange('dest', 'idEstrangeiro', e.target.value)} helpText="Documento de identificação no país de origem" />
                                                <Input label="Nome/Razão Social" value={nfe.infNFe.dest.xNome} onChange={e => handleInputChange('dest', 'xNome', e.target.value)} required helpText="Nome do fornecedor estrangeiro" />
                                                <div><label className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm">Indicador IE</label><select value={nfe.infNFe.dest.indIEDest} onChange={e => handleInputChange('dest', 'indIEDest', e.target.value)} className="glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] p-2.5"><option value="1">1 - Contribuinte ICMS</option><option value="2">2 - Contribuinte isento</option><option value="9">9 - Não Contribuinte</option></select></div>
                                            </div>
                                            <h4 className="font-bold text-[var(--color-text-muted)] mt-5 mb-3 border-b border-[var(--color-border)] pb-1">Endereço do Destinatário</h4>
                                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                <Input label="Logradouro" value={nfe.infNFe.dest.enderDest.xLgr} onChange={e => handleInputChange('dest', 'enderDest.xLgr', e.target.value)} required />
                                                <Input label="Número" value={nfe.infNFe.dest.enderDest.nro} onChange={e => handleInputChange('dest', 'enderDest.nro', e.target.value)} />
                                                <Input label="Bairro" value={nfe.infNFe.dest.enderDest.xBairro} onChange={e => handleInputChange('dest', 'enderDest.xBairro', e.target.value)} required />
                                                <Input label="Município" value={nfe.infNFe.dest.enderDest.xMun} onChange={e => handleInputChange('dest', 'enderDest.xMun', e.target.value)} required />
                                                <div><label className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm">País</label><select value={nfe.infNFe.dest.enderDest.cPais} onChange={e => handleCountryChange(e.target.value)} className="glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] p-2.5"><option value="">Selecione...</option>{countryCodes.sort((a, b) => a.name.localeCompare(b.name)).map(country => (<option key={country.code} value={country.code}>{country.name}</option>))}</select></div>
                                            </div>
                                        </Section>
                                    )}{activeMainTab === 'declaração de importação' && (
                                        <Section title="Informações da Declaração de Importação" onClear={() => clearSection('diInfo')} isCollapsible={false}>
                                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                                <Input label="Número do Documento" value={diInfo.nDI} onChange={e => handleDiInfoChange('nDI', e.target.value)} helpText="Número da Declaração de Importação" />
                                                <Input label="Data do Registro" type="date" value={diInfo.dDI} onChange={e => handleDiInfoChange('dDI', e.target.value)} helpText="Data de registro da DI" />
                                                <Input label="Local de Desembaraço" value={diInfo.xLocDesemb} onChange={e => handleDiInfoChange('xLocDesemb', e.target.value)} helpText="Local onde ocorreu o desembaraço aduaneiro" />
                                                <Input label="UF do Desembaraço" value={diInfo.UFDesemb} onChange={e => handleDiInfoChange('UFDesemb', e.target.value)} helpText="UF do local de desembaraço" />
                                                <Input label="Data do Desembaraço" type="date" value={diInfo.dDesemb} onChange={e => handleDiInfoChange('dDesemb', e.target.value)} helpText="Data do desembaraço aduaneiro" />
                                                <Input label="Nome do Exportador" value={diInfo.cExportador} onChange={e => handleDiInfoChange('cExportador', e.target.value)} helpText="Código ou Nome do exportador estrangeiro" />
                                                <Input label="Taxa SISCOMEX" type="number" step="0.01" value={diInfo.vSISCOMEX} onChange={e => handleDiInfoChange('vSISCOMEX', e.target.value)} helpText="Valor da taxa SISCOMEX" />
                                                <Input label="Valor AFRMM" type="number" step="0.01" value={diInfo.vAFRMM} onChange={e => handleDiInfoChange('vAFRMM', e.target.value)} helpText="Adicional ao Frete para Renovação da Marinha Mercante" />
                                                <div><label className="block text-sm font-semibold text-[var(--color-text-secondary)] mb-1.5 drop-shadow-sm">Via de Transporte</label><select value={diInfo.tpViaTransp} onChange={e => handleDiInfoChange('tpViaTransp', e.target.value)} className="glass-input block w-full rounded-lg sm:text-sm text-[var(--color-text-primary)] p-2.5"><option value="1">Marítima</option><option value="4">Aérea</option><option value="7">Rodoviária</option></select></div>
                                            </div>
                                        </Section>
                                    )}{activeMainTab === 'itens' && (
                                        <Section title={`Itens da Nota Fiscal (${nfe.infNFe.det.length})`} onClear={() => clearSection('det')} isCollapsible={false} headerContent={(<div className="flex items-center gap-2 ml-4"><button type="button" onClick={() => { const s = {}; nfe.infNFe.det.forEach((_, i) => s[i] = true); setOpenItems(s); }} className="p-1 px-3 text-xs font-bold text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] border border-[var(--color-border)] rounded-full transition-colors glass-button bg-white/50 text-slate-800">Expandir</button><button type="button" onClick={() => setOpenItems({})} className="p-1 px-3 text-xs font-bold text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] border border-[var(--color-border)] rounded-full transition-colors glass-button bg-white/50 text-slate-800">Recolher</button></div>)}>
                                            <div className="space-y-6">{nfe.infNFe.det.map((item, index) => (<ItemRow key={index} item={item} index={index} onItemChange={handleItemChange} onAdiChange={handleAdiChange} onAddAdi={() => addAdi(index, 0)} onRemoveAdi={(adIdx) => removeAdi(index, 0, adIdx)} onRemove={() => removeItem(index)} onDuplicate={() => duplicateItem(index)} isOpen={!!openItems[index]} onToggleOpen={() => setOpenItems(p => ({ ...p, [index]: !p[index] }))} onOpenTaxModal={openTaxModal} />))}</div>
                                            <button type="button" onClick={addItem} className="mt-8 w-full flex items-center justify-center gap-2 rounded-xl border border-dashed border-[var(--color-accent)] bg-[var(--color-accent-subtle-bg)] px-4 py-4 text-sm font-bold text-[var(--color-accent-text)] hover:bg-[var(--color-accent)] hover:text-white transition-all duration-300 shadow-sm hover:shadow-lg"><svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clipRule="evenodd" /></svg>Adicionar Novo Item</button>
                                        </Section>
                                    )}
                                    {activeMainTab === 'totais' && (
                                        <Section title="Totais da Nota Fiscal" onClear={() => handleInputChange('total', 'ICMSTot', getEmptyNfeData().infNFe.total.ICMSTot)} isCollapsible={false}>
                                            <Totals icmsTot={nfe.infNFe.total.ICMSTot} onTotalChange={(field, value) => handleInputChange('total', `ICMSTot.${field}`, value)} />
                                        </Section>
                                    )}
                                    {activeMainTab === 'info. adicionais' && (
                                        <Section title="Informações Adicionais" onClear={() => clearSection('infAdic')} isCollapsible={false}>
                                            <TextArea label="Informações Complementares" value={nfe.infNFe.infAdic.infCpl} onChange={e => handleInputChange('infAdic', 'infCpl', e.target.value)} title="Texto livre para observações na nota" />
                                        </Section>
                                    )}
                                </div>
                            </form>
                        </div>
                    </main>
                </div>
            );
        };

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<React.StrictMode><App /></React.StrictMode>);
    </script>
</body>

</html>