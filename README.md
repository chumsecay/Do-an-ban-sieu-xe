# Do an ban sieu xe (FLCar)

Website showroom xe sang, xay dung bang PHP + MySQL.
Du an ho tro ca 2 cach chay:
- XAMPP (Apache + MySQL local)
- Docker Compose (PHP + MariaDB + phpMyAdmin)

## 1) Chay bang XAMPP

1. Dat source code vao:
   - `C:\xampp\htdocs\Do-an-ban-sieu-xe`
2. Tao `.env`:
```powershell
Copy-Item .env.xampp.example .env
```
3. Mo XAMPP Control Panel va bat `Apache`, `MySQL`.
4. Vao `http://localhost/phpmyadmin`, tao DB `flcar_db`.
5. Import schema: `database/schema.sql`.
6. Truy cap:
   - `http://localhost/Do-an-ban-sieu-xe/`
   - `http://localhost/Do-an-ban-sieu-xe/admin/`

## 2) Chay bang Docker

1. Tao `.env`:
```powershell
Copy-Item .env.docker.example .env
```
2. Chay:
```powershell
docker compose up -d --build
```
3. Import schema:
   - Vao phpMyAdmin: `http://localhost:8081`
   - DB host: `db`
   - User/pass theo `.env` (mac dinh `carserv` / `carserv123`)
   - Import `database/schema.sql` vao DB `flcar_db`
4. Truy cap:
   - Website: `http://localhost:8080`
   - phpMyAdmin: `http://localhost:8081`

Dung Docker:
```powershell
docker compose down
```

## 3) Luu y

- Docker DB map cong mac dinh `3306` (`DB_HOST_PORT=3306`).
- Neu dang bat MySQL cua XAMPP, co the bi trung cong 3306. Khi do:
  - Hoac tat MySQL XAMPP khi chay Docker
  - Hoac doi `DB_HOST_PORT` trong `.env` Docker sang cong khac (vi du `3307`)

## 4) Kiem tra ket noi nhanh

Neu gap loi DB, mo `test_db.php` de kiem tra cau hinh `.env`.
