# 📌 SEMED Workshops Registration System 🇧🇷

![GitHub repo size](https://img.shields.io/github/repo-size/louanfontenele/SEMED-Workshops?style=flat-square)
![GitHub contributors](https://img.shields.io/github/contributors/louanfontenele/SEMED-Workshops?color=blue&style=flat-square)
![GitHub license](https://img.shields.io/github/license/louanfontenele/SEMED-Workshops?style=flat-square)
![Made with ChatGPT](https://img.shields.io/badge/Made%20with-ChatGPT%20o3--mini--high-blueviolet?style=flat-square)

## 📖 About

The **SEMED Workshops Registration System** is a complete web-based application for managing workshop registrations in **Chapadinha, Maranhão, Brazil**. It was built 100% using **ChatGPT o3-mini-high**, ensuring an efficient and robust implementation.

This system allows teachers, school administrators, and other education professionals to **register for workshops**, **consult their registration**, and **administrators can manage participants**, all in real time! ⏳✨

## 🎯 Features

✅ **Workshop Registration** – Users can register for workshops based on their professional area.  
✅ **Real-Time Vacancy Updates** – The system dynamically updates available workshop slots **in real time**.  
✅ **Admin Panel** – Allows administrators to edit, delete, and reset registrations with proper vacancy adjustments.  
✅ **Automatic CPF Validation** – Prevents duplicate registrations.  
✅ **Workshop Management** – Admins can update workshop details, either **keeping existing vacancies** or **resetting them**.  
✅ **Export to Excel** – Generate reports in `.xls` format for easy analysis.  
✅ **Secure Authentication** – Admin login with `.env` credentials.  
✅ **Fully Responsive Design** – Works smoothly on **desktop and mobile** devices.  
✅ **SQLite / MySQL Support** – Can be configured to work with either SQLite or MySQL.

---

## 🚀 Installation

Follow these steps to set up the project on your local machine:

### 📌 Prerequisites

- PHP 7+ installed
- SQLite **or** MySQL database
- Web server (Apache, Nginx, etc.)

### 📌 Steps

1️⃣ Clone this repository:

```sh
git clone https://github.com/louanfontenele/SEMED-Workshops.git
cd SEMED-Workshops
```

2️⃣ Install dependencies (if needed for extensions):

```sh
composer install
```

3️⃣ **Configure your database:**

- **SQLite** (default): The system will automatically create a `data.db` file in the project folder.
- **MySQL**: Edit the `.env` file and set:

  ```env
  DB_DRIVER=mysql
  DB_HOST=localhost
  DB_NAME=semedforms
  DB_USER=root
  DB_PASS=yourpassword
  ```

4️⃣ **Run the installation script** (creates tables and initializes the system):

```sh
php install.php
```

5️⃣ Start your local PHP server (for testing):

```sh
php -S localhost:8000
```

Then, open `http://localhost:8000` in your browser. 🎉

---

## 🛠️ Usage

### 🔹 User Registration Flow

1. Open the homepage.
2. Fill in personal details, including **CPF validation**.
3. Select an available workshop (**real-time slot availability**).
4. Review and confirm the registration.
5. Receive confirmation with **Google Maps integration** (if applicable).

### 🔹 Admin Panel

- **Login:** Use the credentials set in `.env`.
- **Manage registrations:** Edit or delete users, updating workshop slots automatically.
- **Reset database:** Removes all registrations but keeps workshops.
- **Update Workshops:** Two modes:
  - **Full Update (Keeping Vacancies)** – Updates only descriptions, locations, etc.
  - **Reset and Update** – Resets all workshops and re-imports from `oficinas.php`.

### 🔹 Export Data

Admins can export all registrations to an **Excel file (`.xls`)** for further analysis.

---

## 🎨 UI & Styling

The design follows a **clean and modern approach**, inspired by **Material Design principles**:

- **Intuitive navigation** 🧭
- **Awesomplete-powered inputs** 🔍
- **Mobile-friendly responsive layout** 📱
- **Consistent color scheme matching SEMED’s branding** 🎨

---

## 🛡️ Security Considerations

🔒 **Protected Admin Access** – Only authorized users can manage registrations.  
🔒 **Sanitized Inputs** – Prevents SQL injections and XSS attacks.  
🔒 **No Direct Database Modifications** – All operations go through **secure PHP prepared statements**.

---

## 👨‍💻 Contributing

We welcome contributions! To contribute:

1. **Fork the repository** 🍴
2. **Create a new branch** (`feature/amazing-feature`) 🌿
3. **Commit your changes** (`git commit -m "Added a cool feature"`).
4. **Push the branch** (`git push origin feature/amazing-feature`) 🚀
5. **Create a Pull Request** 🔄

---

## 📜 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

## 🏆 Acknowledgments

Special thanks to **SEMED Chapadinha** for their initiative in creating this platform for **improving professional education** in public schools. 🎓✨

This system was built **entirely using ChatGPT o3-mini-high**, showcasing the power of AI-assisted development. 🚀💡

---

## 🤝 Contact

📩 **Email:** <organizacao@semed.gov.br>  
📞 **Phone:** +55 (11) 98765-4321

Visit **[SEMED Official Website](https://www.chapadinha.semed.br)** for more information. 🌍
