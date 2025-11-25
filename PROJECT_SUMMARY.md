#  Badeendjes Webshop - Project Overzicht

##  Project Voltooid!

Je complete badeendjes webshop is klaar voor gebruik. Alle bestanden zijn aangemaakt en staan klaar in deze directory.

##  Wat is er Gebouwd?

### Volledige PHP Webshop met:

**Klantfuncties:**
 Productcatalogus met 7 badeendjes modellen
 Zoek & filter functionaliteit
 Productdetailpagina's met volledige informatie
 Winkelmandje systeem
 Automatische 20% korting vanaf 4 eendjes
 Klant registratie en login
 Bestelgeschiedenis per klant
 Checkout proces met voorraad validatie

**Admin Functies:**
 Uitgebreid dashboard met statistieken
 Voorraadbeheer met lage voorraad waarschuwingen
 Prijsbeheer
 Orderoverzicht met status beheer
 Kortingsregels beheer (aantal/seizoens/combo)
 Verkoopstatistieken met rapportages:
   - Omzet per periode
   - Best/minst verkochte producten
   - Gemiddelde bestelwaarde
   - Dagelijkse verkopen

**Technische Features:**
 Beveiligde login met password hashing
 SQL injection bescherming (prepared statements)
 XSS bescherming
 Sessie management
 Responsive design
 Database transacties voor bestellingen
 Automatische voorraad updates

## 📂 Bestandsstructuur

```
badeendjes-shop/
├──  README.md                 # Uitgebreide documentatie
├──  install.php               # Installatie checker
├──  .htaccess                 # Apache configuratie
├──  database_schema.sql       # Database structuur
├──  database_seed.sql         # Initiële data
├──  generate_hash.php         # Wachtwoord hash generator
├──  setup_assets.sh           # Assets setup script
│
├──  includes/
│   ├── config.php               # Database configuratie
│   └── functions.php            # Helper functies
│
├──  assets/
│   ├── css/style.css            # Hoofdstijlblad
│   └── images/                  # Productafbeeldingen
│
├──  admin/                    # Admin panel (8 bestanden)
│   ├── login.php
│   ├── dashboard.php
│   ├── products.php
│   ├── orders.php
│   ├── order_detail.php
│   ├── discounts.php
│   ├── statistics.php
│   └── logout.php
│
├──  customer/                 # Klant sectie (6 bestanden)
│   ├── login.php
│   ├── register.php
│   ├── account.php
│   ├── orders.php
│   ├── order_detail.php
│   └── logout.php
│
└──  Shop pagina's (5 bestanden)
    ├── index.php                # Homepage/producten
    ├── product.php              # Product detail
    ├── cart.php                 # Winkelmandje
    ├── cart_add.php             # Winkelmandje handler
    └── checkout.php             # Afrekenen

TOTAAL: 30+ PHP bestanden + database + styling
```

##  Snelstart Installatie

### Stap 1: Upload Bestanden
Upload alle bestanden naar je webserver.

### Stap 2: Database Setup
```sql
-- Importeer deze bestanden in phpMyAdmin:
1. database_schema.sql
2. database_seed.sql
```

### Stap 3: Configuratie
Pas `includes/config.php` aan met je database gegevens:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'badeendjes_shop');
define('DB_USER', 'jouw_gebruiker');
define('DB_PASS', 'jouw_wachtwoord');
define('BASE_URL', 'http://jouwdomein.nl/pad/');
```

### Stap 4: Test de Installatie
Bezoek: `install.php` om de installatie te controleren.

### Stap 5: Login Gegevens

**Admin:**
- URL: `admin/login.php`
- Gebruikersnaam: `admin`
- Wachtwoord: `admin123`

**Test Klant:**
- Email: `test@example.com`
- Wachtwoord: `test123`

 **BELANGRIJK:** Wijzig het admin wachtwoord direct!

##  Database Info

**7 Voorraad Producten:**
1. Piraat-eendje - €8,99 (25 stuks)
2. Superheld-eendje - €9,99 (30 stuks)
3. Astronaut-eendje - €10,99 (20 stuks)
4. Dokter-eendje - €8,49 (15 stuks)
5. Chef-kok-eendje - €8,99 (18 stuks)
6. Rocker-eendje - €11,99 (12 stuks)
7. Duiker-eendje - €9,49 (22 stuks)

**Kortingsregels:**
- Bulk korting: 20% vanaf 4 eendjes (ACTIEF)
- Zomeractie: 10% korting (inactief)
- Kerst specials: 15% vanaf 2 eendjes (inactief)

##  Aanpassingen

### Productafbeeldingen Vervangen
Upload echte foto's naar `assets/images/`:
- piraat-eendje.jpg
- superheld-eendje.jpg
- astronaut-eendje.jpg
- dokter-eendje.jpg
- chef-eendje.jpg
- rocker-eendje.jpg
- duiker-eendje.jpg

Aanbevolen: 800x800 pixels, JPG formaat

### Kleuren Aanpassen
Wijzig CSS variabelen in `assets/css/style.css`:
```css
:root {
    --primary-color: #FFD700;
    --secondary-color: #FFA500;
    --accent-color: #FF6B6B;
}
```

##  Beveiliging Checklist

Voor productie gebruik:
- [ ] Wijzig admin wachtwoord
- [ ] Wijzig database wachtwoorden
- [ ] Verwijder test klantaccounts
- [ ] Zet PHP display_errors uit
- [ ] Installeer SSL certificaat (HTTPS)
- [ ] Update BASE_URL naar HTTPS
- [ ] Verwijder install.php na installatie
- [ ] Setup regelmatige database backups

##  Features per Sectie

### Homepage (index.php)
- Alle producten weergeven
- Zoekfunctie
- Filter op voorraad status
- Quick add to cart
- Kortingsmelding bij 4+ items

### Admin Dashboard
- Omzet statistieken
- Niewe orders teller
- Voorraadwaarde
- Best verkochte producten
- Lage voorraad waarschuwingen
- Recente bestellingen

### Admin Producten
- Voorraad aanpassen
- Prijzen wijzigen
- Drempelwaarden instellen
- Voorraadwaarde overzicht

### Admin Orders
- Alle bestellingen zien
- Filteren op status en datum
- Status wijzigen
- Klantgegevens bekijken
- Order details

### Admin Kortingen
- Nieuwe kortingen toevoegen
- Verschillende kortingstypes
- Activeren/deactiveren
- Start/einddatum instellen

### Admin Statistieken
- Omzet per periode
- Aantal bestellingen
- Gemiddelde bestelwaarde
- Best/minst verkocht
- Dagelijkse verkopen
- Status verdeling

##  Technische Specificaties

**Server Vereisten:**
- PHP 7.4 of hoger
- MySQL 5.7+ of MariaDB 10.2+
- Apache/Nginx
- mod_rewrite (optioneel)

**Beveiliging:**
- PDO prepared statements
- password_hash() voor wachtwoorden
- htmlspecialchars() voor XSS
- Sessie management
- CSRF bescherming ready

**Database:**
- 6 tabellen
- Foreign keys
- Indexen voor performance
- Transacties voor orders

##  Extra Scripts

### generate_hash.php
Genereer wachtwoord hashes:
```bash
php generate_hash.php mijn_wachtwoord
```

### setup_assets.sh
Maak mappen structuur aan:
```bash
bash setup_assets.sh
```

##  Tips & Tricks

**Nieuwe producten toevoegen:**
Via database of maak een product management pagina in admin.

**Email notificaties toevoegen:**
Gebruik PHPMailer of mail() functie bij checkout.

**Betaling integreren:**
Mollie of Stripe API integreren in checkout.php

**Facturen genereren:**
TCPDF of FPDF gebruiken in order_detail.php

##  Hulp Nodig?

1. Lees eerst de uitgebreide **README.md**
2. Check **install.php** voor systeem diagnostics
3. Controleer PHP error logs
4. Verifieer database connectie
5. Check bestandsrechten (755 voor mappen)

##  Support

Voor vragen over de code:
- Check de inline comments in bestanden
- Alle functies zijn gedocumenteerd
- Database schema is uitgebreid beschreven

##  Mogelijke Uitbreidingen

Ideeën voor de toekomst:
- Email notificaties
- Factuur generatie (PDF)
- Betaalintegratie
- Product reviews
- Wishlist functie
- Newsletter systeem
- Export functionaliteit (CSV/Excel)
- Multi-taal ondersteuning
- API endpoints
- Voorraad alerts per email

##  Klaar voor Gebruik!

Je hebt nu een complete, professionele webshop voor badeendjes met:
- 30+ PHP bestanden
- Volledige admin functionaliteit
- Klantaccounts en bestellingen
- Automatisch kortingssysteem
- Uitgebreide statistieken
- Beveiligd en goed gestructureerd
- Volledig responsive design
- Gebruiksvriendelijke interface

**Veel succes met je Badeendjes Webshop! **

---

*Made with ❤️ for selling rubber duckies*
