# Custom WordPress Plugin Boilerplate

A clean and structured boilerplate for building custom WordPress plugins.  
Designed to be lightweight, scalable, and easy to extend.

---

## 📦 File Structure

    custom-plugin/
    │
    ├── custom-plugin.php
    ├── uninstall.php
    │
    ├── assets/
    │   ├── css/
    │   │   ├── public.css
    │   │   └── admin.css
    │   └── js/
    │       ├── public.js
    │       └── admin.js
    │
    └── inc/
        ├── Core/
        │   ├── class-starter.php
        │   ├── class-script-loader.php
        │   └── class-db.php
        │
        ├── Helpers/
        │   └── class-load-helpers.php
        │
        └── Features/
            └── custom-feature.php

---

## 🚀 Getting Started

1. Download the root folder (`custom-plugin`).
2. Rename:
   - `custom-plugin` (root folder)
   - `custom-plugin.php`
3. Perform a global **Find & Replace** (preserve casing and structure):

       custom_plugin
       CustomPlugin
       custom-plugin
       CUSTOMPLUGIN

4. Start adding your functionality inside:

       inc/Features/

---

## 🧩 What’s Included

- Structured bootstrap system
- Core / Features separation
- Script & style loader (frontend + admin)
- Database setup with version handling
- Clean uninstall procedure
- Namespaced architecture
- Version-based asset cache busting

---

## 📝 To-Do

- Add Ajax-integrated functionality
- Expand example feature modules

---

## 🔄 Recent Changes

- **[6 February 2025]**  
  Initial commit with base boilerplate structure.

- **[7 February 2025]**  
  Added script loading (CSS/JS) and database functionality.

- **[18 April 2025]**  
  Refactored file structure for easier setup and cleaner organization.

- **[9 September 2025]**  
  Added namespaces, structured classes, and improved script handling.

- **[16 February 2026]**  
  Major structural refinement: separated Core and Features architecture, implemented database versioning, automatic plugin version detection, improved asset handling, and finalized a clean, production-ready boilerplate foundation.
