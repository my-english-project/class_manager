import os

print("=== ALL FILES RECURSIVELY ===")
for root, dirs, files in os.walk('.'):
    for file in files:
        if 'valores' in file or '.xlsx' in file or '.xls' in file:
            print(os.path.join(root, file))
