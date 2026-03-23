# Ð? án bán siêu xe (FLCar)

Website showroom xe sang, xây d?ng b?ng PHP + MySQL.
D? án h? tr? c? 2 cách ch?y:
- XAMPP (Apache + MySQL local)
- Docker Compose (PHP + MariaDB + phpMyAdmin)

## 1) Ch?y b?ng XAMPP

1. Ð?t source code vào:
   - `C:\xampp\htdocs\Do-an-ban-sieu-xe`
2. T?o `.env`:
```powershell
Copy-Item .env.xampp.example .env
```
3. M? XAMPP Control Panel và b?t `Apache`, `MySQL`.
4. Vào `http://localhost/phpmyadmin`, t?o DB `flcar_db`.
5. Import schema: `database/schema.sql`.
6. Truy c?p:
   - `http://localhost/Do-an-ban-sieu-xe/`
   - `http://localhost/Do-an-ban-sieu-xe/admin/`

## 2) Ch?y b?ng Docker

1. T?o `.env`:
```powershell
Copy-Item .env.docker.example .env
```
2. Ch?y:
```powershell
docker compose up -d --build
```
3. Import schema:
   - Vào phpMyAdmin: `http://localhost:8081`
   - DB host: `db`
   - User/pass theo `.env` (m?c ð?nh `carserv` / `carserv123`)
   - Import `database/schema.sql` vào DB `flcar_db`
4. Truy c?p:
   - Website: `http://localhost:8080`
   - phpMyAdmin: `http://localhost:8081`

D?ng Docker:
```powershell
docker compose down
```

## 3) Lýu ?

- Docker DB map c?ng m?c ð?nh `3306` (`DB_HOST_PORT=3306`).
- N?u ðang b?t MySQL c?a XAMPP, có th? b? trùng c?ng 3306. Khi ðó:
  - Ho?c t?t MySQL XAMPP khi ch?y Docker
  - Ho?c ð?i `DB_HOST_PORT` trong `.env` Docker sang c?ng khác (ví d? `3307`)

## 4) Ki?m tra k?t n?i nhanh

N?u g?p l?i DB, m? `test_db.php` ð? ki?m tra c?u h?nh `.env`.
