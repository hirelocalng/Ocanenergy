from PIL import Image
from pathlib import Path

src = Path('assets/images/logo.png')
dst = Path('assets/images/logo-transparent.png')

img = Image.open(src).convert('RGBA')
data = img.getdata()
new_data = []
for r, g, b, a in data:
    if r > 240 and g > 240 and b > 240:
        new_data.append((r, g, b, 0))
    else:
        new_data.append((r, g, b, a))
img.putdata(new_data)
img.save(dst)
print(dst)
