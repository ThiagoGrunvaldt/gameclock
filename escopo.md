# GameClock
# 1. Indentificação
- Nome do Projeto: Desenvolvimento do GameClock.
- Autor: Thiago Grunvaldt
- Orientador: Clarissa Xavier
- Data Aprovação: x/11/25

# 2. Sobre:
Desenvolver um sistema web que centralize e apresente o tempo total de jogos de seus usuários a partir de múltiplas plataformas, oferecendo visualizações claras e úteis sobre o hábito de jogo.

# 3. Objetivo do projeto:
Desenvolver o sistema com suas principais funcionalidades como:
- Sistema de cadastro e login de usuário;
- Gerenciamento do perfil de usuário.
- Gerenciamento dos jogos com dados das plataformas externas (steam, epic, riot).
- Sistema de recomendação de jogo ao usuário, conforme os seus dados.

# 4. Escopo do Projeto
## Entregáveis:
- Software web do Gameclock funcional.
- Documentação técnica do software.
- Manual para instalação e utilização.

## Requisitos:
### Requisitos Funcionais (RF)
1. RF01 – Cadastro e Autenticação de Usuário: O sistema deve permitir cadastro com nome, e-mail e senha.
2. RF02 – Gerenciamento de Perfil: O usuário pode editar seu perfil (nome, imagem, descrição).
3. RF03 - Visualização de Estatísticas:Total de horas jogadas (somando todas as plataformas), jogos mais jogados, conquistas dos jogos.
4. RF04 – Gerenciamento Manual:Permitir que o usuário adicione, edite ou exclua jogos e conquistas manualmente, caso deseje controlar além das integração futura.
5. RF05 – Logout:O sistema deve permitir que o usuário encerre sua sessão de forma segura.
6. RF06 – Integração com Plataformas de Jogos:
O sistema deve permitir que o usuário vincule contas de plataformas (ex.: Steam, Riot). Deve coletar automaticamente dados de horas jogadas, lista de jogos e atualizar periodicamente essas informações via API.

### Requisitos Não Funcionais (RNF)
1. RNF01 – Desempenho e Acessibilidade: Sistema web responsivo (desktop e mobile).
2. RNF02 – Segurança: Dados de login protegidos por criptografia, comunicação segura via HTTPS, tokens das APIs das plataformas armazenados com segurança.
3. RNF03 – Disponibilidade e Escalabilidade: Capacidade de suportar grande número de usuários simultâneos.

## Exclusões:
- Integração com Steam, EpicGames, RiotGames. Para utilização dos dados dos usuários de jogos e conquistas.
