import requests
from bs4 import BeautifulSoup
import json
import re

url = "http://moc.sped.fazenda.pr.gov.br/Leiaute.html"

try:
    response = requests.get(url)
    response.raise_for_status()
    soup = BeautifulSoup(response.content, 'html.parser')

    layout_data = []
    
    # Find all tables
    tables = soup.find_all('table')
    
    for table in tables:
        # Try to find a preceding header for the group
        group = "Geral"
        prev = table.find_previous_sibling()
        if prev and prev.name in ['h1', 'h2', 'h3', 'h4', 'p', 'div']:
            group = prev.get_text(strip=True)

        rows = table.find_all('tr')
        for row in rows:
            cols = row.find_all('td')
            # We expect around 10 columns based on the description
            # #, ID, Campo, Descrição, Ele, Pai, Tipo, Ocor., Tam., Obs.
            if len(cols) >= 9:
                item = {
                    "id": cols[1].get_text(strip=True),
                    "campo": cols[2].get_text(strip=True),
                    "descricao": cols[3].get_text(strip=True),
                    "ele": cols[4].get_text(strip=True),
                    "pai": cols[5].get_text(strip=True),
                    "tipo": cols[6].get_text(strip=True),
                    "ocor": cols[7].get_text(strip=True),
                    "tam": cols[8].get_text(strip=True),
                    "obs": cols[9].get_text(strip=True) if len(cols) > 9 else ""
                }
                # Filter out header rows that might have been caught
                if item["id"] != "ID": 
                    layout_data.append(item)

    with open('c:/xampp/htdocs/utool/data/nfe_layout.json', 'w', encoding='utf-8') as f:
        json.dump(layout_data, f, ensure_ascii=False, indent=4)

    print(f"Successfully extracted {len(layout_data)} layout items.")

except Exception as e:
    print(f"Error: {e}")
