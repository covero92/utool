<?php
// leitura_retorno.php
require_once 'includes/portal_auth.php';
require_once 'includes/portal_helpers.php';
require_once 'includes/LeituraRemessaConfig.php';

if (!isLoggedIn()) {
    // header("Location: index.php");
    // exit;
}

$currentUser = getCurrentUser();

$banks = [
    ['code' => '001', 'name' => 'Banco do Brasil', 'color' => '#f9dd16', 'text' => '#003da5', 'logo' => ''],
    ['code' => '004', 'name' => 'Banco do Nordeste', 'color' => '#f23e31', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '070', 'name' => 'BRB', 'color' => '#005da3', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '021', 'name' => 'Banestes', 'color' => '#0072bc', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '041', 'name' => 'Banrisul', 'color' => '#004d8c', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '237', 'name' => 'Bradesco', 'color' => '#cc092f', 'text' => '#ffffff', 'logo' => 'bancoconfig/Bradesco/bradesco.png'],
    ['code' => '104', 'name' => 'Caixa', 'color' => '#0066b3', 'text' => '#ffffff', 'logo' => 'bancoconfig/Caixa/caixa-download-png-5.png'],
    ['code' => '085', 'name' => 'Cecred (Ailos)', 'color' => '#004d40', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '097', 'name' => 'Credsis', 'color' => '#00bfa5', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '133', 'name' => 'Cresol', 'color' => '#f37021', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '707', 'name' => 'Daycoval', 'color' => '#f39200', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '399', 'name' => 'HSBC', 'color' => '#db0011', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '077', 'name' => 'Inter', 'color' => '#ff7a00', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '341', 'name' => 'Itaú', 'color' => '#ec7000', 'text' => '#ffffff', 'logo' => 'bancoconfig/Itau/images.png'],
    ['code' => '422', 'name' => 'Safra', 'color' => '#1a1843', 'text' => '#d4af37', 'logo' => ''],
    ['code' => '033', 'name' => 'Santander', 'color' => '#ec0000', 'text' => '#ffffff', 'logo' => 'bancoconfig/Santander/santander-br.png'],
    ['code' => '756', 'name' => 'Sicoob', 'color' => '#003641', 'text' => '#ffffff', 'logo' => 'bancoconfig/Sicoob/images.png'],
    ['code' => '748', 'name' => 'Sicredi', 'color' => '#3cb33f', 'text' => '#ffffff', 'logo' => ''],
    ['code' => '136', 'name' => 'Unicred', 'color' => '#006c5b', 'text' => '#ffffff', 'logo' => ''],
];

$banksJson = json_encode($banks);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Leitura de Retorno - CNAB</title>
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
                    <h1 class="text-xl font-bold">Leitura de Retorno</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Suporte Retaguarda</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="editor_resolucoes.php" target="_blank"
                    class="flex items-center gap-2 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/30 hover:bg-purple-100 dark:hover:bg-purple-900/50 border border-purple-200 dark:border-purple-800 rounded-lg transition-colors text-xs font-medium text-purple-700 dark:text-purple-300">
                    <span class="material-symbols-outlined text-[16px]">edit_note</span>
                    <span class="hidden md:inline">Editar Resoluções</span>
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
                    <form id="uploadRemessaForm">
                        <input type="file" id="remessaFile" name="remessaFile" class="hidden" accept=".rem,.txt,.ret"
                            onchange="handleFileSelect(this)">

                        <!-- Upload Area -->
                        <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-6 flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-900/50 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-900 transition mb-6"
                            onclick="document.getElementById('remessaFile').click()"
                            ondragover="event.preventDefault(); this.classList.add('border-primary')"
                            ondragleave="this.classList.remove('border-primary')"
                            ondrop="event.preventDefault(); this.classList.remove('border-primary'); handleDrop(event)">

                            <div
                                class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined">upload_file</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white mb-1">Selecione o Arquivo</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">CNAB 240 ou 400 (.RET)</p>

                            <button type="submit" id="btnProcessar" disabled
                                class="px-6 py-2 bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-blue-700 text-white font-medium rounded-lg transition-all shadow-sm text-sm w-full">
                                Processar e Analisar
                            </button>
                        </div>
                    </form>

                    <!-- File Info Card -->
                    <div id="fileInfoCard"
                        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden hidden">
                        <div
                            class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div id="bankLogoBox"
                                    class="w-10 h-10 rounded-lg bg-slate-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                                    ?</div>
                                <div>
                                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">
                                        Banco Identificado</h4>
                                    <div id="txtBankName" class="font-bold text-slate-700 dark:text-slate-200 text-sm">
                                        --</div>
                                </div>
                            </div>
                            <span id="txtBankCode"
                                class="font-mono text-xs text-slate-400 bg-white dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-slate-700">--</span>
                        </div>

                        <div class="p-4 bg-white dark:bg-slate-800">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] uppercase font-bold text-slate-400">Arquivo</span>
                                <span id="txtFilename"
                                    class="font-mono text-xs font-semibold truncate max-w-[150px]">--</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] uppercase font-bold text-slate-400">Layout</span>
                                <span id="txtLayout"
                                    class="bg-slate-900 dark:bg-slate-600 text-white text-[10px] px-2 py-0.5 rounded font-bold">--</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] uppercase font-bold text-slate-400">Total Linhas</span>
                                <span id="txtTotalLines" class="font-bold text-base">0</span>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>

            <main
                class="lg:col-span-8 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col overflow-hidden">
                <div
                    class="bg-slate-50 dark:bg-slate-900/50 px-6 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-shrink-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Conteúdo Detalhado</span>
                    <div class="flex items-center gap-3 text-[10px] font-medium">
                        <button id="btnShowCorrections"
                            class="hidden flex items-center gap-2 px-3 py-1 bg-purple-50 text-purple-600 border border-purple-200 rounded hover:bg-purple-100 transition-colors"
                            onclick="openCorrectionModal()">
                            <span class="material-symbols-outlined text-[14px]">warning</span> Ver Resoluções
                        </button>
                        <div class="flex items-center gap-1.5"><span
                                class="w-2 h-2 rounded-full bg-green-500"></span><span
                                class="text-slate-500">Valores</span></div>
                        <div class="flex items-center gap-1.5"><span
                                class="w-2 h-2 rounded-full bg-blue-600"></span><span
                                class="text-slate-500">Datas</span></div>
                        <div class="flex items-center gap-1.5"><span
                                class="w-2 h-2 rounded-full bg-purple-500"></span><span
                                class="text-slate-500">Ocorrências</span></div>
                    </div>
                </div>

                <div class="flex-grow overflow-y-auto custom-scrollbar relative">
                    <table class="w-full text-left border-collapse">
                        <tbody id="linesTableBody" class="divide-y divide-slate-100 dark:divide-slate-700">
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
                    class="bg-slate-50 dark:bg-slate-900/50 p-2 px-6 flex items-center justify-between border-t border-slate-200 dark:border-slate-700 flex-shrink-0">
                    <div id="footerStatus" class="text-[10px] text-slate-500 font-medium">Aguardando...</div>
                </div>
            </main>
        </div>
    </div>

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
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-purple-600">lightbulb</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Resoluções Encontradas</h3>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <p>O sistema identificou ocorrências com resoluções conhecidas na base de conhecimento.
                                </p>
                            </div>
                            <div class="mt-4 max-h-[60vh] overflow-y-auto pr-2 flex flex-col gap-3"
                                id="correctionListContainer">
                                <!-- Generated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="closeCorrectionModal()">Fechar</button>
                    <a href="editor_resolucoes.php" target="_blank"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Gerenciar
                        Resoluções</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const banksData = <?php echo $banksJson; ?>;
        let globalResolutions = [];

        function handleFileSelect(input) {
            const file = input.files[0];
            if (file) {
                // document.getElementById('fileNameDisplay').innerText = file.name; // No longer separate element
                document.getElementById('btnProcessar').disabled = false;
                document.getElementById('btnProcessar').innerHTML = "Processar: " + file.name;
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

        document.getElementById('uploadRemessaForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const btn = document.getElementById('btnProcessar');
            const originalHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin material-symbols-outlined text-sm mr-2">progress_activity</span> Processando...';

            fetch('includes/processar_retorno_upload.php', {
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
            document.getElementById('fileInfoCard').classList.remove('hidden');
            document.getElementById('txtFilename').innerText = data.filename;
            document.getElementById('txtLayout').innerText = data.type || 'Desconhecido';
            document.getElementById('txtTotalLines').innerText = data.total_lines;
            document.getElementById('footerStatus').innerText = `Exibindo ${data.preview_lines.length} de ${data.total_lines} linhas processadas`;

            // Bank
            const bankCode = data.bank_in_file;
            const bankInfo = banksData.find(b => b.code == bankCode) || { name: 'Desconhecido', code: bankCode };
            document.getElementById('txtBankName').innerText = bankInfo.name;
            document.getElementById('txtBankCode').innerText = 'Cód. ' + bankCode;

            const logoBox = document.getElementById('bankLogoBox');
            if (bankInfo.logo) {
                logoBox.innerHTML = `<img src="${bankInfo.logo}" class="w-full h-full object-contain">`;
                logoBox.style.background = 'transparent';
                logoBox.innerText = '';
            } else {
                logoBox.innerHTML = bankCode;
                logoBox.style.background = bankInfo.color || '#64748b';
            }

            // Table
            renderTable(data.preview_lines);
        }

        function renderTable(lines) {
            const tbody = document.getElementById('linesTableBody');
            tbody.innerHTML = '';
            globalResolutions = [];

            lines.forEach((line, index) => {
                let fieldsHtml = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-2">';

                if (line.fields) {
                    line.fields.forEach(field => {
                        let borderColor = 'border-slate-200 dark:border-slate-700';
                        let textColor = 'text-slate-600 dark:text-slate-300';
                        let hasRes = false;

                        if (field.resolution) {
                            borderColor = 'border-purple-400 bg-purple-50 dark:bg-purple-900/20';
                            textColor = 'text-purple-700 dark:text-purple-300 font-bold';
                            hasRes = true;
                            globalResolutions.push({
                                line: line.number,
                                field: field.name,
                                resolution: field.resolution,
                                value: field.value
                            });
                        } else if (field.name.includes('Valor')) {
                            textColor = 'text-emerald-600 dark:text-emerald-400 font-medium';
                        } else if (field.name.includes('Data') || field.name.includes('Vencimento')) {
                            textColor = 'text-blue-600 dark:text-blue-400';
                        }

                        fieldsHtml += `
                            <div class="p-1.5 rounded border ${borderColor} text-xs cursor-pointer hover:shadow-md transition-all relative group"
                                 onclick="highlightField('${index}', ${field.start}, ${field.end}, '${field.value}')">
                                 ${hasRes ? '<span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>' : ''}
                                <div class="text-[9px] uppercase font-bold text-slate-400 mb-0.5 truncate" title="${field.name}">${field.name}</div>
                                <div class="font-mono truncate ${textColor}" title="${field.value}">${field.value}</div>
                            </div>
                        `;
                    });
                }
                fieldsHtml += '</div>';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-4 py-4 w-12 align-top border-r border-slate-100 dark:border-slate-700">
                        <div class="w-7 h-7 rounded border flex items-center justify-center text-[10px] font-bold bg-slate-50 dark:bg-slate-700">${line.number}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="mb-3">
                            <div class="text-[9px] font-bold text-slate-400 uppercase mb-1">Linha Bruta</div>
                            <div id="raw-content-${index}" class="font-mono text-[10px] bg-slate-50 dark:bg-slate-900 p-2 rounded border border-slate-100 dark:border-slate-700 break-all text-slate-500">${line.content}</div>
                            
                            <!-- Ruler -->
                            <div id="ruler-${index}" class="hidden mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-100 dark:border-blue-800 flex gap-4 text-xs">
                                <div><span class="text-[9px] font-bold text-slate-400 block">Pos</span><span id="ruler-pos-${index}" class="font-mono font-bold text-primary">--</span></div>
                                <div><span class="text-[9px] font-bold text-slate-400 block">Val</span><span id="ruler-val-${index}" class="font-mono font-bold">--</span></div>
                            </div>
                        </div>
                        ${fieldsHtml}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update Resolution Button
            const btnRes = document.getElementById('btnShowCorrections');
            if (globalResolutions.length > 0) {
                btnRes.classList.remove('hidden');
                btnRes.innerHTML = `<span class="material-symbols-outlined text-[14px]">warning</span> Ver ${globalResolutions.length} Resoluções`;
            } else {
                btnRes.classList.add('hidden');
            }
        }

        function highlightField(index, start, end, value) {
            // Simplified highlight logic
            const rawDiv = document.getElementById(`raw-content-${index}`);
            const originalText = rawDiv.textContent; // assuming we haven't messed with it too much yet or we store it

            // Re-render raw content just to be safe if we want to handle multiple clicks properly, 
            // but for now let's just highlight the slice.
            // Note: start is 1-based.
            const sIdx = start - 1;
            const eIdx = end;

            const before = originalText.substring(0, sIdx);
            const match = originalText.substring(sIdx, eIdx);
            const after = originalText.substring(eIdx);

            rawDiv.innerHTML = `${before}<span class="bg-yellow-300 dark:bg-yellow-600 text-black dark:text-white font-bold px-0.5 rounded-sm">${match}</span>${after}`;

            // Show ruler
            document.getElementById(`ruler-${index}`).classList.remove('hidden');
            document.getElementById(`ruler-pos-${index}`).innerText = `${start}-${end}`;
            document.getElementById(`ruler-val-${index}`).innerText = value;
        }

        function openCorrectionModal() {
            const container = document.getElementById('correctionListContainer');
            container.innerHTML = '';

            globalResolutions.forEach(res => {
                const item = document.createElement('div');
                item.className = 'bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700';
                item.innerHTML = `
                    <div class="flex items-start gap-4">
                        <div class="p-2 bg-red-100 text-red-600 rounded-lg"><span class="material-symbols-outlined">error</span></div>
                        <div class="flex-grow">
                            <h4 class="font-bold text-sm text-slate-800 dark:text-white mb-1">Linha ${res.line}: ${res.resolution.ui_title}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">${res.resolution.ui_description}</p>
                            
                            <div class="bg-white dark:bg-slate-800 p-3 rounded border border-slate-100 dark:border-slate-700 text-xs">
                                <div class="font-bold text-slate-500 mb-1 uppercase text-[10px]">Ação Recomendada</div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><span class="text-slate-400">Tela:</span> <span class="font-medium dark:text-slate-200">${res.resolution.action_screen}</span></div>
                                    <div><span class="text-slate-400">Campo:</span> <span class="font-medium dark:text-slate-200">${res.resolution.action_field}</span></div>
                                </div>
                                <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-medium">
                                    ${res.resolution.action_instruction}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(item);
            });

            document.getElementById('correctionModal').classList.remove('hidden');
        }

        function closeCorrectionModal() {
            document.getElementById('correctionModal').classList.add('hidden');
        }
    </script>
</body>

</html>