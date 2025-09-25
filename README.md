# Dockerized Laravel + Node.js Application

## Task Objective
Set up a Dockerized environment for a Laravel backend and Node.js WebSocket server with docker-compose that runs both services and exposes ports.

## Architecture
- **Laravel 10** - Backend API (PHP 8.2-fpm)  
- **Node.js** - WebSocket server for real-time communication
- **Nginx** - Reverse proxy handling HTTP and WebSocket traffic
- **Docker Compose** - Container orchestration

## Prerequisites
- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose v2.0+
- Git

## Quick Setup

### 1. Clone Repository
```bash
git clone https://github.com/Aaronthegreat-star/Virtual-Card-Platform.git
cd Virtual-Card-Platform
```

### 2. Build and Start Services
```bash
# Build and start in one command 
docker-compose build
docker-compose up -d

# Check running services
docker-compose ps
```

### 3. Access Services
- **Laravel API**: http://localhost (port 80)
- **WebSocket Server**: ws://localhost/ws (port 8081 exposed)

## Container Details

| Service | Image | Exposed Port | Internal Port |
|---------|-------|--------------|---------------|
| Laravel API | Custom PHP 8.2-fpm | - | 9000 |
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
│   ├── Dockerfile             # PHP-FPM container
│   └── [Laravel files]
├── websocket/                  # Node.js WebSocket s
│   ├── Dockerfile             # Node.js container
│   ├── index.js               # WebSocket logic
│   └── package.json
└── nginx/
    └── nginx.conf             # Proxy configuration
```

## Key Features
✅ **Multi-container setup** with proper networking  
✅ **Port exposure** for both Laravel and Node.js services  
✅ **Volume mounting** for development workflow  
✅ **Reverse proxy** handling HTTP and WebSocket traffic  
✅ **Security** - Laravel served from /public directory only  

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

# Rebuild containers
docker-compose build --no-cache
```

---

**Deliverable**: Dockerized Laravel + Node.js environment with docker-compose orchestration and exposed ports as requested.
