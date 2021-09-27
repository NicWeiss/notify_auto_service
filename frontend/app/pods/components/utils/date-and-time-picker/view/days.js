import leftArrow from '../assets/left-arrow';
import rightArrow from '../assets/right-arrow';


const DaysTemplate = function (date) {
  let container = document.createElement('div');

  container.appendChild(createHeader(date));
  container.appendChild(createCalendar(date));

  return container;
}

function createHeader(date) {
  const MONTH_NAMES = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май',
    'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
  const year = date.getFullYear();
  const month = date.getMonth();

  let header = document.createElement('div');
  let monthInfo = document.createElement('div');
  let monthControl = document.createElement('div');
  let previous = document.createElement('div');
  let next = document.createElement('div');

  previous.innerHTML = leftArrow;
  previous.classList.add('datetime-picker__previous-month');
  previous.classList.add('datetime-picker__control-button');
  next.innerHTML = rightArrow;
  next.classList.add('datetime-picker__next-month');
  next.classList.add('datetime-picker__control-button');
  monthInfo.classList.add('datetime-picker__month-info')
  monthControl.classList.add('datetime-picker__month-control')
  header.classList.add('datetime-picker__header')

  monthInfo.innerText = `${MONTH_NAMES[month]} (${year}г.)`;
  monthControl.appendChild(previous);
  monthControl.appendChild(next);
  header.appendChild(monthInfo);
  header.appendChild(monthControl);

  return header;
}

function createCalendar(date) {
  const DAYS_IN_TABLE = 43;
  let calendar = [[], [], [], [], [], []];
  let week = 0;
  let table = document.createElement('table');
  const selectedDay = date.getDate();
  const year = date.getFullYear();
  const month = date.getMonth();
  const dayOfWeek = new Date(year, month, 1).getDay() === 0 ? 7 : new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const daysInPreviousMonth = new Date(year, month, 0).getDate()

  for (let i = 1; i < DAYS_IN_TABLE; i++) {
    let localMonth = month;
    let localYear = year;
    let localday = (i >= dayOfWeek) && (i - dayOfWeek + 1 <= daysInMonth) ? i - dayOfWeek + 1 : '0';
    let isCurrentMonth = true;

    week = Math.trunc(i / 7.1);

    if (i < dayOfWeek) {
      localMonth--;
      localday = daysInPreviousMonth - dayOfWeek + i + 1;
      isCurrentMonth = false;
    }
    if (i - dayOfWeek + 1 > daysInMonth) {
      localMonth++;
      localday = i - dayOfWeek + 1 - daysInMonth;
      isCurrentMonth = false;
    }

    if (localMonth === -1) {
      localYear--;
      localMonth = 11;
    }
    if (localMonth === 12) {
      localYear++;
      localMonth = 0;
    }

    calendar[week].push(
      {
        'day': localday,
        'month': localMonth,
        'year': localYear,
        'isSelected': false,
        'isCurrentMonth': isCurrentMonth
      }
    )
  }

  calendar.forEach(week => {
    let tr = document.createElement('tr');

    week.forEach(item => {
      let td = document.createElement('td');

      td.innerText = item.day;
      td.dataset.date = `${item.year}-${`00${item.month}`.slice(-2)}-${`00${item.day}`.slice(-2)}`;

      if (item.isCurrentMonth) {
        td.classList.add('datetime-picker__current-month');
      }
      if (item.day === selectedDay && item.month === month) {
        td.classList.add('datetime-picker__selected-day');
      }

      td.classList.add('datetime-picker__day')
      tr.appendChild(td);
    });
    table.appendChild(tr);
  });

  return table;
}

export default DaysTemplate;
