# Utool Hub - Guia Rápido de Migração

## 📋 Resumo

Este guia fornece os passos essenciais para migrar o Utool Hub para o servidor Linux.

## 🚀 Início Rápido

### Passo 1: Preparar Pacote (Windows)

```powershell
cd c:\xampp\htdocs\utool

# Exportar banco de dados
.\export-database.ps1

# Criar pacote de deployment
.\create-deployment-package.ps1
```

**Resultado**: Dois arquivos criados:
- `utool-deployment-YYYYMMDD-HHMMSS.zip`
- `suporte_hub_export_YYYYMMDD-HHMMSS.sql`

### Passo 2: Upload para Servidor

```bash
# No Windows (PowerShell)
scp utool-deployment-*.zip user@SERVER_IP:/tmp/
scp suporte_hub_export_*.sql user@SERVER_IP:/tmp/
```

**Substitua**: `SERVER_IP` pelo IP do seu servidor

### Passo 3: Deployment no Servidor

```bash
# Conectar ao servidor
ssh user@SERVER_IP

# Ir para diretório temporário
cd /tmp

# Executar deployment rápido
chmod +x quick-deploy.sh
./quick-deploy.sh
```

O script irá:
- ✅ Extrair pacote para `/var/www/html/utool`
- ✅ Instalar dependências PHP
- ✅ Configurar permissões
- ✅ Importar banco de dados
- ✅ Criar arquivo `.env`

### Passo 4: Configurar Ambiente

```bash
sudo nano /var/www/html/utool/.env
```

**Edite estas linhas**:
```env
DB_HOST=localhost
DB_PASSWORD=SUA_SENHA_POSTGRES
APP_URL=http://SEU_IP/utool
```

### Passo 5: Configurar Web Server

#### Para Apache:

```bash
sudo cp /var/www/html/utool/docs/apache-vhost.conf /etc/apache2/sites-available/utool.conf
sudo a2ensite utool.conf
sudo a2enmod rewrite proxy_fcgi headers
sudo systemctl reload apache2
```

#### Para Nginx:

```bash
sudo cp /var/www/html/utool/docs/nginx-server-block.conf /etc/nginx/sites-available/utool
sudo ln -s /etc/nginx/sites-available/utool /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Passo 6: Testar

Abra no navegador:
```
http://SEU_IP/utool
```

## ✅ Checklist de Verificação

- [ ] Página inicial carrega sem erros
- [ ] Login funciona
- [ ] Widgets aparecem (senha técnica, clima, versão)
- [ ] Ferramentas principais abrem
- [ ] Dados de usuários foram migrados

## 🔧 Troubleshooting Rápido

### Erro de conexão com banco de dados
```bash
# Verificar se PostgreSQL está rodando
sudo systemctl status postgresql

# Testar conexão
sudo -u postgres psql -d suporte_hub -c "SELECT 1;"
```

### Erro 404 em todas as páginas
```bash
# Apache: Ativar mod_rewrite
sudo a2enmod rewrite
sudo systemctl reload apache2

# Nginx: Verificar configuração
sudo nginx -t
```

### Erro de permissões
```bash
cd /var/www/html/utool
sudo chown -R www-data:www-data .
sudo chmod -R 775 logs_uploaded uploads font
```

## 📚 Documentação Completa

Para informações detalhadas, consulte:
- **Guia Completo**: `docs/deployment-guide.md`
- **Configuração Apache**: `docs/apache-vhost.conf`
- **Configuração Nginx**: `docs/nginx-server-block.conf`

## 🆘 Suporte

Em caso de problemas:
1. Verifique os logs do servidor web
2. Verifique o arquivo `.env`
3. Consulte a documentação completa

---

**Tempo estimado**: 15-30 minutos
