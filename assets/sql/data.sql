-- Active: 1715943590648@@127.0.0.1@3306@ecoride
CREATE DATABASE IF NOT EXISTS `ecorides`
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

-- Création de l’utilisateur BDD backoffice pour la création de compte
CREATE USER 'user'@'localhost' IDENTIFIED BY 'test';

  -- Attribution des droits sur la table "users"
GRANT SELECT, INSERT, UPDATE, DELETE ON ecorides.utilisateur TO 'user'@'localhost';

--création de compte utilisateur BDd tous privilèges 
  CREATE USER 'root'@'localhost' IDENTIFIED BY '';
  GRANT ALL PRIVILEGES ON * . * TO 'root'@'localhost';

-- CREATION DES TABLES

CREATE TABLE `avis` (
    `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `is_valid` TINYINT(1) NOT NULL,
    `comments` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `rate_comments` INTEGER,
    `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    `covoiturage_id` INTEGER NOT NULL,
    `conducteur_id` INTEGER NOT NULL,
    `passager_id` INTEGER NOT NULL,
    `is_signal` TINYINT(1) NOT NULL
    PRIMARY KEY (`id`),
    KEY `IDX_8F91ABF062671590` (`covoiturage_id`),
    KEY `IDX_8F91ABF0F16F4AC6` (`conducteur_id`),
    KEY `IDX_8F91ABF071A51189` (`passager_id`),
    CONSTRAINT `FK_8F91ABF062671590` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturage` (`id`),
    CONSTRAINT `FK_8F91ABF071A51189` FOREIGN KEY (`passager_id`) REFERENCES `utilisateur` (`id`),
    CONSTRAINT `FK_8F91ABF0F16F4AC6` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateur` (`id`)
    );

CREATE TABLE `covoiturage` (
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `prix` INTEGER NOT NULL,
    `date_depart` DATETIME NOT NULL,
    `lieu_depart` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `heure_depart` DATETIME NOT NULL,
    `lieu_arrivee` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `status` TINYINT(1) NOT NULL,
    `place_dispo` INTEGER DEFAULT NULL,
    `voiture_id` INTEGER NOT NULL,
    `created_at` DATETIME NOT NULL,
    `heure_arrivee` DATETIME NOT NULL,
    `conducteur_id` INTEGER DEFAULT NULL,
    `is_go` TINYINT (1) NOT NULL,
    `is_arrived` TINYINT(1) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `IDX_28C79E89181A8BA` (`voiture_id`),
    KEY `IDX_28C79E89F16F4AC6` (`conducteur_id`),
    CONSTRAINT `FK_28C79E89181A8BA` FOREIGN KEY (`voiture_id`) REFERENCES `voiture` (`id`),
    CONSTRAINT `FK_28C79E89F16F4AC6` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateur` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci

CREATE TABLE `covoiturage_utilisateur` (
    `covoiturage_id` INTEGER NOT NULL,
    `utilisateur_id` INTEGER NOT NULL,
    PRIMARY KEY (
        `covoiturage_id`,
        `utilisateur_id`
    ),
    KEY `IDX_96E46B0D62671590` (`covoiturage_id`),
    KEY `IDX_96E46B0DFB88E14F` (`utilisateur_id`),
    CONSTRAINT `FK_96E46B0D62671590` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturage` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_96E46B0DFB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'liee a la fonction validateUsers'

CREATE TABLE `utilisateur` (
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(180) COLLATE utf8mb4_unicode_ci NOT NULL,
    `roles` JSON NOT NULL,
    `password` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nom` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `prenom` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `pseudo` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `credits` INTEGER DEFAULT NULL,
    `photo_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_actif` TINYINT(1) NOT NULL,
    `created_at` DATETIME NOT NULL COMMENT '(DC2Type:DATETIME_immutable)',
    `observation` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_conducteur` TINYINT(1) NOT NULL,
    `is_passager` TINYINT(1) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci

CREATE TABLE `utilisateur_covoiturage` (
    `utilisateur_id` INTEGER NOT NULL,
    `covoiturage_id` INTEGER NOT NULL,
    PRIMARY KEY (
        `utilisateur_id`,
        `covoiturage_id`
    ),
    KEY `IDX_DC21931AFB88E14F` (`utilisateur_id`),
    KEY `IDX_DC21931A62671590` (`covoiturage_id`),
    CONSTRAINT `FK_DC21931A62671590` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturage` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_DC21931AFB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'liaison utilisateur/covoiturage'

CREATE TABLE `voiture` (
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `immat` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `first_immat` DATETIME NOT NULL,
    `constructeur` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `modele` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `couleur` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `energie` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `options` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `nbre_place` INTEGER NOT NULL,
    `utilisateur_id` INTEGER NOT NULL,
    `created_at` DATETIME NOT NULL COMMENT '(DC2Type:DATETIME_immutable)',
    PRIMARY KEY (`id`),
    KEY `IDX_E9E2810FFB88E14F` (`utilisateur_id`),
    CONSTRAINT `FK_E9E2810FFB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci

--INSERTION DES DONNEES

INSERT INTO `utilisateur` (`id`, `email`, `roles`, `password`, `nom`, `prenom`, `pseudo`, `credits`, `photo_path`, `is_actif`, `created_at`, `observation`, `is_conducteur`, `is_passager`) VALUES
(1, 'test@mail.com', '[\"ROLE_USER\"]', '$2y$13$IU3RKryiVDUcm9AvKPenx.8ALfONZLx5bj8RP98P6qUxY.58HFU1m', 'nomtest', 'prenomtest', 'test', 8, 'model_gars.jpg', 1, '2024-12-20 00:00:00', NULL, 1, 0),
(3, 'dupont@mail.com', '[\"ROLE_EMPLOYE\"]', '$2y$13$idIeka.aoxBa9oDh1xoRO.iPYoYsKfO1lxHINfXxrdn0fvW4mXTOO', 'Dupont', 'Jean', 'jeand', NULL, '', 0, '2024-12-21 00:00:00', NULL, 0, 0),
(4, 'daviau@mail.com', '[\"ROLE_ADMIN\"]', '$2y$13$qn.1y0s4EvexV2J0fbl00emYCbZ2ikuGGBVfNPEzhTddWr3VccVH6', 'daviau', 'celena', 'ccel', NULL, '', 1, '2024-12-23 00:00:00', NULL, 0, 0),
(5, 'jadetest@mail.com', '[\"ROLE_USER\"]', '$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq', 'test', 'jade', 'jade', 25, 'model_fille.jpg', 1, '2024-12-27 00:00:00', NULL, 1, 0),
(6, 'marie@mail.com', '[\"ROLE_USER\"]', '$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq', 'Users', 'Marie', 'marie User', 8, 'model_fille.jpg', 1, '2024-12-30 00:00:00', 'J\'adore le cinéma et les mangas,Non fumeur,pas d\'animaux', 1, 1),
(7, 'annie@mail.com', '[\"ROLE_USER\"]', '$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq', 'User', 'Annie', 'annieU', 8, 'model_fille.jpg', 1, '2024-12-30 00:00:00', 'J\'adore le cinéma et les mangas,Non fumeur,pas d\'animaux', 1, 0),
(8, 'david@mail.com', '[\"ROLE_USER\"]', '$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq', 'User', 'David', 'davidU', 20, 'model_gars.jpg', 1, '2024-12-30 00:00:00', NULL, 0, 0),
(9, 'sophie@mail.com', '[\"ROLE_USER\"]', '$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq', 'User', 'Sophie', 'sophieU', 8, 'model_fille.jpg', 1, '2024-12-30 00:00:00', NULL, 1, 0),
(10, 'chloe@mail.com', '[\"ROLE_USER\"]', '$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq', 'User', 'Chloe', 'chloeU', 2, 'model_fille.jpg', 1, '2024-12-30 00:00:00', NULL, 1, 0),
(11, 'jean@mail.com', '[\"ROLE_USER\"]', '$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq', 'User', 'Jean', 'jeanU', 12, 'model_gars.jpg', 1, '2024-12-30 00:00:00', NULL, 0, 0),
(12, 'clement@mail.com', '[\"ROLE_USER\"]', '$2y$13$jYlQ3cM3BcmCc2eAjUtW6eCXwLGwmmKLaoIKWa4W5t/NvxY94wSU6', 'daviau', 'Clement', 'clement D', 16, 'model_gars.jpg', 1, '2025-01-19 10:26:57', 'Non fumeur, Accepte les animaux', 1, 0),
(13, 'coco@mail.com', '[\"ROLE_USER\"]', '$2y$13$a9MmVyqHhYoA5oTVIW4jOuJuUuDSn4VRILYGiSNGauEUTwr92/HEq', 'Chanel', 'Coco', 'Coco', 20, 'model_fille.jpg', 1, '2025-01-19 20:19:04', NULL, 0, 0),
(14, 'brad@mail.com', '[\"ROLE_USER\"]', '$2y$13$KnyjhiLArBqWd.WJOXeoz.BKCtS5yWyu.YxbpzNTwT08Z5Qtff3jS', 'Pitt', 'Brad', 'brad', 20, 'model_gars.jpg', 1, '2025-01-19 20:21:29', NULL, 0, 0),
(15, 'ccelena@mail.com', '[\"ROLE_USER\"]', '$2y$13$DywgLISQGnFddmCs3yvTz.U/1Rxmg.oEDeUfOoXJ/R9pZtzrOcmDG', 'Daviau', 'Céléna', 'ccelena', 20, NULL, 1, '2025-02-12 12:37:30', NULL, 0, 0),
(16, 'lilly@mail.com', '[\"ROLE_USER\"]', '$2y$13$k/s274ctQsmuhysde3eJauJUEYGs1u/eqs4hOYDubpNHBhNUy9KoS', 'daviau', 'lilly', 'dlilly', 20, NULL, 1, '2025-02-12 18:22:31', NULL, 0, 0),
(17, 'doe@mail.com', '[\"ROLE_EMPLOYE\"]', '$2y$13$kut9O4VGFOJj2gx7WZUyneL7oj2FWjFTeSdJDUNRHSOfRnD9LleAi', 'doe', 'john', 'jdoe', 0, NULL, 1, '2025-02-12 18:36:41', NULL, 0, 0);


INSERT INTO `voiture` values (1,'DE-555-RT','2020-12-02','RENAULT','Megane','noire','Diesel','climatisation','4','6','2024-12-30');
INSERT INTO `voiture` values (2,'DE-556-ZS','2021-03-02','RENAULT','clio','blanche','Essence','climatisation','3','7','2024-12-30');
INSERT INTO `voiture` values (3,'YJ-146-GY','2024-02-12','DACIA','Duster','rouge','Diesel','climatisation','4','8','2024-12-30');
INSERT INTO `voiture` values (4,'DU-520-ZM','2005-10-25','MERCEDES','class A','noire','essence','climatisation','3','9','2024-12-30');
INSERT INTO `voiture` values (5,'AQ-489-CF','2018-11-02','VW','New beetle','noire','Diesel','climatisation','3','10','2024-12-30');
INSERT INTO `voiture` values (6,'CY-964-PM','2020-10-27','FORD','C-max','noire','Diesel','climatisation','4','6','2024-12-30');
INSERT INTO `voiture` values (7,'SX-276-AS','2021-11-07','FORD','Focus','noire','Diesel','climatisation','3','9','2024-12-30');
INSERT INTO `voiture` values (8,'WQ-128-TB','2022-09-05','PEUGEOT','406','noire','hybride','climatisation','3','10','2024-12-30');

INSERT INTO `avis` (`id`, `is_valid`, `comments`, `rate_comments`, `created_at`, `covoiturage_id`, `conducteur_id`, `passager_id`, `is_signal`) VALUES
(1, 1, 'Site permettant le covoiturage donc de réduire les frais lors de déplacements (notamment entre grandes villes) ; le site est fiabilisé et permet soit de proposer des places ou bien de chercher des places... grand choix de propositions, prix attractifs et ', 3, '2024-12-30 00:00:00', 4, 8, 6, 0),
(2, 1, 'Très bon voyage, toute en sécurité a un prix imbattable! Quelle bonne idée! Super site!', 4, '2024-11-05 00:00:00', 4, 8, 5, 0),
(3, 0, 'J\'ai utilisé ce site une fois, ça marche bien et j\'ai trouvé une personne pour m\'emmener.', 5, '2024-11-27 00:00:00', 4, 8, 12, 0),
(4, 0, 'Très bon voyage, toute en sécurité a un prix imbattable! Quelle bonne idée! Super site!', 4, '2024-12-18 00:00:00', 1, 5, 12, 0),
(5, 0, 'a ne pas reproduire', 1, '2025-02-10 10:52:00', 1, 5, 6, 1);


INSERT INTO `covoiturage` (`id`, `prix`, `date_depart`, `lieu_depart`, `heure_depart`, `lieu_arrivee`, `status`, `place_dispo`, `voiture_id`, `created_at`, `heure_arrivee`, `conducteur_id`, `is_go`, `is_arrived`) VALUES
(1, 5, '2025-02-10 00:00:00', 'Angers', '2025-01-07 09:00:00', 'Nantes', 1, 3, 1, '2024-12-30 00:00:00', '2025-01-07 10:00:00', 5, 1, 1),
(4, 3, '2025-05-01 00:00:00', 'Montaigu', '2025-01-07 16:30:00', 'Nantes', 1, 2, 4, '2024-11-20 00:00:00', '2025-01-07 17:00:00', 8, 0, 0),
(5, 5, '2025-03-20 00:00:00', 'Nantes', '2025-01-07 20:00:00', 'Angers', 1, 4, 5, '2024-11-30 00:00:00', '2025-01-07 21:00:00', 9, 0, 0),
(6, 8, '2025-03-24 00:00:00', 'Nantes', '1970-01-01 10:00:00', 'Rennes', 1, 3, 1, '2025-01-08 20:27:38', '1970-01-01 11:30:00', 1, 0, 0),
(15, 5, '2025-03-31 00:00:00', 'Angers', '1970-01-01 10:00:00', 'Nantes', 1, 0, 11, '2025-01-20 18:26:54', '1970-01-01 12:00:00', 12, 0, 0),
(21, 10, '2025-03-20 00:00:00', 'PARIS', '1970-01-01 18:00:00', 'NANTES', 1, 2, 6, '2025-02-15 12:49:20', '1970-01-01 22:00:00', 6, 0, 0);