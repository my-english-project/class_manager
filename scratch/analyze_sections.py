import pypdf
import re

reader = pypdf.PdfReader('baseline/examen_ingles_120_preguntas.pdf')
full_text = ""
for page in reader.pages:
    full_text += page.extract_text() + "\n"

# Search for PART and SECTION headings
headings = re.findall(r'(PART \d+:.*?|SECTION [A-D]:.*)', full_text)
print("Found Headings:")
for h in headings:
    print(h)
