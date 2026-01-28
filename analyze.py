import cv2
import sys

image_path = sys.argv[1]

if len(sys.argv) > 1:
    image_path = sys.argv[1]
    img = cv2.imread(image_path)


results = 'results'
print(results)