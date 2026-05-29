-- ============================================================
-- Banco de Preguntas y Opciones - Examen Escrito en Línea
-- Universidad Tecnológica de Salamanca
-- ============================================================

USE uts;

-- 1. Crear tablas del banco de preguntas
CREATE TABLE IF NOT EXISTS pregunta (
  id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
  numero      INT NOT NULL,
  texto       TEXT NOT NULL,
  parte       TINYINT NOT NULL,
  seccion     CHAR(1) NOT NULL,
  UNIQUE KEY uq_pregunta_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS opcion (
  id_opcion   INT AUTO_INCREMENT PRIMARY KEY,
  id_pregunta INT NOT NULL,
  letra       CHAR(1) NOT NULL,
  texto       TEXT NOT NULL,
  es_correcta TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpiar tablas si ya existen datos
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE opcion;
TRUNCATE TABLE pregunta;
SET FOREIGN_KEY_CHECKS = 1;

-- Pregunta 1 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (1, 'Look at those dark clouds! It __________ rain later, so take an umbrella just in case.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'must not', 0);

-- Pregunta 2 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (2, 'John has been working for 14 hours straight. He __________ be absolutely exhausted.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'must', 1);

-- Pregunta 3 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (3, '"Where is Sarah?" – "I''m not sure. She __________ be in the library, or maybe she went home."', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'could', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'can''t', 0);

-- Pregunta 4 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (4, 'That __________ be the famous actor! He looks completely different and much shorter in person.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'can''t', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'must', 0);

-- Pregunta 5 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (5, 'I lost my keys. They __________ be in the car, but I''ve already checked the kitchen and they aren''t
there.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'can''t', 0);

-- Pregunta 6 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (6, 'The streets are completely wet. It __________ have rained heavily while we were inside.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'must', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'might not
English Assessment — Modals & Conditionals 1', 0);

-- Pregunta 7 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (7, 'David has lived in Tokyo for fifteen years. He __________ speak Japanese fluently by now.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'can''t', 0);

-- Pregunta 8 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (8, '"Whose jacket is this?" – "I''m not entirely sure. It __________ belong to Kevin, or maybe to Leo."', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'can''t', 0);

-- Pregunta 9 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (9, 'You haven''t eaten anything since yesterday morning. You __________ be starving!', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'must', 1);

-- Pregunta 10 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (10, '"Is that the new teacher over there?" – "No, it __________ be him. The new teacher is much older
and wears glasses."', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'can''t', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'must', 0);

-- Pregunta 11 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (11, 'If you can''t find your passport in your bag, check your drawer. It __________ be in there.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'could', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'can''t', 0);

-- Pregunta 12 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (12, 'They have the exact same facial features and the same voice. They __________ be identical twins.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'must', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'could', 0);

-- Pregunta 13 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (13, '"Why is the baby crying?" – "I don''t know. He __________ be sleepy, or perhaps his stomach
hurts."', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'may', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'can''t', 0);

-- Pregunta 14 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (14, 'That story __________ be true! It sounds completely ridiculous and scientifically impossible.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'can''t', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'must
English Assessment — Modals & Conditionals 2', 0);

-- Pregunta 15 (Parte 1, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (15, 'We __________ go to Spain for our vacation this summer, but we haven''t booked any flights yet.', 1, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'could', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'can''t', 0);

-- Pregunta 16 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (16, 'Order the words: be / the / true / story / must / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'The must be true story.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'The story must be true.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Must the story be true.', 0);

-- Pregunta 17 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (17, 'Order the words: not / they / come / might / party / the / to / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'They might not come to the party.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'They not might come to the party.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Might they not come to the party.', 0);

-- Pregunta 18 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (18, 'Order the words: could / answer / right / be / this / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'This answer could right be.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Could this answer be right.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'This answer could be right.', 1);

-- Pregunta 19 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (19, 'Order the words: at / home / she / must / now / be / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'She must be at home now.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Must she be at home now.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'At home she must be now.', 0);

-- Pregunta 20 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (20, 'Order the words: have / we / dynamic / could / a / problem / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'We could have a problem.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Could we have a problem.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'We have could a problem.', 0);

-- Pregunta 21 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (21, 'Order the words: be / keys / the / kitchen / might / in / your / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Your keys might be in the kitchen.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Might your keys be in the kitchen.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Your keys in the kitchen might be.', 0);

-- Pregunta 22 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (22, 'Order the words: not / answer / they / know / the / may / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'They may not know the answer.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'They not may know the answer.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'May they not know the answer.
English Assessment — Modals & Conditionals 3', 0);

-- Pregunta 23 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (23, 'Order the words: dangerous / mountain / climbing / could / that / be / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'That climbing mountain could be dangerous.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Climbing that mountain could be dangerous.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Could climbing that mountain be dangerous.', 0);

-- Pregunta 24 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (24, 'Order the words: tired / after / must / match / they / be / the / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'They must be tired after the match.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'After the match they be must tired.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Must they be tired after the match.', 0);

-- Pregunta 25 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (25, 'Order the words: wrong / package / be / this / must / the / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'This package must be the wrong.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'This must be the wrong package.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Must this package be the wrong.', 0);

-- Pregunta 26 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (26, 'Order the words: tonight / call / she / might / us / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'She might call us tonight.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Might she call us tonight.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Tonight she call might us.', 0);

-- Pregunta 27 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (27, 'Order the words: expensive / restaurant / that / could / be / highly / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'That restaurant could be highly expensive.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Could that restaurant be highly expensive.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'That highly expensive restaurant could be.', 0);

-- Pregunta 28 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (28, 'Order the words: not / he / remember / details / might / the / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'He might not remember the details.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'He not might remember the details.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Might he not remember the details.', 0);

-- Pregunta 29 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (29, 'Order the words: traffic / heavy / be / must / the / now / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'The traffic must be heavy now.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Must the traffic be heavy now.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Heavy the traffic must be now.', 0);

-- Pregunta 30 (Parte 1, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (30, 'Order the words: solution / another / be / there / could / .', 1, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Could there be another solution.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'There could be another solution.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Another solution there could be.', 0);

-- Pregunta 31 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (31, 'She mights (A) be (B) at the office, but I am not entirely sure (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'She mights', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'be', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'not entirely sure', 0);

-- Pregunta 32 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (32, 'He must not (A) to be (B) the boss; he doesn''t have the keys (C) to the building.', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'to be', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'the keys', 0);

-- Pregunta 33 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (33, 'It coulds (A) be (B) a mistake, but we should double-check (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'coulds', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'be', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'double-check', 0);

-- Pregunta 34 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (34, 'You must (A) be hungry because you just ate (B) a huge three-course meal (C). (Contextual error)', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'just ate', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'three-course meal', 0);

-- Pregunta 35 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (35, 'They may not (A) knowing (B) the truth yet because nobody called them (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'may not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'knowing', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'called them', 0);

-- Pregunta 36 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (36, 'The performance musts (A) be (B) over by now because the lights are turning on (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'musts', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'be', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'are turning on', 0);

-- Pregunta 37 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (37, 'She may not (A) to want (B) to talk to us after what happened (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'may not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'to want', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'what happened', 0);

-- Pregunta 38 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (38, 'They coulds (A) arrive (B) late because of the train delay (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'coulds', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'arrive', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'delay
English Assessment — Modals & Conditionals 5', 0);

-- Pregunta 39 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (39, 'This can''t (A) be the right house because the family living here has been (B) on vacation for a month
(C). (Logical contradiction error)', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'can''t', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'has been', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'for a month', 0);

-- Pregunta 40 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (40, 'He might (A) be having (B) a meeting right now, so not call him (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'be having', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'not call him', 1);

-- Pregunta 41 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (41, 'You must not (A) to think (B) that learning a language is easy (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'to think', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'is easy', 0);

-- Pregunta 42 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (42, 'The document could (A) contains (B) sensitive information, so keep it safe (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'contains', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'keep it safe', 0);

-- Pregunta 43 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (43, 'The children may (A) sleeping (B) upstairs, so please be quiet (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'may', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'sleeping', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'be quiet', 0);

-- Pregunta 44 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (44, 'That must (A) be the real diamond because it scratches (B) easily, and real diamonds never scratch
(C). (Contextual deduction error)', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'must', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'scratches', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'never scratch', 0);

-- Pregunta 45 (Parte 1, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (45, 'We might not (A) having (B) enough time to visit the museum (C).', 1, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'having', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'the museum', 0);

-- Pregunta 46 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (46, 'Which modal verb expresses the highest degree of certainty that something is true based on
evidence?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Might', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Must', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Could', 0);

-- Pregunta 47 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (47, 'What is the main grammatical rule regarding the verb that follows a modal of speculation?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'It must be in the gerund form (-ing).', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'It must be a base verb (infinitive without ''to'').', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'It must agree with the subject (add -s/-es).', 0);

-- Pregunta 48 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (48, 'If someone says "It may rain tomorrow," what is the speaker''s level of certainty?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', '100% certain it will happen.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', '0% certain it will happen.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Around 50% possibility.', 1);

-- Pregunta 49 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (49, 'Which of the following sentences uses a modal of speculation incorrectly according to context?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', '"The lights are off. They must be home."', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', '"The lights are off. They might be asleep."', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', '"The lights are off. They could be out for dinner."', 0);

-- Pregunta 50 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (50, 'Choose the correct negative form used to speculate that something is logically impossible.', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Must not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Can''t', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Might not', 0);

-- Pregunta 51 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (51, 'Which modal verb represents a logical deduction that an event or state is impossible?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Must not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Can''t', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Might not', 0);

-- Pregunta 52 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (52, 'What does the modal "could" imply when used for speculation in the present?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Past ability only.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'A theoretical possibility based on conditions.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Absolute certainty.', 0);

-- Pregunta 53 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (53, 'Identify the correct sentence structure for a negative speculation with weak possibility.', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Subject + can''t + verb base.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Subject + might not + verb base.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Subject + must not + to + verb base.
English Assessment — Modals & Conditionals 7', 0);

-- Pregunta 54 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (54, 'Why is the sentence "He musts be a doctor" grammatically incorrect?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Because "must" cannot be followed by "be".', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Because modal verbs never change form or add "-s" for the third person singular.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Because "doctor" requires a past participle verb before it.', 0);

-- Pregunta 55 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (55, 'What is the communicative purpose of using modals like "may" or "might"?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'To state facts and absolute truths.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'To give strict orders and commands.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'To express degrees of uncertainty or hypothesis about a situation.', 1);

-- Pregunta 56 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (56, 'If you are 90% certain something is true based on strong evidence, which modal should you use?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'May', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Could', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Must', 1);

-- Pregunta 57 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (57, 'Which of the following options cannot be followed directly by a base verb?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Might', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Ought', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Could', 0);

-- Pregunta 58 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (58, 'In speculative grammar, how do "may not" and "can''t" differ?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', '"May not" means it''s possible it isn''t true; "can''t" means it is impossible to be true.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', '"May not" is more certain than "can''t".', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'They mean exactly the same thing.', 0);

-- Pregunta 59 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (59, 'Choose the sentence that represents a purely logical deduction based on evidence.', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'I might go to the beach if I feel like it.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'His car is not in the driveway, so he must be out.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Can you open the window for me?', 0);

-- Pregunta 60 (Parte 1, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (60, 'What happens to the meaning of "must" when used for speculation instead of obligation?', 1, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'It changes from a requirement imposed by authority to a strong logical conclusion.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'It means the exact same thing in both contexts.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'It refers exclusively to past events.
English Assessment — Modals & Conditionals 8', 0);

-- Pregunta 61 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (61, 'If you don''t study for the exam, you __________ fail.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will probably', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'could probably', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'probably will to', 0);

-- Pregunta 62 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (62, 'If we leave right now, we __________ catch the 5:00 PM train, but it''s going to be tight.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will definitely', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'wouldn''t', 0);

-- Pregunta 63 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (63, 'If it rains this weekend, the match __________ be canceled, though the organizers haven''t decided
yet.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'may', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'must', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'will definitely', 0);

-- Pregunta 64 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (64, 'If she gets the job offer, she __________ accept it because the salary is very high.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'will probably', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'could to', 0);

-- Pregunta 65 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (65, 'If you press this red button, the machine __________ stop working immediately.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will probably', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'could not', 0);

-- Pregunta 66 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (66, 'If you drink too much coffee before bed, you __________ sleep well.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might not', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'could to not', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'will probably not to', 0);

-- Pregunta 67 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (67, 'If the company expands next year, they __________ hire more software engineers.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'will probably
English Assessment — Modals & Conditionals 9', 1);

-- Pregunta 68 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (68, 'If he studies the vocabulary words every day, he __________ pass the test easily.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will probably', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'could probably to', 0);

-- Pregunta 69 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (69, 'If they miss the flight, they __________ have to reschedule the whole conference.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'may', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'must to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'will definitely to', 0);

-- Pregunta 70 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (70, 'If she applies for the scholarship, she __________ get it, but competition is fierce.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will definitely', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'wouldn''t', 0);

-- Pregunta 71 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (71, 'If we don''t protect the environment, global temperatures __________ rise significantly.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will probably', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'could to', 0);

-- Pregunta 72 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (72, 'If you buy this smartphone today, the store __________ give you a discount.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'could to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'will probably to', 0);

-- Pregunta 73 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (73, 'If he practices his presentation, he __________ make fewer mistakes during the meeting.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will probably', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may not to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'could to', 0);

-- Pregunta 74 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (74, 'If it snows heavily tonight, the school buses __________ run tomorrow morning.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'might not', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'won''t probably', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'could not to', 0);

-- Pregunta 75 (Parte 2, Sección A)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (75, 'If you follow the instructions carefully, you __________ solve the puzzle in ten minutes.', 2, 'A');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may to', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'will probably to', 0);

-- Pregunta 76 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (76, 'Order the words: if / free / I / am / , / come / I / might / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If free I am, I might come.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If I am free, I might come.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'I might come, if free I am.', 0);

-- Pregunta 77 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (77, 'Order the words: will / probably / they / stay / home / at / if / rains / it / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'They will probably stay at home if it rains.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If it rains they probably will stay at home.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'They probably stay will at home if it rains.', 0);

-- Pregunta 78 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (78, 'Order the words: we / lose / if / don''t / hurry / could / we / the / train / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If we don''t hurry, we could lose the train.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'We could lose the train if we hurry don''t.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If we don''t hurry, could we lose the train.', 0);

-- Pregunta 79 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (79, 'Order the words: she / if / she / invites / me / , / go / may / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If she invites me, I may go.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'I may go if invites she me.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If she me invites, I may go.', 0);

-- Pregunta 80 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (80, 'Order the words: will / she / if / passes / the / test / celebrate / probably / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'She will probably celebrate if she passes the test.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If she passes the test, probably she celebrate will.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'She probably will celebrate if passes she the test.', 0);

-- Pregunta 81 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (81, 'Order the words: if / works / hard / he / , / succeed / could / he / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If he works hard, he could succeed.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'He could succeed, if works hard he.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If works hard he, he could succeed.', 0);

-- Pregunta 82 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (82, 'Order the words: will / probably / we / go / out / if / stops / raining / it / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'We will probably go out if it stops raining.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If it stops raining we probably will go out.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'We probably go will out if it stops raining.', 0);

-- Pregunta 83 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (83, 'Order the words: she / if / feels / sick / , / stay / home / may / at / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If she feels sick, she may stay at home.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'She may stay at home if feels sick she.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If she sick feels, she may at home stay.', 0);

-- Pregunta 84 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (84, 'Order the words: if / price / drops / the / , / buy / I / might / it / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If the price drops, I might buy it.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'I might buy it if drops the price.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If drops the price, I might buy it.
English Assessment — Modals & Conditionals 11', 0);

-- Pregunta 85 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (85, 'Order the words: they / lose / will / probably / the / match / if / don''t / practice / they / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'They will probably lose the match if they don''t practice.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If they don''t practice they probably will lose the match.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'They probably lose will the match if they don''t practice.', 0);

-- Pregunta 86 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (86, 'Order the words: if / you / help / me / , / finish / early / could / we / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If you help me, we could finish early.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'We could finish early if help me you.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If you me help, could we finish early.', 0);

-- Pregunta 87 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (87, 'Order the words: the / event / may / cancel / they / if / cold / is / it / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'They may cancel the event if it is cold.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If it is cold they cancel the event may.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'They cancel the event may if it is cold.', 0);

-- Pregunta 88 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (88, 'Order the words: if / arrives / late / she / , / miss / the / intro / might / she / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If she arrives late, she might miss the intro.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'She might miss the intro if arrives late she.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If arrives late she, she might miss the intro.', 0);

-- Pregunta 89 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (89, 'Order the words: will / probably / he / call / you / if / time / has / he / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'He will probably call you if he has time.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If he has time probably he will call you.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'He probably call will you if he has time.', 0);

-- Pregunta 90 (Parte 2, Sección B)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (90, 'Order the words: we / find / a / solution / if / talk / we / could / .', 2, 'B');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'We could find a solution if we talk.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If we talk could we find a solution.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'We find a solution could if we talk.', 0);

-- Pregunta 91 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (91, 'If you will help (A) me, I might finish (B) the project by tonight (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will help', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might finish', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'by tonight', 0);

-- Pregunta 92 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (92, 'If he arrives (A) late, the manager probably will (B) be (C) angry.', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'arrives', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'probably will', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'be
English Assessment — Modals & Conditionals 12', 0);

-- Pregunta 93 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (93, 'We could to go (A) to the beach tomorrow if the weather (B) improves (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could to go', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'if the weather', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'improves', 0);

-- Pregunta 94 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (94, 'If she doesn''t practice (A), she may not passing (B) her driving test next week (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'doesn''t practice', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may not passing', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'next week', 0);

-- Pregunta 95 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (95, 'If they save (A) enough money, they will probably buying (B) a new car (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'save', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'will probably buying', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'car', 0);

-- Pregunta 96 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (96, 'If she will study (A) every day, she might pass (B) the certification test (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'will study', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might pass', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'test', 0);

-- Pregunta 97 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (97, 'If we don''t leave (A) now, the train will probably leaving (B) without us (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'don''t leave', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'will probably leaving', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'without us', 0);

-- Pregunta 98 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (98, 'You could to get (A) lost if you don''t use (B) the map provided (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'could to get', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'don''t use', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'provided', 0);

-- Pregunta 99 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (99, 'If they offering (A) him the position, he may accept (B) it immediately (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'offering', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'may accept', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'immediately', 0);

-- Pregunta 100 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (100, 'If the weather is bad (A) tomorrow, we probably will (B) postpone (C) the picnic.', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'is bad', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'probably will', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'postpone', 0);

-- Pregunta 101 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (101, 'If you not add (A) sugar, the cake might taste (B) bitter (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'not add', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'might taste', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'bitter
English Assessment — Modals & Conditionals 13', 0);

-- Pregunta 102 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (102, 'We may to go (A) to the museum if it opens (B) early (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'may to go', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'opens', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'early', 0);

-- Pregunta 103 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (103, 'If he drives (A) too fast, he coulds (B) cause (C) an accident.', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'drives', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'coulds', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'cause', 0);

-- Pregunta 104 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (104, 'If they don''t pay (A) the bill, the company will probably cuts (B) the service (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'don''t pay', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'will probably cuts', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'the service', 0);

-- Pregunta 105 (Parte 2, Sección C)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (105, 'If you mix (A) blue and yellow, you will probably getting (B) green (C).', 2, 'C');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'mix', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'will probably getting', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'green', 0);

-- Pregunta 106 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (106, 'In a First Conditional sentence, what tense is used in the "If-clause" (the condition)?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Future Simple', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Present Simple', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Past Simple', 0);

-- Pregunta 107 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (107, 'What is the difference between using "will" and "might" in the result clause of a First
Conditional?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', '"Will" expresses a certain future outcome; "might" expresses a possible outcome.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', '"Will" is used for the past; "might" is used for the future.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'There is no difference; they are completely interchangeable.', 0);

-- Pregunta 108 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (108, 'Where is the word "probably" correctly placed when used with "will" in an affirmative result
clause?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Before the word "will" (e.g., probably will go)', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'After the word "will" (e.g., will probably go)', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'At the very end of the sentence.
English Assessment — Modals & Conditionals 14', 0);

-- Pregunta 109 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (109, 'Which of these structures represents a valid variation of the First Conditional?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If + Present Simple, Modal (may/might/could) + Verb base.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If + Future Simple, Present Simple.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If + Past Simple, Modal (may/might/could) + Verb base.', 0);

-- Pregunta 110 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (110, 'Read the sentence: "If I have time, I could help you." What does "could" mean in this context?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'An ability I had in the past.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'A definite promise to help.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'A possibility of helping in the future.', 1);

-- Pregunta 111 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (111, 'In the variation "If + Present Simple, may + base verb", what does "may" modify compared to
"will"?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'It makes the result certain.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'It changes the result into a mere possibility instead of an absolute certainty.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'It shifts the tense to the past.', 0);

-- Pregunta 112 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (112, 'Which of the following sentences correctly displays the position of "probably" in a negative First
Conditional result clause?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'He will probably not come if it rains.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'He probably will not come if it rains.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Both A and B are grammatically acceptable positions.', 1);

-- Pregunta 113 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (113, 'Why can we NOT say "If it will rain, we might stay home"?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Because "might" cannot be used with "stay".', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Because the "if-clause" in a standard first conditional cannot contain the future marker "will".', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Because "rain" must always be in the past tense.', 0);

-- Pregunta 114 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (114, 'What degree of certainty does the phrase "will probably" express?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', '100% absolute guarantee.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'High probability, but short of complete certainty (around 70-80%).', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Low, unlikely chance.', 0);

-- Pregunta 115 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (115, 'When using "could" in the result clause of a First Conditional, what are we implying?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'That the outcome is completely impossible.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'That the outcome is a possible ability or option in the future dependent on the condition.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'That the outcome happened yesterday.', 0);

-- Pregunta 116 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (116, 'Which structural element is entirely prohibited directly after modals like may, might, and could?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'The infinitive particle "to".', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'The base form of a verb.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Adverbs of frequency like "always".
English Assessment — Modals & Conditionals 15', 0);

-- Pregunta 117 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (117, 'What is the function of the "If-clause" in a conditional sentence?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'It states the definitive consequence.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'It establishes the prerequisite condition that must be met.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'It acts as the main subject of the sentence.', 0);

-- Pregunta 118 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (118, 'Identify the incorrect combination for a First Conditional variation.', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'If + Present Simple, will probably + base verb.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'If + Present Simple, might + base verb.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'If + Future Simple, could + base verb.', 1);

-- Pregunta 119 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (119, 'What does changing "will" to "might not" in a conditional sentence do to the meaning?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'It converts a certain positive outcome into an uncertain negative outcome.', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'It has no effect on the meaning.', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'It makes the sentence a command.', 0);

-- Pregunta 120 (Parte 2, Sección D)
INSERT INTO pregunta (numero, texto, parte, seccion) VALUES (120, 'In online automated grading, why is the sentence "If you work, you may to pass" marked wrong?', 2, 'D');
SET @last_q_id = LAST_INSERT_ID();
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'A', 'Because "work" should be "works".', 0);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'B', 'Because modal verbs like "may" must never be followed by a "to-infinitive".', 1);
INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, 'C', 'Because the comma is missing.
English Assessment — Modals & Conditionals 16', 0);

