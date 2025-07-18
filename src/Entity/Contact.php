<?php
#
// namespace App\Entity;
//
// class Contact
// {
//
//     private ?int $id;
//     private ?string $email;
//     private ?string $message;
//     private ?string $message;
//     private ?string $created_at;
// }

// -- Table pour le compte admin (1 seul utilisateur, mais extensible)
// CREATE TABLE IF NOT EXISTS admin_users (
//     id            INTEGER PRIMARY KEY AUTOINCREMENT,
//     username      TEXT NOT NULL UNIQUE,
//     password_hash TEXT NOT NULL,   -- hashé avec password_hash() PHP
//     created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
// );
//
// -- Table pour les messages de contact
// CREATE TABLE IF NOT EXISTS contacts (
//     id         INTEGER PRIMARY KEY AUTOINCREMENT,
//     nom        TEXT NOT NULL,
//     email      TEXT NOT NULL,
//     message    TEXT NOT NULL,
//     ip         TEXT,
//     created_at DATETIME DEFAULT CURRENT_TIMESTAMP
// );
//
// -- Table pour les événements/agenda
// CREATE TABLE IF NOT EXISTS events (
//     id          INTEGER PRIMARY KEY AUTOINCREMENT,
//     titre       TEXT NOT NULL,
//     description TEXT NOT NULL,
//     date_event  DATETIME NOT NULL,
//     lieu        TEXT,
//     image       TEXT, -- chemin du fichier image lié, si besoin
//     created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
// );
//
// INSERT INTO admin_users (username, password_hash) VALUES (
//     'admin',
//     'admin'
// );
