CREATE TABLE IF NOT EXISTS nc_competence
(
    ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    NAME VARCHAR(255) NOT NULL,
    DESCRIPTION TEXT,
    CREATE_DATE VARCHAR(255) NOT NULL,
    PREV_COMPETENCE_ID INT UNSIGNED NULL,
    NEXT_COMPETENCE_ID INT UNSIGNED NULL,
    FOREIGN KEY (PREV_COMPETENCE_ID) REFERENCES nc_competence (ID) ON DELETE SET NULL,
    FOREIGN KEY (NEXT_COMPETENCE_ID) REFERENCES nc_competence (ID) ON DELETE SET NULL
);