# Escopo do Projeto - GameClock

## 1. Identificação

* **Nome do Projeto:** Desenvolvimento do GameClock
* **Autor:** Thiago Grunvaldt
* **Orientador:** Clarissa Xavier
* **Data Aprovação:** 11/2025

## 2. Sobre

Desenvolver um sistema web que centralize e apresente o tempo total de jogos de seus usuários. O diferencial do sistema é a capacidade de **sincronização automática** com a biblioteca da Steam e a utilização de um algoritmo de Ciência de Dados para recomendar novos jogos com base na similaridade entre usuários.

## 3. Objetivo do Projeto

Desenvolver o sistema com suas principais funcionalidades:
* Sistema de cadastro e login de usuário.
* Gerenciamento do perfil de usuário com personalização.
* **Integração via API da Steam** para coleta automática de jogos, tempo de jogo e conquistas.
* **Sistema Inteligente de Recomendação** (Python) para sugerir jogos baseados no perfil do usuário.

## 4. Escopo do Projeto

### Entregáveis:
* Software web do GameClock funcional.
* Documentação técnica do software (Diagramas UML e Banco de Dados).
* Script de recomendação em Python integrado.
* Manual para instalação e utilização.

### Requisitos Funcionais (RF)

* **RF01 – Cadastro e Autenticação:** O sistema deve permitir cadastro com nome, e-mail, senha e gerenciamento de sessão.
* **RF02 – Gerenciamento de Perfil:** O usuário pode editar seu perfil, incluindo upload de foto, descrição e definição de "ID Personalizado" (Vanity URL).
* **RF03 – Integração com Steam:** O sistema deve permitir vincular uma conta Steam (via ID ou URL). Deve coletar automaticamente a lista de jogos possuídos e o tempo total jogado de cada um.
* **RF04 – Sincronização de Conquistas:** O sistema deve buscar e armazenar as conquistas desbloqueadas de cada jogo importado da Steam.
* **RF05 – Sistema de Recomendação:** O sistema deve processar os dados dos usuários e exibir uma lista de 5 jogos recomendados, utilizando um algoritmo de filtragem colaborativa (intersecção de bibliotecas).
* **RF06 – Visualização de Dados:** Exibição clara dos jogos em *cards*, mostrando horas jogadas (convertidas de minutos) e lista de conquistas.

### Requisitos Não Funcionais (RNF)

* **RNF01 – Desempenho e Acessibilidade:** Interface responsiva utilizando Bootstrap 5.3 (Desktop e Mobile).
* **RNF02 – Interoperabilidade:** O backend em PHP deve ser capaz de executar scripts externos em Python para processamento de dados de recomendação.
* **RNF03 – Integridade de Dados:** O sistema deve evitar duplicidade de jogos ao sincronizar múltiplas vezes (`INSERT ... ON DUPLICATE KEY`).
* **RNF04 – Dependências:** Gerenciamento de bibliotecas via Composer (PHP) e Pip (Python).

### Exclusões (O que não está no MVP):

* Integração com Epic Games, Riot Games, PSN e Xbox (ficará para versões futuras).
* Sistema de chat ou rede social entre usuários.
