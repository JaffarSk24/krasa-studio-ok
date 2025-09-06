# Krása štúdio "OK" - Website

A professional multilingual website for a beauty studio in Bratislava.

---

## ✨ Features

- **Multilingual support**: Slovak (primary), Russian, Ukrainian  
- **Modern design**: Responsive layout using TailwindCSS  
- **Booking system**:
  - Service categories and services fully managed from DB
  - Dynamic service dropdown (filtered by category)
  - Booking with date & time slot management
  - Slot blocking/unblocking via Telegram or file system
  - Telegram notifications (pending/approved statuses)
  - Inline approval button for admins in Telegram
- **Integrations**:
  - Telegram Bot for booking notifications and slot control
  - Google reCAPTCHA v3 for form protection
  - Google Maps iframe for location
  - WhatsApp buttons for quick chat
- **Content sections**: Services, Price list, Gallery, Blog, Reviews, About, Contacts  
- **SEO Ready**:
  - Localized meta tags and titles
  - Structured data (JSON-LD)
  - Optimized multilingual URLs
  - Indexable content on pricing page
- **Security**:
  - reCAPTCHA
  - SQL injection safe (PDO prepared statements)
  - XSS protection via htmlspecialchars()
  - CSRF request origin check  

---

## 🛠 Technologies

- **Backend**: PHP 7.4+  
- **Database**: MySQL / MariaDB (UUID as PK)  
- **Frontend**: HTML5, CSS3 (TailwindCSS), JavaScript  
- **Admin panel**: Prepared for Textolite v2.12e Extended  

---

## 🚀 Installation

### 1. Upload project files
Upload the entire project folder to your hosting (tested on Webglobe).

### 2. Database setup
- Create a MySQL/MariaDB database  
- Update credentials in `includes/config.php`:

```php
define('DB_HOST', 'your_host');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Initialization
- Run `setup.php?key=krasa_studio_setup_2024` in a browser  
- Wait until installation finishes  
- **Important**: Delete `setup.php` after installation!  

### 4. Integration setup
- Telegram Bot: add bot token in config.php  
- reCAPTCHA: add site key + secret in config.php  
- Google Maps: iframe embedded in contacts.php  

---

## 📂 Project structure

```
krasa_studio_php/
├── includes/
│   ├── config.php
│   ├── database.php
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   ├── header-extra.php
│   └── body-extra.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── api/
├── lang/
├── admin/
├── index.php
├── about.php
├── services.php
├── pricing.php
├── gallery.php
├── blog.php
├── contacts.php
└── setup.php
```

---

## 🔌 API Endpoints

- `api/booking.php` – Booking request processing  
- `api/contact.php` – Contact form processing  
- `api/services.php` – Fetch list of services  
- `api/time-slots.php` – Fetch available slots  
- `api/reviews.php` – Fetch reviews  
- `api/gallery.php` – Fetch gallery images  

---

## 📅 Booking System

### Booking Form
- Select **Category** → subselection of **Service**  
- Date picker only shows working days and non-blocked slots  
- Time slots (09:00–21:00, customizable step = 1h)  
- Fields: client name, phone, optional message  
- reCAPTCHA protection  
- Booking request stored in DB with pending status  
- Immediate notification to Telegram channel  

### Booking Flow
1. Client creates booking  
2. System checks:
   - Slot availability
   - Blocked slot list (`blocked_slots.txt`)
   - reCAPTCHA validation
3. Booking saved in DB  
4. Telegram Bot posts new booking with inline "Approve" button  
5. Admin approves via button → booking confirmed, slot blocked, Telegram message updated  

---

## 📲 Telegram Bot

### Features:
- Sends new booking requests immediately  
- Inline button `[Approve]` to confirm booking  
- On approval edits message:

```
✅ Booking confirmed
📅 Date ⏰ Time
👤 Client name
📞 Phone number
💆 Service
```

### Manual slot management:
- Block: `/block YYYY-MM-DD HH:MM`  
- Unblock: `/add YYYY-MM-DD HH:MM`  

**Note**: Bot must be **Admin** in the channel with posting rights.  

---

## 🌐 Languages

- SK – Slovak (primary)  
- RU – Russian  
- UA – Ukrainian  

Detection: browser language or `?lang` param.  

---

## 🔍 SEO

- Localized meta tags  
- Structured data (JSON-LD)  
- Optimized multilingual URLs  
- Indexable price list  

---

## 🔒 Security

- reCAPTCHA v3  
- PDO prepared statements  
- htmlspecialchars() for XSS  
- CSRF protection  

---

## 🧪 Debugging

- `booking.php` provides detailed JSON debug array  
- Example:

```
STEP 1: booking.php started
STEP 2: Required fields OK
STEP 3: reCAPTCHA passed
STEP 4: slotKey=...
STEP 5: Slot is free
STEP 6: Booking inserted
STEP 7: Loaded service+category
STEP 8: Sent to Telegram
```

---

## 🧑‍💻 Usage Example

1. User opens booking form  
2. Selects service  
3. Submits  
4. Booking saved as pending + Telegram notification  
5. Admin clicks Approve → booking confirmed  

---

## 📝 Changelog

### v1.0.0 – Initial release
- First deployment (static content + basic booking)  

### v1.1.0 – DB services
- Introduced `services` + `categories`  
- Booking integrated with DB  

### v1.2.0 – Telegram integration
- Bot notifications + inline Approve  

### v1.3.0 – Time slots
- Automatic slots generation  
- Block/unblock system  

### v1.4.0 – Security
- reCAPTCHA v3 + debug logging  

### v1.5.0 – Current
- Approval flow shows client name, phone, service  
- Service_id linking to DB  
- Cleaner DB architecture  
- Frontend UX improvements  

---

## 📧 Support

For technical support, contact the developer.  

---

## 📜 License

All rights reserved © 2025 Krása štúdio "OK" s.r.o.  
Design by White Eagles & Co. s.r.o.  
