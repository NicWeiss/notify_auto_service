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
    const dateISO = this.flatpickrDateRef ? this.flatpickrDateRef.latestSelectedDateObj : new Date();
    const timeISO = this.flatpickrTimeRef.latestSelectedDateObj;

    const days = dateISO.getUTCDate() < 10 ? '0' + dateISO.getUTCDate() : dateISO.getUTCDate();
    const month = (parseInt(dateISO.getUTCMonth()) + 1) < 10 ? '0' + (parseInt(dateISO.getUTCMonth()) + 1) : (parseInt(dateISO.getUTCMonth()) + 1);
    const years = dateISO.getUTCFullYear();
    const hours = timeISO.getUTCHours() < 10 ? '0' + timeISO.getUTCHours() : timeISO.getUTCHours();
    const minutes = timeISO.getUTCMinutes() < 10 ? '0' + timeISO.getUTCMinutes() : timeISO.getUTCMinutes();

    this.notifyNew.date = month + '.' + days + '.' + years;
    this.notifyNew.time = hours + ':' + minutes;
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
