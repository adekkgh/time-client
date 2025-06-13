<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Gioev Time Server</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 font-sans">
<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-4 text-center">📅 Получить записи по времени</h1>

    <form id="filterForm" class="space-y-4 flex flex-col">
        <div class="flex flex-col">
            <label>Начальная дата:</label>
            <input type="datetime-local" step="60" name="start_date" class="border p-2 rounded" required>
        </div>
        <div class="flex flex-col">
            <label>Конечная дата:</label>
            <input type="datetime-local" step="60" name="end_date" class="border p-2 rounded" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 max-w-[200px] self-center">
            🔍 Получить данные
        </button>
    </form>

    <div id="results" class="mt-6 flex flex-col"></div>
</div>

<script>
    const form       = document.getElementById('filterForm');
    const resultsDiv = document.getElementById('results');

    let currentPage  = 1;
    const perPage    = 10;
    let startDate, endDate;

    async function fetchEntries() {
        const url = `/api/v1/time-entries?` +
            `start_date=${startDate}&end_date=${endDate}` +
            `&page=${currentPage}&per_page=${perPage}`;

        const response = await fetch(url);
        const result   = await response.json();

        if ((!result.data || result.data.length === 0) && currentPage === 1) {
            resultsDiv.innerHTML =
                '<p class="text-gray-500">Нет данных за указанный период.</p>';
            return;
        }

        if (currentPage === 1) {
            resultsDiv.innerHTML = '<ul id="entryList" class="space-y-2"></ul>';
        }

        const list = document.getElementById('entryList');
        result.data.forEach(entry => {
            const li  = document.createElement('li');
            li.className = "p-3 bg-gray-200 rounded";
            li.textContent = entry.server_time;
            list.appendChild(li);
        });

        currentPage++;

        let btn = document.getElementById('loadMoreBtn');
        if (result.next_page_url) {
            if (!btn) {
                btn = document.createElement('button');
                btn.id = 'loadMoreBtn';
                btn.textContent = '⬇ Загрузить ещё';
                btn.className = 'mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 self-center';
                btn.onclick = fetchEntries;
                resultsDiv.appendChild(btn);
            }
        } else if (btn) {
            btn.remove();
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        currentPage = 1;
        startDate = encodeURIComponent(form.start_date.value);
        endDate   = encodeURIComponent(form.end_date.value);
        fetchEntries();
    });
</script>
</body>
</html>
