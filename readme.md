# Sistem Pengaduan Online

Website pengaduan online berbasis PHP Native dengan UI minimalis terinspirasi YouTube Studio.

## 🚀 Fitur

### Fitur Publik
- ✅ Buat pengaduan (anonim atau dengan identitas)
- ✅ Lihat daftar pengaduan
- ✅ Cek status pengaduan dengan nomor tiket
- ✅ Filter berdasarkan kategori dan status
- ✅ Pencarian pengaduan

### Fitur Admin
- ✅ Login admin
- ✅ Dashboard statistik
- ✅ Kelola semua pengaduan
- ✅ Ubah status pengaduan (Menunggu, Diproses, Selesai, Ditolak)
- ✅ Export data ke CSV
- ✅ Lihat detail pengaduan lengkap

## 🛠️ Tech Stack

- **Backend**: PHP 8.2 Native (No Framework)
- **Frontend**: HTML + CSS (Bootstrap 5)
- **Database**: MySQL 8.0
- **Containerization**: Docker
- **No JavaScript** - Pure PHP form submissions

## 📦 Instalasi

### Prasyarat
- Docker
- Docker Compose

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone <repository-url>
cd sistem-pengaduan-online
```

2. **Jalankan Docker**
```bash
docker-compose up -d
```

3. **Akses Aplikasi**
- Website: http://localhost:8000
- phpMyAdmin: http://localhost:8080
- Admin Login: http://localhost:8000/admin/login.php

4. **Login Admin**
- Username: `admin`
- Password: `admin123`

## 📁 Struktur Folder

```
sistem-pengaduan-online/
├── docker-compose.yml
├── Dockerfile
├── README.md
├── sql/
│   └── init.sql
└── src/
    ├── index.php
    ├── daftar-pengaduan.php
    ├── cek-pengaduan.php
    ├── admin/
    │   ├── login.php
    │   ├── dashboard.php
    │   ├── pengaduan.php
    │   ├── detail-pengaduan.php
    │   ├── export-csv.php
    │   └── logout.php
    ├── includes/
    │   ├── config.php
    │   ├── functions.php
    │   ├── header.php
    │   ├── footer.php
    │   ├── admin-header.php
    │   └── admin-footer.php
    └── assets/
        └── css/
            ├── style.css
            └── admin-style.css
```

## 🎨 Kategori Pengaduan

1. **Infrastruktur** - Jalan, jembatan, fasilitas umum
2. **Kebersihan** - Sampah, kebersihan lingkungan
3. **Keamanan** - Keamanan, premanisme, pencurian
4. **Pelayanan** - Layanan publik, administrasi
5. **Lainnya** - Kategori lain

## 📊 Status Pengaduan

- 🟡 **Menunggu** - Pengaduan baru masuk
- 🔵 **Diproses** - Sedang ditindaklanjuti
- 🟢 **Selesai** - Pengaduan selesai ditangani
- 🔴 **Ditolak** - Pengaduan ditolak

## 🔒 Keamanan

- Password hashing menggunakan `password_hash()`
- Prepared statements untuk query database
- Input sanitization dan validation
- Session management untuk admin
- CSRF protection ready

## 📝 Database

Database akan otomatis dibuat saat pertama kali menjalankan Docker. Schema database ada di file `sql/init.sql`.

### Tabel Utama

1. **pengaduan** - Menyimpan data pengaduan
2. **admin** - Menyimpan data admin

## 🖥️ Screenshots

### Halaman Utama
Formulir pengaduan dengan opsi anonim

### Dashboard Admin
Statistik dan monitoring pengaduan

### Kelola Pengaduan
Filter, search, dan export CSV

## 👨‍💻 Developer

**Dafa al hafiz**  
NIM: 24_0085

---

© 2025 Dafa al hafiz - 24_0085. All rights reserved.

## 📄 License

This project is for educational purposes.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## ⚠️ Notes

- Pastikan Docker sudah running sebelum menjalankan `docker-compose up`
- Database akan otomatis ter-initialize dengan data sample
- Ganti password admin default setelah instalasi pertama
- Untuk production, tambahkan SSL/HTTPS

## 🔧 Troubleshooting

### Port sudah digunakan
Jika port 8000, 8080, atau 3306 sudah digunakan, edit file `docker-compose.yml` dan ubah port mapping.

### Database connection error
Tunggu beberapa saat agar MySQL container selesai inisialisasi. Biasanya butuh 30-60 detik pada run pertama.

### Permission denied
Pastikan user Anda memiliki akses ke Docker daemon:
```bash
sudo usermod -aG docker $USER
```

## 📞 Support

Jika ada pertanyaan atau issue, silakan buat issue di GitHub repository.