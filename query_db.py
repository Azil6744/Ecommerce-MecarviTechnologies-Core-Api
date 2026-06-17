import sqlite3

conn = sqlite3.connect('database.sqlite')
cursor = conn.cursor()

# Get list of tables
cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
tables = [row[0] for row in cursor.fetchall()]
print("Tables:", ", ".join(tables))
print()

for table in tables:
    if any(k in table.lower() for k in ['home', 'hero', 'setting', 'section']):
        print(f"--- Table: {table} ---")
        try:
            cursor.execute(f"PRAGMA table_info(`{table}`)")
            cols = [col[1] for col in cursor.fetchall()]
            print("Columns:", cols)
            
            cursor.execute(f"SELECT * FROM `{table}` LIMIT 3")
            rows = cursor.fetchall()
            for r in rows:
                print(r)
        except Exception as e:
            print("Error:", e)
        print()

conn.close()
