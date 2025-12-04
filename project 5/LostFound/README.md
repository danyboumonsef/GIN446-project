# 📦 Lost & Found Hub

A full-stack Lost & Found web application built using **HTML, CSS, JavaScript, PHP, MySQL, XML, and JSON**.  
Users can post lost/found items, browse all items, and contact the poster.  
Admins have additional control to delete posts.

---

## 🚀 Features

### User Features
- Sign up / Login / Logout  
- Post lost or found items (+ photo upload)  
- Search, filter, and sort items  
- View item details in a modal  
- Contact item owner

### Admin Features
- Admins see a **Delete** button on every post  
- Admins can delete any item  
- Owners can delete their own posts  
- Role stored safely in the database

---

## 🏗 How It Works

### Viewing Items


MySQL → get_items.php → XML → JavaScript → Homepage



### Performing Actions (Delete / Add)


JavaScript → JSON → PHP → MySQL



### Authentication


login.php → PHP Sessions



---

## 🗂 File Structure (Simple)


```
lostfound/
├── home.html
├── login.php
├── signup.php
├── add_item.php
├── delete_item.php
├── get_items.php
├── db.php
│
└── assets/
├── style.css
└── script.js
└── profile.js
```

---

## 🗄 Database

### users
- id  
- name  
- email  
- password  
- phone  
- role (`user` or `admin`)

### items
- id  
- item_name  
- description  
- category  
- status (Lost/Found)  
- location  
- date  
- photo  
- user_id  
- returned  

Admins are created manually in phpMyAdmin:

---

## ✔ Status

Fully functional: supports multiple admins, XML-based loading, JSON-based actions, and secure role handling.