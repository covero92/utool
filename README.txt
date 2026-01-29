# NextN Tools (Versão PHP)

Este projeto é uma recriação das ferramentas NextN (Validador, Analisador XML, Dicionário) utilizando PHP e HTML para fácil execução em servidores como XAMPP.

## Requisitos
- Servidor Web (Apache/Nginx)
- PHP 8.0 ou superior

## Como Executar no XAMPP

1. Copie a pasta `php-version` para dentro do diretório `htdocs` do seu XAMPP (geralmente `C:\xampp\htdocs`).
2. Renomeie a pasta para `nextn` (opcional, para facilitar a URL).
3. Inicie o Apache no painel de controle do XAMPP.
4. Acesse no navegador: `http://localhost/php-version` (ou o nome que você escolheu).

## Estrutura de Arquivos

- `index.php`: Página inicial (Dashboard).
- `validator.php`: Ferramenta de validação de arquivos de texto posicional.
- `xml-analyzer.php`: Ferramenta para visualizar a estrutura de arquivos XML.
- `dictionary.php`: Ferramenta para ler e pesquisar no dicionário de dados (HTML).
- `includes/`: Cabeçalho e rodapé compartilhados.
- `assets/`: Arquivos CSS e JS.

## Notas de Desenvolvimento

- O projeto utiliza **Bootstrap 5** via CDN para estilização. Certifique-se de estar conectado à internet.
- As regras de validação foram portadas do projeto original em TypeScript para um array PHP em `validator.php`.
- O limite de upload do PHP pode precisar ser ajustado no `php.ini` (`upload_max_filesize` e `post_max_size`) para arquivos muito grandes.
