# Utool Hub - Guia de Deployment

## Visão Geral

Este guia detalha o processo completo de deployment do Utool Hub para um servidor Linux.

## Pré-requisitos

### Servidor
- Ubuntu 20.04 LTS ou superior
- Acesso SSH com sudo
- Mínimo 2GB RAM, 10GB disco

### Software Necessário
- PostgreSQL 12+
- Apache 2.4+ ou Nginx 1.18+
- PHP 8.0+
- Composer

## Processo de Deployment

### 1. Preparação Local (Windows)

#### 1.1 Exportar Banco de Dados

```powershell
cd c:\xampp\htdocs\utool
.\export-database.ps1
```

Isso criará um arquivo `suporte_hub_export_YYYYMMDD-HHMMSS.sql`.

#### 1.2 Criar Pacote de Deployment

```powershell
.\create-deployment-package.ps1
```

Isso criará um arquivo `utool-deployment-YYYYMMDD-HHMMSS.zip`.

### 2. Upload para o Servidor

#### Via SCP (recomendado)

```bash
# No Windows (PowerShell)
scp utool-deployment-*.zip user@server-ip:/tmp/
scp suporte_hub_export_*.sql user@server-ip:/tmp/
```

#### Via SFTP

Use um cliente SFTP como FileZilla ou WinSCP para transferir os arquivos.

### 3. Instalação no Servidor

#### 3.1 Conectar ao Servidor

```bash
ssh user@server-ip
```

#### 3.2 Extrair Pacote

```bash
# Criar diretório de destino
sudo mkdir -p /var/www/html/utool
sudo chown $USER:$USER /var/www/html/utool

# Extrair pacote
cd /var/www/html/utool
unzip /tmp/utool-deployment-*.zip
```

#### 3.3 Executar Setup

```bash
chmod +x setup.sh
./setup.sh
```

#### 3.4 Configurar Ambiente

```bash
nano .env
```

Edite as seguintes variáveis:

```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=suporte_hub
DB_USER=postgres
DB_PASSWORD=SUA_SENHA_AQUI
APP_URL=http://SEU_IP/utool
```

#### 3.5 Importar Banco de Dados

```bash
# Copiar arquivo SQL
sudo -u postgres psql -f /tmp/suporte_hub_export_*.sql

# Ou, se precisar criar o banco primeiro:
sudo -u postgres createdb suporte_hub
sudo -u postgres psql -d suporte_hub -f /tmp/suporte_hub_export_*.sql
```

### 4. Configuração do Servidor Web

#### Opção A: Apache

```bash
sudo nano /etc/apache2/sites-available/utool.conf
```

Cole a configuração:

```apache
<VirtualHost *:80>
    ServerName utool.intelidata.local
    DocumentRoot /var/www/html/utool
    
    <Directory /var/www/html/utool>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # PHP Configuration
        <FilesMatch \.php$>
            SetHandler "proxy:unix:/var/run/php/php8.0-fpm.sock|fcgi://localhost"
        </FilesMatch>
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/utool-error.log
    CustomLog ${APACHE_LOG_DIR}/utool-access.log combined
</VirtualHost>
```

Ativar site:

```bash
sudo a2ensite utool.conf
sudo a2enmod rewrite proxy_fcgi
sudo systemctl reload apache2
```

#### Opção B: Nginx

```bash
sudo nano /etc/nginx/sites-available/utool
```

Cole a configuração:

```nginx
server {
    listen 80;
    server_name utool.intelidata.local;
    root /var/www/html/utool;
    index index.php index.html;
    
    access_log /var/log/nginx/utool-access.log;
    error_log /var/log/nginx/utool-error.log;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ativar site:

```bash
sudo ln -s /etc/nginx/sites-available/utool /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Ajustes de Permissões

```bash
cd /var/www/html/utool

# Definir ownership
sudo chown -R www-data:www-data .

# Permissões de diretórios
sudo find . -type d -exec chmod 755 {} \;

# Permissões de arquivos
sudo find . -type f -exec chmod 644 {} \;

# Diretórios de escrita
sudo chmod -R 775 logs_uploaded uploads
```

> [!NOTE]
> As pastas `font` e `_archive` foram excluídas do pacote de deployment para reduzir o tamanho.

### 6. Verificação

#### 6.1 Teste de Conectividade do Banco

```bash
cd /var/www/html/utool
php -r "require 'includes/db_connection.php'; \$conn = getDBConnection(); echo \$conn ? 'DB OK' : 'DB FAIL';"
```

#### 6.2 Teste Web

Abra no navegador:
```
http://SEU_IP/utool
```

Você deve ver a página inicial do Utool Hub.

#### 6.3 Teste de Login

- Tente fazer login com um usuário existente
- Verifique se a sessão persiste
- Teste algumas ferramentas

## Integração com Nexus-Dashboard

### Opção 1: Link Simples

Adicione um link no nexus-dashboard apontando para o utool:

```html
<a href="/utool">Utool Hub</a>
```

### Opção 2: Navegação Integrada

Edite o header do utool para incluir link de volta ao nexus-dashboard:

```php
// Em includes/header.php, adicionar:
<nav>
    <a href="/nexus-dashboard">← Voltar ao Dashboard</a>
</nav>
```

## Troubleshooting

### Erro: "Database connection failed"

1. Verifique credenciais no `.env`
2. Confirme que PostgreSQL está rodando: `sudo systemctl status postgresql`
3. Verifique se o banco foi importado: `sudo -u postgres psql -l | grep suporte_hub`

### Erro: "Permission denied" em uploads

```bash
sudo chown -R www-data:www-data /var/www/html/utool/uploads
sudo chmod -R 775 /var/www/html/utool/uploads
```

### Erro 404 em todas as páginas

- Apache: Verifique se `mod_rewrite` está ativado
- Nginx: Verifique a configuração do `try_files`

### Erro: "Composer dependencies not found"

```bash
cd /var/www/html/utool
composer install --no-dev --optimize-autoloader
```

## Manutenção

### Backup do Banco de Dados

```bash
sudo -u postgres pg_dump suporte_hub > backup_$(date +%Y%m%d).sql
```

### Atualização do Código

1. Criar novo pacote de deployment
2. Fazer backup do `.env` atual
3. Extrair novo pacote
4. Restaurar `.env`
5. Executar `composer install`

## Segurança

### Recomendações

1. **HTTPS**: Configure SSL/TLS para produção
2. **Firewall**: Restrinja acesso ao PostgreSQL
3. **Senhas**: Use senhas fortes no `.env`
4. **Backups**: Configure backups automáticos diários

### Configurar HTTPS (Opcional)

```bash
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d utool.intelidata.local
```

## Suporte

Para problemas ou dúvidas:
- Verifique os logs: `/var/log/apache2/utool-error.log` ou `/var/log/nginx/utool-error.log`
- Verifique logs do PHP: `/var/log/php8.0-fpm.log`
- Consulte a documentação do PostgreSQL

---

**Última atualização**: Janeiro 2026
