# Task Manager Test

Aplicação fullstack para gerenciamento de tarefas construída com **Laravel (API)** no backend e **Vue 3 + Tailwind 4** no frontend.

---

# Stack

## Backend
- PHP 8.2+
- Laravel 10+
- SQLite (ambiente padrão para facilitar setup)
- Eloquent ORM
- REST API

## Frontend
- Vue 3
- Vite
- TypeScript
- Axios

---

# Instruções de Instalação

## Pré-requisitos

Antes de começar, certifique-se de ter instalado:

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
Instalar dependências
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
Rodar migrations e seed
```bash
php artisan migrate --seed
```
Esse comando irá:
- Criar as tabelas
- Popular o banco com dados de teste

Subir o servidor backend
```bash
php artisan serve
```

## Frontend

Entre na pasta do frontend:
```bash
cd frontend
```
Instalar dependências
```bash
npm install
```
Rodar ambiente de desenvolvimento
```bash
npm run dev
```
