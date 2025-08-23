<?php

function getDB(): PDO
{

    $dbFile = __DIR__ . '/database.sqlite';
    $createTables = !file_exists($dbFile);
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($createTables) {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (\n" .
            "    id INTEGER PRIMARY KEY AUTOINCREMENT,\n" .
            "    username TEXT NOT NULL,\n" .
            "    email TEXT NOT NULL UNIQUE,\n" .
            "    password TEXT NOT NULL,\n" .
            "    created_at DATETIME DEFAULT CURRENT_TIMESTAMP\n" .
            ");\n" .
            "CREATE TABLE IF NOT EXISTS foods (\n" .
            "    id INTEGER PRIMARY KEY AUTOINCREMENT,\n" .
            "    user_id INTEGER NOT NULL,\n" .
            "    nombre TEXT NOT NULL,\n" .
            "    descripcion TEXT NOT NULL,\n" .
            "    caducidad DATE NOT NULL,\n" .
            "    imagen TEXT,\n" .
            "    ubicacion TEXT,\n" .
            "    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,\n" .
            "    FOREIGN KEY(user_id) REFERENCES users(id)\n" .
            ");"
        );
    }
    return $pdo;
}

?>