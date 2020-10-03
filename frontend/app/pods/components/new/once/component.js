import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';


export default class OnceComponent extends Component {

  constructor(owner, args) {
    super(owner, args);
    this.date = new Date();
  }

  @action
  onSelectDate(){
    console.log('select date')
  }

  @action
  onSelectTime(){
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
  onComplete(){ 
    console.log('create once notify and send to server');
  }

}
