import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class PeriodicComponent extends Component {
  @service store;

  @tracked periodic = "day_of_week";
  @tracked weekDay = "1";
  @tracked acceptorList = null;

  constructor(owner, args) {
    super(owner, args);
    this.date = new Date();
    this.acceptorList = this.store.createRecord('acceptor-list', {});
  }

  @action
  onSelectDate() {
    console.log('select date')
  }

  @action
  onSelectTime() {
    console.log('select time')
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
    this.periodic = value;
  }

  @action
  onChangeWeekDay (value) {
    this.weekDay = value;
  }

  @action
  onComplete() {
    console.log(this.periodic, this.weekDay);
    console.log('create periodic notify and send to server');
  }

}
