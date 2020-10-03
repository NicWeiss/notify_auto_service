import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class PeriodicComponent extends Component {
  @tracked periodic = "day_of_week";
  @tracked weekDay = "1";

  constructor(owner, args) {
    super(owner, args);
    this.date = new Date();
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
