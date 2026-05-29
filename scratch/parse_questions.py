import pypdf
import re
import html

reader = pypdf.PdfReader('baseline/examen_ingles_120_preguntas.pdf')
full_text = ""
for page in reader.pages:
    full_text += page.extract_text() + "\n"

# 1. Parse the Answer Key
# Locate "Answer Key (Clave de Respuestas)"
key_section = full_text.split("Answer Key (Clave de Respuestas)")[-1]
answers = {}
# Find pairs of number and capital letter
all_pairs = re.findall(r'(\d+)\s+([A-C])', key_section)
for num_str, ans in all_pairs:
    answers[int(num_str)] = ans

print(f"Total parsed answers in key: {len(answers)}")

# 2. Parse all questions
# Let's use a robust line-by-line or regex parser
questions = []

# Let's define the parts and sections boundaries
# We can find the index of each section heading in full_text to assign part and section dynamically
headings = [
    {"part": 1, "seccion": "A", "title": "PART 1: Modals for Speculating (may, might, could, must)\nSECTION A: MULTIPLE CHOICE"},
    {"part": 1, "seccion": "B", "title": "SECTION B: WORD ORDERING"},
    {"part": 1, "seccion": "C", "title": "SECTION C: ERROR IDENTIFICATION"},
    {"part": 1, "seccion": "D", "title": "SECTION D: THEORETICAL REASONING & CONCEPT MATCHING"},
    {"part": 2, "seccion": "A", "title": "PART 2: First Conditional Variations\nSECTION A: MULTIPLE CHOICE"},
    {"part": 2, "seccion": "B", "title": "SECTION B: WORD ORDERING"},
    {"part": 2, "seccion": "C", "title": "SECTION C: ERROR IDENTIFICATION"},
    {"part": 2, "seccion": "D", "title": "SECTION D: THEORETICAL REASONING & CONCEPT MATCHING"},
]

# A robust way to parse:
# Iterate through question numbers 1 to 120 and find text between 'N. ' and 'N+1. '
for num in range(1, 121):
    part = 1 if num <= 60 else 2
    if num <= 15 or (60 < num <= 75):
        seccion = "A"
    elif num <= 30 or (75 < num <= 90):
        seccion = "B"
    elif num <= 45 or (90 < num <= 105):
        seccion = "C"
    else:
        seccion = "D"
        
    next_anchor = rf"(?:^|\n){num+1}\.\s" if num < 120 else r"Answer Key"
    pattern = rf"(?:^|\n){num}\.\s*(.*?)(?=\n\s*(?:{num+1}\.\s|PART |SECTION |Answer Key|\Z))"
    match = re.search(pattern, full_text, re.DOTALL)
    
    if match:
        q_block = match.group(1).strip()
        
        # Split options with robust support for symbols like = before letter (e.g. =B))
        opt_pattern = r"(.*?)\n\s*[-=\s]*A\)\s*(.*?)\n\s*[-=\s]*B\)\s*(.*?)\n\s*[-=\s]*C\)\s*(.*)"
        opt_match = re.match(opt_pattern, q_block, re.DOTALL)
        
        if opt_match:
            q_text = opt_match.group(1).strip()
            opt_a = opt_match.group(2).strip()
            opt_b = opt_match.group(3).strip()
            opt_c = opt_match.group(4).strip()
            
            questions.append({
                "number": num,
                "part": part,
                "seccion": seccion,
                "text": q_text,
                "options": {
                    "A": opt_a,
                    "B": opt_b,
                    "C": opt_c
                }
            })
        else:
            print(f"Failed to split options for question {num}: {q_block[:100]}...")
    else:
        print(f"Failed to find question {num} in PDF text.")

print(f"Successfully parsed {len(questions)} questions out of 120.")

# 3. Generate SQL file with questions DDL and INSERTS
sql_content = """-- ============================================================
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

"""

# Helper to escape single quotes for SQL
def sql_escape(s):
    return s.replace("'", "''")

for q in questions:
    num = q["number"]
    correct_letra = answers.get(num, "A")
    
    sql_content += f"-- Pregunta {num} (Parte {q['part']}, Sección {q['seccion']})\n"
    sql_content += f"INSERT INTO pregunta (numero, texto, parte, seccion) VALUES ({num}, '{sql_escape(q['text'])}', {q['part']}, '{q['seccion']}');\n"
    sql_content += f"SET @last_q_id = LAST_INSERT_ID();\n"
    
    for letra, opt_text in q["options"].items():
        is_correct = 1 if letra == correct_letra else 0
        sql_content += f"INSERT INTO opcion (id_pregunta, letra, texto, es_correcta) VALUES (@last_q_id, '{letra}', '{sql_escape(opt_text)}', {is_correct});\n"
    sql_content += "\n"

with open("sql/question_bank.sql", "w", encoding="utf-8") as f:
    f.write(sql_content)

print("✓ Generated sql/question_bank.sql successfully.")
