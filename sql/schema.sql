-- ============================================================
-- EduLib — Schéma de base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS edulib
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE edulib;

-- ------------------------------------------------------------
-- Table : users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id         INT          AUTO_INCREMENT PRIMARY KEY,
  nom        VARCHAR(100) NOT NULL,
  prenom     VARCHAR(100) NOT NULL,
  email      VARCHAR(255) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id   INT          AUTO_INCREMENT PRIMARY KEY,
  nom  VARCHAR(100) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : resources
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS resources (
  id           INT          AUTO_INCREMENT PRIMARY KEY,
  titre        VARCHAR(255) NOT NULL,
  description  TEXT         NOT NULL,
  contenu      LONGTEXT     NOT NULL,
  status       ENUM('draft','published') NOT NULL DEFAULT 'published',
  categorie_id INT          NOT NULL,
  auteur_id    INT          NOT NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_res_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE RESTRICT,
  CONSTRAINT fk_res_auteur    FOREIGN KEY (auteur_id)    REFERENCES users(id)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : login_attempts (rate limiting)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
  ip           VARCHAR(45) NOT NULL,
  attempts     TINYINT     NOT NULL DEFAULT 1,
  last_attempt DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : votes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS votes (
  resource_id INT NOT NULL,
  user_id     INT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (resource_id, user_id),
  CONSTRAINT fk_vote_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  CONSTRAINT fk_vote_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : favorites
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
  resource_id INT NOT NULL,
  user_id     INT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (resource_id, user_id),
  CONSTRAINT fk_fav_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  CONSTRAINT fk_fav_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : comments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comments (
  id          INT       AUTO_INCREMENT PRIMARY KEY,
  resource_id INT       NOT NULL,
  user_id     INT       NOT NULL,
  parent_id   INT       NULL DEFAULT NULL,
  contenu     TEXT      NOT NULL,
  created_at  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_com_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  CONSTRAINT fk_com_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
  CONSTRAINT fk_com_parent   FOREIGN KEY (parent_id)   REFERENCES comments(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Données : catégories
-- ------------------------------------------------------------
INSERT IGNORE INTO categories (nom, slug) VALUES
  ('Informatique',      'informatique'),
  ('Mathématiques',     'mathematiques'),
  ('Physique',          'physique'),
  ('Électronique',      'electronique'),
  ('Gestion de projet', 'gestion-projet'),
  ('Langues',           'langues'),
  ('Économie',          'economie'),
  ('Autre',             'autre');
