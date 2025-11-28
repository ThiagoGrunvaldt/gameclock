# GameClock

## 1. Identificação

* **Nome do Projeto:** Desenvolvimento do GameClock
* **Autor:** Thiago Grunvaldt
* **Orientador:** Clarissa Xavier
* **Data Aprovação:** 11/2025

## 2. Sobre

O **GameClock** é um sistema web desenvolvido para centralizar e apresentar o tempo total de jogos dos usuários. Diferente de listas manuais, o sistema evoluiu para integrar-se diretamente com a API da **Steam**, importando automaticamente a biblioteca de jogos, horas jogadas e conquistas. Além disso, conta com um sistema inteligente de recomendação de jogos baseado em filtragem colaborativa (Python).

## 3. Objetivos e Funcionalidades

O objetivo principal é oferecer uma visão unificada do perfil *gamer* do usuário.

### Funcionalidades Principais:
* **Integração com Steam:** Vínculo de conta via SteamID ou URL personalizada para importação automática de jogos e tempo de jogo.
* **Sincronização de Conquistas:** Coleta automática de conquistas desbloqueadas na Steam.
* **Sistema de Recomendação:** Sugestão de novos jogos baseada na similaridade com a biblioteca de outros usuários (Algoritmo em Python).
* **Gerenciamento de Perfil:** Edição de dados pessoais, foto de perfil e biografia.
* **Ranking:** Visualização de usuários com mais horas ou conquistas.

## 4. Tecnologias Utilizadas

### Front-End
* **HTML5, CSS3 e JavaScript:** Estrutura e interatividade.
* **Bootstrap 5.3:** Framework para design responsivo (Desktop/Mobile).

### Back-End
* **PHP:** Linguagem principal do servidor e regras de negócio.
* **Python:** Utilizado no microsserviço de recomendação de jogos (Data Science).

### Banco de Dados
* **MySQL:** Armazenamento relacional de usuários, jogos e conquistas.

### Utilitários e Dependências
* **XAMPP:** Ambiente de servidor local (Apache + MySQL).
* **Composer:** Gerenciador de dependências PHP (utilizado para PHPMailer).
* **Git/GitHub:** Controle de versionamento.

## 5. Pré-Requisitos

Para rodar o projeto localmente, você precisará de:
* **XAMPP** (ou outro servidor Apache/MySQL).
* **PHP** (versão 7.4 ou superior).
* **Composer** instalado.
* **Python** (versão 3.x instalada e adicionada ao PATH do sistema).

## 6. Instalação e Configuração

Siga os passos abaixo para configurar o ambiente:

### Passo 1: Arquivos
1.  Clone este repositório ou baixe o ZIP.
2.  Coloque a pasta do projeto dentro do diretório `htdocs` do seu XAMPP (ex: `C:\xampp\htdocs\GameClock`).

### Passo 2: Banco de Dados
1.  Abra o XAMPP e inicie os serviços **Apache** e **MySQL**.
2.  Acesse o PHPMyAdmin (`http://localhost/phpmyadmin`).
3.  Crie um banco de dados chamado `game_clock`, conforme configurado no `conexao.php`.
4.  Importe o arquivo `.sql` que está na raiz do projeto (ex: `GameClock.sql` ou `game_clockAttSteam.sql`).

### Passo 3: Dependências do PHP
1.  Abra o terminal na pasta do projeto.
2.  Execute o comando para instalar o PHPMailer e outras libs:
    ```bash
    composer install
    ```
    *(Isso criará a pasta `vendor` automaticamente).*

### Passo 4: Dependências do Python
Para que o sistema de recomendação funcione, instale o conector do MySQL para Python:
1.  No terminal, execute:
    ```bash
    pip install mysql-connector-python
    ```

### Passo 5: Execução
1.  Acesse no seu navegador: `http://localhost/GameClock/`.
2.  Crie uma conta e vincule seu perfil da Steam na página de configurações para testar a sincronização.

## 7. Estrutura de Pastas Relevantes

* `/pages`: Páginas visíveis ao usuário (Login, Perfil, Detalhes).
* `/resources`: Scripts de lógica PHP (Conexão, Classes, Funções de API).
* `/uploads`: Armazena as fotos
