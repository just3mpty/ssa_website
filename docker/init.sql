CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    email         VARCHAR(255) NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    date_event  DATE NOT NULL,
    hours       TIME NOT NULL,
    lieu        VARCHAR(255),
    image       VARCHAR(255),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    author_id   INT NOT NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contacts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    message    TEXT NOT NULL,
    ip         VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;



INSERT INTO users (username, password_hash, role, email) 
VALUES ('admin', '$2y$12$DdRaR1i6wNQbPGxbmgeB9OvAnhSzFvN98/wIBdO3w0Qcqsu62BMEy','admin', 'admin@example.org');

INSERT INTO events (titre, description, date_event, hours, lieu, image, author_id)
VALUES
  ('Réunion mensuelle', 'Présentation des avancées du projet', '2025-08-01', '18:00:00', 'Salle des fêtes', NULL, 1),
  ('Atelier alimentation durable', 'Initiation à la cuisine locale et responsable.', '2025-08-15', '14:00:00', 'Centre social', NULL, 1),
  ('Assemblée générale', 'AG annuelle de lassociation.', '2025-09-10', '17:00:00', 'Mairie de Morlaix', NULL, 1);
