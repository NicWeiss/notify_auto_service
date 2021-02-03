import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { PERIODIC_SELECT, PERIDOIC_TYPES_NEED_DAY, WEEK_SELECT } from 'frontend/constants';


export default class NewComponent extends Component {
  @service store;
  @service notify

  PERIODIC_SELECT = PERIODIC_SELECT
  PERIDOIC_TYPES_NEED_DAY = PERIDOIC_TYPES_NEED_DAY
  WEEK_SELECT = WEEK_SELECT

  @tracked flatpickrDateRef = null
  @tracked flatpickrTimeRef = null
  @tracked notifyNew = null
  @tracked isDate = false
  @tracked queryParams = { 'status': 1 }
  @tracked date = null
  @tracked time = null

  constructor(owner, args) {
    super(owner, args);
    this.notifyNew = this.args.model;

    if (!this.notifyNew.id) {
      this.notifyNew = this.store.createRecord('notify');
      this.notifyNew.acceptorsList = [];
      this.notifyNew.status = '1';
      this.time = new Date();
      this.date = new Date();
    } else {
      this.restoreDateAndTime();

      if (PERIDOIC_TYPES_NEED_DAY.includes(this.notifyNew.periodic)) {
        this.isDate = true;
      }
    }
  }

  restoreDateAndTime() {
    const time = this.notifyNew.time;
    const date = this.notifyNew.date || '01.01.1970'
    const restoredDate = new Date(date + ' ' + time + ' GMT-0');

    if (this.notifyNew.date) {
      this.date = restoredDate.getDate() +
        '.' + (parseInt(restoredDate.getMonth()) + 1) +
        '.' + restoredDate.getFullYear();
    } else {
      this.date = new Date();
    }
    this.time = restoredDate;
  }

  prepareDateAndTime() {
    let dayCorrection = 0;
    const time = this.flatpickrTimeRef.latestSelectedDateObj;

    const gmt = -1 * time.getTimezoneOffset() / 60;
    const minutes = time.getMinutes() < 10 ? '0' + time.getMinutes() : time.getMinutes();
    let hours = time.getHours() < 10 ? '0' + time.getHours() : time.getHours();
    if (hours < gmt) {
      hours = (parseInt(hours) + 24) - gmt;
      dayCorrection = -1;
    } else {
      hours -= gmt;
    }

    this.notifyNew.time = hours + ':' + minutes;

    if (this.isDate) {
      const date = this.flatpickrDateRef.latestSelectedDateObj;

      const intDay = parseInt(date.getDate());
      const intMonth = parseInt(date.getMonth()) + 1;

      let day = intDay < 10 ? '0' + intDay : intDay;
      let month = intMonth < 10 ? '0' + intMonth : intMonth;
      let year = date.getFullYear();

      if (dayCorrection === (-1)) {

        if (intDay === 1) {
          if ((intMonth - 1) === 0) {
            month = 12;
            year--;
          } else {
            month = (intMonth - 1) < 10 ? '0' + (intMonth - 1) : (intMonth - 1);
          }

          day = new Date(year, month - 1, 0).getDate();
          day = day < 10 ? '0' + day : day;
        } else {
          day = intDay - 1 < 10 ? '0' + (intDay - 1) : intDay - 1;
        }
      }

      this.notifyNew.date = month + '.' + day + '.' + year;
    }
  }

  @action
  onSelectDate() {
  }

  @action
  onSelectTime() {
  }

  @action
  onDateReady(_selectedDates, _dateStr, instance) {
    this.flatpickrDateRef = instance;
  }
  @action
  onTimeReady(_selectedDates, _dateStr, instance) {
    this.flatpickrTimeRef = instance;
  }

  @action
  onChangePeriodic(value) {
    this.notifyNew.dayOfWeek = null;
    this.notifyNew.periodic = value;
    this.isDate = PERIDOIC_TYPES_NEED_DAY.includes(value);
  }

  @action
  onChangeWeekDay(value) {
    this.notifyNew.dayOfWeek = value;
  }

  validate() {
    let isValid = true;

    this.notifyNew.timeZoneOffset = -1 * this.flatpickrTimeRef.latestSelectedDateObj.getTimezoneOffset() / 60;

    if (this.notifyNew.acceptorsList.length === 0) {
      isValid = false;
    }
    if (!this.notifyNew.periodic) {
      isValid = false;
    }
    if (this.notifyNew.periodic === 'day_of_week') {
      if (!this.notifyNew.dayOfWeek) {
        isValid = false;
      }
    }
    if (!this.notifyNew.name) {
      isValid = false;
    }

    if (!this.isDate) {
      this.notifyNew.date = null;
    }

    if (!isValid) {
      this.notify.error('Остались пустые поля');
    }
    return isValid;
  }

  @action
  complete() {
    this.prepareDateAndTime();
    if (!this.validate()) {
      return;
    }
    this.notifyNew.save()
    this.notify.idDeleted = false;
    this.args.onComplete();
  }

  @action
  cancel() {
    if (!this.notifyNew.id) {
      this.notifyNew.destroyRecord();
    }
    this.args.onCancel();
  }
}
