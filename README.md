Here’s a complete `README.md` for your **Telemedicine Web App** using **PHP, JavaScript, HTML, and CSS**, with features like appointment scheduling, medical history sharing, and doctor response handling.

---

```md
# Telemedicine Web Application

A virtual consultation system connecting patients and doctors. Built with PHP, MySQL, JavaScript, HTML, and CSS.

---

## 🔧 Features

- ✅ Patient registration and login
- ✅ Doctor login
- ✅ Appointment scheduling by patients
- ✅ Appointment response by doctors (Accept / Reject / Complete)
- ✅ Medical history upload by patients
- ✅ Medical history viewing and download by doctors
- ✅ Role-based access (patient / doctor)

---



## ⚙️ Requirements

- PHP >= 8.0
- MySQL
- Apache (XAMPP/Laragon/etc.)
- A modern browser

---

## 📦 Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/telemedicine-app.git
cd telemedicine-app
````

CREATE DATABASE telemedicine;

USE telemedicine;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('patient', 'doctor')
);

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT,
  doctor_id INT,
  date DATE,
  time TIME,
  status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
  FOREIGN KEY (patient_id) REFERENCES users(id),
  FOREIGN KEY (doctor_id) REFERENCES users(id)
);

CREATE TABLE medical_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT,
  file_name VARCHAR(255),
  file_path VARCHAR(255),
  description TEXT,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES users(id)
);
```

### 2. Configure the Backend

- Update `backend/config/db.php` with your DB credentials:

```php
$host = 'localhost';
$db = 'telemedicine';
$user = 'root';
$pass = '';
```

### 3. Set Up File Permissions

- Ensure the `uploads/history/` folder is writable:

```bash
chmod -R 755 backend/uploads/history
```

---

## 🧪 How to Use

### 🧑‍💼 Patient

1. Register and log in.
2. Book an appointment by selecting a doctor, date, and time.
3. Upload medical history files with descriptions.
4. Track appointment status in your dashboard.

### 👨‍⚕️ Doctor

1. Log in with your credentials.
2. View assigned appointments on the dashboard.
3. Accept, reject, or complete appointments.
4. View and download the patient’s medical history.

---

## 📂 Important Notes

- App uses PHP sessions for authentication.
- `middleware/auth.php` guards private routes.
- Use Postman or frontend forms to test endpoints.
- File download links are dynamically generated via `view.php`.

---

## 📮 API Endpoints Summary

| Endpoint                    | Method | Role    | Description                             |
| --------------------------- | ------ | ------- | --------------------------------------- |
| `/auth/register.php`        | POST   | Public  | Register a new patient                  |
| `/auth/login.php`           | POST   | Public  | Login as patient or doctor              |
| `/appointments/create.php`  | POST   | Patient | Book an appointment                     |
| `/appointments/respond.php` | POST   | Doctor  | Accept/Reject/Complete appointment      |
| `/appointments/view.php`    | GET    | Doctor  | View doctor’s appointments              |
| `/history/upload.php`       | POST   | Patient | Upload medical file                     |
| `/history/view.php`         | GET    | Doctor  | View and download patient history files |


---
