# Event Management API

API RESTful para gerenciamento de eventos desenvolvida com Laravel 12. Permite criar eventos, gerenciar participantes e controlar acesso através de autenticação e autorização baseada em policies e gates.

## 🚀 Tecnologias

- **PHP 8.2+**
- **Laravel 12.0**
- **Laravel Sanctum** - Autenticação via tokens
- **MySQL/PostgreSQL/SQLite** - Banco de dados

## 📋 Requisitos

- PHP >= 8.2
- Composer
- Node.js e NPM (para assets)
- Banco de dados (MySQL, PostgreSQL ou SQLite)

## 🔧 Instalação

1. Clone o repositório:
```bash
git clone <repository-url>
cd event-managment-api
```

2. Instale as dependências:
```bash
composer install
npm install
```

3. Configure o ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure o arquivo `.env` com suas credenciais de banco de dados:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_management
DB_USERNAME=root
DB_PASSWORD=
```

5. Execute as migrations:
```bash
php artisan migrate
```

6. (Opcional) Execute os seeders para dados de teste:
```bash
php artisan db:seed
```

## 🏃 Executando o Projeto

### Desenvolvimento
```bash
composer run dev
```

Este comando inicia:
- Servidor Laravel (http://localhost:8000)
- Queue worker
- Laravel Pail (logs)
- Vite (assets)

### Servidor simples
```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000/api`

## 📚 Estrutura do Projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php      # Autenticação (login/logout)
│   │       ├── EventController.php     # CRUD de eventos
│   │       └── AttendeeController.php   # CRUD de participantes
│   ├── Resources/
│   │   ├── EventResource.php           # Transformação de dados de eventos
│   │   ├── AttendeeResource.php        # Transformação de dados de participantes
│   │   └── UserResource.php            # Transformação de dados de usuários
│   └── Traits/
│       └── CanLoadRelationships.php    # Trait para carregar relacionamentos
├── Models/
│   ├── User.php                        # Model de usuário
│   ├── Event.php                       # Model de evento
│   └── Attendee.php                    # Model de participante
└── Policies/
    ├── EventPolicy.php                 # Políticas de autorização para eventos
    └── AttendeePolicy.php              # Políticas de autorização para participantes
```

## 🔐 Autenticação

A API utiliza **Laravel Sanctum** para autenticação via tokens. Todas as rotas (exceto login) requerem autenticação.

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Resposta:**
```json
{
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### Usando o Token

Inclua o token no header `Authorization`:
```http
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

## 🛡️ Autorização

O projeto utiliza **Policies** e **Gates** para controlar o acesso às operações:

### EventPolicy

- **viewAny**: Qualquer usuário pode listar eventos
- **view**: Qualquer usuário pode visualizar um evento
- **create**: Qualquer usuário autenticado pode criar eventos
- **update**: Apenas o dono do evento pode atualizá-lo
- **delete**: Apenas o dono do evento pode deletá-lo

### AttendeePolicy

- **viewAny**: Dono do evento ou participantes podem listar participantes
- **view**: Dono do evento ou o próprio participante podem visualizar
- **create**: Dono do evento pode adicionar qualquer participante; outros usuários podem se inscrever apenas se ainda não estiverem inscritos
- **delete**: Dono do evento ou o próprio participante podem remover a inscrição

## 📡 Endpoints da API

### Eventos

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| GET | `/api/events` | Lista todos os eventos | ✅ |
| POST | `/api/events` | Cria um novo evento | ✅ |
| GET | `/api/events/{id}` | Exibe um evento específico | ✅ |
| PUT/PATCH | `/api/events/{id}` | Atualiza um evento | ✅ |
| DELETE | `/api/events/{id}` | Deleta um evento | ✅ |

**Criar Evento:**
```http
POST /api/events
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Conferência de Tecnologia",
    "description": "Evento sobre as últimas tendências em tecnologia",
    "start_time": "2025-12-01 09:00:00",
    "end_time": "2025-12-01 18:00:00"
}
```

**Atualizar Evento:**
```http
PUT /api/events/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Conferência Atualizada",
    "description": "Nova descrição",
    "start_time": "2025-12-01 10:00:00",
    "end_time": "2025-12-01 19:00:00"
}
```

### Participantes

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| GET | `/api/events/{event}/attendees` | Lista participantes de um evento | ✅ |
| POST | `/api/events/{event}/attendees` | Adiciona participante ao evento | ✅ |
| GET | `/api/events/{event}/attendees/{attendee}` | Exibe um participante específico | ✅ |
| DELETE | `/api/events/{event}/attendees/{attendee}` | Remove participante do evento | ✅ |

**Adicionar Participante:**
```http
POST /api/events/{event}/attendees
Authorization: Bearer {token}
```

O usuário autenticado será automaticamente adicionado como participante.

## 🗄️ Estrutura do Banco de Dados

### Tabela: `users`
- `id` (bigint, primary key)
- `name` (string)
- `email` (string, unique)
- `password` (string, hashed)
- `email_verified_at` (timestamp, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

### Tabela: `events`
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key -> users.id)
- `name` (string)
- `description` (text, nullable)
- `start_time` (datetime)
- `end_time` (datetime)
- `created_at` (timestamp)
- `updated_at` (timestamp)

### Tabela: `attendees`
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key -> users.id)
- `event_id` (bigint, foreign key -> events.id)
- `created_at` (timestamp)
- `updated_at` (timestamp)

### Tabela: `personal_access_tokens`
- Gerenciada pelo Laravel Sanctum para autenticação via tokens

## 🧪 Testes

Execute os testes com:
```bash
composer run test
```

ou

```bash
php artisan test
```

## 📝 Validações

### Eventos
- `name`: obrigatório, string, máximo 255 caracteres
- `description`: opcional, string
- `start_time`: obrigatório, formato datetime
- `end_time`: obrigatório, formato datetime, deve ser posterior a `start_time`

### Autenticação
- `email`: obrigatório, formato email válido
- `password`: obrigatório

## 🔄 Relacionamentos

- **User** `hasMany` **Event** (um usuário pode ter vários eventos)
- **User** `hasMany` **Attendee** (um usuário pode participar de vários eventos)
- **Event** `belongsTo` **User** (um evento pertence a um usuário)
- **Event** `hasMany` **Attendee** (um evento pode ter vários participantes)
- **Attendee** `belongsTo` **User** (um participante pertence a um usuário)
- **Attendee** `belongsTo` **Event** (um participante pertence a um evento)

## 📦 Recursos (Resources)

A API utiliza **API Resources** para transformar os modelos em JSON estruturado:

- **EventResource**: Inclui informações do evento, criador e lista de participantes
- **AttendeeResource**: Inclui informações do participante, usuário e evento
- **UserResource**: Informações básicas do usuário

## 🛠️ Comandos Úteis

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Executar migrations
php artisan migrate

# Reverter última migration
php artisan migrate:rollback

# Criar novo modelo com migration
php artisan make:model ModelName -m

# Criar nova policy
php artisan make:policy PolicyName --model=ModelName
```

## 📄 Licença

Este projeto está sob a licença MIT.
