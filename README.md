#  Badeendjes Webshop

Een complete PHP webshop voor de verkoop van badeendjes met uitgebreid voorraadbeheer, klantenaccounts en admin panel.

##  Functionaliteiten

### Klantgerichte Features
- **Productcatalogus** - Overzicht van 7 verschillende badeendjes modellen
- **Zoek & Filter** - Producten zoeken op naam en filteren op voorraad
- **Productdetailpagina** - Uitgebreide informatie per product
- **Winkelmandje** - Producten verzamelen en aantallen aanpassen
- **Automatische kortingen** - 20% korting vanaf 4 eendjes
- **Klantaccounts** - Registratie en login functionaliteit
- **Bestelgeschiedenis** - Overzicht van alle eerdere bestellingen
- **Checkout systeem** - Eenvoudig afrekenproces

### Admin Features
- **Dashboard** - Overzicht van belangrijkste statistieken
- **Voorraadbeheer** - Voorraad aanpassen per product
- **Prijsbeheer** - Prijzen aanpassen per product
- **Lage voorraad waarschuwing** - Automatische meldingen
- **Orderoverzicht** - Alle bestellingen beheren
- **Status beheer** - Order status aanpassen (nieuw/verwerkt/verzonden)
- **Kortingsregels** - Meerdere kortingsregels aanmaken en beheren
- **Verkoopstatistieken** - Uitgebreide rapportage met:
  - Totale omzet per periode
  - Best/minst verkochte producten
  - Gemiddelde bestelwaarde
  - Dagelijkse verkopen

##  Technische Details

**Backend:**
- PHP 7.4+
- MySQL/MariaDB database
- PDO voor database connecties
- Password hashing met bcrypt

**Beveiliging:**
- Prepared statements tegen SQL injection
- XSS bescherming met htmlspecialchars
- Password hashing met password_hash()
- Sessie management

**Frontend:**
- Responsive CSS design
- Mobiel vriendelijk
- Modern en gebruiksvriendelijk interface

##  Installatie

### Vereisten
- PHP 7.4 of hoger
- MySQL 5.7 of hoger / MariaDB 10.2 of hoger
- Apache/Nginx webserver
- mod_rewrite ingeschakeld (optioneel)

### Stap 1: Bestanden Uploaden
Upload alle bestanden naar je webserver (bijvoorbeeld in `/var/www/html/badeendjes-shop/`).

### Stap 2: Database Aanmaken
1. Open phpMyAdmin of je database management tool
2. Importeer `database_schema.sql` om de database structuur aan te maken
3. Importeer `database_seed.sql` om initiële data in te laden

### Stap 3: Database Configuratie
Pas het bestand `includes/config.php` aan met jouw database gegevens:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'badeendjes_shop');
define('DB_USER', 'jouw_gebruikersnaam');  // Pas aan
define('DB_PASS', 'jouw_wachtwoord');      // Pas aan
```

### Stap 4: Base URL Instellen
Pas de `BASE_URL` aan in `includes/config.php`:

```php
define('BASE_URL', 'http://jouwdomein.nl/badeendjes-shop/');
```

### Stap 5: Mappen Rechten
Zorg dat de webserver schrijfrechten heeft voor:
```bash
chmod 755 assets/images
chmod 755 uploads
```

### Stap 6: Assets Setup
Voer het setup script uit (of maak handmatig de mappen aan):
```bash
bash setup_assets.sh
```

### Stap 7: Admin Account
De standaard admin inloggegevens zijn:
- **Gebruikersnaam:** admin
- **Wachtwoord:** admin123

 **BELANGRIJK:** Wijzig deze gegevens direct na installatie!

Om het wachtwoord te wijzigen, genereer een nieuwe hash:
```php
echo password_hash('jouw_nieuwe_wachtwoord', PASSWORD_DEFAULT);
```

En update deze in de database:
```sql
UPDATE admin_users SET password = 'nieuwe_hash_hier' WHERE username = 'admin';
```

##  Bestandsstructuur

```
badeendjes-shop/
├── admin/                      # Admin panel
│   ├── dashboard.php          # Dashboard met statistieken
│   ├── products.php           # Productbeheer
│   ├── orders.php             # Orderbeheer
│   ├── order_detail.php       # Order details
│   ├── discounts.php          # Kortingsbeheer
│   ├── statistics.php         # Verkoopstatistieken
│   ├── login.php              # Admin login
│   └── logout.php             # Admin logout
│
├── customer/                   # Klant sectie
│   ├── register.php           # Registratie
│   ├── login.php              # Login
│   ├── logout.php             # Logout
│   ├── account.php            # Account overzicht
│   ├── orders.php             # Bestelgeschiedenis
│   └── order_detail.php       # Bestelling details
│
├── includes/                   # PHP includes
│   ├── config.php             # Database configuratie
│   └── functions.php          # Helper functies
│
├── assets/                     # Static assets
│   ├── css/
│   │   └── style.css          # Hoofdstijlblad
│   └── images/                # Productafbeeldingen
│
├── index.php                   # Homepage / productoverzicht
├── product.php                 # Product detailpagina
├── cart.php                    # Winkelmandje
├── cart_add.php                # Product toevoegen aan winkelmandje
├── checkout.php                # Checkout pagina
├── database_schema.sql         # Database structuur
├── database_seed.sql           # Initiële data
└── README.md                   # Deze handleiding
```

##  Productafbeeldingen

De shop gebruikt placeholder afbeeldingen. Vervang deze met echte productfoto's:

```
assets/images/piraat-eendje.jpg
assets/images/superheld-eendje.jpg
assets/images/astronaut-eendje.jpg
assets/images/dokter-eendje.jpg
assets/images/chef-eendje.jpg
assets/images/rocker-eendje.jpg
assets/images/duiker-eendje.jpg
```

**Aanbevolen afmetingen:** 800x800 pixels, JPG formaat

##  Beveiliging

### Productie Checklist
- [ ] Wijzig admin wachtwoord
- [ ] Verwijder test klantaccounts uit database
- [ ] Wijzig database wachtwoorden
- [ ] Zet display_errors op OFF in php.ini
- [ ] Installeer SSL certificaat (HTTPS)
- [ ] Backup strategie opzetten
- [ ] Update BASE_URL naar HTTPS
- [ ] Controleer bestandsrechten

### Wachtwoord Wijzigen
Voor het wijzigen van wachtwoorden, gebruik altijd:
```php
$hashed_password = password_hash('nieuw_wachtwoord', PASSWORD_DEFAULT);
```

##  Database Info

### Standaard Producten
De database bevat 7 badeendjes:
1. Piraat-eendje - €8,99
2. Superheld-eendje - €9,99
3. Astronaut-eendje - €10,99
4. Dokter-eendje - €8,49
5. Chef-kok-eendje - €8,99
6. Rocker-eendje - €11,99
7. Duiker-eendje - €9,49

### Standaard Kortingsregels
- **Bulk korting:** 20% vanaf 4 eendjes (actief)
- **Zomeractie:** 10% korting (inactief)
- **Kerst specials:** 15% vanaf 2 eendjes (inactief)

### Test Account
- **Email:** test@example.com
- **Wachtwoord:** test123

##  Gebruik

### Voor Klanten
1. Bezoek de homepage om producten te bekijken
2. Zoek en filter producten naar wens
3. Klik op een product voor meer details
4. Voeg producten toe aan winkelmandje
5. Registreer of log in
6. Rond bestelling af bij checkout
7. Bekijk bestelgeschiedenis in "Mijn Account"

### Voor Admin
1. Ga naar `/admin/login.php`
2. Log in met admin credentials
3. **Dashboard:** Bekijk overzicht en statistieken
4. **Producten:** Pas voorraad en prijzen aan
5. **Bestellingen:** Beheer orders en wijzig status
6. **Kortingen:** Maak en beheer kortingsregels
7. **Statistieken:** Bekijk uitgebreide verkooprapporten

##  Kortingssysteem

Het kortingssysteem ondersteunt verschillende types:

1. **Quantity (Aantal-gebaseerd)**
   - Korting op basis van aantal items
   - Voorbeeld: 20% vanaf 4 eendjes

2. **Seasonal (Seizoens)**
   - Tijdgebonden kortingen
   - Voorbeeld: Zomeractie 10% korting

3. **Combo (Combinatie)**
   - Speciale combinatiekortingen
   - Flexibel in te stellen

Kortingen worden automatisch toegepast op basis van:
- Aantal items in winkelmandje
- Actieve kortingsregels
- Start- en einddatum van kortingen

##  Statistieken

De admin heeft toegang tot:
- Totale omzet (per periode instelbaar)
- Aantal bestellingen
- Gemiddelde bestelwaarde
- Totale kortingen gegeven
- Best verkochte producten met percentage
- Minst verkochte producten
- Dagelijkse verkopen
- Status verdeling (nieuw/verwerkt/verzonden)

## 🐛 Troubleshooting

### Database connectie fout
- Controleer database credentials in `includes/config.php`
- Verifieer dat MySQL service draait
- Check of database en tabellen bestaan

### Afbeeldingen worden niet geladen
- Controleer of `assets/images/` map bestaat
- Verifieer bestandsrechten (755 voor mappen)
- Check of afbeeldingen de juiste naam hebben

### Admin kan niet inloggen
- Verifieer dat `admin_users` tabel bestaat
- Check of admin account in database staat
- Probeer wachtwoord opnieuw te hashen en updaten

### Sessie problemen
- Check of `session_start()` wordt aangeroepen
- Verifieer PHP sessie configuratie
- Controleer schrijfrechten voor sessie map

##  Aanpassingen

### Nieuwe producten toevoegen
```sql
INSERT INTO products (name, description, image_url, price, stock, low_stock_threshold)
VALUES ('Naam', 'Beschrijving', 'assets/images/naam.jpg', 9.99, 20, 5);
```

### Prijzen aanpassen
Kan via admin panel of direct in database:
```sql
UPDATE products SET price = 12.99 WHERE id = 1;
```

### Voorraad aanpassen
Best via admin panel voor logging, of:
```sql
UPDATE products SET stock = 50 WHERE id = 1;
```

##  Updates & Onderhoud

### Database Backup
Maak regelmatig backups:
```bash
mysqldump -u gebruiker -p badeendjes_shop > backup_$(date +%Y%m%d).sql
```

### Logbestanden
Overweeg logging toe te voegen voor:
- Admin acties
- Voorraad wijzigingen
- Mislukte login pogingen

##  Support

Voor vragen of problemen:
- Check eerst deze README
- Controleer database en PHP error logs
- Verifieer alle configuratie instellingen

##  Licentie

Dit project is gemaakt als demo/educatief project.

##  Toekomstige Uitbreidingen

Mogelijke verbeteringen:
- Email notificaties voor bestellingen
- Betaalintegratie (Mollie, Stripe)
- Factuur generatie (PDF)
- Productvarianten (kleuren, maten)
- Klantreviews en ratings
- Nieuwsbrief functionaliteit
- Voorraad historie tracking
- Barcode scanning voor voorraad
- Multi-taal ondersteuning
- Dark mode

---

**Veel succes met je Badeendjes Webshop! **
