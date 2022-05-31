import leftArrow from '../assets/left-arrow';
import rightArrow from '../assets/right-arrow';


const MONTH_NAMES = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май',
  'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];


const MonthsTemplate = function (date) {
  let container = document.createElement('div');

  container.appendChild(createHeader(date));
  container.appendChild(createCalendar());

  return container;
}

function createHeader(date) {
  const year = date.getFullYear();
  const month = date.getMonth();

  let header = document.createElement('div');
  let info = document.createElement('div');
  let control = document.createElement('div');
  let previous = document.createElement('div');
  let next = document.createElement('div');

  previous.innerHTML = leftArrow;
  previous.classList.add('datetime-picker__previous-year');
  previous.classList.add('datetime-picker__control-button');
  next.innerHTML = rightArrow;
  next.classList.add('datetime-picker__next-year');
  next.classList.add('datetime-picker__control-button');
  info.classList.add('datetime-picker__years-info')
  control.classList.add('datetime-picker__years-control')
  header.classList.add('datetime-picker__header')


  info.innerText = `${year}г.`;
  control.appendChild(previous);
  control.appendChild(next);
  header.appendChild(info);
  header.appendChild(control);

  return header;
}

function createCalendar() {
  const rows = 3;
  const cells = 4;
  let index = 0;
  let table = document.createElement('table');

  for (let i = 0; i < rows; i++) {
    let tr = document.createElement('tr');

    for (let j = 0; j < cells; j++) {
      let td = document.createElement('td');

      td.innerText = MONTH_NAMES[index];
      td.classList.add('datetime-picker__month-item');
      td.dataset.month = `${index}`;
      index++;
      tr.appendChild(td);
    }
    table.appendChild(tr);
  }

  return table;
}

export default MonthsTemplate;
