// FullCalendar の本体と表示プラグインを読み込む
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';

// 日本語ロケールを追加で読み込む（これが「ja」の部分）
import jaLocale from '@fullcalendar/core/locales/ja';

// ページの DOM がすべて読み込まれた後に実行される
document.addEventListener('DOMContentLoaded', () => {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  // Bladeのdata属性からイベントJSONを取得
  const eventsData = JSON.parse(calendarEl.dataset.events);

  // FullCalendarを初期化
  const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin],
    locale: jaLocale,
    initialView: 'dayGridMonth',
    events: eventsData,
    eventDisplay: 'auto',
  });

  calendar.render();
});
