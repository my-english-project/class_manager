import pypdf
import re

reader = pypdf.PdfReader('baseline/examen_ingles_120_preguntas.pdf')
full_text = ""
for page in reader.pages:
    full_text += page.extract_text() + "\n"

# Let's search for questions by number. e.g. "1. Look at...", "16. ...", "31. ...", "46. ...", "61. ...", etc.
for num in [1, 15, 16, 30, 31, 45, 46, 60, 61, 75, 76, 90, 91, 105, 106, 120]:
    # Find the question number followed by a dot, and some lines of text, until the options A), B), C) or the next question
    # Let's do a simple regex find
    pattern = rf"(?:^|\n){num}\.\s*(.*?)(?=\n(?:{num+1}\.\s*|PART |SECTION |Answer Key|\Z))"
    match = re.search(pattern, full_text, re.DOTALL)
    if match:
        print(f"=== Question {num} ===")
        print(match.group(1).strip()[:400])
        print()
    else:
        print(f"=== Question {num} not found ===")
