import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';
import { updateModelByModel } from 'frontend/helpers/updateModelByModel';

import { AUTODISABLE_STATES, PERIODIC_SELECT, PERIDOIC_TYPES_NEED_DAY, WEEK_SELECT } from 'frontend/constants';


export default class NewComponent extends Component {
  @service store;
  @service notify

  AUTODISABLE_STATES = AUTODISABLE_STATES
  PERIODIC_SELECT = PERIODIC_SELECT
  PERIDOIC_TYPES_NEED_DAY = PERIDOIC_TYPES_NEED_DAY
  WEEK_SELECT = WEEK_SELECT

  @tracked categories = {}
  @tracked notifyNew = null
  @tracked isDateTime = false
  @tracked queryParams = { 'only_enabled': true }
  @tracked date = null
  @tracked autodisableAt= null
  @tracked selectedDate = null
  @tracked isNotDisabled = true

  constructor(owner, args) {
    super(owner, args);
    this.notifyNew = this.args.model;

    if (!this.notifyNew?.id) {
      this.notifyNew = this.store.createRecord('notify');
      this.notifyNew.acceptors = [];
      this.notifyNew.isDisabled = false;
      this.notifyNew.categoryId = this.args.categoryId || 0;
      this.date = new Date();
    } else {
      this.isNotDisabled = !this.notifyNew.isDisabled;
      this.restoreDateAndTime();

      if (PERIDOIC_TYPES_NEED_DAY.includes(this.notifyNew.periodic)) {
        this.isDateTime = true;
      }
    }

    this.categories = this.store.peekAll('category');
  }

  restoreDateAndTime() {
    const time = this.notifyNew.time;
    const date = this.notifyNew.date || `${new Date().getUTCFullYear()}-01-01`

    this.date = new Date(`${date}T${time}Z`);
    if (this.notifyNew.autodisableAt) {
    this.autodisableAt = new Date(this.notifyNew.autodisableAt);
    } else {
      this.autodisableAt = new Date();
    }
  }

  prepareDateAndTime() {
    let dateTime = this.selectedDate;

    const days = dateTime.getUTCDate() < 10 ? '0' + dateTime.getUTCDate() : dateTime.getUTCDate();
    const month = (parseInt(dateTime.getUTCMonth()) + 1) < 10 ? '0' + (parseInt(dateTime.getUTCMonth()) + 1) : (parseInt(dateTime.getUTCMonth()) + 1);
    const years = dateTime.getUTCFullYear();
    const hours = dateTime.getUTCHours() < 10 ? '0' + dateTime.getUTCHours() : dateTime.getUTCHours();
    const minutes = dateTime.getUTCMinutes() < 10 ? '0' + dateTime.getUTCMinutes() : dateTime.getUTCMinutes();

    if (this.isDateTime) {
      this.notifyNew.date = `${years}-${month}-${days}`;
    }

    this.notifyNew.time = `${hours}:${minutes}`;
  }

  @action
  changeDisabled(value) {
    this.notifyNew.isDisabled = !this.notifyNew.isDisabled;
  }

  @action
  onSelectDate(date) {
    this.selectedDate = date;
  }

  @action
  onChangeAutodisable(value) {
    this.notifyNew.isAutodisable =  Boolean(Number(value));
  }

  @action
  onSelectDateAutodisable(date) {
    if (!date) {
      this.date = new Date();
    }

    this.notifyNew.autodisableAt = date.toISOString();
  }

  @action
  onChangePeriodic(value) {
    this.notifyNew.dayOfWeek = null;
    this.notifyNew.periodic = value;
    this.isDateTime = PERIDOIC_TYPES_NEED_DAY.includes(value);
  }

  @action
  onChangeCategory(categoryId) {
    this.notifyNew.categoryId = categoryId;
  }

  @action
  onChangeWeekDay(value) {
    this.notifyNew.dayOfWeek = value;
  }

  validate() {
    let isValid = true;

    if (this.notifyNew.acceptors.length === 0) {
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

    if (!this.isDateTime) {
      this.notifyNew.date = null;
    }

    if (!isValid) {
      this.notify.error('Остались пустые поля');
    }
    return isValid;
  }

  @action
  async complete() {
    this.prepareDateAndTime();
    if (!this.validate()) {
      return;
    }

    const isNew = !!this.notifyNew.id;
    if (this.args.model?.id) {
      updateModelByModel(this.args.model, this.notifyNew);
      await this.args.model.save();
    } else {
      await this.notifyNew.save();
    }
    // this.notify.idDeleted = false;

    this.args.onComplete(isNew);
  }

  @action
  cancel() {
    if (!this.notifyNew.id) {
      this.notifyNew.destroyRecord();
    }
    this.args.onCancel();
  }
}
