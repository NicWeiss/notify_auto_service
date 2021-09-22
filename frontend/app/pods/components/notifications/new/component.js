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
    const date = this.notifyNew.date || `${new Date().getUTCFullYear()}-01-01`

    const restoredDate = new Date(`${date}T${time}Z`);

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
    const date = this.flatpickrDateRef ? this.flatpickrDateRef.latestSelectedDateObj : new Date();
    const time = this.flatpickrTimeRef.latestSelectedDateObj;
    let dateTime = new Date(
      (parseInt(date.getMonth()) + 1) + '.' + date.getDate() + '.' + date.getFullYear() + ' ' +
      time.getHours() + ':' + time.getMinutes());

    const days = dateTime.getUTCDate() < 10 ? '0' + dateTime.getUTCDate() : dateTime.getUTCDate();
    const month = (parseInt(dateTime.getUTCMonth()) + 1) < 10 ? '0' + (parseInt(dateTime.getUTCMonth()) + 1) : (parseInt(dateTime.getUTCMonth()) + 1);
    const years = dateTime.getUTCFullYear();
    const hours = dateTime.getUTCHours() < 10 ? '0' + dateTime.getUTCHours() : dateTime.getUTCHours();
    const minutes = dateTime.getUTCMinutes() < 10 ? '0' + dateTime.getUTCMinutes() : dateTime.getUTCMinutes();

    this.notifyNew.date = `${years}-${month}-${days}`;
    this.notifyNew.time = `${hours}:${minutes}`;
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
