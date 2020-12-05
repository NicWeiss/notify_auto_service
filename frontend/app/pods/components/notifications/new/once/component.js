import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class OnceComponent extends Component {
  @service store;
  @service notify;

  @tracked flatpickrDateRef = null;
  @tracked flatpickrTimeRef = null;

  constructor(owner, args) {
    super(owner, args);
    if (!this.args.notifyNew.id) {
      this.time = new Date();
      this.date = new Date();
      this.args.notifyNew.periodic = 'once';
    } else {
      this.time = new Date(Number(this.args.notifyNew.time));
      this.date = new Date(Number(this.args.notifyNew.date));
    }
  }

  @action
  onSelectDate() {
    this.args.notifyNew.date = this.flatpickrDateRef.latestSelectedDateObj.getTime();
  }

  @action
  onSelectTime() {
    this.args.notifyNew.time = this.flatpickrTimeRef.latestSelectedDateObj.getTime();
  }

  @action
  onDateReady(_selectedDates, _dateStr, instance) {
    this.flatpickrDateRef = instance;
    this.args.notifyNew.date = this.flatpickrDateRef.latestSelectedDateObj.getTime();
  }
  @action
  onTimeReady(_selectedDates, _dateStr, instance) {
    this.flatpickrTimeRef = instance;
    this.args.notifyNew.time = this.flatpickrTimeRef.latestSelectedDateObj.getTime();
  }


  validate() {
    let isValid = true;

    this.args.notifyNew.timeZoneOffset = this.flatpickrTimeRef.latestSelectedDateObj.getTimezoneOffset() / 60;

    if (this.args.notifyNew.acceptorsList.length == 0) {
      isValid = false;
    }
    if (!this.args.notifyNew.date) {
      isValid = false;
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
