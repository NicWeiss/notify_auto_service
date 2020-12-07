import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { PERIODIC_SELECT, PERIDOIC_TYPES_NEED_DAY, WEEK_SELECT } from 'frontend/constants';
import { PERIODIC } from '../../../../constants';


export default class NewComponent extends Component {
  @service store;
  @service notify;

  PERIODIC_SELECT = PERIODIC_SELECT;
  PERIDOIC_TYPES_NEED_DAY = PERIDOIC_TYPES_NEED_DAY;
  WEEK_SELECT = WEEK_SELECT;

  @tracked flatpickrDateRef = null;
  @tracked flatpickrTimeRef = null;
  @tracked notifyNew = null;
  @tracked isDate = false;

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
      this.date = new Date(Number(this.notifyNew.date));
      this.time = new Date(Number(this.notifyNew.time));
    }
  }

  @action
  onSelectDate() {
    this.notifyNew.date = this.flatpickrDateRef.latestSelectedDateObj.getTime();
  }

  @action
  onSelectTime() {
    this.notifyNew.time = this.flatpickrTimeRef.latestSelectedDateObj.getTime();
  }

  @action
  onDateReady(_selectedDates, _dateStr, instance) {
    this.flatpickrDateRef = instance;
    this.notifyNew.date = this.flatpickrDateRef.latestSelectedDateObj.getTime();
  }
  @action
  onTimeReady(_selectedDates, _dateStr, instance) {
    this.flatpickrTimeRef = instance;
    this.notifyNew.time = this.flatpickrTimeRef.latestSelectedDateObj.getTime();
  }

  @action
  onChangePeriodic(value) {
    this.notifyNew.periodic = value;
    this.isDate = PERIDOIC_TYPES_NEED_DAY.includes(value);
  }

  @action
  onChangeWeekDay(value) {
    this.notifyNew.dayOfWeek = value;
  }

  validate() {
    let isValid = true;

    this.notifyNew.timeZoneOffset = this.flatpickrTimeRef.latestSelectedDateObj.getTimezoneOffset() / 60;

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
    if (!this.validate()) {
      return;
    }
    this.notifyNew.save()
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
