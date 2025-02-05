import requests
from bs4 import BeautifulSoup
import re
import json

search_term = sys.argv[1]
stores = json.loads(sys.argv[2])
products = []

for store in stores:
    name = store['name']
    url = store['search_url']
    headers = {"User-Agent": "Mozilla/5.0"}
    response = requests.get(url + search_term, headers=headers)

    soup = BeautifulSoup(response.text, "html.parser")


    if name == 'Anhoch':
        for product_html in soup.select(".product-card"):
            prod = {}
            prod['store'] = 'Anhoch'
            prod['name'] = product.select_one(".product-name").text.strip()
            prod['price'] = int(''.join(re.findall(r'\d+',product.select_one(".product-price").text.strip())))/100
            prod['url'] = product.select_one(".product-name")["href"]
            prod['imgURL'] = product.select_one(".product-image")["src"]
            prod['available'] = bool(product.select_one('.badge-notice'))

            products.append(prod)

    elif name == 'Setec':
        for product in soup.select(".product"):
            prod={}
            prod['store'] = 'Setec'
            prod['name'] = product.select_one(".name").text.strip()
            prod['price'] = int(''.join(re.findall(r'\d+',product.select_one(".category-price-redovna").text.strip())))
            prod['url'] = product.select_one(".name a")["href"]
            prod['imgURL'] = product.select_one(".image img")["src"]
            prod['available'] = True if bool(product.select_one('.ima_zaliha')) else False

            products.append(prod)

    elif name == 'Technomarket':
        for product in soup.select(".product-fix"):
            prod = {}
            prod['store'] = 'Technomarket'
            prod['name'] = name = product.select_one(".product-name").text.strip()
            for div in product.select_one('.product-price'):
                if 'Редовна Цена' in div.text:
                    prod['price'] = int(div.select_one('.nm').text.strip())
            prod['url'] = product.select_one(".product-name a")["href"]
            imgURL = product.select_one(".product-figure")['style']
            imgURL = re.search(r'url\([\'"]?(https?://[^\s\'"]+)', imgURL)
            prod['imgURL'] = imgURL.group(1) if imgURL else None
            prod['available'] = True if product.select_one('i.icon-ok') else False

            products.append(prod)

# TODO: add products to database
