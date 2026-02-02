# Deployment Package - Exclusões

## Pastas Excluídas do Deployment

As seguintes pastas **NÃO** são incluídas no pacote de deployment:

### 1. `font/` (83,932 arquivos)
- **Motivo**: Tamanho muito grande, não essencial para funcionamento
- **Impacto**: Nenhum - fontes são carregadas via CDN (Google Fonts)
- **Tamanho economizado**: ~500MB+

### 2. `_archive/` (2,126 arquivos)
- **Motivo**: Arquivos de desenvolvimento e histórico
- **Impacto**: Nenhum - apenas arquivos de referência
- **Tamanho economizado**: ~50MB+

### 3. Outras Exclusões
- `vendor/` - Será reinstalado via Composer no servidor
- `node_modules/` - Não utilizado
- `.git/` - Histórico de versão
- `.vscode/`, `.idea/` - Configurações de IDE
- `*.zip`, `*.tar.gz`, `*.sql`, `*.log` - Arquivos temporários

## Tamanho Estimado do Pacote

- **Sem exclusões**: ~550MB+
- **Com exclusões**: ~10-20MB
- **Redução**: ~97%

## Observações

Se alguma dessas pastas for necessária no servidor:
1. Elas podem ser copiadas separadamente
2. Ou removidas da lista de exclusão no script `create-deployment-package.ps1`

---

**Última atualização**: 27/01/2026
