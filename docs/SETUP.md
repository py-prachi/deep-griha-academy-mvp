# 🚀 DGA Project Setup Guide

Everything you need to get the DGA School Management System running on your machine.
Follow each step in order. Don't skip anything.

---

## Prerequisites — Install These First

- [ ] [Docker Desktop](https://www.docker.com/products/docker-desktop/) — download and install
- [ ] [Git](https://git-scm.com/downloads) — download and install
- [ ] A code editor — [VS Code](https://code.visualstudio.com/) recommended

---

## Step 1 — Clone the Repository
```bash
git clone https://github.com/py-prachi/deep-griha-academy-mvp.git
cd deep-griha-academy-mvp
```

## Step 2 — Switch to develop branch
```bash
git checkout develop
```

Always work on `develop` or a feature branch. Never directly on `main`.

---

## Step 3 — Create your `.env` file
```bash
cp .env.example .env
```

Then open `.env` and update these lines:
```env
APP_NAME="Deep Griha Academy"
APP_URL=http://localhost:8080
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=dga_school
DB_USERNAME=root
DB_PASSWORD=root
```

---

## Step 4 — Create the storage folder
```bash
mkdir -p storage/app/purify
```

---

## Step 5 — Start Docker
```bash
docker-compose up -d
```

Wait 30 seconds then verify:
```bash
docker ps
```

You should see 3 containers running: `app`, `db`, `nginx`.

---

## Step 6 — Create the database
```bash
docker exec -it db sh
mysql -u root -proot
```

Inside MySQL:
```sql
CREATE DATABASE dga_school;
GRANT ALL ON dga_school.* TO 'dga_school'@'%' IDENTIFIED BY 'secret';
FLUSH PRIVILEGES;
EXIT;
```

Then exit Docker:
```bash
exit
```

---

## Step 7 — Install dependencies and setup Laravel
```bash
docker exec -it app sh
composer install
php artisan key:generate
php artisan config:cache
```

---

## Step 8 — Run migrations and seed
```bash
php artisan migrate:fresh --seed
exit
```

---

## Step 9 — Open the app
```
http://localhost:8080
Email:    admin@deepgriha.com
Password: dga@admin2026
```

---

## If Already Set Up — Sync Latest Changes
```bash
git checkout develop
git pull origin develop
docker exec -it app sh
php artisan migrate:fresh --seed
exit
```

---

## Daily Workflow
```bash
# Pull latest before starting
git checkout develop
git pull origin develop

# Create feature branch
git checkout -b feature/your-feature-name

# When done
git add .
git commit -m "Description of what you built"
git push origin feature/your-feature-name
# Then raise a Pull Request: feature/xxx → develop
```

---

## Branch Structure
```
main        → stable production code only
develop     → main working branch
feature/xxx → individual feature branches
```

---

## Login Credentials

| Role    | Email                      | Password        |
|---------|----------------------------|-----------------|
| Admin   | admin@deepgriha.com        | dga@admin2026   |
| Teacher | anita.sharma@deepgriha.com | dga@teacher2026 |
| Teacher | nikita.verma@deepgriha.com | dga@teacher2026 |
| Teacher | neena.gupta@deepgriha.com  | dga@teacher2026 |

⚠️ Change all passwords after first login!

---

## If Something Goes Wrong
```bash
docker exec -it app sh
php artisan migrate:fresh --seed
exit
```

Safe to run anytime — rebuilds database from scratch.

---

## Repository

https://github.com/py-prachi/deep-griha-academy-mvp
