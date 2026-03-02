# Task Manager Test

Aplicação fullstack para gerenciamento de tarefas construída com **Laravel (API)** no backend e **Vue 3 + Tailwind CSS 4** no frontend.

---

# Stack

## Backend
- PHP 8.2+
- Laravel 10+
- SQLite (configuração padrão para facilitar setup)
- Eloquent ORM
- REST API

## Frontend
- Vue 3
- Vite
- TypeScript
- Axios
- Tailwind CSS 4

---

# Instruções de Instalação

## Pré-requisitos

Certifique-se de possuir instalado:

- PHP **8.2+**
- Composer
- Node.js **20.19+ ou 22.12+**
- NPM
- Git

---

## Backend

Entre na pasta do backend:

```bash
cd backend
```
Instale as dependências:
```bash
composer install
```
Copie o arquivo de ambiente:
```bash
cp .env.example .env
```
Gere a chave da aplicação:
```bash
php artisan key:generate
```
Crie o banco SQLite (necessário para evitar erro nas migrations):
```bash
touch database/database.sqlite
```
Execute as migrations e seeders:
```bash
php artisan migrate --seed
```
Esse comando irá:
- Criar as tabelas
- Popular o banco com dados de teste

Inicie o servidor backend:
```bash
php artisan serve
```

## Frontend

Entre na pasta do frontend:
```bash
cd frontend
```
Instalar dependências:
```bash
npm install
```
Execute o ambiente de desenvolvimento:
```bash
npm run dev
```

O Vite está configurado com proxy para redirecionar requisições /api para o backend, evitando problemas de CORS e dispensando outras configuração.

## Descrição técnica

A aplicação foi estruturada utilizando uma arquitetura baseada em API REST no backend (Laravel 10) e SPA no frontend (Vue 3). O backend é responsável exclusivamente pela exposição de endpoints RESTful e pelas regras de negócio, enquanto o frontend consome esses endpoints de forma independente. Essa separação reduz acoplamento entre camadas, facilita manutenção e permite reutilização da API por outros clientes.

No backend, foi adotado o Laravel 10 com PHP 8.2+, utilizando o padrão MVC nativo do framework. O Eloquent ORM é responsável pela abstração da camada de persistência, enquanto migrations garantem versionamento estruturado do banco de dados. Seeders e factories foram implementados para assegurar reprodutibilidade do ambiente e permitir validação funcional imediata após a instalação.

A lógica foi mantida nos controllers devido ao escopo reduzido do projeto. Em um cenário de maior complexidade, seria recomendada a introdução de uma camada de Services ou Application Layer para melhor separação entre regras de negócio e camada HTTP.

O SQLite foi definido como banco padrão para simplificar o setup e eliminar dependências externas. Essa decisão é adequada para ambiente de desenvolvimento e avaliação técnica, mantendo compatibilidade com outros SGBDs suportados pelo Laravel, como MySQL ou PostgreSQL.

No frontend, foi utilizado Vue 3 com Vite como bundler. O Vue fornece uma arquitetura baseada em componentes, promovendo organização e reutilização. O Vite oferece inicialização rápida do ambiente de desenvolvimento e HMR eficiente. A comunicação com o backend é realizada via Axios, mantendo a camada de acesso à API isolada da lógica de apresentação.

## O que falta implementar

Algumas melhorias arquiteturais foram mantidas fora do escopo para priorizar clareza e entrega funcional:

- Autenticação e autorização: A API está pública e não possui controle de acesso. Em um cenário real, seria recomendável implementar autenticação baseada em token (ex: Laravel Sanctum ou JWT) e controle de permissões via Policies.

- Paginação e filtros: A listagem retorna todos os registros. Para maior escalabilidade, seria recomendável utilizar paginação e permitir filtros e ordenação via query parameters.

- Camada de Service: A lógica permanece nos controllers devido ao tamanho do projeto. Em aplicações maiores, seria adequado extrair regras de negócio para uma camada intermediária visando melhor organização e testabilidade.

- Testes automatizados: Não foram incluídos testes unitários ou de integração. Em ambiente produtivo, seria essencial implementar testes no backend (PHPUnit) e frontend.

Esses pontos representam evoluções naturais do projeto, mas foram mantidos fora do escopo para priorizar organização e atendimento aos requisitos principais.
