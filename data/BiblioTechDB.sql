
-- Tabella Utenti
CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('user', 'admin') DEFAULT 'user',
    otp_code VARCHAR(6) DEFAULT NULL
) ENGINE=InnoDB;

-- Tabella Libri
CREATE TABLE IF NOT EXISTS libri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    autore VARCHAR(255),
    quantita_totale INT NOT NULL,
    quantita_disponibile INT NOT NULL
) ENGINE=InnoDB;

-- Tabella Prestiti
CREATE TABLE IF NOT EXISTS prestiti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_libro INT NOT NULL,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fine DATETIME NULL,
    stato ENUM('attivo', 'restituito') DEFAULT 'attivo',
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libri(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabella Sessioni
CREATE TABLE IF NOT EXISTS sessioni (
    session_id VARCHAR(255) PRIMARY KEY,
    user_id INT NULL,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_attivita DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    scadenza DATETIME NOT NULL,
    data_logout DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Inserimento Libri di esempio
INSERT INTO libri (titolo, autore, quantita_totale, quantita_disponibile) VALUES 
('Il nome della rosa', 'Umberto Eco', 3, 3),
('1984', 'George Orwell', 5, 5),
('Cronaca di una morte annunciata', 'Gabriel García Márquez', 2, 2);