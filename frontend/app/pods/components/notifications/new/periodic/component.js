import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class PeriodicComponent extends Component {
  @service store;
  @service notify;

  @tracked periodic = "day_of_week";

  constructor(owner, args) {
    super(owner, args);
    this.time = new Date();
  }

  @action
  onSelectTime() {
    this.args.notifyNew.time = this.flatpickrTimeRef.latestSelectedDateObj.getTime();
  }

  @action
  onTimeReady(_selectedDates, _dateStr, instance) {
    this.flatpickrTimeRef = instance;
    this.args.notifyNew.time = this.flatpickrTimeRef.latestSelectedDateObj.getTime();
  }

  @action
  onChangePeriodic(value) {
    this.args.notifyNew.periodic = value;
  }

  @action
  onChangeWeekDay(value) {
    this.args.notifyNew.dayOfWeek = value;
  }

  validate() {
    let isValid = true;

    this.args.notifyNew.timeZoneOffset = this.flatpickrTimeRef.latestSelectedDateObj.getTimezoneOffset() / 60;

    if (this.args.notifyNew.acceptorsList.length === 0) {
      isValid = false;
    }
    if (!this.args.notifyNew.periodic) {
      isValid = false;
    }
    if (this.args.notifyNew.periodic === 'day_of_week') {
      if (!this.args.notifyNew.dayOfWeek) {
        isValid = false;
      }
    }
    if (!this.args.notifyNew.name) {
      isValid = false;
    }

    if (!isValid) {
      this.notify.error('Остались пустые поля');
    }
    return isValid;
  }

  @action
  onComplete() {
    if (!this.validate()) {
      return;
    }
    this.args.notifyNew.save()
    this.args.onComplete();
  }

}
