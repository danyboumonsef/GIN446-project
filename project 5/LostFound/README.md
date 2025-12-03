📦 Lost & Found Hub

A full-stack Lost & Found web application built using
HTML, CSS, JavaScript, PHP, MySQL, XML, and JSON.

Users can post items, browse all items, and contact owners.
Admins have full control to delete any post.

🚀 Features
User Features

Sign up / Login / Logout

Post lost or found items (with photo upload)

Browse all posts with search & filters

View item details in a modal

Contact the item owner

Delete their own posts

Admin Features

Delete any post

Admin role stored securely in the database

Admins automatically see “Delete” on all items

🏗 How the System Works
Viewing Items Flow
MySQL → get_items.php → XML → JavaScript → Homepage

Performing Actions (Add / Delete)
JavaScript → JSON → PHP → MySQL

Authentication
login.php → PHP Sessions

🗂 File Structure (Clean GitHub-Friendly Version)
lostfound/
│
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
    ├── script.js
    └── profile.js

🗄 Database Structure
users

id

name

email

password

phone

role (user or admin)

items

id

item_name

description

category

status (Lost / Found)

location

date

photo

user_id

returned

Admin accounts are created manually in phpMyAdmin.

✔ Project Status

Fully functional:
Supports multiple admins, XML loading for items, JSON-based actions, and secure role handling.