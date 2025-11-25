-- Badeendjes Webshop - Initial Data Seeding
--  mysql -u badeendadmin -pbadeendpw < database_step3_seed.sql
USE badeendjes_shop;

-- Admin gebruiker toevoegen
-- Wachtwoord: admin123 (MOET GEWIJZIGD WORDEN NA INSTALLATIE!)
INSERT INTO admin_users (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Note: Dit is een voorbeeld hash. Je moet deze vervangen met een echte hash bij productie.

-- Producten toevoegen (7 badeendjes)
INSERT INTO products (name, description, image_url, price, stock, low_stock_threshold) VALUES
(
    'Piraat-eendje',
    'Ahoy maatje! Dit stoere piraat-eendje vaart de zeven zeeën af op zoek naar avontuur. Compleet met piratenkapitein hoed en ooglapje. Perfect voor kleine zeerovers in bad!',
    'assets/images/piraat-eendje.jpg',
    8.99,
    25,
    5
),
(
    'Superheld-eendje',
    'Met bliksemsnelle zwemkracht redt dit superheld-eendje de dag! Uitgerust met cape en masker. Elke badtijd wordt een heroïsch avontuur.',
    'assets/images/superheld-eendje.jpg',
    9.99,
    30,
    5
),
(
    'Astronaut-eendje',
    'Houston, we hebben geen probleem! Dit astronaut-eendje verkent vreemde nieuwe werelden in je badkuip. Met ruimtepak en helm voor interstellaire badavonturen.',
    'assets/images/astronaut-eendje.jpg',
    10.99,
    20,
    5
),
(
    'Dokter-eendje',
    'Dit dokter-eendje zorgt ervoor dat iedereen gezond blijft! Met witte jas en stethoscoop. Ideaal voor kleine dokters in opleiding.',
    'assets/images/dokter-eendje.jpg',
    8.49,
    15,
    5
),
(
    'Chef-kok-eendje',
    'Bon appétit! Dit chef-kok-eendje bereidt de lekkerste gerechten voor. Compleet met koksmuts en pollepel. Voor toekomstige topkoks.',
    'assets/images/chef-eendje.jpg',
    8.99,
    18,
    5
),
(
    'Rocker-eendje',
    'Rock \'n\' roll! Dit rocker-eendje brengt de beste gitaarriffs naar je badkuip. Met elektrische gitaar en zonnebril. Voor kleine rocksterren.',
    'assets/images/rocker-eendje.jpg',
    11.99,
    12,
    5
),
(
    'Duiker-eendje',
    'Duik in het diepe! Dit duiker-eendje ontdekt de onderwaterwereld. Met duikbril en snorkel. Perfect voor kleine oceaan ontdekkers.',
    'assets/images/duiker-eendje.jpg',
    9.49,
    22,
    5
);

-- Kortingsregels toevoegen
INSERT INTO discount_rules (name, type, min_quantity, discount_percentage, active, start_date, end_date) VALUES
(
    'Bulk korting - 4+ eendjes',
    'quantity',
    4,
    20.00,
    TRUE,
    '2024-01-01',
    '2025-12-31'
),
(
    'Zomer actie',
    'seasonal',
    1,
    10.00,
    FALSE,
    '2025-06-01',
    '2025-08-31'
),
(
    'Kerst specials',
    'seasonal',
    2,
    15.00,
    FALSE,
    '2025-12-01',
    '2025-12-31'
);

-- Test klant toevoegen (optioneel voor development)
-- Email: test@example.com, Wachtwoord: test123
INSERT INTO customers (email, password, name) VALUES
('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Klant');

-- Test bestellingen toevoegen (optioneel voor development)
INSERT INTO orders (customer_id, total_price, discount_applied, status) VALUES
(1, 35.96, 8.99, 'verzonden'),
(1, 27.97, 0, 'verwerkt');

INSERT INTO order_items (order_id, product_id, quantity, price_per_unit) VALUES
(1, 1, 2, 8.99),
(1, 2, 2, 9.99),
(2, 3, 1, 10.99),
(2, 5, 2, 8.99);
