export function formatDataToUser(data) {
    // Если уже отформатированное время (HH:MM)
    if (typeof data === 'string' && /^\d{1,2}:\d{2}$/.test(data)) {
        return data;
    }

    let date;

    if (typeof data === 'number') {
        // timestamp в секундах или миллисекундах
        date = new Date(data > 9999999999 ? data : data * 1000);
    } else if (typeof data === 'string') {
        // пытаемся создать дату напрямую
        date = new Date(data);

        if (isNaN(date)) {
            // Удаляем лишние микросекунды — максимально 3 знака после точки
            let cleaned = data.replace(/\.(\d{3})\d+/, '.$1');

            // Убедимся, что заканчивается на 'Z' (UTC)
            if (!cleaned.endsWith('Z')) {
                cleaned += 'Z';
            }

            date = new Date(cleaned);

            if (isNaN(date)) {
                // Финальный запасной вариант — просто убрать дробную секунду полностью
                cleaned = data.replace(/\.\d+/, '');
                date = new Date(cleaned);
            }
        }
    } else {
        return 'Некорректный формат даты';
    }

    if (isNaN(date)) {
        console.error('Не удалось распарсить дату:', data);
        return 'Некорректная дата';
    }

    // Форматируем в 24-часовом формате, с ведущими нулями в часах и минутах
    return date.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
}

export function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
}
