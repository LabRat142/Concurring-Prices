import requests
from bs4 import BeautifulSoup
import re

term = ???search_term

for store in ???stores:
    name = store['name']
    url = store['search_url']
#     headers = {"User-Agent": "Mozilla/5.0"}
    response = requests.get(url + term, headers=headers)

    soup = BeautifulSoup(response.text, "html.parser")

    if name == 'Anhoch':
        for product in soup.select(".product-card"):
            name = product.select_one(".product-name").text.strip()
            price = int(''.join(re.findall(r'\d+',product.select_one(".product-price").text.strip())))/100
            url = product.select_one(".product-name")["href"]
            imgURL = product.select_one(".product-image")["src"]
            available = bool(product.select_one('.badge-notice')

#             print(f"{name} - {price} - {link}")

    elif name == 'Setec':
        for product in soup.select(".product"):
            name = product.select_one(".name").text.strip()
            price = int(''.join(re.findall(r'\d+',product.select_one("category-price-redovna").text.strip())))
            url = product.select_one(".name a")["href"]
            imgURL = product.select_one(".image img")["src"]
            available = True if product.select_one('.ima_zaliha').text.strip() == 'Производот е достапен' else False

#             print(f"{name} - {price} - {link}")

    elif name == 'Technomarket':
        for product in soup.select(".product-fix"):
            name = name = product.select_one(".product-name").text.strip()
            price = int(''.join(re.findall(r'\d+',product.select_one("category-price-redovna").text.strip())))
            url = product.select_one(".product-name a")["href"]
            imgURL = product.select_one(".product-figure")['style']
            imgURL = re.search(r'url\([\'"]?(https?://[^\s\'"]+)', imgURL)
            imgURL = imgURL.group(1) if imgURL else None
            available = True if product.select_one('i.icon-ok') else False

#             print(f"{name} - {price} - {link}")
