const TimeTemplate = function (date, type = 'slave') {
  let container = document.createElement('div');
  let hours = document.createElement('div');
  let minutes = document.createElement('div');

  hours.classList.add('datetime-picker__hours');
  minutes.classList.add('datetime-picker__minutes');

  for (let i = 0; i < 24; i++) {
    let hour = document.createElement('div');

    hour.id = `datetime-picker__hour-${i}`;
    hour.dataset.type = 'hour';
    hour.dataset.value = i;
    hour.classList.add('datetime-picker__hour-item');

    if (date[3] === i) {
      hour.classList.add('datetime-picker__selected-hour');
    }

    hour.innerText = i;
    hours.appendChild(hour);
  }

  for (let i = 0; i < 60; i++) {
    let minute = document.createElement('div');

    minute.id = `datetime-picker__minute-${i}`;
    minute.dataset.type = 'minute';
    minute.dataset.value = i;
    minute.classList.add('datetime-picker__minute-item');

    if (date[4] === i) {
      minute.classList.add('datetime-picker__selected-minute');
    }

    minute.innerText = i;
    minutes.appendChild(minute);
  }

  container.appendChild(hours);
  container.appendChild(minutes);

  return container;
}

export default TimeTemplate;
