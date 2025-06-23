# Concurring-Prices

## Description
A simple PHP laravel app intended to showcase the different prices of products in stores across Macedonia.

---

## Prerequisites

Ensure you have the following software installed on your machine:

- **PHP** (for Laravel framework)
- **Composer** (for managing PHP dependencies)
- **Python 3.x** (for running Python scripts)
- **pip** (Python package manager)
- **Virtualenv** (for Python virtual environment)

Additionally, ensure that you have a working MySQL database setup for Laravel migrations. The database should be named concurringprices

---

## Installation Instructions

Follow the steps below to set up and run the project:

### 1. Install PHP Dependencies

Start by installing the required PHP dependencies using Composer:

```bash
composer install
```

### 2. Set Up Python Virtual Environment

Create a Python virtual environment to isolate your project’s dependencies:

For Windows & Mac/Linux:

```bash
py -m venv venv
```

### 3. Activate the Virtual Environment

To activate the virtual environment:

#### For Windows (Command Prompt):
```bash
cd venv\Scripts
activate
```

#### For Windows (Powershell):
```bash
cd venv\Scripts
./activate
```

#### For Unix/macOS
```bash
source venv/bin/activate
```

### 3. Install Python Dependencies

With the virtual environment activated, return to the root directory and install the necessary Python dependencies by running:

```bash
cd ../..
pip install -r requirements.txt
```


### 4. Run the Laravel Migrations

To set up your database and run the necessary migrations, use the following command:

```bash
php artisan migrate
```


### 5. Running the Scraper Script

To execute the scraper script and start scraping the required data, use the following command:

```bash
python scripts/scraper.py
```
### 6. Generate App Key & Run the Development Server

```bash
php artisan key:generate
php artisan serve
```