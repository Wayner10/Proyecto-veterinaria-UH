<div id="top" align="center">

<h1>PROYECTO-VETERINARIA-UH</h1>
<p><em>Transforming Pet Care with Innovation and Compassion</em></p>

<img alt="last-commit" src="https://img.shields.io/github/last-commit/Wayner10/Proyecto-veterinaria-UH?style=flat&logo=git&logoColor=white&color=0080ff" style="margin:0 2px;">
<img alt="repo-top-language" src="https://img.shields.io/github/languages/top/Wayner10/Proyecto-veterinaria-UH?style=flat&color=0080ff" style="margin:0 2px;">
<img alt="repo-language-count" src="https://img.shields.io/github/languages/count/Wayner10/Proyecto-veterinaria-UH?style=flat&color=0080ff" style="margin:0 2px;">

<p><em>Built with the tools and technologies:</em></p>
<img alt="JSON" src="https://img.shields.io/badge/JSON-000000.svg?style=flat&logo=JSON&logoColor=white" style="margin:0 2px;">
<img alt="Markdown" src="https://img.shields.io/badge/Markdown-000000.svg?style=flat&logo=Markdown&logoColor=white" style="margin:0 2px;">
<img alt="Composer" src="https://img.shields.io/badge/Composer-885630.svg?style=flat&logo=Composer&logoColor=white" style="margin:0 2px;">
<img alt="JavaScript" src="https://img.shields.io/badge/JavaScript-F7DF1E.svg?style=flat&logo=JavaScript&logoColor=black" style="margin:0 2px;">
<img alt="PHP" src="https://img.shields.io/badge/PHP-777BB4.svg?style=flat&logo=PHP&logoColor=white" style="margin:0 2px;">

</div>

<br>
<hr>

## 📚 Table of Contents
- [Overview](#overview)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Usage](#usage)
  - [Testing](#testing)

---

## 🐶 Overview

**Proyecto-veterinaria-UH** is a comprehensive veterinary management system built with **PHP** and **CodeIgniter 4**, designed to streamline clinic operations from client management to billing. It offers a secure, scalable architecture with extensive features for both developers and end-users.

**Why Proyecto-veterinaria-UH?**  
This project simplifies the development of veterinary applications by integrating robust testing, CLI command facilitation, and security features. The core features include:

- 🧩 **Modular Architecture:** Facilitates easy extension and maintenance with dependency management.  
- 🛡️ **Security & User Management:** Implements access restrictions, session handling, and role-based controls.  
- 🧪 **Testing & Debugging:** Configured with PHPUnit and detailed error views for reliable development.  
- ⚙️ **CLI Command Integration:** Streamlines maintenance tasks with framework commands.  
- 🎯 **Rich User Interfaces:** Provides comprehensive views for clients, veterinarians, and admins.

---

## 🚀 Getting Started

### ✅ Prerequisites

This project requires the following dependencies:

- Programming Language: PHP  
- Package Manager: Composer

---

### 📦 Installation

Build **Proyecto-veterinaria-UH** from the source and install dependencies:

1. Clone the repository:  
   `git clone https://github.com/Wayner10/Proyecto-veterinaria-UH`

2. Navigate to the project directory:  
   `cd Proyecto-veterinaria-UH`

3. Install the dependencies:  
   `composer install`

---

### ▶️ Usage

Run the project with:  
`php spark serve`  

Then open in your browser:  
`http://localhost:8080`

---

### 🧪 Testing

**Proyecto-veterinaria-UH** uses the **PHPUnit** test framework. Run the test suite with:  
`vendor/bin/phpunit`

---

<div align="left"><a href="#top">⬆ Return</a></div>

<hr>

# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.  
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.  
It has been built from the [development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/) corresponding to the latest version of the framework.

---

## Installation & Updates

To create a new project:  
`composer create-project codeigniter4/appstarter`

To update the framework:  
`composer update`

When updating, check the release notes to see if there are any changes you might need to apply to your `app` folder.  
The affected files can be copied or merged from `vendor/codeigniter4/framework/app`.

---

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL and any database settings.

---

## Important Change with index.php

`index.php` is no longer in the root of the project — it has been moved inside the **public** folder for better security and separation of components.

You should configure your web server to point to your project's **public** folder, not the root.  
A better practice is to configure a virtual host to point there.

---

## Repository Management

We use GitHub issues in our main repository to track **BUGS** and approved **DEVELOPMENT** work packages.  
We use our [forum](http://forum.codeigniter.com) to provide **SUPPORT** and discuss **FEATURE REQUESTS**.

This repository is a "distribution" one, built by our release preparation script.  
Problems with it can be raised on our forum or as issues in the main repository.

---

## Server Requirements

PHP version **7.4 or higher** is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> ⚠️ **Warning:**  
> - PHP 7.4 EOL: November 28, 2022  
> - PHP 8.0 EOL: November 26, 2023  
> - PHP 8.1 EOL: November 25, 2024  
> Upgrade if you are still using older versions.

Also ensure these extensions are enabled:

- json (enabled by default - don't turn it off)  
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) – if using MySQL  
- [libcurl](http://php.net/manual/en/curl.requirements.php) – if using the HTTP\CURLRequest library
