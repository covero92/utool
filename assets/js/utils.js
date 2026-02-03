function filterTable(tableId, query) {
    const table = document.getElementById(tableId);
    const tr = table.getElementsByTagName('tr');
    const filter = query.toLowerCase();

    for (let i = 1; i < tr.length; i++) { // Start from 1 to skip header
        let visible = false;
        const tds = tr[i].getElementsByTagName('td');
        for (let j = 0; j < tds.length; j++) {
            if (tds[j]) {
                const txtValue = tds[j].textContent || tds[j].innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    visible = true;
                    break;
                }
            }
        }
        tr[i].style.display = visible ? "" : "none";
    }
}
