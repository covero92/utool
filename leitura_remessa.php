<?php
// leitura_remessa.php
require_once 'includes/portal_auth.php';
require_once 'includes/portal_helpers.php';
require_once 'includes/LeituraRemessaConfig.php';

// Note: Not including standard header.php to respect the requested full-screen custom layout
if (!isLoggedIn()) {
    // header("Location: index.php");
    // exit;
}

$currentUser = getCurrentUser();

// Banks List (Metadata for lookup)
// Banks List (Metadata for lookup)
$banks = [
    ['code' => '001', 'name' => 'Banco do Brasil', 'color' => '#f9dd16', 'text' => '#003da5', 'logo' => 'bancoconfig/BB/041775a0013dd88d46bb7590b354984b.jpg'],
    ['code' => '004', 'name' => 'Banco do Nordeste', 'color' => '#f23e31', 'text' => '#ffffff', 'logo' => 'bancoconfig/Banco do Nordeste/images (1).png'],
    ['code' => '070', 'name' => 'BRB', 'color' => '#005da3', 'text' => '#ffffff', 'logo' => 'bancoconfig/Banco Regional de Brasília__/42692987_2022773584410786_5313482106930724864_n.jpg'],
    ['code' => '021', 'name' => 'Banestes', 'color' => '#0072bc', 'text' => '#ffffff', 'logo' => 'bancoconfig/Banestes/beemore.jpg'],
    ['code' => '041', 'name' => 'Banrisul', 'color' => '#004d8c', 'text' => '#ffffff', 'logo' => 'bancoconfig/Banrisul/356920260_639346454897843_5694843754612830476_n.jpg'],
    ['code' => '237', 'name' => 'Bradesco', 'color' => '#cc092f', 'text' => '#ffffff', 'logo' => 'bancoconfig/Bradesco/bradesco.png'],
    ['code' => '104', 'name' => 'Caixa', 'color' => '#0066b3', 'text' => '#ffffff', 'logo' => 'bancoconfig/Caixa/caixa-download-png-5.png'],
    ['code' => '085', 'name' => 'Cecred (Ailos)', 'color' => '#004d40', 'text' => '#ffffff', 'logo' => 'bancoconfig/Cecred/unnamed.png'],
    ['code' => '097', 'name' => 'Credsis', 'color' => '#00bfa5', 'text' => '#ffffff', 'logo' => 'bancoconfig/Credsis/unnamed.png'],
    ['code' => '133', 'name' => 'Cresol', 'color' => '#f37021', 'text' => '#ffffff', 'logo' => 'bancoconfig/Cresol/images.png'],
    ['code' => '707', 'name' => 'Daycoval', 'color' => '#f39200', 'text' => '#ffffff', 'logo' => 'bancoconfig/Daycoval/images (1).png'],
    ['code' => '399', 'name' => 'HSBC', 'color' => '#db0011', 'text' => '#ffffff', 'logo' => 'bancoconfig/HSBC/HSBC-Emblema.jpg'],
    ['code' => '077', 'name' => 'Inter', 'color' => '#ff7a00', 'text' => '#ffffff', 'logo' => 'bancoconfig/Inter/1200x630wa.png'],
    ['code' => '341', 'name' => 'Itaú', 'color' => '#ec7000', 'text' => '#ffffff', 'logo' => 'bancoconfig/Itau/Itaú-novo-logotipo-2023-1000x600.jpg'],
    ['code' => '422', 'name' => 'Safra', 'color' => '#1a1843', 'text' => '#d4af37', 'logo' => 'bancoconfig/Safra/logo-banco-safra-icon-2048.png'],
    ['code' => '033', 'name' => 'Santander', 'color' => '#ec0000', 'text' => '#ffffff', 'logo' => 'bancoconfig/Santander/santander-br.png'],
    ['code' => '756', 'name' => 'Sicoob', 'color' => '#003641', 'text' => '#ffffff', 'logo' => 'bancoconfig/Sicoob/images.png'],
    ['code' => '748', 'name' => 'Sicredi', 'color' => '#3fb051', 'text' => '#ffffff', 'logo' => 'bancoconfig/Sicred/images.jpg'],
    ['code' => '136', 'name' => 'Unicred', 'color' => '#006c5b', 'text' => '#ffffff', 'logo' => 'bancoconfig/Unicred/channels4_profile.jpg'],
    ['code' => '461', 'name' => 'Asaas', 'color' => '#0030b9', 'text' => '#ffffff', 'logo' => 'bancoconfig/Asaas/unnamed.png'],
];

$banksJson = json_encode($banks);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Leitura de Remessa - CNAB</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#2563eb",
                        success: "#15803d",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        .main-height {
            height: calc(100vh - 120px);
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark min-h-screen text-slate-900 dark:text-slate-100 transition-colors duration-200 overflow-hidden">
    <div class="max-w-[1600px] mx-auto px-4 py-4 h-screen flex flex-col">
        <header
            class="bg-white dark:bg-slate-800 rounded-xl p-4 mb-4 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-4">
                <a href="index.php"
                    class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined text-slate-500">arrow_back</span>
                </a>
                <div>
                    <h1 id="headerTitle" class="text-xl font-bold">Leitura de Remessa</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Suporte Retaguarda</p>
                </div>
            </div>

            <!-- API Integration Info Banner -->
            <div
                class="hidden md:flex items-center gap-4 mx-6 px-6 border-l border-r border-slate-100 dark:border-slate-700 h-10">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Integração API</span>
                    <span class="text-[9px] text-slate-400">Disponível para:</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center -space-x-2">
                        <img src="bancoconfig/Asaas/unnamed.png" title="Asaas"
                            class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-800 bg-white object-contain"
                            alt="Asaas">
                        <img src="bancoconfig/Sicoob/images.png" title="Sicoob"
                            class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-800 bg-white object-contain"
                            alt="Sicoob">
                        <img src="bancoconfig/Itau/Itaú-novo-logotipo-2023-1000x600.jpg" title="Itaú"
                            class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-800 bg-white object-contain"
                            alt="Itaú">
                        <img src="bancoconfig/BB/041775a0013dd88d46bb7590b354984b.jpg" title="Banco do Brasil"
                            class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-800 bg-white object-contain"
                            alt="BB">
                    </div>
                </div>
                <div
                    class="hidden lg:block bg-indigo-50 dark:bg-indigo-900/20 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800/30 max-w-[200px]">
                    <p class="text-[9px] text-indigo-600 dark:text-indigo-300 leading-tight">
                        <span class="font-bold">Nota:</span> A integração do <span class="font-bold">Asaas</span> é
                        inteiramente via API.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="https://kb.beemore.com/dc/pt-br/domains/suporte/documents/cobranca-escritural" target="_blank"
                    class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 rounded-lg transition-colors text-xs font-medium text-slate-600 dark:text-slate-300">
                    <img src="bancoconfig/intelidata.png" alt="Intelidata" class="h-4 w-auto opacity-80">
                    <span class="hidden md:inline">Config. Conta</span>
                </a>

                <a id="btnApiIntegration"
                    href="https://kb.beemore.com/dc/pt-br/domains/suporte/documents/banco-on-line-integracao-com-api-bancaria"
                    target="_blank"
                    class="hidden flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800 rounded-lg transition-colors text-xs font-medium text-indigo-700 dark:text-indigo-300">
                    <span class="material-symbols-outlined text-[16px]">api</span>
                    <span class="hidden md:inline">Integração API</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <button
                    class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center transition-all hover:bg-slate-50"
                    onclick="document.documentElement.classList.toggle('dark')">
                    <span class="material-symbols-outlined text-slate-500">dark_mode</span>
                </button>
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white"
                    title="<?php echo htmlspecialchars($currentUser); ?>">
                    <span class="material-symbols-outlined">account_circle</span>
                </div>
            </div>
        </header>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow overflow-hidden mb-4">
            <aside class="lg:col-span-4 flex flex-col gap-4 overflow-y-auto custom-scrollbar pr-1">
                <section
                    class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">

                    <!-- Mode Switcher -->
                    <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-900 rounded-lg mb-4">
                        <button id="tabRemessa" onclick="setMode('remessa')"
                            class="flex-1 py-1.5 px-3 rounded-md text-xs font-bold transition-all bg-blue-100 text-blue-700 border border-blue-200 shadow-sm flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">upload_file</span>
                            Remessa
                        </button>
                        <button id="tabRetorno" onclick="setMode('retorno')"
                            class="flex-1 py-1.5 px-3 rounded-md text-xs font-bold transition-all text-slate-600 hover:bg-slate-50 border border-transparent flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">file_download</span>
                            Retorno
                        </button>
                    </div>

                    <!-- FORM -->
                    <form id="uploadRemessaForm">
                        <input type="file" id="remessaFile" name="remessaFile" class="hidden" accept=".rem,.txt,.ret"
                            onchange="handleFileSelect(this)">
                        <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-6 flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-900/50 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-900 transition"
                            onclick="document.getElementById('remessaFile').click()"
                            ondragover="event.preventDefault(); this.classList.add('border-primary')"
                            ondragleave="this.classList.remove('border-primary')"
                            ondrop="event.preventDefault(); this.classList.remove('border-primary'); handleDrop(event)">
                            <div
                                class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined text-primary text-2xl">cloud_upload</span>
                            </div>
                            <h2 id="uploadTitle" class="text-md font-semibold mb-1 text-center">Selecione o arquivo de
                                remessa</h2>
                            <p id="uploadSubtitle" class="text-xs text-slate-500 dark:text-slate-400 mb-4 text-center">
                                Suporta arquivos
                                .REM, .TXT e .RET (CNAB 240/400)</p>
                            <div id="fileInfoDisplay"
                                class="hidden flex items-center gap-2 px-3 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs text-primary font-medium mb-4">
                                <span class="material-symbols-outlined text-sm">insert_drive_file</span>
                                <span id="fileNameDisplay">--</span>
                            </div>
                            <button type="submit" id="btnProcessar" onclick="event.stopPropagation()" disabled
                                class="w-full bg-primary hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white py-2 rounded-lg font-medium transition-all transform active:scale-95 flex items-center justify-center gap-2 text-sm">
                                <span>Processar e Analisar</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- RESULTADO: BANCO -->
                <div id="cardBank"
                    class="hidden bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div id="bankLogoBox"
                            class="w-10 h-10 bg-slate-900 dark:bg-slate-700 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                            -
                        </div>
                        <div>
                            <span class="text-[9px] uppercase tracking-wider font-bold text-slate-400">Banco
                                Identificado</span>
                            <h3 id="txtBankName" class="text-md font-bold leading-tight">--</h3>
                        </div>
                    </div>
                    <div id="txtBankCode"
                        class="bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-[10px] font-mono text-slate-600 dark:text-slate-300">
                        Cód. --
                    </div>
                </div>

                <!-- RESULTADO: RESUMO -->
                <section id="cardSummary"
                    class="hidden bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="bg-success px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-white">
                            <span class="material-symbols-outlined text-lg">fact_check</span>
                            <span class="text-sm font-semibold">Resultado da Análise</span>
                        </div>
                        <button onclick="resetUpload()"
                            class="bg-white/20 hover:bg-white/30 text-white text-[10px] font-bold uppercase px-3 py-1 rounded transition-colors">
                            Nova
                        </button>
                    </div>
                    <div class="grid grid-cols-1 divide-y divide-slate-100 dark:divide-slate-700">
                        <div class="p-4 flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Arquivo</span>
                            <span id="txtFilename"
                                class="font-mono text-xs font-semibold truncate max-w-[200px]">--</span>
                        </div>
                        <div class="p-4 flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Layout</span>
                            <span id="txtLayout"
                                class="bg-slate-900 dark:bg-slate-600 text-white text-[10px] px-2 py-0.5 rounded font-bold">--</span>
                        </div>
                        <div class="p-4 flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Total Linhas</span>
                            <span id="txtTotalLines" class="font-bold text-lg leading-none">0</span>
                        </div>
                    </div>
                </section>
            </aside>
            <main
                class="lg:col-span-8 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col overflow-hidden">
                <div
                    class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Conteúdo Detalhado e Campos
                        Extraídos</span>
                    <div class="flex items-center gap-4 text-[10px] font-medium">
                        <button id="btnShowCorrections"
                            class="hidden flex items-center gap-2 px-3 py-1 bg-red-50 text-red-600 border border-red-200 rounded hover:bg-red-100 transition-colors"
                            onclick="openCorrectionModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Ver Correção
                        </button>
                        <div class="flex items-center gap-1.5" title="Campos de Valor Monetário">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span class="text-slate-500">Valores</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Campos de Data">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="text-slate-500">Datas</span>
                        </div>

                    </div>
                </div>
                <div class="flex-grow overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <tbody id="linesTableBody" class="divide-y divide-slate-100 dark:divide-slate-700">
                            <!-- Dynamic Content -->
                            <tr>
                                <td colspan="2" class="p-10 text-center text-slate-400 text-sm">
                                    <span class="material-symbols-outlined text-4xl mb-2">text_snippet</span><br>
                                    Aguardando processamento do arquivo...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="bg-slate-50 dark:bg-slate-900/50 p-3 px-6 flex items-center justify-between border-t border-slate-200 dark:border-slate-700">
                    <div id="footerStatus" class="text-[10px] text-slate-500 font-medium">Aguardando...</div>
                    <div class="flex gap-2">
                        <!-- Pagination placeholders (not functioning yet, just visual) -->
                        <button
                            class="w-7 h-7 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center opacity-50 cursor-not-allowed">
                            <span class="material-symbols-outlined text-sm">chevron_left</span>
                        </button>
                        <button
                            class="w-7 h-7 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700">
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const banksData = <?php echo $banksJson; ?>;
        let currentMode = 'remessa'; // 'remessa' or 'retorno'

        function setMode(mode) {
            currentMode = mode;
            // Visual Update
            const btnRemessa = document.getElementById('tabRemessa');
            const btnRetorno = document.getElementById('tabRetorno');
            const uploadTitle = document.getElementById('uploadTitle');
            const uploadSubtitle = document.getElementById('uploadSubtitle');

            if (mode === 'remessa') {
                btnRemessa.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
                btnRemessa.classList.remove('bg-white', 'text-slate-600', 'border-transparent', 'hover:bg-slate-50');

                btnRetorno.classList.remove('bg-purple-100', 'text-purple-700', 'border-purple-200');
                btnRetorno.classList.add('bg-white', 'text-slate-600', 'border-transparent', 'hover:bg-slate-50');

                uploadTitle.innerText = "Selecione o arquivo de Remessa";
                uploadSubtitle.innerText = "Suporta arquivos .REM e .TXT (CNAB 240/400)";
                document.getElementById('headerTitle').innerText = "Leitura de Remessa";
            } else {
                btnRetorno.classList.add('bg-purple-100', 'text-purple-700', 'border-purple-200');
                btnRetorno.classList.remove('bg-white', 'text-slate-600', 'border-transparent', 'hover:bg-slate-50');

                btnRemessa.classList.remove('bg-blue-100', 'text-blue-700', 'border-blue-200');
                btnRemessa.classList.add('bg-white', 'text-slate-600', 'border-transparent', 'hover:bg-slate-50');

                uploadTitle.innerText = "Selecione o arquivo de Retorno";
                uploadSubtitle.innerText = "Suporta arquivos .RET (CNAB 240/400)";
                document.getElementById('headerTitle').innerText = "Leitura de Retorno";
            }

            resetUpload();
        }

        function handleFileSelect(input) {
            const file = input.files[0];
            if (file) {
                document.getElementById('fileNameDisplay').innerText = file.name;
                document.getElementById('fileInfoDisplay').classList.remove('hidden');
                document.getElementById('btnProcessar').disabled = false;
            }
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                document.getElementById('remessaFile').files = files;
                handleFileSelect(document.getElementById('remessaFile'));
            }
        }

        function resetUpload() {
            document.getElementById('uploadRemessaForm').reset();
            document.getElementById('fileNameDisplay').innerText = '--';
            document.getElementById('fileInfoDisplay').classList.add('hidden');
            document.getElementById('btnProcessar').disabled = true;

            document.getElementById('cardBank').classList.add('hidden');
            document.getElementById('cardSummary').classList.add('hidden');
            document.getElementById('linesTableBody').innerHTML = `<tr><td colspan="2" class="p-10 text-center text-slate-400 text-sm"><span class="material-symbols-outlined text-4xl mb-2">text_snippet</span><br>Aguardando processamento do arquivo...</td></tr>`;
            document.getElementById('footerStatus').innerText = 'Aguardando...';
        }

        document.getElementById('uploadRemessaForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const btn = document.getElementById('btnProcessar');
            const originalHtml = btn.innerHTML;

            // Check mode to select endpoint
            let endpoint = 'includes/processar_remessa_upload.php';
            if (currentMode === 'retorno') {
                endpoint = 'includes/processar_retorno_upload.php';
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin material-symbols-outlined text-sm mr-2">progress_activity</span> Processando...';

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showResults(data.data);
                    } else {
                        alert('Erro: ' + data.error);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao comunicar com o servidor.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        });

        function showResults(data) {
            // Show Sidebar Cards
            document.getElementById('cardBank').classList.remove('hidden');
            document.getElementById('cardBank').classList.add('flex'); // Ensure flex display
            document.getElementById('cardSummary').classList.remove('hidden');

            // Summary Data
            document.getElementById('txtFilename').innerText = data.filename;
            document.getElementById('txtLayout').innerText = data.type;
            document.getElementById('txtTotalLines').innerText = data.total_lines;
            document.getElementById('footerStatus').innerText = `Exibindo ${data.preview_lines.length} de ${data.total_lines} linhas processadas`;

            // Bank Data
            const bankCode = data.bank_in_file;
            const bankInfo = banksData.find(b => b.code === bankCode);

            if (bankInfo) {
                document.getElementById('txtBankName').innerText = bankInfo.name;
                document.getElementById('txtBankCode').innerText = 'Cód. ' + bankCode;

                // API Integration Button Logic
                const apiBanks = ['001', '341', '756', '461']; // BB, Itaú, Sicoob, Asaas
                const btnApi = document.getElementById('btnApiIntegration');
                if (btnApi) {
                    if (apiBanks.includes(bankCode)) {
                        btnApi.classList.remove('hidden');
                        btnApi.classList.add('flex');
                    } else {
                        btnApi.classList.add('hidden');
                        btnApi.classList.remove('flex');
                    }
                }

                const logoBox = document.getElementById('bankLogoBox');
                logoBox.innerText = ''; // Clear text

                // Use logo if available
                if (bankInfo.logo) {
                    logoBox.innerHTML = `<img src="${bankInfo.logo}" alt="${bankInfo.name}" class="w-full h-full object-contain rounded-lg">`;
                    logoBox.style.backgroundColor = 'transparent';
                } else {
                    logoBox.style.backgroundColor = bankInfo.color;
                    logoBox.style.color = bankInfo.text;
                    logoBox.innerText = bankInfo.name.substring(0, 1);
                }
            } else {
                document.getElementById('txtBankName').innerText = 'Banco Desconhecido';
                document.getElementById('txtBankCode').innerText = 'Cód. ' + (bankCode || '?');

                const logo = document.getElementById('bankLogoBox');
                logo.style.backgroundColor = '#64748b'; // slate-500
                logo.style.color = 'white';
                logo.innerText = '?';
            }

            // Render Table Rows
            const tbody = document.getElementById('linesTableBody');
            tbody.innerHTML = '';

            let allErrors = [];

            data.preview_lines.forEach((line, index) => {
                let fieldsHtml = '';

                if (line.fields && line.fields.length > 0) {
                    // Collect Errors
                    line.fields.forEach(f => {
                        if (f.valid === false) {
                            allErrors.push({
                                line: line.number,
                                field: f.name,
                                pos: f.pos,
                                value: f.value,
                                error: f.error
                            });
                        }
                    });

                    fieldsHtml = `<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-2">`;
                    line.fields.forEach(field => {
                        let valueClass = 'text-slate-600 dark:text-slate-300';
                        let borderClass = 'border-slate-100 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-600';
                        let titleAttr = `title="${field.name}"`;
                        let valueTitleAttr = `title="${field.value}"`;

                        if (field.valid === false) {
                            valueClass = 'text-red-600 dark:text-red-400 font-bold';
                            borderClass = 'border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/10';
                            titleAttr = `title="Erro: ${field.error}"`;
                            valueTitleAttr = `title="Erro: ${field.error} (Valor: ${field.value})"`;
                        } else {
                            if (field.name.includes('Valor')) valueClass = 'text-emerald-600 dark:text-emerald-400 font-medium';
                            if (field.name.includes('Vencimento') || field.name.includes('Data')) valueClass = 'text-blue-600 dark:text-blue-400';
                        }

                        // Add click handler to highlight
                        fieldsHtml += `
                        <div class="field-card group p-1.5 border ${borderClass} rounded bg-white dark:bg-slate-800/50 shadow-sm overflow-hidden cursor-pointer transition-colors relative"
                             ${titleAttr}
                             onclick="highlightField(${index}, ${field.start}, ${field.end}, '${field.value}', '${field.name}')">
                             
                            ${field.valid === false ? `<div class="absolute top-0.5 right-0.5 w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>` : ''}

                            <div class="flex justify-between items-start mb-0.5">
                                <span class="text-[8px] font-bold text-slate-400 uppercase truncate pr-1">${field.name}</span>
                                <span class="text-[7px] font-mono text-slate-300 flex-shrink-0">${field.pos}</span>
                            </div>
                            <div class="text-xs font-mono truncate ${valueClass}" ${valueTitleAttr}>${field.value}</div>
                        </div>
                    `;
                    });
                    fieldsHtml += `</div>`;
                }

                const tr = document.createElement('tr');
                tr.className = "group hover:bg-slate-50/50 dark:hover:bg-slate-900/20";
                tr.innerHTML = `
                <td class="px-4 py-4 w-12 align-top border-r border-slate-100 dark:border-slate-700">
                    <div class="w-7 h-7 rounded border border-slate-200 dark:border-slate-600 flex items-center justify-center text-[10px] font-bold bg-white dark:bg-slate-800">${line.number}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="mb-3">
                        <div class="text-[9px] font-bold text-slate-400 uppercase mb-1">Dado Bruto</div>
                        <!-- Container for content with highlight capability -->
                        <div id="raw-content-${index}" class="relative bg-slate-50 dark:bg-slate-900/80 p-2 rounded font-mono text-[10px] text-slate-600 dark:text-slate-400 break-all border border-slate-100 dark:border-slate-800 leading-relaxed">
                            ${line.content}
                        </div>
                        <!-- Ruler / Detail View (Hidden by default) -->
                         <div id="ruler-${index}" class="hidden mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-100 dark:border-blue-800">
                            <div class="flex items-center gap-4 text-xs">
                                <div>
                                    <span class="text-[9px] uppercase font-bold text-slate-400 block">Posição</span>
                                    <span id="ruler-pos-${index}" class="font-mono font-bold text-primary">--</span>
                                </div>
                                <div>
                                    <span class="text-[9px] uppercase font-bold text-slate-400 block">Valor</span>
                                    <span id="ruler-val-${index}" class="font-mono font-bold bg-white dark:bg-slate-800 px-2 rounded border border-blue-200 dark:border-blue-700">--</span>
                                </div>
                                <div class="flex-grow">
                                    <span class="text-[9px] uppercase font-bold text-slate-400 block">Indices</span>
                                    <div id="ruler-indices-${index}" class="flex gap-1 font-mono text-[9px] text-slate-500 overflow-x-auto"></div>
                                </div>
                            </div>
                         </div>
                    </div>
                    ${fieldsHtml}
                </td>
            `;
                tbody.appendChild(tr);
            });

            // Handle Correction Button
            const btnCorrection = document.getElementById('btnShowCorrections');
            if (allErrors.length > 0) {
                btnCorrection.classList.remove('hidden');
                // Store errors globally or attach to button
                window.currentErrors = allErrors;
            } else {
                btnCorrection.classList.add('hidden');
                window.currentErrors = [];
            }
        }

        // Modal Functions
        function getFriendlyError(err) {
            let friendly = {
                title: 'Dado Inválido',
                desc: 'O valor encontrado não corresponde ao esperado.',
                solution: 'Verifique o formato do campo.',
                icon: '⚠️'
            };

            if (err.error.includes('Numérico')) {
                friendly.title = 'Apenas Números';
                friendly.desc = 'Este campo deve conter apenas números, mas encontramos letras ou espaços.';
                friendly.solution = 'Remova qualquer caractere que não seja número e preencha com zeros à esquerda se necessário.';
                friendly.icon = '🔢';
            } else if (err.error.includes('Data')) {
                friendly.title = 'Data Inválida';
                friendly.desc = 'A data informada não existe ou está no formato errado.';
                friendly.solution = 'Informe uma data válida no formato DDMMAAAA (Dia, Mês, Ano).';
                friendly.icon = '📅';
            }

            return friendly;
        }

        function openCorrectionModal() {
            const modal = document.getElementById('correctionModal');
            const container = document.getElementById('correctionListContainer');
            container.innerHTML = '';

            if (window.currentErrors && window.currentErrors.length > 0) {
                window.currentErrors.forEach(err => {
                    const info = getFriendlyError(err);

                    const card = `
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm flex gap-4 items-start hover:border-red-200 transition-colors">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-xl">
                                ${info.icon}
                            </div>
                            <div class="flex-grow min-w-0">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm">
                                        ${info.title} <span class="text-slate-400 font-normal ml-1">— ${err.field}</span>
                                    </h4>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">Linha ${err.line}</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">${info.desc}</p>
                                
                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 rounded px-2 py-1.5 flex flex-col">
                                        <span class="text-[9px] uppercase font-bold text-red-400 mb-0.5">Encontrado (Pos ${err.pos})</span>
                                        <span class="font-mono text-red-600 dark:text-red-400 font-bold break-all">"${err.value}"</span>
                                    </div>
                                    <div class="flex items-center text-slate-300">➜</div>
                                    <div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30 rounded px-2 py-1.5 flex flex-col flex-grow">
                                        <span class="text-[9px] uppercase font-bold text-emerald-500 mb-0.5">Como Corrigir</span>
                                        <span class="font-medium text-emerald-700 dark:text-emerald-400">${info.solution}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.innerHTML += card;
                });
            }

            modal.classList.remove('hidden');
        }

        function closeCorrectionModal() {
            document.getElementById('correctionModal').classList.add('hidden');
        }

        function highlightField(lineIndex, start, end, value, name) {
            // 1. Reset all highlights in this line (or all lines? maybe just this one)
            // Ideally we keep the original text in a data attribute
            const container = document.getElementById(`raw-content-${lineIndex}`);
            const originalText = container.textContent.trim(); // Or store in data-original

            // Reconstruct HTML with highlight
            // Indices are 1-based (start, end). String is 0-based.
            const sIdx = start - 1;
            const eIdx = end; // substring length is end - start + 1. 

            const before = originalText.substring(0, sIdx);
            const match = originalText.substring(sIdx, eIdx);
            const after = originalText.substring(eIdx);

            container.innerHTML = `${before}<span class="bg-yellow-300 dark:bg-yellow-600 text-black dark:text-white font-bold px-0.5 rounded-sm border border-yellow-400">${match}</span>${after}`;

            // 2. Show Ruler
            const ruler = document.getElementById(`ruler-${lineIndex}`);
            ruler.classList.remove('hidden');

            document.getElementById(`ruler-pos-${lineIndex}`).innerText = `${String(start).padStart(3, '0')} - ${String(end).padStart(3, '0')}`;
            document.getElementById(`ruler-val-${lineIndex}`).innerText = value;

            // indices visualizer (1 2 3 \n v a l)
            // Just showing the internal offset indices relative to start? 
            // User example: 1 2 3 (vertical stack) 7 5 6.
            // Let's simpler: Just show the character indices
            let indicesHtml = '';
            for (let i = 0; i < match.length; i++) {
                // Use absolute position: start + i
                const absolutePos = start + i;
                const char = match[i];

                // Logic: Hide index/value if char is just a space? 
                // User request: "campos que nao tem nada nao precisa de numero"
                // If it's a space, we render a placeholder to keep alignment or just skip?
                // "Nao precisa de numero" implies the number is hidden.
                // Let's render the block but with visibility hidden for the number if char is space.

                const isSpace = char === ' ';
                const numberClass = isSpace ? 'invisible' : 'text-blue-500'; // Blue as requested

                indicesHtml += `
                <div class="flex flex-col items-center flex-shrink-0" style="width: 14px;">
                    <span class="text-[9px] font-bold ${numberClass} font-mono leading-tight">${absolutePos}</span>
                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 font-mono leading-tight whitespace-pre">${char}</span>
                </div>`;
            }
            document.getElementById(`ruler-indices-${lineIndex}`).innerHTML = indicesHtml;
        }
    </script>
    <!-- Correction Modal -->
    <div id="correctionModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="closeCorrectionModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Relatório de Correções
                            </h3>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <p>Foram encontrados erros no arquivo. Veja abaixo os detalhes e como corrigir.</p>
                            </div>

                            <div class="mt-4 max-h-[60vh] overflow-y-auto pr-2 flex flex-col gap-3"
                                id="correctionListContainer">
                                <!-- Cards generated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="closeCorrectionModal()">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>