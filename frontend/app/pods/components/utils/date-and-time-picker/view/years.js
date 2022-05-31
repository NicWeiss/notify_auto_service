import leftArrow from '../assets/left-arrow';
import rightArrow from '../assets/right-arrow';


const MonthsTemplate = function (date) {
  let container = document.createElement('div');

  container.appendChild(createHeader(date));
  container.appendChild(createCalendar(date));

  return container;
}

function createHeader(date) {
  const year = parseInt(date.getFullYear());

  let header = document.createElement('div');
  let info = document.createElement('div');
  let control = document.createElement('div');
  let previous = document.createElement('div');
  let next = document.createElement('div');

  previous.innerHTML = leftArrow;
  previous.classList.add('datetime-picker__previous-decade');
  previous.classList.add('datetime-picker__control-button');
  next.innerHTML = rightArrow;
  next.classList.add('datetime-picker__next-decade');
  next.classList.add('datetime-picker__control-button');
  info.classList.add('datetime-picker__decade-info')
  control.classList.add('datetime-picker__decade-control')
  header.classList.add('datetime-picker__header')


  info.innerText = `${year - 6} - ${year + 5}`;
  control.appendChild(previous);
  control.appendChild(next);
  header.appendChild(info);
  header.appendChild(control);

  return header;
}

function createCalendar(date) {
  const year = parseInt(date.getFullYear()) - 6;
  const rows = 3;
  const cells = 4;
  let index = 0;
  let table = document.createElement('table');

  for (let i = 0; i < rows; i++) {
    let tr = document.createElement('tr');

    for (let j = 0; j < cells; j++) {
      let td = document.createElement('td');

      td.innerText = year + index;
      td.classList.add('datetime-picker__decade-item');
      td.dataset.year = `${year + index}`;
      index++;
      tr.appendChild(td);
    }
    table.appendChild(tr);
  }

  return table;
}

export default MonthsTemplate;
