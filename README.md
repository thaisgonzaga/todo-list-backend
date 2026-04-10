# Todo List Backend

API backend para gerenciamento de tarefas com autenticação por token. O projeto foi construído com Laravel e expõe rotas para cadastro, login, logout e operação de tarefas vinculadas ao usuário autenticado.

## Objetivo do projeto

O objetivo deste projeto é disponibilizar um backend REST para uma aplicação de lista de tarefas, com foco em dois fluxos centrais:

- autenticação de usuários com emissão de token via Laravel Sanctum;
- gerenciamento de tarefas por usuário autenticado, com criação, listagem, atualização e remoção.

O sistema garante isolamento dos dados por usuário. Cada tarefa pertence a um usuário específico e as operações protegidas consultam apenas os registros do usuário autenticado.

## Arquitetura e stack

### Stack principal

- PHP 8.3+
- Laravel 13
- Laravel Sanctum para autenticação por token
- Eloquent ORM para acesso a dados
- MySQL 8.4 no fluxo principal com Docker/Sail
- PHPUnit 12 para testes automatizados
- Vite e Tailwind CSS para assets do ecossistema Laravel

### Organização da aplicação

- `routes/api.php`: define as rotas públicas e protegidas da API.
- `app/Http/Controllers/AuthController.php`: concentra cadastro, login e logout.
- `app/Http/Controllers/TaskController.php`: concentra o CRUD de tarefas.
- `app/Models/User.php`: define o usuário autenticável e o relacionamento `hasMany` com tarefas.
- `app/Models/Task.php`: representa a tarefa e define o relacionamento `belongsTo` com usuário.
- `database/migrations`: versiona a estrutura do banco, incluindo a tabela `tasks`.

### Regras principais da API

- Rotas públicas:
	- `POST /api/register`
	- `POST /api/login`
- Rotas protegidas por `auth:sanctum`:
	- `POST /api/logout`
	- `GET /api/tasks`
	- `POST /api/tasks`
	- `PATCH /api/tasks/{id}`
	- `DELETE /api/tasks/{id}`

### Modelo de dados de tarefas

A tabela `tasks` possui os seguintes campos principais:

- `id`
- `user_id`
- `title`
- `description`
- `done`
- `created_at`
- `updated_at`

O relacionamento com `users` usa chave estrangeira com `cascadeOnDelete`, então as tarefas são removidas automaticamente quando o usuário é excluído.

## Acesso e execução do código

### Pré-requisitos

- Docker
- Docker Compose
- PHP 8.3+
- Composer
- Node.js e npm

### 1. Instale as dependências locais

Mesmo usando containers, o projeto depende dos pacotes PHP e Node declarados no repositório.

```bash
composer install
npm install
```

### 2. Configure o ambiente

Crie o arquivo `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Como o fluxo principal deste README usa MySQL com Docker/Sail, ajuste o `.env` para usar MySQL em vez de SQLite:

```env
APP_NAME="Todo List Backend"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=todo_list
DB_USERNAME=sail
DB_PASSWORD=password
```

Se esses valores forem diferentes no seu ambiente, mantenha consistência entre `.env` e `compose.yaml`.

### 3. Suba os containers

```bash
docker compose up -d
```

Esse comando sobe:

- a aplicação Laravel em `http://localhost`
- o banco MySQL
- a porta do Vite configurada no compose

### 4. Gere a chave da aplicação e rode as migrations

```bash
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
```

### 5. Acesse a API

Com os containers ativos, a aplicação fica disponível em:

```text
http://localhost
```

As rotas da API ficam sob o prefixo:

```text
http://localhost/api
```

### Comandos úteis

```bash
docker compose exec laravel.test php artisan route:list
docker compose exec laravel.test php artisan migrate:fresh
docker compose exec laravel.test php artisan test
docker compose down
```

## Alterações, teste e validações

### Fluxo recomendado ao alterar o código

Ao modificar o projeto, use este fluxo mínimo de verificação:

1. atualizar dependências, se necessário;
2. rodar migrations quando houver alteração de schema;
3. executar a suíte de testes;
4. validar manualmente os endpoints principais.

Comandos úteis:

```bash
composer test
docker compose exec laravel.test php artisan test
docker compose exec laravel.test php artisan migrate
```

### Estado atual dos testes automatizados

O projeto já possui configuração de PHPUnit com suítes `Unit` e `Feature`, mas neste momento ainda não há cobertura real do fluxo de autenticação e tarefas. Os arquivos existentes em `tests/Unit` e `tests/Feature` são exemplos iniciais e devem ser expandidos conforme a evolução do projeto.

### Validação manual da API

Os exemplos abaixo assumem que a aplicação está rodando em `http://localhost`.

#### 1. Registrar usuário

```bash
curl -X POST http://localhost/api/register \
	-H "Content-Type: application/json" \
	-d '{
		"name": "Maria",
		"email": "maria@example.com",
		"password": "12345678",
		"password_confirmation": "12345678"
	}'
```

Resposta esperada:

```json
{
	"user": {
		"id": 1,
		"name": "Maria",
		"email": "maria@example.com",
		"created_at": "2026-04-10T00:00:00.000000Z",
		"updated_at": "2026-04-10T00:00:00.000000Z"
	},
	"token": "1|token-gerado-aqui"
}
```

#### 2. Fazer login

```bash
curl -X POST http://localhost/api/login \
	-H "Content-Type: application/json" \
	-d '{
		"email": "maria@example.com",
		"password": "12345678"
	}'
```

Guarde o token retornado para as próximas chamadas.

#### 3. Criar tarefa

```bash
curl -X POST http://localhost/api/tasks \
	-H "Content-Type: application/json" \
	-H "Authorization: Bearer SEU_TOKEN" \
	-d '{
		"title": "Estudar Laravel",
		"description": "Revisar autenticação com Sanctum"
	}'
```

Resposta esperada:

```json
{
	"id": 1,
	"user_id": 1,
	"title": "Estudar Laravel",
	"description": "Revisar autenticação com Sanctum",
	"done": false,
	"created_at": "2026-04-10T00:00:00.000000Z",
	"updated_at": "2026-04-10T00:00:00.000000Z"
}
```

#### 4. Listar tarefas

```bash
curl http://localhost/api/tasks \
	-H "Authorization: Bearer SEU_TOKEN"
```

#### 5. Atualizar tarefa

```bash
curl -X PATCH http://localhost/api/tasks/1 \
	-H "Content-Type: application/json" \
	-H "Authorization: Bearer SEU_TOKEN" \
	-d '{
		"done": true
	}'
```

#### 6. Remover tarefa

```bash
curl -X DELETE http://localhost/api/tasks/1 \
	-H "Authorization: Bearer SEU_TOKEN"
```

Resposta esperada:

```json
{
	"message": "Tarefa deletada com sucesso."
}
```

#### 7. Fazer logout

```bash
curl -X POST http://localhost/api/logout \
	-H "Authorization: Bearer SEU_TOKEN"
```

Resposta esperada:

```json
{
	"message": "Logout realizado com sucesso."
}
```
