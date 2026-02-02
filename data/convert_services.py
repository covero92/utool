import pandas as pd
import json
import os

# Paths
input_file = r'c:\xampp\htdocs\utool\NFSE Nacional\listaservico.xlsx'
output_file = r'c:\xampp\htdocs\utool\data\nfse_services.json'

try:
    # Read all sheets
    xls = pd.ExcelFile(input_file)
    data = {}
    
    for sheet_name in xls.sheet_names:
        df = pd.read_excel(xls, sheet_name=sheet_name)
        # Convert to records (list of dicts)
        # Fill NaN with empty string
        records = df.fillna('').to_dict(orient='records')
        data[sheet_name] = records
        print(f"Processed sheet: {sheet_name} with {len(records)} rows.")

    # Save to JSON
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)
        
    print(f"Successfully saved to {output_file}")

except Exception as e:
    print(f"Error: {e}")
