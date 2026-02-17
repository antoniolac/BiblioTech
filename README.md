# BiblioTech

**Questa è una semplie guida per avviare il progetto.**

****La descrizione dettagliata del progetto è inserita nella cartella doc, questa è solo una guida all'avvio***

**N.B** bisogna avere Docker-Desktop sul proprio device, con installata l'image php:apache

Per installare docker:[ https://docs.docker.com/desktop/setup/install/windows-install/](), poi avviare l'app e cercare php.

(è conisgliiato informarsi sul concetto di container)

Il progetto per funzionare sfrutta le seguenti tecnologie:

* Docker, per lo sviluppo
* PHP, per il lato server
* HTML/CSS, per la GUI
* SQL, per la creazione dei database
* phpMyAdmin, per mettere in relazione server e db
* MailPit, per simulare una casella di posta elettornica per l'arrivo dell'OTP
* 

****ho realizzato l'html da solo, mentre inizialemente il css con gemini, (non avevo voglia), poi per curiosità, ho passato entrambi a claude.ai che mi ha migliorato tanto, forse anche troppo il tutto, ma la base della struttura è mia giuro, ci sono le committttttttt (odio la gui), il php l ho fatto io documentandomi per le prepared query***

Come prima cosa bisogna clonare il repository o scaricare la zip del proggetto dalla piattaforma [github.com/antoniolac/BiblioTech]()

nella root del progetto, troviamo:

* i file per far funzionare docker e il .env (che non andrebbe pushato, ma ho sbagliato)
* cartella src che contiene i file php
* cartella data che contiene il database

Avendo caricato il .env non si sono bisogno di modifiche, lì dove inserite le avriabili di ambiente.

Consiglio di aprire il proggetto in vscode e di scaricare le estensioni per php e sql

In seguito, bisogna aprire docker desktop e lasciarla in background

Poi su vscode, aprire il docker-compose.yaml, e aprire il terminale integrato per digitare:

    *docker-compose up -d*

questo comando serve per creare il server e i vari container, per cancellarlo in caso di modifiche al docker-compose:

   *docker-compose down -v*

A questo punto il gioco è fatto, per accedere al server bisogna prima di tutto accedere a phpMyAdmin tramite

    *http://localhost:9021*

e importare le tabelle SQL che si trovano in data, selezionado il db creato da docker e poi importa.

Infine, via con la prova! Il sito si torva all'indirizzo:

    *http://localhost:9020*

mentre MailPit:

    *http://localhost:8025*

**Diveriti con la Biblioteca Digitale!**

****se vuoi provare il menu admin oltre quello utente, fai l'accesso con queste credenziali:***

***email: admin@bibliotech.edu.it***

***pwd: admin1234***


   **mi sono ricordato che l'account admin lo avevo inserito manualemente tramite phpMyAdmin, selezionando il DB, e cliccando sulla voce SQL e scrivendo la seguente query:

    INSERT INTO utenti (username, email, password, ruolo)
VALUES (
    'admin',
    'admin@bibliotech.edu.it',
    '$2y$10$fTEWFRL851NHuwM1w2gMAOvRQl271aWnhU/O6zcJQk7XAafr9gCIW',
    'admin'
);
