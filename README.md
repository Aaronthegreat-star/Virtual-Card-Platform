# Dockerized Laravel + Node.js Application

## Task Objective
Configuring Of a Dockerized environment for a Laravel backend and a Node.js WebSocket server using Docker Compose, running both services with exposed ports. An Nginx container was added as a reverse proxy to enhance security,reverse-proxying the WebSocket connections, and securely serving the Laravel backend

## Architecture
- **Laravel 10** - Backend API (PHP 8.2-fpm)  
- **Node.js** - WebSocket server for real-time communication
- **Nginx** - Reverse proxy handling HTTP and WebSocket traffic
- **Docker Compose** - Container orchestration

## Prerequisites
Before running this project, make sure you have the following installed as they form the backbone of the containerized environment:

- **Docker Desktop** (Windows/Mac) or **Docker Engine** (Linux)  
  Following the official installation guide here: [Install Docker](https://docs.docker.com/desktop/).  
  Choose the installation instructions specific to your operating system.

- **Docker Compose v2.0+**  
  Docker Compose is required to orchestrate multi-container applications.  
  Installation guide: [Install Docker Compose](https://docs.docker.com/compose/).


## Quick Setup

### 1. Clone the Repository
```bash
git clone https://github.com/Aaronthegreat-star/Virtual-Card-Platform.git
cd Virtual-Card-Platform
```

### 2. Build and Start Services
```bash
# Build the images defined in the docker-compose.yml file
docker-compose build


# Start the containers in detached mode
docker-compose up -d

# Check running services
docker-compose ps
```

### 3. Access Services
- **Laravel API**: http://localhost (port 80)
- **WebSocket Server**: http//localhost/ws 

## Container Details

| Service | Image | Exposed Port | Internal Port |
|---------|-------|--------------|---------------|
| Laravel API | Custom PHP 8.2-fpm | 80 | 9000 |
| WebSocket | Custom Node.js | 8081 | 8080 |
| Nginx Proxy | nginx:1.27-alpine | 80 | 80 |

## Docker Compose Configuration

```yaml
services:
  server:           # Laravel PHP-FPM
    build: ./server-api/Dockerfile
    expose: ["9000"]
    
  websocket:        # Node.js WebSocket
    build: ./websocket/Dockerfile  
    ports: ["8081:8080"]
    
  nginx:            # Reverse Proxy
    image: nginx:1.27-alpine
    ports: ["80:80"]
    depends_on: [server, websocket]
```

## File Structure
```
├── docker-compose.yml          # Container orchestration
├── server-api/                 # Laravel application
│   ├── Dockerfile             # PHP-FPM dockerfile
│   └── [Laravel files]
├── websocket/                  # Node.js WebSocket 
│   ├── Dockerfile             # Node.js dockerfile
│   ├── index.js               # WebSocket logic
│   └── package.json           # manifest file
└── nginx/
    └── nginx.conf             # Proxy configuration
```

## Key Features
✅ **Multi-container setup** with proper networking  
✅ **Port exposure** for both Laravel and Node.js services  
✅ **Volume mounting** for development workflow  
✅ **Reverse proxy** handling HTTP and WebSocket traffic  
✅ **Security** - The Laravel application is served strictly from the /public directory, ensuring that no sensitive files outside of /public are exposed to the web server.

## Short Note
⚠️ In Laravel, the /public directory is the only directory meant to be web-accessible. It contains the index.php front controller, compiled assets, and other public resources.

Sensitive files such as .env, configuration files, migrations, and application code live outside the /public directory. When serving through a web server like Nginx or Apache, it is best practice to serve the application only from /public. This ensures those files are never exposed to the browser, protecting your application secrets and business logic.

## Management Commands
```bash
# build services
docker-compose build

# Start services
docker-compose up

# Stop services  
docker-compose down

# View logs
docker-compose logs -f
```

---

**Deliverable**: Dockerized Laravel + Node.js environment with docker-compose orchestration and exposed ports as requested.
