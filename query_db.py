import sqlite3

conn = sqlite3.connect('database.sqlite')
cursor = conn.cursor()

def print_table_info(table_name):
    print(f"\n--- Info for {table_name} ---")
    try:
        cursor.execute(f"PRAGMA table_info(`{table_name}`)")
        cols = cursor.fetchall()
        for col in cols:
            print(f"Col: {col[1]} ({col[2]})")
        
        cursor.execute(f"SELECT * FROM `{table_name}` LIMIT 1")
        print("Row preview:", cursor.fetchone())
    except Exception as e:
        print(f"Error reading {table_name}: {e}")

print_table_info("site_settings")
print_table_info("users")
print_table_info("ecommerce_orders")
print_table_info("ecommerce_loyalty_transactions")

conn.close()


