-- ============================================================
-- ClassHub — Schema & Seed Data
-- Universidad Tecnológica de Salamanca
-- Database: uts | Engine: InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS uts
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE uts;

-- ============================================================
-- 1. TABLA: docente
-- ============================================================
CREATE TABLE IF NOT EXISTS docente (
  id_docente    INT           NOT NULL AUTO_INCREMENT,
  matricula     VARCHAR(20)   NOT NULL,
  nombre        VARCHAR(80)   NOT NULL,
  apellido_pat  VARCHAR(60)   NOT NULL,
  apellido_mat  VARCHAR(60)   DEFAULT NULL,
  email         VARCHAR(120)  DEFAULT NULL,
  password_hash VARCHAR(255)  NOT NULL DEFAULT '$2y$10$placeholder',
  rol           VARCHAR(50)   DEFAULT 'docente',
  activo        TINYINT(1)    NOT NULL DEFAULT 1,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_docente),
  UNIQUE KEY uq_docente_matricula (matricula),
  UNIQUE KEY uq_docente_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 1.5. TABLA: usuario
-- ============================================================
CREATE TABLE IF NOT EXISTS usuario (
  id_usuario    INT           NOT NULL AUTO_INCREMENT,
  username      VARCHAR(120)  NOT NULL,
  password      VARCHAR(255)  NOT NULL,
  rol           VARCHAR(50)   NOT NULL,
  id_referencia INT           NOT NULL,
  created_at    DATETIME      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuario_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TABLA: materia
-- ============================================================
CREATE TABLE IF NOT EXISTS materia (
  id_materia  INT           NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(120)  NOT NULL,
  siglas      VARCHAR(20)   NOT NULL,
  descripcion TEXT          DEFAULT NULL,
  activo      TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (id_materia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABLA: grupo
-- ============================================================
CREATE TABLE IF NOT EXISTS grupo (
  id_grupo     INT           NOT NULL AUTO_INCREMENT,
  id_docente   INT           NOT NULL,
  id_materia   INT           DEFAULT NULL,
  carrera      VARCHAR(120)  NOT NULL,
  siglas       VARCHAR(20)   NOT NULL,
  cuatrimestre TINYINT       NOT NULL,
  grupo        VARCHAR(10)   NOT NULL,
  periodo      ENUM('ene-abr','may-ago','sep-dic') NOT NULL,
  ciclo        VARCHAR(6)    NOT NULL,
  anio         SMALLINT      NOT NULL,
  activo       TINYINT(1)    NOT NULL DEFAULT 1,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_grupo),
  KEY idx_grupo_docente (id_docente),
  KEY idx_grupo_ciclo (ciclo),
  CONSTRAINT fk_grupo_docente FOREIGN KEY (id_docente)
    REFERENCES docente(id_docente) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_grupo_materia FOREIGN KEY (id_materia)
    REFERENCES materia(id_materia) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TABLA: alumno
-- ============================================================
CREATE TABLE IF NOT EXISTS alumno (
  id_alumno    INT           NOT NULL AUTO_INCREMENT,
  matricula    VARCHAR(20)   NOT NULL,
  nombre       VARCHAR(80)   NOT NULL,
  apellido_pat VARCHAR(60)   NOT NULL,
  apellido_mat VARCHAR(60)   DEFAULT NULL,
  email        VARCHAR(120)  DEFAULT NULL,
  activo       TINYINT(1)    NOT NULL DEFAULT 1,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_alumno),
  UNIQUE KEY uq_alumno_matricula (matricula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TABLA: grupo_alumno (relación M:N)
-- ============================================================
CREATE TABLE IF NOT EXISTS grupo_alumno (
  id_grupo_alumno INT      NOT NULL AUTO_INCREMENT,
  id_grupo        INT      NOT NULL,
  id_alumno       INT      NOT NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_grupo_alumno),
  UNIQUE KEY uq_grupo_alumno (id_grupo, id_alumno),
  KEY idx_ga_alumno (id_alumno),
  CONSTRAINT fk_ga_grupo  FOREIGN KEY (id_grupo)
    REFERENCES grupo(id_grupo)  ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_ga_alumno FOREIGN KEY (id_alumno)
    REFERENCES alumno(id_alumno) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. TABLA: sesion
-- ============================================================
CREATE TABLE IF NOT EXISTS sesion (
  id_sesion  INT      NOT NULL AUTO_INCREMENT,
  id_grupo   INT      NOT NULL,
  fecha      DATE     NOT NULL,
  parcial    TINYINT  NOT NULL,
  tema       VARCHAR(120) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_sesion),
  UNIQUE KEY uq_sesion_grupo_fecha (id_grupo, fecha),
  CONSTRAINT fk_sesion_grupo FOREIGN KEY (id_grupo)
    REFERENCES grupo(id_grupo) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. TABLA: asistencia
-- ============================================================
CREATE TABLE IF NOT EXISTS asistencia (
  id_asistencia INT NOT NULL AUTO_INCREMENT,
  id_sesion     INT NOT NULL,
  id_alumno     INT NOT NULL,
  estado        ENUM('asistencia','retardo','falta','justificado') NOT NULL DEFAULT 'falta',
  updated_at    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_asistencia),
  UNIQUE KEY uq_asistencia (id_sesion, id_alumno),
  KEY idx_asist_alumno (id_alumno),
  CONSTRAINT fk_asist_sesion FOREIGN KEY (id_sesion)
    REFERENCES sesion(id_sesion) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_asist_alumno FOREIGN KEY (id_alumno)
    REFERENCES alumno(id_alumno) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. TABLA: examen_escrito
-- ============================================================
CREATE TABLE IF NOT EXISTS examen_escrito (
  id_examen_escrito INT          NOT NULL AUTO_INCREMENT,
  id_grupo          INT          NOT NULL,
  id_alumno         INT          NOT NULL,
  parcial           TINYINT      NOT NULL,
  calificacion      DECIMAL(4,2) DEFAULT NULL,
  updated_at        DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_examen_escrito),
  UNIQUE KEY uq_we (id_grupo, id_alumno, parcial),
  CONSTRAINT fk_we_grupo  FOREIGN KEY (id_grupo)
    REFERENCES grupo(id_grupo)  ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_we_alumno FOREIGN KEY (id_alumno)
    REFERENCES alumno(id_alumno) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. TABLA: examen_oral
-- ============================================================
CREATE TABLE IF NOT EXISTS examen_oral (
  id_examen_oral INT          NOT NULL AUTO_INCREMENT,
  id_grupo       INT          NOT NULL,
  id_alumno      INT          NOT NULL,
  parcial        TINYINT      NOT NULL,
  calificacion   DECIMAL(4,2) DEFAULT NULL,
  updated_at     DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_examen_oral),
  UNIQUE KEY uq_oe (id_grupo, id_alumno, parcial),
  CONSTRAINT fk_oe_grupo  FOREIGN KEY (id_grupo)
    REFERENCES grupo(id_grupo)  ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_oe_alumno FOREIGN KEY (id_alumno)
    REFERENCES alumno(id_alumno) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. TABLA: actividad (catálogo portafolio/tarea)
-- ============================================================
CREATE TABLE IF NOT EXISTS actividad (
  id_actividad INT         NOT NULL AUTO_INCREMENT,
  id_grupo     INT         NOT NULL,
  tipo         ENUM('portafolio','tarea') NOT NULL,
  parcial      TINYINT     NOT NULL,
  nombre       VARCHAR(80) NOT NULL,
  orden        TINYINT     DEFAULT NULL,
  created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_actividad),
  KEY idx_act_grupo (id_grupo),
  CONSTRAINT fk_act_grupo FOREIGN KEY (id_grupo)
    REFERENCES grupo(id_grupo) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. TABLA: calificacion_actividad
-- ============================================================
CREATE TABLE IF NOT EXISTS calificacion_actividad (
  id_calificacion_actividad INT          NOT NULL AUTO_INCREMENT,
  id_actividad              INT          NOT NULL,
  id_alumno                 INT          NOT NULL,
  calificacion              DECIMAL(4,2) DEFAULT NULL,
  updated_at                DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_calificacion_actividad),
  UNIQUE KEY uq_cal_act (id_actividad, id_alumno),
  CONSTRAINT fk_ca_actividad FOREIGN KEY (id_actividad)
    REFERENCES actividad(id_actividad) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_ca_alumno    FOREIGN KEY (id_alumno)
    REFERENCES alumno(id_alumno) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Docente principal
INSERT INTO docente (matricula, nombre, apellido_pat, apellido_mat, email, password_hash)
VALUES ('1808005', 'JOSE ISAURO', 'RIOS', 'RODRIGUEZ', 'jrios@utsleon.edu.mx',
        '$2y$10$defaultHashPlaceholderForV1PrototypeAccess');

-- Materia
INSERT INTO materia (nombre, siglas, descripcion)
VALUES ('Inglés VII', 'IER', 'Inglés nivel VII para Ingeniería en Energías Renovables, 8vo cuatrimestre');

-- Grupo IER8A
INSERT INTO grupo (id_docente, id_materia, carrera, siglas, cuatrimestre, grupo, periodo, ciclo, anio)
VALUES (1, 1, 'Ingeniería en Energías Renovables', 'IER', 8, 'A', 'ene-abr', '26C1', 2026);

-- 20 Alumnos
INSERT INTO alumno (matricula, nombre, apellido_pat, apellido_mat) VALUES
  ('612310503', 'EMILY',            'ARREDONDO',  'PEREZ'),
  ('612310220', 'YAEL',             'CAVAL',      'MARTINEZ'),
  ('612310550', 'JESUS ANDRES',     'CERVANTES',  'AMEZQUITA'),
  ('612310216', 'MIGUEL ANGEL',     'CUELLAR',    'MORALES'),
  ('612310272', 'LESLIE VALERIA',   'DOMINGUEZ',  'HERNANDEZ'),
  ('612310173', 'JESUS TADEO',      'GONZALEZ',   'MENDOZA'),
  ('612310273', 'ROBERTO',          'GOYTORTUO',  'MELENDEZ'),
  ('612310545', 'DAVID EMILIANO',   'HERNANDEZ',  'GARCIA'),
  ('612310128', 'DIEGO LEONEL',     'LOPEZ',      'PEREZ'),
  ('612310647', 'RODOLFO ANTONIO',  'MONTAÑEZ',   'HERNANDEZ'),
  ('612110492', 'JOSUE JOVANNI',    'MONTAÑO',    'GARCIA'),
  ('612310552', 'GUSTAVO',          'MORALES',    'MARTINEZ'),
  ('612310252', 'SEBASTIAN',        'MOSQUEDA',   'RODRIGUEZ'),
  ('612310114', 'CHRISTIAN',        'OJEDA',      'FIGUEROA'),
  ('612310335', 'MARIO',            'OLMOS',      'SILVA'),
  ('612310083', 'MIRIAM ABIGAIL',   'PADILLA',    'CUELLAR'),
  ('612310556', 'CARLOS AMADOR',    'PEREZ',      'BALDERAS'),
  ('612310171', 'ALVARO',           'RAMIREZ',    'DE LA CRUZ'),
  ('612310518', 'JUAN DE DIOS',     'RAMIREZ',    'MOSQUEDA'),
  ('612210579', 'MARIA GUADALUPE',  'VAZQUEZ',    'LARA');

-- Inscribir los 20 alumnos al grupo 1
INSERT INTO grupo_alumno (id_grupo, id_alumno) VALUES
  (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),
  (1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20);
