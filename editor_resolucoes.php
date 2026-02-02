<?php
// editor_resolucoes.php
require_once 'includes/portal_auth.php';
require_once 'includes/portal_helpers.php';

// Authentication Check
// if (!isLoggedIn()) { header("Location: index.php"); exit; }
$currentUser = getCurrentUser() ?? 'Admin';

$dbPath = __DIR__ . '/database/utool_retorno.sqlite';
$pdo = new PDO("sqlite:" . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle Save
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_resolution') {
        $id = $_POST['resolution_id'];
        $title = $_POST['ui_title'];
        $desc = $_POST['ui_description'];
        $screen = $_POST['action_screen'];
        $field = $_POST['action_field'];
        $instruction = $_POST['action_instruction'];

        $stmt = $pdo->prepare("UPDATE uniplus_resolutions SET ui_title = ?, ui_description = ?, action_screen = ?, action_field = ?, action_instruction = ? WHERE id = ?");
        $stmt->execute([$title, $desc, $screen, $field, $instruction, $id]);
        $message = "Resolução atualizada com sucesso!";
    }
}

// Fetch Data
$items = $pdo->query("
    SELECT o.bank_code, o.cnab_type, o.occurrence_code, o.error_code, o.description as bank_desc, 
           r.id as res_id, r.ui_title, r.ui_description, r.action_screen, r.action_field, r.action_instruction
    FROM bank_occurrences o
    JOIN uniplus_resolutions r ON r.occurrence_id = o.id
    ORDER BY o.bank_code, o.occurrence_code
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Editor de Resoluções - CNAB</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { fontFamily: { display: ["Inter", "sans-serif"] } } }
        };
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 min-h-screen">

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <header class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <a href="index.php"
                    class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-full transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-2xl font-bold">Editor de Resoluções</h1>
                    <p class="text-sm text-slate-500">Gerencie as instruções de correção para erros de retorno bancário.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-mono bg-slate-200 dark:bg-slate-800 px-2 py-1 rounded">Base:
                    utool_retorno.sqlite</span>
            </div>
        </header>

        <?php if ($message): ?>
            <div
                class="bg-emerald-50 text-emerald-700 px-4 py-3 rounded-lg border border-emerald-200 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- List -->
        <div
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-slate-500">Banco / Ocorrência</th>
                            <th class="px-6 py-4 font-semibold text-slate-500">Título e Descrição (UniPlus)</th>
                            <th class="px-6 py-4 font-semibold text-slate-500">Ação Sugerida</th>
                            <th class="px-6 py-4 font-semibold text-slate-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 group">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            class="bg-blue-100 text-blue-800 text-[10px] font-bold px-1.5 py-0.5 rounded">Banco
                                            <?php echo $item['bank_code']; ?>
                                        </span>
                                        <span
                                            class="bg-slate-100 text-slate-600 text-[10px] font-bold px-1.5 py-0.5 rounded">CNAB
                                            <?php echo $item['cnab_type']; ?>
                                        </span>
                                    </div>
                                    <div class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Cód:
                                        <?php echo $item['occurrence_code']; ?>
                                        <?php if ($item['error_code']): ?> / Erro:
                                            <?php echo $item['error_code']; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-1">
                                        <?php echo $item['bank_desc']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top max-w-xs">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 mb-1">
                                        <?php echo $item['ui_title']; ?>
                                    </div>
                                    <div class="text-xs text-slate-500 line-clamp-2">
                                        <?php echo $item['ui_description']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top max-w-xs">
                                    <div class="flex items-center gap-2 text-xs mb-1">
                                        <span class="font-semibold text-purple-600">Tela:</span>
                                        <span>
                                            <?php echo $item['action_screen']; ?>
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 italic">"
                                        <?php echo $item['action_instruction']; ?>"
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-right">
                                    <button onclick="editResolution(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 hover:bg-blue-50 px-3 py-1.5 rounded transition-colors">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    Nenhuma resolução cadastrada ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST">
                    <input type="hidden" name="action" value="save_resolution">
                    <input type="hidden" name="resolution_id" id="resolution_id">

                    <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">
                            Editar Resolução</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título
                                    Amigável (UniPlus)</label>
                                <input type="text" name="ui_title" id="ui_title" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição
                                    Detalhada</label>
                                <textarea name="ui_description" id="ui_description" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600 sm:text-sm"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tela
                                        de Ação</label>
                                    <input type="text" name="action_screen" id="action_screen"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campo
                                        Específico</label>
                                    <input type="text" name="action_field" id="action_field"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600 sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instrução
                                    Passo a Passo</label>
                                <textarea name="action_instruction" id="action_instruction" rows="3" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600 sm:text-sm"></textarea>
                                <p class="text-xs text-slate-500 mt-1">Explique exatamente o que o usuário deve fazer
                                    para corrigir.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar Alterações
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editResolution(data) {
            document.getElementById('resolution_id').value = data.res_id;
            document.getElementById('ui_title').value = data.ui_title;
            document.getElementById('ui_description').value = data.ui_description;
            document.getElementById('action_screen').value = data.action_screen;
            document.getElementById('action_field').value = data.action_field;
            document.getElementById('action_instruction').value = data.action_instruction;

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>

</html>