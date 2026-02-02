import sys

# Read the file
with open(r'c:\xampp\htdocs\utool\xml_generator.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Find the line with "// --- DANFE PREVIEW ---"
insert_index = None
for i, line in enumerate(lines):
    if '// --- DANFE PREVIEW ---' in line:
        insert_index = i
        break

if insert_index is None:
    print("ERROR: Could not find insertion point")
    sys.exit(1)

# Credits Modal component
credits_modal = """        const CreditsModal = ({ isOpen, onClose }) => {
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

"""

# Insert the component before the DANFE PREVIEW comment
lines.insert(insert_index, credits_modal)

# Write back
with open(r'c:\xampp\htdocs\utool\xml_generator.php', 'w', encoding='utf-8') as f:
    f.writelines(lines)

print("SUCCESS: CreditsModal component inserted successfully")
