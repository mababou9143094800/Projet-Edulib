-- ============================================================
-- EduLib — Migration : ajout des nouvelles fonctionnalités
-- À exécuter une seule fois sur une base existante
-- ============================================================

USE edulib;

-- Status brouillon/publié sur les ressources (ignoré si la colonne existe déjà)
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'resources' AND COLUMN_NAME = 'status'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE resources ADD COLUMN status ENUM(\'draft\',\'published\') NOT NULL DEFAULT \'published\' AFTER contenu',
  'SELECT "Column status already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Table : tentatives de connexion (rate limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
  ip           VARCHAR(45) NOT NULL,
  attempts     TINYINT     NOT NULL DEFAULT 1,
  last_attempt DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table : votes (upvote unique par utilisateur)
CREATE TABLE IF NOT EXISTS votes (
  resource_id INT NOT NULL,
  user_id     INT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (resource_id, user_id),
  CONSTRAINT fk_vote_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  CONSTRAINT fk_vote_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table : favoris
CREATE TABLE IF NOT EXISTS favorites (
  resource_id INT NOT NULL,
  user_id     INT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (resource_id, user_id),
  CONSTRAINT fk_fav_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  CONSTRAINT fk_fav_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table : commentaires (imbriqués sur 1 niveau)
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

-- Table : images de ressources (stockées en WebP)
CREATE TABLE IF NOT EXISTS resource_images (
  id          INT          AUTO_INCREMENT PRIMARY KEY,
  resource_id INT          NOT NULL,
  filename    VARCHAR(255) NOT NULL,
  sort_order  TINYINT      NOT NULL DEFAULT 0,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_img_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
