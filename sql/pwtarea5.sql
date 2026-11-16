-- ============================================================
--  Biblioteca 04 · Tarea 5 · Programación Web Avanzada
--  Esquema y datos iniciales (MySQL / MariaDB)
-- ============================================================

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(100) NOT NULL,
  role_id INT NOT NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(50) NOT NULL,
  author VARCHAR(50) NOT NULL,
  year INT,
  genre VARCHAR(50),
  quantity INT NOT NULL
);

CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  date_of_issue DATE,
  date_of_return DATE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (book_id) REFERENCES books(id)
);

-- ------------------------------------------------------------
--  Roles
-- ------------------------------------------------------------
INSERT INTO roles (id, name) VALUES
  (1, 'Administrador'),
  (2, 'Bibliotecario'),
  (3, 'Lector');

-- ------------------------------------------------------------
--  Integrantes del Grupo 04
--  Contraseña inicial para las cinco cuentas: Grupo04-2026!
-- ------------------------------------------------------------
INSERT INTO users (id, username, email, password, role_id) VALUES
  (1, 'Jorge Villamar Sánchez', 'jorge.villamar@grupo04.edu.ec', '$2y$10$4SRHGfLvBu.1X5aTYhB7neWBsejdvuKrviqSXkn4CTXp0j7zGmn0S', 1),
  (2, 'Norka Mideros Quiñonez', 'norka.mideros@grupo04.edu.ec', '$2y$10$4SRHGfLvBu.1X5aTYhB7neWBsejdvuKrviqSXkn4CTXp0j7zGmn0S', 2),
  (3, 'Bruce Balarezo Chacón', 'bruce.balarezo@grupo04.edu.ec', '$2y$10$4SRHGfLvBu.1X5aTYhB7neWBsejdvuKrviqSXkn4CTXp0j7zGmn0S', 2),
  (4, 'Mauro Chinlle Carrillo', 'mauro.chinlle@grupo04.edu.ec', '$2y$10$4SRHGfLvBu.1X5aTYhB7neWBsejdvuKrviqSXkn4CTXp0j7zGmn0S', 3),
  (5, 'Liseth Criollo', 'liseth.criollo@grupo04.edu.ec', '$2y$10$4SRHGfLvBu.1X5aTYhB7neWBsejdvuKrviqSXkn4CTXp0j7zGmn0S', 3);

-- ------------------------------------------------------------
--  Catálogo inicial
-- ------------------------------------------------------------
INSERT INTO books (id, title, author, year, genre, quantity) VALUES
  (1,  'Cien años de soledad',                'Gabriel García Márquez', 1967, 'Novela',        4),
  (2,  'La ciudad y los perros',              'Mario Vargas Llosa',     1963, 'Novela',        3),
  (3,  'Rayuela',                             'Julio Cortázar',         1963, 'Novela',        2),
  (4,  'El Aleph',                            'Jorge Luis Borges',      1949, 'Cuento',        3),
  (5,  'Árbol de Diana',                      'Alejandra Pizarnik',     1962, 'Poesía',        2),
  (6,  'Ensayo sobre la ceguera',             'José Saramago',          1995, 'Novela',        3),
  (7,  'La invención de Morel',               'Adolfo Bioy Casares',    1940, 'Novela',        2),
  (8,  'Historia del Ecuador',                'Enrique Ayala Mora',     2008, 'Historia',      5),
  (9,  'Learning PHP, MySQL y JavaScript',    'Robin Nixon',            2014, 'Informática',   4),
  (10, 'Aplicaciones web con PHP',            'Héctor Flores Fernández',2020, 'Informática',   3),
  (11, 'Breve historia del tiempo',           'Stephen Hawking',        1988, 'Divulgación',   2),
  (12, 'Cuentos de la selva',                 'Horacio Quiroga',        1918, 'Cuento',        3);

-- ------------------------------------------------------------
--  Préstamos de ejemplo
-- ------------------------------------------------------------
INSERT INTO transactions (id, user_id, book_id, date_of_issue, date_of_return) VALUES
  (1, 4, 7,  '2026-11-02', '2026-11-12'),
  (2, 5, 1,  '2026-11-10', NULL),
  (3, 4, 11, '2026-11-13', NULL);
