import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class PeriodicComponent extends Component {
  @service store;
  @service notify;

  @tracked periodic = "day_of_week";
  @tracked notifyNew = null;
  @tracked acceptorList = null;
  @tracked time = null;

  constructor(owner, args) {
    super(owner, args);
    this.notifyNew = this.args.notifyNew;
    this.time = new Date();
    this.acceptorList = this.store.createRecord('acceptor-list', {});
  }

  @action
  onSelectTime() {}

  @action
  onTimeReady(_selectedDates, _dateStr, instance) {
    this.flatpickrTimeRef = instance;
  }

  @action
  onChangePeriodic(value) {
    this.notifyNew['periodic'] = value;
  }

  @action
  onChangeWeekDay(value) {
    this.notifyNew['dayOfWeek'] = value;
  }

  validate() {
    let isValid = true;
    let acceptors = [];

    this.acceptorList.acceptor.map(item => { acceptors.push(item.id) })

    this.notifyNew['acceptors'] = acceptors;
    this.notifyNew['time'] = this.flatpickrTimeRef.latestSelectedDateObj.getTime();

    if (acceptors.length == 0) {
      isValid = false;
    }
    if (!this.notifyNew['periodic']) {
      isValid = false;
    }
    if (this.notifyNew['periodic'] ==='day_of_week') {
      if (!this.notifyNew['dayOfWeek']) {
        isValid = false;
      }
    }
    if (!this.notifyNew['name']) {
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
    this.notifyNew.save()
    this.args.onComplete();
  }

}
