-- Active: 1715943590648@@127.0.0.1@3306@ecoride
CREATE USER 'user'@'localhost' IDENTIFIED BY 'test';
GRANT ALL PRIVILEGES ON `ECORIDE`.* TO 'user'@'localhost';
INSERT INTO `utilisateur` values (11,'jean@mail.com','["ROLE_USER"]','$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq','jean','User','jeanU','12','4','Logo.png','1','2024-12-30');
INSERT INTO `utilisateur` values (6,'marie@mail.com','["ROLE_USER"]','$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq','marie','User','marieU','3','5','Logo.png','1','2024-12-30');
INSERT INTO `utilisateur` values (7,'annie@mail.com','["ROLE_USER"]','$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq','annie','User','annieU','8','3','Logo.png','1','2024-12-30');
INSERT INTO `utilisateur` values (8,'david@mail.com','["ROLE_USER"]','$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq','david','User','davidU','0','4','Logo.png','1','2024-12-30');
INSERT INTO `utilisateur` values (9,'sophie@mail.com','["ROLE_USER"]','$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq','sophie','User','sophieU','8','4','Logo.png','1','2024-12-30');
INSERT INTO `utilisateur` values (10,'chloe@mail.com','["ROLE_USER"]','$2y$13$SJLhFPUOVYAZlc0FXEnKN.TAaY5CHk1EePucY8Gg3wD0DdkcUhOpq','chloe','User','chloeU','2','4','Logo.png','1','2024-12-30');

INSERT INTO `voiture` values (1,'DE-555-RT','2020-12-02','RENAULT','Megane','noire','Diesel','climatisation','4','6','2024-12-30');
INSERT INTO `voiture` values (2,'DE-556-ZS','2021-03-02','RENAULT','clio','blanche','Essence','climatisation','3','7','2024-12-30');
INSERT INTO `voiture` values (3,'YJ-146-GY','2024-02-12','DACIA','Duster','rouge','Diesel','climatisation','4','8','2024-12-30');
INSERT INTO `voiture` values (4,'DU-520-ZM','2005-10-25','MERCEDES','class A','noire','essence','climatisation','3','9','2024-12-30');
INSERT INTO `voiture` values (5,'AQ-489-CF','2018-11-02','VW','New beetle','noire','Diesel','climatisation','3','10','2024-12-30');
INSERT INTO `voiture` values (6,'CY-964-PM','2020-10-27','FORD','C-max','noire','Diesel','climatisation','4','6','2024-12-30');
INSERT INTO `voiture` values (7,'SX-276-AS','2021-11-07','FORD','Focus','noire','Diesel','climatisation','3','9','2024-12-30');
INSERT INTO `voiture` values (8,'WQ-128-TB','2022-09-05','PEUGEOT','406','noire','hybride','climatisation','3','10','2024-12-30');

INSERT INTO `avis` values (1,'1','Site permettant le covoiturage donc de réduire les frais lors de déplacements (notamment entre grandes villes) ; le site est fiabilisé et permet soit de proposer des places ou bien de chercher des places... grand choix de propositions, prix attractifs et une utilité indéniable pour les amateurs(trices) de ce type de déplacements.','5','5','2024-12-30');
INSERT INTO `avis` values (2,'1','Très bon voyage, toute en sécurité a un prix imbattable! Quelle bonne idée! Super site!','4','6','2024-11-5');
INSERT INTO `avis` values (3,'1','J\'ai utilisé ce site une fois, ça marche bien et j\'ai trouvé une personne pour m\'emmener.','5','7','2024-11-27');
INSERT INTO `avis` values (4,'0','Très bon voyage, toute en sécurité a un prix imbattable! Quelle bonne idée! Super site!','4','8','2024-12-18');
INSERT INTO `avis` values (5,'1','Site permettant le covoiturage donc de réduire les frais lors de déplacements (notamment entre grandes villes) ; le site est fiabilisé et permet soit de proposer des places ou bien de chercher des places... grand choix de propositions, prix attractifs et une utilité indéniable pour les amateurs(trices) de ce type de déplacements.','5','7','2024-12-30');
INSERT INTO `avis` values (6,'1','Très bon voyage, toute en sécurité a un prix imbattable! Quelle bonne idée! Super site!','5','6','2024-110-05');
INSERT INTO `avis` values (7,'0','J\'ai utilisé ce site une fois, ça marche bien et j\'ai trouvé une personne pour m\'emmener.','4','5','2024-10-18');
INSERT INTO `avis` values (8,'1','Site permettant le covoiturage donc de réduire les frais lors de déplacements (notamment entre grandes villes) ; le site est fiabilisé et permet soit de proposer des places ou bien de chercher des places... grand choix de propositions, prix attractifs et une utilité indéniable pour les amateurs(trices) de ce type de déplacements.','5','8','2024-12-30');
INSERT INTO `avis` values (9,'1','Site permettant le covoiturage donc de réduire les frais lors de déplacements (notamment entre grandes villes) ; le site est fiabilisé et permet soit de proposer des places ou bien de chercher des places... grand choix de propositions, prix attractifs et une utilité indéniable pour les amateurs(trices) de ce type de déplacements.','5','6','2024-12-30');
INSERT INTO `avis` values (10,'1','J\'ai utilisé ce site une fois, ça marche bien et j\'ai trouvé une personne pour m\'emmener.','5','9','2024-11-12');

INSERT INTO `covoiturage` VALUES (1, '5', '2025-03-10', 'Angers', '09:00', 'Nantes', '1', '4', '1', '2024-12-30');
INSERT INTO `covoiturage` VALUES (2, '2', '2025-01-10', 'Nantes', '12:00', 'Aubigny', '1', '2', '2', '2024-11-30');
INSERT INTO `covoiturage` VALUES (3, '10', '2025-01-12', 'Pornic', '14:30', 'Nantes', '1', '4', '3', '2024-12-10');
INSERT INTO `covoiturage` VALUES (4, '3', '2025-02-01', 'Montaigu', '17:00', 'Nantes', '1', '3', '4', '2024-11-20');
INSERT INTO `covoiturage` VALUES (5, '5', '2025-01-20', 'Nantes', '18:45', 'Angers', '1', '4', '5', '2024-11-30');