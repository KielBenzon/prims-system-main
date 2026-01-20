import sys
from sklearn import datasets
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.svm import SVC
from sklearn.metrics import accuracy_score

def classify_text(text):
    inappropriate_keywords = ['inappropriate', 'offensive']
    print(f"Classifying text: {text}")
    for keyword in inappropriate_keywords:
        if keyword in text.lower():
            return 'inappropriate'
    return 'appropriate'

input_text = sys.argv[1] if len(sys.argv) > 1 else ''

classification = classify_text(input_text)

print(classification)