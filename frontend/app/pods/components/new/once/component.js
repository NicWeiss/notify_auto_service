import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';


export default class OnceComponent extends Component {
  @service store;

  @tracked flatpickrDateRef = null;
  @tracked flatpickrTimeRef = null;

  @tracked notifyNew = null;
  @tracked acceptorList = null;
  @tracked data = null;
  @tracked time = null;

  constructor(owner, args) {
    super(owner, args);
    this.notifyNew = this.args.notifyNew;
    this.time = new Date();
    this.date = new Date();
    this.notifyNew['periodic'] = 'once';
    this.acceptorList = this.store.createRecord('acceptor-list', {});
  }

  @action
  onSelectDate(){}

  @action
  onSelectTime(){}

  @action
  onDateReady(_selectedDates, _dateStr, instance) {
    this.flatpickrDateRef = instance;
  }
  @action
  onTimeReady(_selectedDates, _dateStr, instance) {
    this.flatpickrTimeRef = instance;
  }


  validate() {
    let isValid = true;
    let acceptors = [];

    this.acceptorList.acceptor.map(item => { acceptors.push(item.id) })

    this.notifyNew['acceptors'] = acceptors;
    this.notifyNew['time'] = this.flatpickrTimeRef.latestSelectedDateObj.getTime();
    this.notifyNew['date'] = this.flatpickrDateRef.latestSelectedDateObj.getTime();

    if (acceptors.length == 0) {
      isValid = false;
    }
    if (!this.notifyNew['date']) {
      isValid = false;
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
