import re
import time
import os
import requests
from bs4 import BeautifulSoup
from concurrent.futures import ThreadPoolExecutor

def neptun_scraping(url, search_term, products):

    body = {
        "term": search_term,
        "page": 1,
        "itemsPerPage": 1000,
        "sort": 0
    }

    response = requests.post(url, data=body)

    # Check if the response status code is OK (200)
    if response.status_code == 200:
        # Parse the JSON response
        data = response.json()

        # Check if results exist and noResults is False
        if data.get("noResults", False) is False and "results" in data:
            # Loop through all products in the results list
            for product in data["results"]:
                base_url = "https://neptun.mk"
                # Extracting required fields for each product
                name = product.get("Title", "No Title")
                price = product.get("DiscountPrice", "No Price")
                product_url = base_url + product.get("Url", "")
                img_url = base_url + product.get("ImagePath", "")

                prod = {
                    'store': 'Neptun',
                    'name': name,
                    'price': price,
                    'url': product_url,
                    'imgURL': img_url,
                }

                products.append(prod)


def anhoch_scraping(url, products):
    # Send a GET request to the API endpoint
    headers = {
        'Accept' : 'application/json'
    }
    response = requests.get(url, headers=headers)

    # Check if the request was successful
    if response.status_code == 200:
        # Load the JSON response
        data = response.json()

        # Extract the products from the JSON response
        for product in data['products']['data']:
            name = product['name']
            price = float(product['price']['amount'])
            product_url = f"https://www.anhoch.com/products/{product['slug']}"  # Constructing product URL
            img_url = product['base_image']['path']
            available = product['is_in_stock']

            # Create a product dictionary
            prod = {
                'store': 'Anhoch',
                'name': name,
                'price': price,
                'url': product_url,
                'imgURL': img_url,
                'available': available
            }
            # Append the product to the list
            products.append(prod)



def setec_scraping(url, products):
    response = requests.get(url)
    soup = BeautifulSoup(response.content, 'html.parser')

    product_elements = soup.select(".product")
    for product in product_elements:
        name = product.select_one(".name").text.strip()
        price = int(''.join(re.findall(r'\d+', product.select_one(".category-price-redovna").text.strip())))
        img = product.select_one(".image img")
        img_url = img["data-echo"] or img["src"]
        available = bool(product.select_one('.ima_zaliha'))

        prod = {
            'store': 'Setec',
            'name': name,
            'price': price,
            'url': product.select_one(".name a")["href"],
            'imgURL': img_url,
            'available': available
        }
        products.append(prod)


def technomarket_scraping(url, products):
    response = requests.get(url)
    soup = BeautifulSoup(response.content, 'html.parser')

    product_elements = soup.select(".product-fix")
    for product in product_elements:
        name = product.select_one(".product-name").text.strip()
        price_divs = product.select('.product-price')
        price = None
        for div in price_divs:
            if 'Редовна Цена' in div.text:
                price = int(''.join(re.findall(r'\d+', div.select_one('.nm').text.strip())))

        img_url = product.select_one(".product-figure")["style"]
        img_url = re.search(r'url\([\'"]?(https?://[^\s\'"]+)', img_url)
        img_url = img_url.group(1) if img_url else None
        available = bool(product.select_one('i.icon-ok'))

        prod = {
            'store': 'Technomarket',
            'name': name,
            'price': price,
            'url': product.select_one(".product-name a")["href"],
            'imgURL': img_url,
            'available': available
        }
        products.append(prod)


def scrape_store(store, search_term, products):
    name = store['name']
    url = store['search_url'] + search_term
    if name == 'Anhoch':
        url += '&perPage=1000'
        anhoch_scraping(url, products)
    elif name == 'Setec':
        url += '&limit=1000'
        setec_scraping(url, products)
    elif name == 'Technomarket':
        url += '#page/1/offset/1000'
        technomarket_scraping(url, products)
    elif name == 'Neptun':
        neptun_scraping(store['search_url'], search_term, products)


def main():

    search_term = 'ram'
    stores = [
        {'name': 'Anhoch', 'search_url': 'https://www.anhoch.com/products?query='},
        {'name': 'Setec', 'search_url': 'https://setec.mk/index.php?route=product/search&search='},
        {'name': 'Technomarket', 'search_url': 'https://www.tehnomarket.com.mk/products/search?search='},
        {'name': 'Neptun', 'search_url': 'https://www.neptun.mk/Product/SearchProductsAutocomplete'},
    ]

    products = []  # Now directly storing products in a list
    num_threads = os.cpu_count() # Dynamically adjust the number of threads depending on the machine

    # Create a ThreadPoolExecutor
    with ThreadPoolExecutor(max_workers=num_threads) as executor:
        # Start a thread for each store
        for store in stores:
            executor.submit(scrape_store, store, search_term, products)

    # TODO: Add products to the database or process further
    for product in products:
        print(product)


if __name__ == "__main__":
    main()
