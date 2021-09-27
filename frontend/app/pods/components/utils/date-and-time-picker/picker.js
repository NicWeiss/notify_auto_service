import DaysTemplate from './view/days';
import EmptyTemplate from './view/empty';
import MonthsTemplate from './view/months';
import YearsTemplate from './view/years';
import TimeTemplate from './view/time';


const TEMPLATES = {
  'empty': EmptyTemplate,
  'days': DaysTemplate,
  'months': MonthsTemplate,
  'years': YearsTemplate,
  'time': TimeTemplate,
};


export default class Picker {

  constructor(date, type, inputId, callback) {
    date = date?.slice(0, 16) || new Date().toTimeString();
    this.updateDate(date);
    this.inputId = inputId;
    this.type = type;
    this.callback = callback || function () { };
    this.canShow = true;
  }

  show() {
    if (!this.canShow) {
      return;
    }

    if (this.type !== 'time') {
      this.buildPicker('days');
    } else {
      this.buildPicker('empty');
    }

    const input = document.getElementById(this.inputId);
    const cover = document.getElementById('datetime-input-cover');
    const parentRect = input.getBoundingClientRect();

    cover.style.height = `${parentRect.height}px`;

    if (this.pickerContainer.style.display === 'block') {
      this.pickerContainer.style.display = 'none';
    } else {
      this.pickerContainer.style.display = 'block';
    }

    this.pickerContainer.style.top = `${parentRect.top + document.documentElement.scrollTop}px`;
    this.pickerContainer.style.left = `${parentRect.left + document.documentElement.scrollLeft}px`;

    this.pickerContainer.style.position = input.style.position;

    this.scrollJump('datetime-picker__hours', 'datetime-picker__selected-hour');
    this.scrollJump('datetime-picker__minutes', 'datetime-picker__selected-minute');

    input.blur();
    this.pickerContainer.focus();
  }

  hide() {
    this.pickerContainer.style.display = 'none';
    this.canShow = false;
    setTimeout(function () {
      this.canShow = true;
    }.bind(this), 500);
  }

  buildPicker(templateType, display = 'none') {
    const body = document.getElementById('body');
    const cover = document.getElementById('datetime-input-cover') || document.createElement('div');
    const wrapper = document.createElement('div');
    const pickerExists = !!document.getElementById('pickerContainer');
    this.pickerContainer = document.getElementById('pickerContainer') || document.createElement('div');

    cover.id = 'datetime-input-cover';
    wrapper.classList.add('datetime-wrapper');
    this.pickerContainer.innerHTML = '';
    this.pickerContainer.tabIndex = 0;
    this.pickerContainer.classList.add('picker-container');
    this.pickerContainer.style.display = display;
    this.pickerContainer.appendChild(cover);

    let picker = TEMPLATES[templateType](this.date);
    if (templateType !== 'empty') {
      picker.classList.add('datetime-picker__calendar');
    }

    wrapper.appendChild(picker);

    if (templateType === 'days' || templateType === 'empty') {
      const timeType = templateType === 'empty' ? 'master' : 'slave';
      const timeContainerClass = `datetime-picker__time`;

      let time = document.getElementById(timeContainerClass);

      if (!time) {
        time = TEMPLATES['time'](this.dateTime);
        time.classList.add(timeContainerClass);
        time.classList.add(`datetime-picker__time-${timeType}`);
      }

      wrapper.appendChild(time);
    }

    this.pickerContainer.appendChild(wrapper);

    if (!pickerExists) {
      this.pickerContainer.id = 'pickerContainer';
      document.body.insertBefore(this.pickerContainer, body);
    }

    this.addEventListeners(templateType);
  }

  updateDatePart(templateType) {
    let calendar = document.getElementsByClassName('datetime-picker__calendar')[0];
    let picker = TEMPLATES[templateType](this.date);

    picker.classList.add('datetime-picker__calendar');
    calendar.replaceWith(picker);

    this.addEventListeners(templateType);
  }

  scrollJump(parentClass, childrenClass) {
    const parent = document.getElementsByClassName(parentClass)[0];
    const children = document.getElementsByClassName(childrenClass)[0];

    if (!parent || !children) {
      return;
    }

    const parentRect = parent.getBoundingClientRect()
    const childrenRect = children.getBoundingClientRect()
    parent.scrollTo(0, (childrenRect.top - parentRect.top) - (parentRect.height / 2));
  }

  onSelectDay(day) {
    const splitedDate = day.getAttribute('data-date').split("-");

    this.dateTime[0] = splitedDate[0];
    this.dateTime[1] = splitedDate[1];
    this.dateTime[2] = splitedDate[2];

    this.updateDate();
    this.updateDatePart('days')
    this.sendCallback();
  }

  onSelectMonth(month) {
    this.dateTime[1] = parseInt(month.getAttribute('data-month'));

    this.checkLastDayOverflow();
    this.updateDate();
    this.buildPicker('days', 'block');
    this.sendCallback();
  }

  onSelectYear(year) {
    this.dateTime[0] = parseInt(year.getAttribute('data-year'));

    this.checkLastDayOverflow();
    this.updateDate();
    this.buildPicker('months', 'block');
    this.sendCallback();
  }

  onSelectTime(node) {
    if (node.getAttribute('data-type') === 'hour') {
      const hours = document.getElementsByClassName('datetime-picker__hour-item');

      for (let i = 0; i < hours.length; i++) {
        hours[i].classList.remove('datetime-picker__selected-hour');
      }

      this.dateTime[3] = parseInt(node.getAttribute('data-value'));
      node.classList.add('datetime-picker__selected-hour')
    }

    if (node.getAttribute('data-type') === 'minute') {
      const minutes = document.getElementsByClassName('datetime-picker__minute-item');

      for (let i = 0; i < minutes.length; i++) {
        minutes[i].classList.remove('datetime-picker__selected-minute');
      }

      this.dateTime[4] = parseInt(node.getAttribute('data-value'));
      node.classList.add('datetime-picker__selected-minute')
    }

    this.updateDate();
    this.sendCallback();
  }

  updateDate(date) {
    if (date) {
      date = new Date(date);

      if (date == 'Invalid Date') {
        date = new Date();
      }

      date = this.dateTime = [
        date.getFullYear(),
        date.getMonth(),
        date.getDate(),
        date.getHours(),
        date.getMinutes()
      ]
    }

    this.date = new Date(this.dateTime[0], this.dateTime[1], this.dateTime[2], this.dateTime[3], this.dateTime[4]);
  }

  changeMonth(type) {
    if (type === 'next') {
      this.dateTime[1]++;

      if (this.dateTime[1] === 12) {
        this.dateTime[1] = 0;
        this.dateTime[0]++;
      }
    } else if (type === 'prevent') {
      this.dateTime[1]--;

      if (this.dateTime[1] === -1) {
        this.dateTime[1] = 11;
        this.dateTime[0]--;
      }
    }

    this.checkLastDayOverflow();
    this.updateDate();
    this.updateDatePart('days', 'block');
    this.sendCallback();
  }

  changeYear(type) {
    if (type === 'next') {
      this.dateTime[0]++;
    } else if (type === 'prevent') {
      this.dateTime[0]--;
    }

    this.checkLastDayOverflow();
    this.updateDate();
    this.updateDatePart('months', 'block');
    this.sendCallback();
  }

  changeDecade(type) {
    if (type === 'next') {
      this.dateTime[0] = parseInt(this.dateTime[0]) + 12;
    } else if (type === 'prevent') {
      this.dateTime[0] = parseInt(this.dateTime[0]) - 12;
    }

    this.checkLastDayOverflow();
    this.updateDate();
    this.updateDatePart('years', 'block');
  }

  checkLastDayOverflow() {
    const daysInMonth = new Date(this.dateTime[0], this.dateTime[1] + 1, 0).getDate();

    if (this.dateTime[2] > daysInMonth) {
      this.dateTime[2] = parseInt(daysInMonth);
    }
  }

  eventStop(event) {
    event.stopPropagation();
  }

  addEventListeners(type) {
    if (type === 'empty') {
      const hours = document.getElementsByClassName('datetime-picker__hour-item');
      const minutes = document.getElementsByClassName('datetime-picker__minute-item');

      for (let i = 0; i < hours.length; i++) {
        hours[i].addEventListener('click', this.onSelectTime.bind(this, hours[i]));
      }
      for (let i = 0; i < minutes.length; i++) {
        minutes[i].addEventListener('click', this.onSelectTime.bind(this, minutes[i]));
      }
    }

    if (type === 'days') {
      const days = document.getElementsByClassName('datetime-picker__day');
      const hours = document.getElementsByClassName('datetime-picker__hour-item');
      const minutes = document.getElementsByClassName('datetime-picker__minute-item');
      const prevent = document.getElementsByClassName('datetime-picker__previous-month')[0];
      const next = document.getElementsByClassName('datetime-picker__next-month')[0];
      const months = document.getElementsByClassName('datetime-picker__month-info')[0];

      for (let i = 0; i < days.length; i++) {
        days[i].addEventListener('click', this.onSelectDay.bind(this, days[i]));
      }
      for (let i = 0; i < hours.length; i++) {
        hours[i].addEventListener('click', this.onSelectTime.bind(this, hours[i]));
      }
      for (let i = 0; i < minutes.length; i++) {
        minutes[i].addEventListener('click', this.onSelectTime.bind(this, minutes[i]));
      }

      prevent.addEventListener('click', this.changeMonth.bind(this, 'prevent'));
      next.addEventListener('click', this.changeMonth.bind(this, 'next'));
      months.addEventListener('click', this.buildPicker.bind(this, 'months'));
    }

    if (type === 'months') {
      const months = document.getElementsByClassName('datetime-picker__month-item');
      const prevent = document.getElementsByClassName('datetime-picker__previous-year')[0];
      const next = document.getElementsByClassName('datetime-picker__next-year')[0];
      const years = document.getElementsByClassName('datetime-picker__years-info')[0];


      for (let i = 0; i < months.length; i++) {
        months[i].addEventListener('click', this.onSelectMonth.bind(this, months[i]));
      }

      prevent.addEventListener('click', this.changeYear.bind(this, 'prevent'));
      next.addEventListener('click', this.changeYear.bind(this, 'next'));
      years.addEventListener('click', this.buildPicker.bind(this, 'years'));
    }

    if (type === 'years') {
      const years = document.getElementsByClassName('datetime-picker__decade-item');
      const prevent = document.getElementsByClassName('datetime-picker__previous-decade')[0];
      const next = document.getElementsByClassName('datetime-picker__next-decade')[0];

      for (let i = 0; i < years.length; i++) {
        years[i].addEventListener('click', this.onSelectYear.bind(this, years[i]));
      }

      prevent.addEventListener('click', this.changeDecade.bind(this, 'prevent'));
      next.addEventListener('click', this.changeDecade.bind(this, 'next'));
    }

    this.pickerContainer.addEventListener('focusout', this.hide.bind(this));
    document.getElementById('datetime-input-cover').addEventListener('click', this.hide.bind(this));
    this.pickerContainer.addEventListener('click', this.eventStop.bind());
  }

  sendCallback() {
    const dateISO = `${this.dateTime[0]}`
      + `-${`0${parseInt(this.dateTime[1]) + 1}`.slice(-2)}`
      + `-${`0${parseInt(this.dateTime[2])}`.slice(-2)}`
      + `T${`0${parseInt(this.dateTime[3])}`.slice(-2)}`
      + `:${`0${parseInt(this.dateTime[4])}`.slice(-2)}`;

    this.callback(dateISO);
  }

}
