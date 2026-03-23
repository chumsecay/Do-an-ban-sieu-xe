# Carserv local dev (PHP + MariaDB)

## 1) Prerequisites
- Docker Desktop (Windows)

## 2) Setup env file
```powershell
Copy-Item .env.example .env
```

Edit `.env` to set app info and DB credentials.

## 3) Start services
```powershell
docker compose up -d --build
```

## 4) Access
- Website: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- MariaDB from app: `db:3306`
- MariaDB from host: `127.0.0.1:3307`

## 5) Main PHP pages
- `/` or `/index.php`
- `/showroom.php`
- `/about.php`
- `/contact.php`

## 6) Stop
```powershell
docker compose down
```

Remove DB volume too:
```powershell
docker compose down -v
```
