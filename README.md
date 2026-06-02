# Akanksha Analytical & Research Lab — College Project
## Full-Stack Website with Admin Panel

---

## 🚀 How to Run (NO server needed!)

Just **double-click `index.html`** in any browser — Chrome, Firefox, Edge.
That's it. No server. No PHP. No database setup required.

---

## 📂 Project Structure

```
akanksha_lab/
├── index.html              ← Main Website (open this!)
├── admin/
│   └── index.html          ← Admin Panel
├── img/                    ← All images
├── config/
│   └── database.php        ← PHP DB config (for live hosting)
├── api/                    ← PHP API endpoints (for live hosting)
│   ├── auth.php
│   ├── dashboard.php
│   ├── contact.php
│   ├── jobs.php
│   └── services.php
├── includes/
│   └── helpers.php         ← PHP helpers (for live hosting)
├── akanksha_analytical_db.sql  ← MySQL schema (for live hosting)
└── README.md
```

---

## 🔐 Admin Panel Login

- **URL:** `admin/index.html`
- **Username:** `admin`
- **Password:** `Admin@1234`

Admin link is also in the website navbar (🔒 Admin button, top-right).

---

## ✨ Features

### Website (index.html)
- Fully responsive design
- Hero section with animations
- About, Services, Directors
- Certifications (ISO 9001, ISO 45001, NABL, CPCB)
- Lab Gallery, Infrastructure
- Testimonials, Projects
- Careers / Job Listings
- Contact Form (saves to browser + sends email via Web3Forms)

### Admin Panel (admin/index.html)
| Feature | Description |
|---------|-------------|
| 📊 Dashboard | Live stats, inquiry trend chart, recent submissions |
| 📬 Inquiries | View, filter, update status, delete contact form submissions |
| 💼 Jobs | Add/edit/delete job openings shown on website |
| 🧪 Services | Add/edit/delete laboratory services |
| ⚙️ Settings | Update lab contact info, change admin password |
| 💾 Database | Export all data as JSON, reset to demo data |

---

## 💾 Database (localStorage)

The project uses **browser localStorage** as its database — perfect for college demos.

- Contact form submissions → auto-saved when visitor submits
- Jobs and Services → managed from Admin Panel
- All data persists between browser sessions
- No MySQL / XAMPP needed

### For Live Hosting (Optional)
If you want to deploy on a real server (XAMPP/hosting):
1. Import `akanksha_analytical_db.sql` into MySQL/phpMyAdmin
2. Edit `config/database.php` — set your DB username & password
3. Upload all files to your hosting
4. The PHP APIs will take over automatically

---

## 🛠️ Technologies Used

| Technology | Purpose |
|-----------|---------|
| HTML5 / CSS3 | Structure & styling |
| JavaScript (ES6+) | Interactivity & localStorage DB |
| Three.js | 3D hero background animation |
| Font Awesome 6 | Icons |
| AOS.js | Scroll animations |
| PHP 8+ | Backend API (for live hosting) |
| MySQL 8+ | Database (for live hosting) |
| Web3Forms | Contact form email delivery |

---

## 📧 Contact Form

The contact form uses **Web3Forms** (free) to send emails to `akankshalab2007@gmail.com`.
All submissions are also stored in the Admin Panel → Inquiries section.

---

*Project developed for college submission — May 2026*
