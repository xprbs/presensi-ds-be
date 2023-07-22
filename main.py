# main.py
#!/usr/bin/python3

from deepface import DeepFace
import sys

source_path = sys.argv[1]
dataset_path = sys.argv[2]

try:
    model_name = "VGG-Face"
    df = DeepFace.find(img_path=source_path, db_path=dataset_path, model_name=model_name)
    print(df)
except Exception as e:
    print("Error:", e)

# print('test')