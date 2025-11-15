# 📢 Sistem Pengaduan Online (SIPMO)

Sistem Informasi Pengaduan Masyarakat Online **(SIPMO)** adalah aplikasi berbasis **PHP** dan **MySQL** yang dirancang untuk memfasilitasi penyampaian laporan/pengaduan masyarakat secara online dengan proses tindak lanjut yang cepat, transparan, dan terstruktur.

---

# ⚙️ Fitur Utama

- **Pengaduan Anonim / Terautentikasi** — Pengguna dapat membuat laporan tanpa akun maupun dengan login.
- **Status Pelaporan** — Pantau perkembangan pengaduan: *Pending*, *Diproses*, *Selesai*.
- **Admin Dashboard** — Panel khusus untuk admin/petugas dalam mengelola dan menanggapi laporan.
- **Manajemen Data** — CRUD untuk data pengaduan, pengguna, dan tanggapan.
- **Autentikasi Sederhana & Aman** — Sistem login dengan session handling.

---

# 🛠️ Persyaratan Sistem
Untuk menjalankan aplikasi di lingkungan lokal, diperlukan:

- PHP **7.4+**
- MySQL / MariaDB
- Apache atau Nginx
- Composer *(opsional)*

**Rekomendasi:** Gunakan **Docker** untuk setup yang lebih cepat dan konsisten.

---

# 🚀 Panduan Instalasi (Development Mode)
Aplikasi ini sudah menyertakan **Docker Compose**, sehingga instalasi sangat mudah.

### 1️⃣ Clone Repositori
```bash
git clone https://github.com/dafagareth/SystemPengaduanOnline.git
cd SystemPengaduanOnline
```

### 2️⃣ Salin File Environment
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database di file **.env** bila diperlukan.

### 3️⃣ Jalankan Docker Compose
Perintah berikut akan membangun image, menjalankan web server dan database:
```bash
docker-compose up -d --build
```

### 4️⃣ Setup Database
Masuk ke container database:
```bash
docker exec -it <NAMA_CONTAINER_DB> mysql -u <DB_USER> -p<DB_PASS> <DB_NAME>
```
Import file SQL:
```sql
SOURCE /path/to/sql/init.sql;
```

### 5️⃣ Akses Aplikasi
- **Aplikasi Publik:** http://localhost:8000
- **Halaman Admin:** http://localhost:8000/src/admin/login.php

---

# 📦 Struktur Direktori Project
```
SystemPengaduanOnline/
├── sql/
│   └── init.sql                 # Skema database
├── src/
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── pengaduan.php
│   │   ├── detail-pengaduan.php
│   │   ├── export-csv.php
│   │   ├── login.php
│   │   └── logout.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css
│   │   │   ├── admin-style.css
│   │   │   └── login-style.css
│   │   └── js/
│   ├── includes/
│   │   ├── config.php
│   │   ├── functions.php
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── admin-header.php
│   │   └── admin-footer.php
│   ├── uploads/
│   ├── index.php
│   ├── cek-pengaduan.php
│   └── daftar-pengaduan.php
├── Dockerfile
├── docker-compose.yml
└── README.md
```

---

# 🤝 Kontribusi
Kontribusi sangat terbuka! Caranya:

1. **Fork** repositori ini.
2. Buat branch baru:
   ```bash
   git checkout -b fitur/nama-fitur
   ```
3. Commit perubahan:
   ```bash
   git commit -m "Menambahkan fitur: X"
   ```
4. Push ke branch Anda:
   ```bash
   git push origin fitur/nama-fitur
   ```
5. Ajukan **Pull Request** ke branch **main**.

---

Terima kasih telah menggunakan dan mengembangkan *Sistem Pengaduan Online (SIPMO)*! 🙌
