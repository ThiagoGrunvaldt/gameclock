# gameclock
Tema / Objetivo
Desenvolver um sistema web que centralize e apresente o tempo total de jogos de seus usuários a partir de múltiplas plataformas, oferecendo visualizações claras e úteis sobre o hábito de jogo.
Principais Funcionalidades:
- Sistema de cadastro e login de usuário;
- Gerenciamento do perfil de usuário.
- Gerenciamento dos jogos com dados das plataformas externas (steam, epic, riot).
- 
Requisitos
Requisitos Funcionais (RF)
RF01 – Cadastro e Autenticação de Usuário: O sistema deve permitir cadastro com nome, e-mail e senha.
RF02 – Gerenciamento de Perfil: O usuário pode editar seu perfil (nome, imagem, descrição).
RF03 - Visualização de Estatísticas:Total de horas jogadas (somando todas as plataformas), jogos mais jogados, conquistas dos jogos.
RF04 – Gerenciamento Manual:Permitir que o usuário adicione, edite ou exclua jogos e conquistas manualmente, caso deseje controlar além das integração futura.
RF05 – Logout:O sistema deve permitir que o usuário encerre sua sessão de forma segura.
RF06 – Integração com Plataformas de Jogos:
O sistema deve permitir que o usuário vincule contas de plataformas (ex.: Steam, Riot). Deve coletar automaticamente dados de horas jogadas, lista de jogos e atualizar periodicamente essas informações via API.

Requisitos Não Funcionais (RNF)
RNF01 – Desempenho e Acessibilidade: Sistema web responsivo (desktop e mobile).
RNF02 – Segurança: Dados de login protegidos por criptografia, comunicação segura via HTTPS, tokens das APIs das plataformas armazenados com segurança.
RNF03 – Disponibilidade e Escalabilidade: Capacidade de suportar grande número de usuários simultâneos.

Prototipagem Simples:
[prototipagemGameClockdrawio.png](https://github.com/ThiagoGrunvaldt/gameclock/blob/main/prototipagemGameClockdrawio.png)

Para o prototipo avançado:
GameClock.zip
Para o sistema rodar, é necessário: Descompactar o zip. 
Possuir o Xampp e colocar o GameClock dentro do htdocs. 
Com o Xampp, ligar apache e MySQL. 
Para o banco pegar o arquivo .sql dentro da pasta do sistema e importar no phpmyadmin. 
Abrir o sistema com: localhost/gameclock/
