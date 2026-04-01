# PropFlow - Rental Property Management System

PropFlow is a robust, full-stack web application designed to streamline the rental process for landlords, tenants, and administrators. Built with PHP and MySQL, it provides distinct, role-based dashboards that improve payment transparency, simplify maintenance tracking, and keep historical property records organized.

## Key Features

- **Role-Based Access Control (RBAC):** Three distinct user experiences with strict session-based backend security for Admins, Landlords, and Tenants.
- **Payment Transparency:** Tenants have a clear view of their payment history, while landlords can track income and outstanding balances across their portfolio.
- **Maintenance Ticketing:** Tenants can submit categorized, prioritized maintenance requests that notify landlords instantly. Includes status tracking and resolution histories.
- **Smart Archiving (Soft Deletes):** Landlords can safely "archive" deprecated properties without destroying historical tenancy, payment, or maintenance data, moving them to a dedicated History view.
- **Dynamic Renting Tips API:** The tenant dashboard features an asynchronous, auto-rotating feed of renting tips fetched via JavaScript periodically.
- **Modern UI & Dark Mode:** A responsive frontend utilizing modern utility classes, glassmorphism, and a robust localStorage-persisted Dark Mode built with vanilla CSS variables and JS.

## Technology Stack

- **Backend:** PHP 8+
- **Database:** MySQL (Object-Oriented PDO / mysqli)
- **Frontend Structure:** HTML5
- **Styling:** CSS3, Tailwind CSS (Utility classes)
- **Interactivity:** Vanilla JavaScript (ES6+), Fetch API
- **Deployment/Environment:** XAMPP (Apache Server)

## Project Structure

```text
PropFlow/
├── actions/       # Backend handlers processing form POST requests (CRUD actions)
├── admin/         # Administrator views and dashboard
├── api/           # JSON endpoints for async JavaScript fetching (e.g., renting tips)
├── includes/      # Reusable UI components (headers, footers, sidebars)
├── landlord/      # Landlord specific views, property management, and history
├── public/        # Publicly accessible pages (login, registration, landing page)
├── settings/      # Core configurations (core.php session rules, db_class.php)
├── tenant/        # Tenant specific views, maintenance requests, and payments
└── propflow_db.sql # Database schema and seed data
```

## Installation & Setup (Local Environment)

To run this project locally, you will need a local server environment like **XAMPP**

1. **Clone or Move the Repository:**
   Place the `PropFlow` folder into your local server's public directory.
   - For XAMPP: `xamppfiles/htdocs/PropFlow` (Mac) or `C:\xampp\htdocs\PropFlow` (Windows)

2. **Start your Server:**
   Open your XAMPP Control Panel and start both **Apache** and **MySQL**.

3. **Set up the Database:**
   - Open your browser and navigate to `http://localhost/phpmyadmin`
   - Create a new, empty database named `propflow_db` (or whatever your `db_class.php` expects).
   - Click on the **Import** tab.
   - Select the `propflow_db.sql` file located in the root of this project folder and upload it to generate the necessary tables.

4. **Run the Application:**
   Open your browser and navigate to the project entry point:
   ```
   http://localhost/PropFlow/public/login.php
   ```

## Security Best Practices Implemented

- Strict Session enforcement across all `/admin`, `/landlord`, and `/tenant` directories.
- `mysqli_real_escape_string()` and/or prepared statements to prevent SQL injections on form submissions.
- Passwords should be hashed using `password_hash()` before database insertion.

## Future Roadmap

- Integration with PayPal APIs for real-time live payments.
- Automated Email and SMS notifications via Twilio/SendGrid integration.
- Graphical data visualization for landlord income vs. expense reporting.

---
### Project Details

- **Developer Name:** Hassan Maltiti Yakubu
- **Major:** Management Information Systems (MIS)
- **School:** Ashesi University
- **Year:** Class of 2026
- **Project Type:** CSIS Applied Capstone Project 2026
- **Supervisor Name:** Eric Ocran

