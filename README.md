# Real-Time Chat System with Laravel 12, Reverb, and Vue (Vite) - Professional Design

A real-time chat application with a professional Bootstrap 5 design, using Laravel 12 (backend) on `http://127.0.0.1:8000` with Reverb for WebSocket messaging, Sanctum for authentication, and Vue with Vite (frontend) on `http://localhost:5173`. Developed for RazinSoft Limited's technical assessment, it offers secure one-to-one messaging with a responsive, user-friendly interface.

## Features
- Email/password registration and login with card-based forms
- User search by name with dynamic results and avatars
- Real-time messaging via Reverb with sleek message bubbles
- Professional, responsive UI with fixed dark sidebar and mobile toggle
- Secure API with Laravel Sanctum and CORS support
- Conversation management with a list of active chats
- Private messages accessible only to sender and receiver
- Database schema for users, conversations, and messages

## Prerequisites
- PHP 8.2+
- Composer
- Node.js (LTS)
- MySQL
- XAMPP 8.2 (Windows)
- Git (optional for version control)

## Setup Instructions

### Backend (Laravel 12)
1. Navigate to `chatting-system`:
   ```bash
   cd D:\xampp8.2\htdocs\chatting-system
   ```
2. Access the Laravel project:
   ```bash
   cd chatting-service
   ```
   If missing, create:
   ```bash
   composer create-project laravel/laravel chatting-service
   cd chatting-service
   ```
3. Install dependencies:
   ```bash
   composer require laravel/sanctum laravel/reverb
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan reverb:install
   ```
4. Configure environment (`chatting-service/.env`):
   ```env
   APP_NAME=ChatSystem
   APP_ENV=local
   APP_KEY=base64:your-generated-key
   APP_DEBUG=true
   APP_URL=http://127.0.0.1:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=chat_system
   DB_USERNAME=root
   DB_PASSWORD=

   BROADCAST_CONNECTION=reverb
   REVERB_APP_ID=chat_app
   REVERB_APP_KEY=chat_key
   REVERB_APP_SECRET=chat_secret
   REVERB_HOST=localhost
   REVERB_PORT=8080
   REVERB_SCHEME=http

   SESSION_DOMAIN=localhost
   SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,127.0.0.1:8000,::1,localhost:5173

   VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
   VITE_REVERB_HOST="${REVERB_HOST}"
   VITE_REVERB_PORT="${REVERB_PORT}"
   VITE_REVERB_SCHEME="${REVERB_SCHEME}"
   ```
   Generate `APP_KEY`:
   ```bash
   php artisan key:generate
   ```
5. Create database:
   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS chat_system"
   ```
6. Run migrations:
   ```bash
   php artisan migrate
   ```
   Creates:
   - `users`: User data
   - `conversations`: Conversation metadata (id, user1_id, user2_id, created_at, updated_at)
   - `chats`: Messages (id, conversation_id, sender_id, message, created_at, updated_at)
   - `personal_access_tokens`: Sanctum tokens
7. Clear caches:
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan cache:clear
   ```
8. Start servers:
   ```bash
   php artisan serve
   php artisan reverb:start
   ```
   Verify:
   ```bash
   curl http://127.0.0.1:8000
   ```

### Frontend (Vue with Vite)
1. Navigate to Vue project:
   ```bash
   cd D:\xampp8.2\htdocs\chatting-system\vue
   ```
   If missing, create:
   ```bash
   npm create vite@latest vue -- --template vue
   cd vue
   npm install
   npm install axios laravel-echo pusher-js bootstrap bootstrap-icons vue-router@4 vuex@4
   ```
2. Configure environment:
   Copy `vue/.env.example` to `vue/.env.local`:
   ```env
   VITE_API_URL=http://127.0.0.1:8000
   VITE_REVERB_KEY=chat_key
   VITE_REVERB_HOST=localhost
   VITE_REVERB_PORT=8080
   VITE_REVERB_SCHEME=http
   ```
   > **Note**: `vue/.env.local` is included for assessment. For production, create from `.env.example` and secure sensitive keys.
3. Start development server:
   ```bash
   npm run dev
   ```

## Usage
1. Open `http://localhost:5173` in a browser.
2. **Register**: Click "Register", enter name, email, password, and submit.
3. **Login**: Enter email and password, then submit.
4. **Chat Interface**:
   - **Search Users**: Type a name in the sidebar search bar to find users.
   - **Start Conversation**: Click a search result to begin or open a conversation.
   - **Send Messages**: Type and send messages, displayed as bubbles in real-time.
   - **View Conversations**: Active conversations listed in the sidebar with previews.
   - **Mobile**: Toggle sidebar with the menu button.
5. **Logout**: Click "Logout" in the sidebar.

## Troubleshooting

### Connection Refused (net::ERR_CONNECTION_REFUSED)
- Verify Laravel server:
  ```bash
  php artisan serve
  curl http://127.0.0.1:8000
  ```
- Check port 8000:
  ```bash
  netstat -aon | findstr :8000
  ```
  If conflicted:
  ```bash
  php artisan serve --port=8001
  ```
  Update `vue/.env.local`:
  ```env
  VITE_API_URL=http://127.0.0.1:8001
  ```
- Ensure MySQL:
  ```bash
  net start mysql
  ```
- Check logs:
  ```bash
  type chatting-service\storage\logs\laravel.log
  ```

### Route Not Found
- Verify `chatting-service/routes/api.php`:
  ```php
  Route::get('/users/search', [ChatController::class, 'searchUsers']);
  ```
- Clear cache:
  ```bash
  php artisan route:clear
  ```

### Authentication Issues
- Confirm `chatting-service/config/auth.php` uses `sanctum` driver.
- Check token in browser console:
  ```javascript
  console.log(localStorage.getItem('token'));
  ```

## Production Notes
- Enable SSL: Set `VITE_REVERB_SCHEME=https`, `forceTLS: true` in `vue/.env.local`.
- Proxy API/WebSocket (port 8080) with Nginx/Apache.
- Run Reverb with Supervisor:
  ```bash
  php artisan reverb:start
  ```
- Build frontend:
  ```bash
  npm run build
  ```

## Repository Notes
- **Environment Files**: `vue/.env.local` is included for assessment with placeholder values. For production, copy `vue/.env.example` to `vue/.env.local` and update `VITE_REVERB_KEY` if needed.
- **Tests**: Unit/feature tests are in `chatting-service/tests` (e.g., `LoginTest.php`, `UserSearchTest.php`).
- **Submission**: Source code, documentation (`README.md`), and tests are in this repository, meeting the technical assessment deliverables.