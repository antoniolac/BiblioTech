CREATE TABLE IF NOT EXISTS utenti (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo    ENUM('user', 'admin') DEFAULT 'user'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS otp_sessions (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT        NOT NULL,
    otp_code  VARCHAR(6) NOT NULL,
    creato_il DATETIME   DEFAULT CURRENT_TIMESTAMP,
    scadenza  DATETIME   NOT NULL,
    usato     TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sessioni (
    session_id      VARCHAR(255) PRIMARY KEY,
    user_id         INT          NULL,
    ip_address      VARCHAR(45)  DEFAULT NULL,
    user_agent      VARCHAR(255) DEFAULT NULL,
    data_inizio     DATETIME     DEFAULT CURRENT_TIMESTAMP,
    ultima_attivita DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    scadenza        DATETIME     NOT NULL,
    data_logout     DATETIME     DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS libri (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    titolo               VARCHAR(255) NOT NULL,
    autore               VARCHAR(255) NOT NULL,
    anno_pubblicazione   YEAR         DEFAULT NULL,
    quantita_totale      INT          NOT NULL DEFAULT 1,
    quantita_disponibile INT          NOT NULL DEFAULT 1,
    descrizione          TEXT         DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prestiti (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_utente   INT      NOT NULL,
    id_libro    INT      NOT NULL,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fine   DATETIME NULL,
    stato       ENUM('attivo', 'restituito') DEFAULT 'attivo',
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (id_libro)  REFERENCES libri(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO libri (titolo, autore, anno_pubblicazione, quantita_totale, quantita_disponibile, descrizione) VALUES
('Il nome della rosa', 'Umberto Eco', 1980, 3, 3, 'Un monaco medievale indaga su una serie di misteriosi omicidi avvenuti in un''abbazia benedettina. Capolavoro del romanzo storico e del giallo intellettuale.'),
('1984', 'George Orwell', 1949, 5, 5, 'In un futuro distopico dominato dal Grande Fratello, Winston Smith cerca disperatamente di sfuggire al controllo totalitario del Partito. Pietra miliare della letteratura politica.'),
('Cronaca di una morte annunciata', 'Gabriel Garcia Marquez', 1981, 2, 2, 'In un piccolo villaggio latinoamericano, l''uccisione di Santiago Nasar viene ricostruita a ritroso tra silenzi e omerta collettiva. Un capolavoro del realismo magico.'),
('Il piccolo principe', 'Antoine de Saint-Exupery', 1943, 4, 4, 'Un aviatore nel deserto del Sahara incontra un misterioso bambino venuto da un altro pianeta. Una favola poetica sull''amicizia, l''amore e il senso della vita.'),
('La metamorfosi', 'Franz Kafka', 1915, 3, 3, 'Gregor Samsa si sveglia una mattina trasformato in un enorme insetto. Un''opera fondamentale dell''esistenzialismo moderno, densa di simbolismi sull''alienazione e la famiglia.'),
('Orgoglio e pregiudizio', 'Jane Austen', 1813, 4, 4, 'Elizabeth Bennet e il ricco e orgoglioso Mr. Darcy si scontrano e si innamorano nell''Inghilterra della Reggenza. Un classico irresistibile sull''amore e le convenzioni sociali.'),
('Fahrenheit 451', 'Ray Bradbury', 1953, 3, 3, 'In un futuro in cui i libri sono proibiti e bruciati dai pompieri, Guy Montag inizia a mettere in discussione il sistema. Un inno potente alla liberta del pensiero e della lettura.'),
('I promessi sposi', 'Alessandro Manzoni', 1827, 5, 5, 'Renzo e Lucia, due giovani promessi sposi nella Lombardia del Seicento, vengono separati dalla prepotenza del potere. Il romanzo storico italiano per eccellenza.'),
('Delitto e castigo', 'Fedor Dostoevskij', 1866, 2, 2, 'Lo studente Raskolnikov commette un duplice omicidio convinto di essere al di sopra della morale comune. Un''intensa esplorazione del rimorso, della redenzione e della psiche umana.'),
('Harry Potter e la Pietra Filosofale', 'J.K. Rowling', 1997, 6, 6, 'Un bambino orfano scopre di essere un mago e viene ammesso alla scuola di magia Hogwarts, dove lo attende il suo destino. L''inizio di una saga che ha conquistato generazioni.');