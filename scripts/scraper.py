import asyncio
import os
import httpx
import mysql.connector
from dotenv import load_dotenv

dotenv_path = os.path.join(os.path.dirname(__file__), "..", ".env")
load_dotenv(dotenv_path)

#if u want to run it in docker instead of XAMPP:
#docker run -d --name laravel-mysql -e MYSQL_DATABASE=concurringprices -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -p 3306:3306 -v laravel-mysql-data:/var/lib/mysql mysql:latest

def connect_db():
     return mysql.connector.connect(
        host=os.getenv("DB_HOST"),
        port=int(os.getenv("DB_PORT")),
        user=os.getenv("DB_USERNAME"),
        password=os.getenv("DB_PASSWORD"),
        database=os.getenv("DB_DATABASE"),
        charset="utf8mb4"
    )

# The 2 Following Functions are for Testing Purposes Only:
def create_table_if_not_exists(cursor): #if this table needs to be created for you, add created_at and updated_at, or remove them in the insert function()
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            price DECIMAL(10, 2),
            discount_price DECIMAL(10, 2),
            url TEXT,
            available BOOLEAN,
            imgURL TEXT,
            store VARCHAR(50),
            category VARCHAR(50)
        )
    """)


def delete_category_data(cursor, category):
    cursor.execute("DELETE FROM products WHERE category = %s", (category,))


def insert_product(cursor, product, category):
    # Get Store ID
    store_sql = """
    SELECT id FROM stores WHERE name LIKE %s
    """
    cursor.execute(store_sql, (product['store'],))
    store_id = cursor.fetchone()[0]

    # Check If Product Exists
    product_sql = """
    SELECT id FROM products WHERE name = %s
    """
    cursor.execute(product_sql, (product['name'],))
    result = cursor.fetchone()

    # If product doesn't exist
    if result is None:
        # Create new product
        insert_product_sql = """
        INSERT INTO products (name, category, created_at, updated_at)
        VALUES (%s, %s, NOW(), NOW())
        """
        cursor.execute(insert_product_sql, (
            product['name'],
            category
        ))

        # Get id of new product
        get_id_sql = """
        SELECT id FROM products WHERE name LIKE %s
        """
        cursor.execute(get_id_sql,(product['name'],))
        result = cursor.fetchone()
        if result:
            new_prod_id = result[0]

        # Create new product-store relation
        insert_prices_sql = """
        INSERT INTO prices (product_id, store_id, price, discount_price, url, available, imgURL,  created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """
        cursor.execute(insert_prices_sql, (
            new_prod_id,
            store_id,
            product['price'],
            product['discount_price'],
            product['url'],
            product['available'],
            product['imgURL'],
        ))
    # else if product exists
    else:
        product_id = result[0]

        # Check if Product-Store relation exists
        prices_sql = """
        SELECT id FROM prices
        WHERE product_id = %s AND store_id = %s
        """
        cursor.execute(prices_sql, (product_id, store_id))
        result = cursor.fetchone()

        # If relation doesn't exist
        if result is None:
            #Insert new relation
            insert_prices_sql = """
            INSERT INTO prices (product_id, store_id, price, discount_price, url, available, imgURL,  created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
            """
            cursor.execute(insert_prices_sql, (
                product_id,
                store_id,
                product['price'],
                product['discount_price'],
                product['url'],
                product['available'],
                product['imgURL'],
            ))
        # else if relation exists
        else:
            prices_id = result[0]
            # Update relation
            update_prices_sql = """
            UPDATE prices SET price = %s, discount_price = %s, url = %s, available = %s, imgURL = %s, updated_at = NOW()
            WHERE id = %s
            """
            cursor.execute(update_prices_sql,(
                product['price'],
                product['discount_price'],
                product['url'],
                product['available'],
                product['imgURL'],
                prices_id
            ))

def parse_neptun_item(item):
    name = item.get('Title') or item.get('name') or 'No name'
    try:
        price = float(item.get('RegularPrice') or 0)
        discount_price = float(item.get('DiscountPrice') or 0)
    except (ValueError, TypeError):
        price = 0.0
        discount_price = 0.0
    category_path = item.get('Category', {}).get('Url', '')
    product_path = item.get('Url', '')
    url = f"https://www.neptun.mk/categories/{category_path}/{product_path}" if category_path and product_path else None
    # available = None
    available = item.get('AvailableWebshop', None)
    imgURL = f"https://www.neptun.mk/{item.get('Thumbnail')}?width=192" if item.get('Thumbnail') else None
    return {
        'name': name,
        'price': price,
        'discount_price': discount_price,
        'url': url,
        'available': available,
        'imgURL': imgURL,
        'store': "Neptun"
    }


def parse_anhoch_item(item):
    name = item.get('name', 'No name')
    try:
        price = float(item.get('price', {}).get('amount', 0))
        get_discount = item.get('special_price')
        if get_discount is not None:
            discount_price = float(get_discount.get('amount') or 0)
        else:
            discount_price = 0.0
    except (ValueError, TypeError):
        price = 0.0
        discount_price = 0.0
    url = f"https://www.anhoch.com/products/{item.get('slug', '')}"
    available = item.get('is_in_stock', None)
    imgURL = None
    if item.get('base_image') and item['base_image'].get('path'):
        imgURL = item['base_image']['path']
    elif item.get('files') and len(item['files']) > 0:
        imgURL = item['files'][0].get('path')
    return {
        'name': name,
        'price': price,
        'discount_price': discount_price,
        'url': url,
        'available': available,
        'imgURL': imgURL,
        'store': "Anhoch"
    }

def parse_setec_item(item):
    name = item.get('normalized_title', 'No name')
    try:
        price = float(item.get('variants', [{}])[0].get('calculated_price', {}).get('original_amount', 0))
        discount_price = float(item.get('variants', [{}])[0].get('calculated_price', {}).get('calculated_amount', 0))
    except (ValueError, TypeError):
        price = 0.0
        discount_price = 0.0
    get_handle = item.get('handle', None)
    url = f"https://www.setec.mk/products/{get_handle}"
    available = False
    for inventory_item in item.get("variants", []):
        for inv in inventory_item.get("inventory", []):
            for loc in inv.get("location_levels", []):
                if loc.get("available_quantity", 0) > 0:
                    available = True

    imgURL = item.get('thumbnail', None)
    return {
        'name': name,
        'price': price,
        'discount_price': discount_price,
        'url': url,
        'available': available,
        'imgURL': imgURL,
        'store': "Setec"
    }


async def scrape_neptun(category_id, min_price, max_price):
    url = "https://www.neptun.mk/NeptunCategories/LoadProductsForCategory"
    headers = {
        "User-Agent": "Mozilla/5.0",
        "Accept": "application/json",
        "Content-Type": "application/json"
    }

    items = []
    current_page = 1
    items_per_page = 20
    total_items = 999

    while True:
        payload = {
            "CategoryId": category_id,
            "Sort": 4,
            "Manufacturers": [],
            "Recomended": False,
            "PriceRange": {"MinPriceValue": min_price, "MaxPriceValue": max_price},
            "BoolFeatures": [],
            "DropdownFeatures": [],
            "MultiSelectFeatures": [],
            "ShowAllProducts": False,
            "ItemsPerPage": items_per_page,
            "CurrentPage": current_page,
            "TotalItems": total_items
        }

        async with httpx.AsyncClient() as client:
            response = await client.post(url, headers=headers, json=payload)
            response.raise_for_status()
            data = response.json()

        products = data.get("Batch", {}).get("Items", [])
        config = data.get("Batch", {}).get("Config", {})
        if not products:
            break

        items.extend(products)
        total_items = config.get("TotalItems", total_items)
        max_pages = (total_items + items_per_page - 1) // items_per_page
        if current_page >= max_pages:
            break
        current_page += 1

    return items


async def scrape_anhoch(endpoint_category):
    base_url = "https://www.anhoch.com/products"
    headers = {
        "User-Agent": "Mozilla/5.0",
        "Accept": "application/json, text/javascript, */*; q=0.01",
        "X-Requested-With": "XMLHttpRequest",
    }

    results = []
    max_pages = 50
    items_per_page = 20

    async with httpx.AsyncClient() as client:
        for config in endpoint_category:
            page = 1
            while page <= max_pages:
                params = {
                    "query": "",
                    "categories[0]": config,
                    "tag": "",
                    "fromPrice": 0,
                    "toPrice": 324980,
                    "inStockOnly": 2,
                    "sort": "latest",
                    "perPage": items_per_page,
                    "page": page
                }
                try:
                    response = await client.get(base_url, params=params, headers=headers)
                    response.raise_for_status()
                    data = response.json()

                    products_page = data.get("products", {})
                    products = products_page.get("data", [])
                    if not products:
                        break

                    results.extend(products)

                    current_page = products_page.get("current_page", page)
                    last_page = data.get("last_page") or products_page.get("last_page")
                    if last_page and current_page >= last_page:
                        break

                    page += 1

                except Exception as e:
                    print(f"[Anhoch Error on category '{config}'] {e}")
                    break
    return results

async def scrape_setec(category_id):
    url = "https://search.setec.mk/indexes/products/search"
    headers = {
        "Content-Type": "application/json",
        "User-Agent": "Mozilla/5.0",
        "Authorization": os.getenv("AUTHORIZATION_HEADER")
    }

    items = []
    limit = 20
    offset = 0

    while True:
        payload = {
            "limit": limit,
            "offset": offset,
            "filter": (
                f"product_categories.id = '{category_id}' "
                "AND variants.calculated_price.calculated_amount >= 1 "
                "AND variants.calculated_price.calculated_amount <= 250000 "
                "AND status = 'published' AND is_web_active = 'true'"
            ),
            "sort": ["variants.calculated_price.calculated_amount:asc"],
            "matchingStrategy": "all"
        }

        async with httpx.AsyncClient() as client:
            response = await client.post(url, headers=headers, json=payload)
            response.raise_for_status()
            data = response.json()

        products = data.get("hits", [])
        if not products:
            break

        items.extend(products)
        offset += limit

        if offset >= data.get("estimatedTotalHits", 0):
            break

    return items

async def main():
    db = connect_db()
    cursor = db.cursor()
    create_table_if_not_exists(cursor)
    db.commit()

    # Define categories with their respective parameters
    categories = [
        {
            'name': 'laptop',
            'neptun': {'category_id': 24, 'min_price': 0, 'max_price': 999999},
            'anhoch': ['site-laptopi'],
            'setec': 'pcat_01JFZ1W5Q38VHNF746YGVYZ0PM'
        },
        {
            'name': 'smartphone',
            'neptun': {'category_id': 151, 'min_price': 1599, 'max_price': 119999},
            'anhoch': ['mobilni-telefoni'],
            'setec': 'pcat_01JFZ1WAWCQ8R8G4KPGDNN7RG1'
        },
        {
            'name': 'computers',
            'neptun': {'category_id': 236, 'min_price': 29999, 'max_price': 77999},
            'anhoch': ['gaming-konfiguracii', 'office-konfiguracii'],
            'setec': 'pcat_01JFZ1W6PYQMK01N93VXT67DJ9'
        }
    ]

    for category in categories:
        print(f"Processing category: {category['name']}")

        # Delete existing data for this category
        delete_category_data(cursor, category['name'])
        db.commit()

        # Scrape Neptun
        neptun_items = await scrape_neptun(
            category['neptun']['category_id'],
            category['neptun']['min_price'],
            category['neptun']['max_price']
        )
        print(f"Neptun {category['name'].capitalize()}: {len(neptun_items)} found")
        for item in neptun_items:
            product = parse_neptun_item(item)
            insert_product(cursor, product, category['name'])

        # Scrape Anhoch
        anhoch_items = await scrape_anhoch(category['anhoch'])
        print(f"Anhoch {category['name'].capitalize()}: {len(anhoch_items)} found")
        for item in anhoch_items:
            product = parse_anhoch_item(item)
            insert_product(cursor, product, category['name'])

        # Scrape Setec
        setec_items = await scrape_setec(category['setec'])
        print(f"Setec {category['name'].capitalize()}: {len(setec_items)} found")
        for item in setec_items:
            product = parse_setec_item(item)
            insert_product(cursor, product, category['name'])

        db.commit()
        print(f"All '{category['name']}' products inserted into the database.\n")

    cursor.close()
    db.close()


if __name__ == "__main__":
    asyncio.run(main())
