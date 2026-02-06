// ==UserScript==
// @name         Beemore Ticket Mobile/Popup
// @namespace    http://tampermonkey.net/
// @version      5.5.3
// @description  Melhora a criação de tickets no chat do Beemore com um popup flutuante, IA Groq e Macros
// @author       Antigravity
// @updateURL    http://10.1.15.204/utool/assets/user_scripts/beemore_ticket_popup.user.js
// @downloadURL  http://10.1.15.204/utool/assets/user_scripts/beemore_ticket_popup.user.js
// @match        https://app.beemore.com/*
// @grant        GM_addStyle
// @grant        GM_xmlhttpRequest
// @grant        GM_setValue
// @grant        GM_getValue
// @connect      api.groq.com
// @connect      *
// ==/UserScript==

(function () {
    'use strict';

    // --- Styles ---
    GM_addStyle(`
        #beemore-ticket-modal {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            pointer-events: none; z-index: 900; display: none;
            justify-content: center; align-items: center;
            font-family: 'Inter', sans-serif;
        }
        #beemore-ticket-modal.active { display: flex; }
        .btm-content {
            pointer-events: auto; background: #fff; width: 90%; max-width: 1100px;
            max-height: 95vh; display: flex; flex-direction: column;
            border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            border: 1px solid #e5e7eb; overflow: hidden;
        }
        .btm-header, .btm-footer {
            padding: 15px 20px; background: #fdfdfd; display: flex; align-items: center;
        }
        .btm-header { border-bottom: 1px solid #f3f4f6; justify-content: space-between; }
        .btm-footer { border-top: 1px solid #f3f4f6; justify-content: flex-start; }
        .btm-header h2 { margin: 0; font-size: 1.1rem; color: #1f2937; font-weight: 600; }
        .btm-controls { display: flex; gap: 10px; align-items: center; }
        .btm-icon-btn { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #9ca3af; transition: color 0.2s; }
        .btm-icon-btn:hover { color: #1f2937; }
        .btm-close:hover { color: #ef4444; }

        .btm-body { padding: 20px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 20px; }
        
        .btm-field { display: flex; flex-direction: column; gap: 5px; }
        .btm-field label {
            font-size: 0.75rem; font-weight: 600; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .btm-field input {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px;
            font-size: 1rem;
        }
        
        .btm-editor-container {
            border: 1px solid #d1d5db; border-radius: 8px;
            display: flex; flex-direction: column;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .btm-editor-container:focus-within {
            border-color: #7c3aed; ring: 2px solid rgba(124, 58, 237, 0.1);
        }
        .btm-toolbar {
            background: #f8fafc; padding: 8px 12px; border-bottom: 1px solid #e5e7eb;
            display: flex; gap: 6px; align-items: center; flex-wrap: wrap;
        }
        .btm-editor {
            padding: 15px; min-height: 400px; max-height: 60vh;
            overflow-y: auto; outline: none; line-height: 1.6; font-size: 1rem;
        }
        .btm-editor p { margin: 0 0 0.5em 0; }
        .btm-editor ul, .btm-editor ol { margin-left: 20px; }
        .btm-editor strong { font-weight: bold; }

        .btm-tool-btn {
            background: white; border: 1px solid #e5e7eb; border-radius: 4px;
            padding: 6px 10px; font-size: 0.9rem; cursor: pointer; color: #4b5563;
            display: flex; align-items: center; gap: 6px;
        }
        .btm-tool-btn:hover { background: #f3f4f6; color: #111; }
        .btm-tool-btn span.icon-proxy { font-size: 1.1rem; }
        .btm-tool-divider { width: 1px; height: 20px; background: #d1d5db; margin: 0 4px; }
        .btm-tool-btn:disabled { opacity: 0.6; cursor: wait; }

        .btm-btn-clear { background: none; border: none; color: #ef4444; font-size: 0.85rem; padding: 8px 12px; cursor: pointer; }
        .btm-btn-clear:hover { background: #fee2e2; border-radius: 6px; }

        #btm-trigger-btn {
            width: 32px; height: 32px; border-radius: 8px; 
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; background: transparent; 
            color: #6b7280; border: none; transition: all 0.2s;
        }
        #btm-trigger-btn:hover { background: #f3f4f6; color: #7c3aed; }
        #btm-trigger-btn span { font-size: 18px; line-height: 1; }

        /* Custom Dialog Styles */
        .btm-dialog-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.5); z-index: 9999; display: none;
            justify-content: center; align-items: center;
        }
        .btm-dialog-overlay.active { display: flex; }
        .btm-dialog {
            background: white; border-radius: 12px; padding: 24px;
            max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .btm-dialog-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 12px; color: #1f2937; }
        .btm-dialog-message { font-size: 0.95rem; color: #4b5563; margin-bottom: 20px; line-height: 1.5; }
        .btm-dialog-input {
            width: 100%; padding: 10px 12px; border: 1px solid #d1d5db;
            border-radius: 6px; font-size: 1rem; margin-bottom: 20px;
        }
        .btm-dialog-buttons {
            display: flex; gap: 10px; justify-content: flex-end;
        }
        .btm-dialog-btn {
            padding: 8px 16px; border-radius: 6px; font-size: 0.9rem;
            cursor: pointer; border: none; font-weight: 500; transition: all 0.2s;
        }
        .btm-dialog-btn-primary {
            background: #7c3aed; color: white;
        }
        .btm-dialog-btn-primary:hover { background: #6d28d9; }
        .btm-dialog-btn-secondary {
            background: #f3f4f6; color: #4b5563;
        }
        .btm-dialog-btn-secondary:hover { background: #e5e7eb; }
        .btm-dialog-btn-danger {
            background: #ef4444; color: white;
        }
        .btm-dialog-btn-danger:hover { background: #dc2626; }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .btm-content {
                background: #1f2937;
                border-color: #374151;
            }
            .btm-header, .btm-footer {
                background: #111827;
            }
            .btm-header h2 {
                color: #f9fafb;
            }
            .btm-header { border-bottom-color: #374151; }
            .btm-footer { border-top-color: #374151; }
            .btm-body { background: #1f2937; }
            
            .btm-field label { color: #9ca3af; }
            .btm-field input {
                background: #111827;
                border-color: #374151;
                color: #f9fafb;
            }
            .btm-field input:focus {
                border-color: #7c3aed;
                background: #1f2937;
            }
            
            .btm-editor-container {
                border-color: #374151;
                background: #111827;
            }
            .btm-toolbar {
                background: #111827;
                border-bottom-color: #374151;
            }
            .btm-editor {
                background: #1f2937;
                color: #f9fafb;
            }
            
            .btm-tool-btn {
                background: #374151;
                border-color: #4b5563;
                color: #d1d5db;
            }
            .btm-tool-btn:hover {
                background: #4b5563;
                color: #f9fafb;
            }
            .btm-tool-btn:disabled {
                opacity: 0.5;
                color: #6b7280;
            }
            
            .btm-btn-clear {
                color: #f87171;
            }
            .btm-btn-clear:hover {
                background: #7f1d1d;
            }
            
            #btm-trigger-btn:hover {
                background: #374151;
                color: #a78bfa;
            }
            
            .btm-dialog {
                background: #1f2937;
            }
            .btm-dialog-title {
                color: #f9fafb;
            }
            .btm-dialog-message {
                color: #d1d5db;
            }
            .btm-dialog-input {
                background: #111827;
                border-color: #374151;
                color: #f9fafb;
            }
            .btm-dialog-btn-secondary {
                background: #374151;
                color: #d1d5db;
            }
            .btm-dialog-btn-secondary:hover {
                background: #4b5563;
            }
        }
        /* Reuse Dark Mode styles for .dark class (Beemore uses html.dark) */
        html.dark .btm-content, body.dark .btm-content, 
        .dark .btm-content {
            background: #1f2937;
            border-color: #374151;
        }
        html.dark .btm-header, body.dark .btm-header,
        html.dark .btm-footer, body.dark .btm-footer {
            background: #111827;
        }
        html.dark .btm-header h2, body.dark .btm-header h2 {
            color: #f9fafb;
        }
        html.dark .btm-header, body.dark .btm-header { border-bottom-color: #374151; }
        html.dark .btm-footer, body.dark .btm-footer { border-top-color: #374151; }
        html.dark .btm-body, body.dark .btm-body { background: #1f2937; }
        
        html.dark .btm-field label, body.dark .btm-field label { color: #9ca3af; }
        html.dark .btm-field input, body.dark .btm-field input {
            background: #111827;
            border-color: #374151;
            color: #f9fafb;
        }
        html.dark .btm-field input:focus {
            border-color: #7c3aed;
            background: #1f2937;
        }
        
        html.dark .btm-editor-container, body.dark .btm-editor-container {
            border-color: #374151;
            background: #111827;
        }
        html.dark .btm-toolbar, body.dark .btm-toolbar {
            background: #111827;
            border-bottom-color: #374151;
        }
        html.dark .btm-editor, body.dark .btm-editor {
            background: #1f2937;
            color: #f9fafb;
        }
        
        html.dark .btm-tool-btn, body.dark .btm-tool-btn {
            background: #374151;
            border-color: #4b5563;
            color: #d1d5db;
        }
        html.dark .btm-tool-btn:hover {
            background: #4b5563;
            color: #f9fafb;
        }
        html.dark .btm-tool-btn:disabled {
            opacity: 0.5;
            color: #6b7280;
        }
        
        html.dark .btm-btn-clear, body.dark .btm-btn-clear {
            color: #f87171;
        }
        html.dark .btm-btn-clear:hover {
            background: #7f1d1d;
        }
        
        html.dark #btm-trigger-btn:hover {
            background: #374151;
            color: #a78bfa;
        }
        
        html.dark .btm-dialog, body.dark .btm-dialog {
            background: #1f2937;
        }
        html.dark .btm-dialog-title, body.dark .btm-dialog-title {
            color: #f9fafb;
        }
        html.dark .btm-dialog-message, body.dark .btm-dialog-message {
            color: #d1d5db;
        }
        html.dark .btm-dialog-input, body.dark .btm-dialog-input {
            background: #111827;
            border-color: #374151;
            color: #f9fafb;
        }
        html.dark .btm-dialog-btn-secondary, body.dark .btm-dialog-btn-secondary {
            background: #374151;
            color: #d1d5db;
        }
        html.dark .btm-dialog-btn-secondary:hover {
            background: #4b5563;
        }
    `);

    // --- HTML Template ---
    const modalHTML = `
        <div id="beemore-ticket-modal">
            <div class="btm-content">
                <div class="btm-header">
                    <h2>Novo Ticket</h2>
                    <div class="btm-controls">
                        <!-- Config button removed for security -->
                        <button class="btm-icon-btn btm-close" title="Fechar">&times;</button>
                    </div>
                </div>
                <div class="btm-body">
                    <!-- Fields go here -->
                </div>
                <div class="btm-footer">
                    <button class="btm-btn btm-btn-clear" title="Limpar campos">🗑️ Limpar Tudo</button>
                </div>
            </div>
        </div>
    `;

    if (!document.getElementById('beemore-ticket-modal')) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    let triggerBtn = document.getElementById('btm-trigger-btn');
    if (!triggerBtn) {
        triggerBtn = document.createElement('button');
        triggerBtn.id = 'btm-trigger-btn';
        triggerBtn.title = 'Expandir Ticket';
        triggerBtn.innerHTML = '<span>&#x26F6;</span>';
        triggerBtn.className = 'flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100';
    }

    const modal = document.getElementById('beemore-ticket-modal');
    const closeBtn = modal.querySelector('.btm-close');
    const clearBtn = modal.querySelector('.btm-btn-clear');
    const modalBody = modal.querySelector('.btm-body');

    // --- Gemini AI Logic ---

    // --- Custom Dialog Functions ---
    function showDialog(title, message, type = 'alert', defaultValue = '') {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'btm-dialog-overlay active';

            const dialog = document.createElement('div');
            dialog.className = 'btm-dialog';

            const titleEl = document.createElement('div');
            titleEl.className = 'btm-dialog-title';
            titleEl.textContent = title;
            dialog.appendChild(titleEl);

            const messageEl = document.createElement('div');
            messageEl.className = 'btm-dialog-message';
            messageEl.textContent = message;
            dialog.appendChild(messageEl);

            let inputEl;
            if (type === 'prompt') {
                inputEl = document.createElement('input');
                inputEl.className = 'btm-dialog-input';
                inputEl.value = defaultValue;
                inputEl.placeholder = 'Digite aqui...';
                dialog.appendChild(inputEl);
            }

            const buttons = document.createElement('div');
            buttons.className = 'btm-dialog-buttons';

            if (type === 'confirm' || type === 'prompt') {
                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'btm-dialog-btn btm-dialog-btn-secondary';
                cancelBtn.textContent = 'Cancelar';
                cancelBtn.onclick = () => {
                    overlay.remove();
                    resolve(type === 'prompt' ? null : false);
                };
                buttons.appendChild(cancelBtn);
            }

            const okBtn = document.createElement('button');
            okBtn.className = `btm-dialog-btn ${type === 'confirm' ? 'btm-dialog-btn-danger' : 'btm-dialog-btn-primary'}`;
            okBtn.textContent = type === 'confirm' ? 'Confirmar' : 'OK';
            okBtn.onclick = () => {
                overlay.remove();
                if (type === 'prompt') {
                    resolve(inputEl.value);
                } else if (type === 'confirm') {
                    resolve(true);
                } else {
                    resolve(true);
                }
            };
            buttons.appendChild(okBtn);

            dialog.appendChild(buttons);
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);

            if (inputEl) {
                inputEl.focus();
                inputEl.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') okBtn.click();
                });
            }
        });
    }

    async function getApiKey() {
        // --- CONFIGURAÇÃO ---
        // Para gerar sua chave mascarada:
        // 1. Abra o console do navegador (F12)
        // 2. Digite: btoa("sua-chave-groq-aqui")
        // 3. Copie o resultado e cole abaixo
        const MASKED_KEY = "Z3NrX2FQR2RIVlppQmJhTExPZ0xRVjhOV0dkeWIzRllORDA0b2ZkMXJqdDJYR2RlYnpvWjBzczc="; // Cole sua chave em Base64 aqui

        // --- UTILS ---
        try {
            // Decodifica a chave mascarada
            return atob(MASKED_KEY);
        } catch (e) {
            console.error("Erro ao decodificar chave API:", e);
            return "";
        }
    }

    async function fetchGroqSummary(chatText, currentTemplate = '') {
        console.log('[BTM Debug] fetchGroqSummary called');
        console.log('[BTM Debug] Chat text length:', chatText.length);
        console.log('[BTM Debug] Template length:', currentTemplate.length);

        const apiKey = await getApiKey();
        console.log('[BTM Debug] API key for request:', apiKey ? `${apiKey.substring(0, 15)}...` : '(empty)');

        if (!apiKey || apiKey.includes("Z3NrX")) {
            throw new Error("API Key não configurada no código. Contate o administrador.");
        }

        // Detect if we have a template to fill (> 50 chars indicates a macro template)
        const isTemplate = currentTemplate.trim().length > 50;

        let promptText;
        if (isTemplate) {
            // Template filling mode
            promptText = `
                Você é um assistente especializado em suporte técnico.
                Você recebeu um TEMPLATE DE MACRO de suporte e um HISTÓRICO DE CHAT.
                
                Sua tarefa é PREENCHER os campos vazios deste template com as informações extraídas do chat.
                
                REGRAS IMPORTANTES:
                1. Mantenha TODOS os nomes dos tópicos e a formatação original (negritos, listas, etc.)
                2. Preencha APENAS os campos que estão vazios, com "-", ou com placeholder
                3. NÃO adicione novos tópicos, apenas preencha os existentes
                4. Mantenha o HTML original do template
                5. Seja objetivo e técnico
                
                CAMPOS COMUNS E COMO PREENCHÊ-LOS:
                
                - "Situação:" ou "Problema:" → Descreva o problema técnico relatado (ex: "Erro ao gerar XML", "Sistema travando", "Nota fiscal rejeitada")
                
                - "Informações para Simulação:" → Liste dados necessários para reproduzir (ex: "Cliente: João Silva, Produto: 001, Data: 04/02/2026")
                  Se não houver dados específicos, deixe "-"
                
                - "Imagens com configurações/erro:" → Se o chat mencionar prints/imagens enviadas, escreva "Anexadas no chat"
                  Se não houver menção, deixe "-"
                
                - "Ações Realizadas:" → Liste testes/verificações feitas (ex: "Verificado cadastro", "Testado em outro computador")
                
                - "Status/Conclusão:" → Estado atual (ex: "Aguardando retorno do cliente", "Resolvido", "Encaminhado para TI")
                
                EXEMPLO DE PREENCHIMENTO:
                Antes: "Situação: -"
                Depois: "Situação: Erro ao emitir NF-e com CFOP 5102"
                
                TEMPLATE A PREENCHER:
                ${currentTemplate}
                
                HISTÓRICO DO CHAT:
                ${chatText}
                
                Retorne APENAS o template preenchido em HTML, sem explicações adicionais.
                Se um campo não tiver informação no chat, mantenha "-" ou deixe em branco.
            `;
        } else {
            // Summary generation mode (original behavior)
            promptText = `
                Aja como um analista de suporte especialista.
                Analise o seguinte histórico de chat de atendimento.
                Gere um resumo executivo claro e conciso.
                Remova saudações genéricas, mensagens automáticas de sistema (como "encerrado por inatividade", "entrou na conversa") e foque no conteúdo técnico.
                
                O resumo deve ter estritamente estas 3 seções em HTML:
                <p><strong>Problema Relatado:</strong> [Descrição CURTA e OBJETIVA do problema - máximo 2 linhas]</p>
                <p><strong>Ações Realizadas:</strong> [Lista RESUMIDA de ações principais - máximo 3-4 itens]</p>
                <p><strong>Status/Conclusão:</strong> [Estado atual em 1 linha]</p>
                
                IMPORTANTE: Seja EXTREMAMENTE CONCISO. Evite detalhes desnecessários.

                Histórico do Chat:
                ${chatText}
            `;
        }

        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: "POST",
                url: "https://api.groq.com/openai/v1/chat/completions",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${apiKey}`
                },
                data: JSON.stringify({
                    model: "llama-3.3-70b-versatile",
                    messages: [
                        {
                            role: "user",
                            content: promptText
                        }
                    ],
                    temperature: 0.7,
                    max_tokens: 2048
                }),
                onload: function (response) {
                    if (response.status !== 200) {
                        console.error('[BTM Debug] Resposta Completa:', response.responseText);
                        try {
                            const errData = JSON.parse(response.responseText || "{}");
                            const msg = errData.error?.message || response.statusText;
                            reject(`Erro na API (${response.status}): ${msg}`);
                        } catch (e) {
                            reject(`Erro na API Groq (${response.status}). Verifique o console (F12) para detalhes.`);
                        }
                        return;
                    }
                    try {
                        const data = JSON.parse(response.responseText);

                        // Groq uses OpenAI format: data.choices[0].message.content
                        if (!data.choices || !data.choices[0] || !data.choices[0].message) {
                            console.warn('[BTM Debug] Groq - Resposta inválida:', data);
                            reject("A IA não retornou conteúdo válido. Verifique sua chave de API.");
                            return;
                        }

                        let result = data.choices[0].message.content;
                        // Improved markdown cleaning
                        result = result.replace(/```html|```/gi, '').trim();
                        console.log('[BTM Debug] Groq - Resposta recebida com sucesso');
                        resolve(result);
                    } catch (e) {
                        console.error('[BTM Debug] Erro ao processar resposta:', e);
                        reject("Erro ao processar resposta da IA: " + e.message);
                    }
                },
                onerror: function (err) {
                    console.error('[BTM Debug] GM_xmlhttpRequest onerror triggered:', err);
                    console.error('[BTM Debug] Error details:', JSON.stringify(err));
                    reject("Erro de conexão com o servidor Groq. Verifique se o Tampermonkey tem permissão para acessar api.groq.com");
                },
                ontimeout: function () {
                    console.error('[BTM Debug] Request timeout');
                    reject("Timeout na requisição para Groq API");
                },
                timeout: 30000  // 30 seconds
            });
        });
    }

    function scrapChatText() {
        let msgElements = Array.from(document.querySelectorAll('app-chat-message, .message-container, div[class*="message"]'));

        // Feature: Filter history starting from "Atendimento atual" separator
        if (msgElements.length > 0) {
            // Find shared parent (container) to look for the separator
            const parent = msgElements[0].parentElement;
            if (parent) {
                const siblings = Array.from(parent.children);
                // Find any element that contains exactly "Atendimento atual" (or close to it)
                const separator = siblings.find(el => el.innerText && el.innerText.includes('Atendimento atual'));

                if (separator) {
                    console.log('[BTM Debug] Separator "Atendimento atual" found. Filtering history...');
                    // Filter messages that appear AFTER the separator in the DOM
                    msgElements = msgElements.filter(msg => {
                        return (separator.compareDocumentPosition(msg) & Node.DOCUMENT_POSITION_FOLLOWING);
                    });
                }
            }
        }

        let fullText = "";

        msgElements.forEach(msg => {
            const author = msg.querySelector('.author-name, span.font-medium, .name')?.innerText || 'Sistema';
            const timeEl = msg.querySelector('.message-time, span.text-12, .time');
            const time = timeEl ? timeEl.innerText.trim() : '';
            const textEl = msg.querySelector('.message-body, .text-editor p, .content');
            let text = textEl ? textEl.innerText.trim() : msg.innerText.trim();
            if (text.includes("entrou na conversa") || text.includes("encerrado automaticamente")) return;

            text = text.replace(time, '').replace(author, '').trim();
            if (text.length > 0) {
                const timeStr = time ? `[${time}]` : '';
                fullText += `${timeStr} ${author}: ${text}\n`;
            }
        });
        return fullText;
    }

    // --- Helpers ---

    function triggerInputEvent(element) {
        if (!element) return;
        // Key fix: Trigger all these events to satisfy Angular/React frameworks
        ['focus', 'input', 'change', 'blur'].forEach(evt =>
            element.dispatchEvent(new Event(evt, { bubbles: true }))
        );
    }

    function findBeemoreField(labelText) {
        // 1. Generic Label Search
        const labels = Array.from(document.querySelectorAll('label'));
        let bestLabel = labels.find(l => {
            const existText = l.innerText.trim().replace(/\s?\*$/, '');
            return existText === labelText && !l.closest('#beemore-ticket-modal');
        });

        // 2. Fallback for Title in Sidebar specifically
        if (!bestLabel && labelText === 'Título') {
            const sidebar = document.querySelector('section.p-4.overflow-y-auto');
            if (sidebar) {
                const sl = Array.from(sidebar.querySelectorAll('label')).find(l => l.innerText.includes('Título'));
                if (sl) bestLabel = sl;
            }
        }

        if (!bestLabel) return null;

        // 3. Try direct container first
        let container = bestLabel.parentElement;
        if (container) {
            let directInput = container.querySelector('input, textarea, [contenteditable="true"]');
            if (directInput) return directInput;
        }

        // 4. Try siblings
        let sibling = bestLabel.nextElementSibling;
        while (sibling) {
            if (sibling.tagName === 'INPUT' || sibling.tagName === 'TEXTAREA') return sibling;
            let nested = sibling.querySelector('input, textarea, [contenteditable="true"]');
            if (nested) return nested;
            sibling = sibling.nextElementSibling;
            if (!sibling || sibling.tagName === 'LABEL') break;
        }

        // 5. NEW: Traverse up the DOM tree (for app-label wrappers)
        let current = bestLabel.parentElement;
        while (current && current.tagName !== 'SECTION' && current.tagName !== 'BODY') {
            // Check siblings of the parent
            let parentSibling = current.nextElementSibling;
            while (parentSibling) {
                if (parentSibling.tagName === 'INPUT' || parentSibling.tagName === 'TEXTAREA') return parentSibling;
                let nested = parentSibling.querySelector('input, textarea, [contenteditable="true"]');
                if (nested) return nested;
                parentSibling = parentSibling.nextElementSibling;
                if (!parentSibling || parentSibling.tagName === 'LABEL') break;
            }
            current = current.parentElement;
        }

        return null;
    }

    function clickSidebarProxy(keyword) {
        if (keyword === 'macro') {
            const macroBtn = document.getElementById('btnMacro') || document.querySelector('app-button[icon="tablerBolt"]');
            if (macroBtn) {
                macroBtn.click();
                return true;
            }
            return false;
        }

        const map = {
            'image': ['M15 8h', 'image', 'photo', 'picture'],
            'attach': ['tablerPaperclip', 'btnAttachment', 'paperclip'],
            'voice': ['microphone', 'M12 1a3'],
            'emoji': ['mood-smile', 'moodsmile', 'M12 8v4']
        };
        const config = map[keyword];
        if (!config) return false;

        const allButtons = Array.from(document.querySelectorAll('button, app-button'));
        const target = allButtons.find(btn => {
            // Check ID (case-insensitive)
            if (btn.id && config.some(k => btn.id.toLowerCase().includes(k.toLowerCase()))) return true;

            // Check icon attribute (case-insensitive)
            const iconAttr = btn.getAttribute('icon');
            if (iconAttr && config.some(k => iconAttr.toLowerCase().includes(k.toLowerCase()))) return true;

            // Check SVG path
            const svgPath = btn.querySelector('path');
            if (svgPath) {
                const d = svgPath.getAttribute('d');
                if (d && config.some(k => d.includes(k))) return true;
            }
            return false;
        });

        if (target) { target.click(); return true; }
        return false;
    }

    // --- Modal Logic ---
    function openModal() {
        modalBody.innerHTML = '';

        const titleInput = findBeemoreField('Título');
        if (titleInput) {
            const wrapper = document.createElement('div');
            wrapper.className = 'btm-field';
            const label = document.createElement('label');
            label.innerText = 'Título';
            wrapper.appendChild(label);

            const input = document.createElement('input');
            input.type = 'text';
            input.value = titleInput.value || '';
            input.addEventListener('input', () => {
                titleInput.value = input.value;
                triggerInputEvent(titleInput);
            });

            // Bidirectional sync: native -> popup
            const titleObserver = new MutationObserver(() => {
                if (input.value !== titleInput.value) {
                    input.value = titleInput.value;
                }
            });
            titleObserver.observe(titleInput, { attributes: true, attributeFilter: ['value'] });
            titleInput.addEventListener('input', () => {
                if (input.value !== titleInput.value) {
                    input.value = titleInput.value;
                }
            });

            wrapper.appendChild(input);
            modalBody.appendChild(wrapper);
        }

        const descInput = findBeemoreField('Descrição');
        if (descInput) {
            const wrapper = document.createElement('div');
            wrapper.className = 'btm-field';

            const editorContainer = document.createElement('div');
            editorContainer.className = 'btm-editor-container';
            const toolbar = document.createElement('div');
            toolbar.className = 'btm-toolbar';

            const createNativeBtn = (label, icon, cmd) => {
                const btn = document.createElement('button');
                btn.className = 'btm-tool-btn';
                btn.innerHTML = `${icon} ${label}`;
                btn.onmousedown = (e) => {
                    e.preventDefault();
                    document.execCommand(cmd, false, null);
                };
                return btn;
            };

            const createProxyBtn = (label, icon, key) => {
                const btn = document.createElement('button');
                btn.className = 'btm-tool-btn';
                btn.innerHTML = `<span class="icon-proxy">${icon}</span> ${label}`;
                btn.onclick = (e) => {
                    e.preventDefault();
                    if (clickSidebarProxy(key)) {
                        btn.style.borderColor = '#22c55e';
                        setTimeout(() => btn.style.borderColor = '#e5e7eb', 400);
                    } else {
                        btn.style.borderColor = '#ef4444';
                        setTimeout(() => btn.style.borderColor = '#e5e7eb', 400);
                    }
                };
                return btn;
            };

            const btnSum = document.createElement('button');
            btnSum.className = 'btm-tool-btn';
            btnSum.innerHTML = '<span>🤖</span> Criar resumo (com IA)';
            btnSum.onclick = async (e) => {
                e.preventDefault();
                const chatText = scrapChatText();
                if (chatText.length < 5) {
                    await showDialog('Aviso', 'Chat vazio. Não há mensagens para resumir.', 'alert');
                    return;
                }

                btnSum.innerText = '⏳ Gerando...';
                btnSum.disabled = true;

                try {
                    // Capture current editor content (template detection)
                    const editor = document.getElementById('btm-editor-div');
                    const currentTemplate = editor ? editor.innerHTML : '';
                    const isTemplate = currentTemplate.trim().length > 50;

                    // Call AI with template context
                    const summaryHtml = await fetchGroqSummary(chatText, currentTemplate);

                    if (editor) {
                        if (isTemplate) {
                            // Template mode: REPLACE entire content
                            editor.innerHTML = summaryHtml;
                        } else {
                            // Summary mode: PREPEND to existing content
                            editor.innerHTML = summaryHtml + "<br/><hr/><br/>" + editor.innerHTML;
                        }

                        // Sync with native Beemore field
                        const src = editor;
                        if (descInput.value !== undefined) descInput.value = src.innerText;
                        else descInput.innerHTML = src.innerHTML;
                        triggerInputEvent(descInput);
                    }
                } catch (err) {
                    await showDialog('Erro', err.message || 'Erro ao gerar resumo', 'alert');
                } finally {
                    btnSum.innerHTML = '<span>🤖</span> IA Resumo';
                    btnSum.disabled = false;
                }
            };
            toolbar.appendChild(btnSum);

            const divider = document.createElement('div');
            divider.className = 'btm-tool-divider';
            toolbar.appendChild(divider);

            toolbar.appendChild(createNativeBtn('', '<b>B</b>', 'bold'));
            toolbar.appendChild(createNativeBtn('', '<i>I</i>', 'italic'));
            toolbar.appendChild(createNativeBtn('', '<u>U</u>', 'underline'));
            toolbar.appendChild(createNativeBtn('', '•', 'insertUnorderedList'));

            const divider2 = document.createElement('div');
            divider2.className = 'btm-tool-divider';
            toolbar.appendChild(divider2);

            // Removed image button as requested
            toolbar.appendChild(createProxyBtn('', '📎', 'attach'));
            toolbar.appendChild(createProxyBtn('', '🎤', 'voice'));
            toolbar.appendChild(createProxyBtn('', '😀', 'emoji'));

            const divider3 = document.createElement('div');
            divider3.className = 'btm-tool-divider';
            toolbar.appendChild(divider3);

            toolbar.appendChild(createProxyBtn('Macros', '⚡', 'macro'));

            editorContainer.appendChild(toolbar);

            const editor = document.createElement('div');
            editor.id = 'btm-editor-div';
            editor.contentEditable = true;
            editor.className = 'btm-editor';

            if (descInput.innerHTML && descInput.innerHTML.length > 5 && descInput.tagName !== 'TEXTAREA') {
                editor.innerHTML = descInput.innerHTML;
            } else if (descInput.value) {
                editor.innerText = descInput.value;
            } else {
                editor.innerHTML = '<p><br/></p>';
            }

            // Track if user is actively editing to prevent observer interference
            let isUserEditing = false;

            // Allow Enter key to create line breaks
            editor.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    isUserEditing = true;
                    // Use insertParagraph instead of insertLineBreak for better behavior
                    document.execCommand('insertParagraph');
                    // Allow time for command to execute before re-enabling observer
                    setTimeout(() => { isUserEditing = false; }, 100);
                }
            });

            // Enable image paste functionality
            // Enable image paste functionality using CAPTURE phase to detect events early
            // Enable image paste functionality using CAPTURE phase to detect events early
            editor.addEventListener('paste', (e) => {
                console.log('[BTM Paste] Event triggered on editor (Capture Phase)', e);

                const items = e.clipboardData?.items;
                if (!items) return;

                // Check if clipboard contains image
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        console.log('[BTM Paste] ✅ Image found!');
                        e.preventDefault();
                        e.stopPropagation(); // Stop others from handling it

                        const blob = items[i].getAsFile();
                        const reader = new FileReader();

                        reader.onload = (event) => {
                            console.log('[BTM Paste] Image loaded, inserting locally...');
                            const img = document.createElement('img');
                            img.src = event.target.result;
                            img.style.maxWidth = '100%';
                            img.style.height = 'auto';

                            // Insert image at cursor position locally for immediate feedback
                            const selection = window.getSelection();
                            if (selection.rangeCount > 0 && editor.contains(selection.anchorNode)) {
                                const range = selection.getRangeAt(0);
                                range.deleteContents();
                                range.insertNode(img);

                                // Move cursor after image
                                range.setStartAfter(img);
                                range.setEndAfter(img);
                                selection.removeAllRanges();
                                selection.addRange(range);
                            } else {
                                editor.appendChild(img);
                            }

                            console.log('[BTM Paste] ✅ Local insert done. Dispatching to NATIVE editor...');

                            // DISPATCH TO NATIVE EDITOR
                            // Instead of syncing innerHTML (which Tiptap might sanitize/strip),
                            // we send a simulated Paste event so Tiptap handles the file insertion naturally.
                            try {
                                const nativeDT = new DataTransfer();
                                nativeDT.items.add(blob);

                                const nativePaste = new ClipboardEvent('paste', {
                                    clipboardData: nativeDT,
                                    bubbles: true,
                                    cancelable: true,
                                    view: window
                                });

                                descInput.dispatchEvent(nativePaste);
                                console.log('[BTM Paste] 📤 Native paste dispatched successfully!');
                            } catch (err) {
                                console.error('[BTM Paste] ❌ Error dispatching native paste:', err);
                                // Fallback: try direct sync if event fails
                                syncDescription(editor, descInput);
                            }
                        };

                        reader.readAsDataURL(blob);
                        break;
                    }
                }
            }, true); // Use capture phase!

            const syncDescription = (src, dest) => {
                const val = (dest.tagName === 'TEXTAREA' || dest.tagName === 'INPUT') ? src.innerText : src.innerHTML;
                if (dest.value !== undefined) dest.value = val;
                else dest.innerHTML = val;
                triggerInputEvent(dest);
            };

            editor.addEventListener('input', () => {
                isUserEditing = true;
                syncDescription(editor, descInput);
                setTimeout(() => { isUserEditing = false; }, 100);
            });
            editor.addEventListener('blur', () => syncDescription(editor, descInput));

            // Bidirectional sync: native -> popup (for macros)
            const descObserver = new MutationObserver(() => {
                // Don't sync if user is actively editing to prevent cursor jump
                if (isUserEditing) return;

                const currentHtml = descInput.innerHTML || '';
                if (editor.innerHTML !== currentHtml && currentHtml.length > 0) {
                    editor.innerHTML = currentHtml;
                }
            });
            descObserver.observe(descInput, {
                childList: true,
                subtree: true,
                characterData: true
            });

            // Also listen to input events on the native field
            descInput.addEventListener('input', () => {
                const currentHtml = descInput.innerHTML || '';
                if (editor.innerHTML !== currentHtml && currentHtml.length > 0) {
                    editor.innerHTML = currentHtml;
                }
            });

            editorContainer.appendChild(editor);
            wrapper.appendChild(editorContainer);
            modalBody.appendChild(wrapper);
        }

        modal.classList.add('active');
    }

    function closeModal() { modal.classList.remove('active'); }

    async function clearAll() {
        const confirmed = await showDialog(
            'Confirmar Limpeza',
            'Tem certeza que deseja limpar todos os campos?',
            'confirm'
        );

        if (confirmed) {
            // Clear modal title input
            const t = document.querySelector('.btm-field input');
            if (t) {
                t.value = '';
                t.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // Clear modal editor
            const ed = document.getElementById('btm-editor-div');
            if (ed) {
                ed.innerHTML = '<p><br></p>';
                ed.dispatchEvent(new Event('input', { bubbles: true }));
                ed.blur();
            }

            // Clear Beemore sidebar fields
            const title = findBeemoreField('Título');
            if (title) {
                title.value = '';
                title.focus();
                triggerInputEvent(title);
            }

            const desc = findBeemoreField('Descrição');
            if (desc) {
                // For TipTap editor, we need to clear it properly
                if (desc.contentEditable === 'true') {
                    desc.innerHTML = '<p></p>';
                    desc.focus();
                    // Simulate typing to trigger TipTap's internal state
                    const event = new InputEvent('input', {
                        bubbles: true,
                        cancelable: true,
                        inputType: 'deleteContentBackward'
                    });
                    desc.dispatchEvent(event);
                } else if (desc.value !== undefined) {
                    desc.value = '';
                } else {
                    desc.innerHTML = '';
                }
                triggerInputEvent(desc);
            }
        }
    }

    triggerBtn.onclick = openModal;
    closeBtn.onclick = closeModal;
    // Config button removed
    clearBtn.onclick = clearAll;

    setInterval(() => {
        const titleInput = findBeemoreField('Título');
        if (titleInput) {
            injectTriggerButton();
        } else {
            if (document.body.contains(triggerBtn)) {
                triggerBtn.remove();
            }
            // Auto-close modal if context lost
            if (modal && modal.classList.contains('active')) {
                closeModal();
            }
        }
    }, 500);

    function injectTriggerButton() {
        // Find the Macros button
        const macroBtn = document.getElementById('btnMacro');
        if (!macroBtn) {
            // Fallback: hide button if Macros button not found
            if (triggerBtn.parentElement) {
                triggerBtn.remove();
            }
            return;
        }

        // Get the header container (parent of Macros button)
        const header = macroBtn.closest('header');
        if (!header) return;

        // Insert our button right after the Macros button
        if (triggerBtn.parentElement !== header) {
            // Insert after Macros button
            if (macroBtn.nextSibling) {
                header.insertBefore(triggerBtn, macroBtn.nextSibling);
            } else {
                header.appendChild(triggerBtn);
            }
        }
    }
})();
