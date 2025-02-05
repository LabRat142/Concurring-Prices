from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from webdriver_manager.chrome import ChromeDriverManager
import re
import json
import time

search_term = sys.argv[1]
stores = json.loads(sys.argv[2])
options = Options()
options.add_argument("--headless")
driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)

products = []

for store in stores:
    name = store['name']
    url = store['search_url'] + search_term
    if name == 'Anhoch':
        url += '&perPage=1000'
    elif name == 'Setec':
        url += '&limit=1000'
    if name == 'Technomarket':
        url += '#page/1/offset/1000'

    # Load the page in the browser
    driver.get(url)
    time.sleep(3)

    if name == 'Anhoch':
        product_elements = driver.find_elements(By.CSS_SELECTOR, ".product-card")
        for product in product_elements:
            prod = {}
            prod['store'] = 'Anhoch'
            prod['name'] = product.find_element(By.CSS_SELECTOR, ".product-name").text.strip()
            prod['price'] = int(''.join(re.findall(r'\d+', product.find_element(By.CSS_SELECTOR, ".product-card-bottom .product-price").text.strip()))) / 100
            prod['url'] = product.find_element(By.CSS_SELECTOR, ".product-name").get_attribute("href")
            prod['imgURL'] = product.find_element(By.CSS_SELECTOR, ".product-image").get_attribute("src")
            prod['available'] = not bool(product.find_elements(By.CSS_SELECTOR, '.badge-notice'))
            products.append(prod)

    elif name == 'Setec':
        product_elements = driver.find_elements(By.CSS_SELECTOR, ".product")
        for product in product_elements:
            prod = {}
            prod['store'] = 'Setec'
            prod['name'] = product.find_element(By.CSS_SELECTOR, ".name").text.strip()
            prod['price'] = int(''.join(re.findall(r'\d+', product.find_element(By.CSS_SELECTOR, ".category-price-redovna").text.strip())))
            prod['url'] = product.find_element(By.CSS_SELECTOR, ".name a").get_attribute("href")
            prod['imgURL'] = product.find_element(By.CSS_SELECTOR, ".image img").get_attribute("src")
            prod['available'] = bool(product.find_elements(By.CSS_SELECTOR, '.ima_zaliha'))
            products.append(prod)

    elif name == 'Technomarket':
        product_elements = driver.find_elements(By.CSS_SELECTOR, ".product-fix")
        for product in product_elements:
            prod = {}
            prod['store'] = 'Technomarket'
            prod['name'] = product.find_element(By.CSS_SELECTOR, ".product-name").text.strip()
            price_divs = product.find_elements(By.CSS_SELECTOR, '.product-price')
            for div in price_divs:
                if 'Редовна Цена' in div.text:
                    prod['price'] = int(''.join(re.findall(r'\d+', div.find_element(By.CSS_SELECTOR, '.nm').text.strip())))
            prod['url'] = product.find_element(By.CSS_SELECTOR, ".product-name a").get_attribute("href")
            imgURL = product.find_element(By.CSS_SELECTOR, ".product-figure").get_attribute("style")
            imgURL = re.search(r'url\([\'"]?(https?://[^\s\'"]+)', imgURL)
            prod['imgURL'] = imgURL.group(1) if imgURL else None
            prod['available'] = bool(product.find_elements(By.CSS_SELECTOR, 'i.icon-ok'))
            products.append(prod)

# Close the browser session
driver.quit()

# TODO: Add products to database

