import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';


export default class FromTimestamp extends Component {
  @tracked output = null;
  @tracked type = null;
  @tracked date = null;
  @tracked time = null;
  tagName = '';

  init() {
    super.init(...arguments);
  }

  didReceiveAttrs() {
    let outTime;
    let outDate;

    this.type;

    const time = this.time || '00:00';
    const date = this.date || '01.01.1970'
    const restoredDate = new Date(date + ' ' + time + ' GMT-0');
    if (this.date) {
      let d = restoredDate.getDate() < 10 ? '0' + restoredDate.getDate() : restoredDate.getDate();
      let m = (parseInt(restoredDate.getMonth()) + 1) < 10 ? '0' + (parseInt(restoredDate.getMonth()) + 1) : (parseInt(restoredDate.getMonth()) + 1);
      let y = restoredDate.getFullYear()
      outDate = d + '.' + m + '.' + y;
    } else {
      outDate = '';
    }

    let h = restoredDate.getHours() < 10 ? '0' + restoredDate.getHours() : restoredDate.getHours();
    let m = restoredDate.getMinutes() < 10 ? '0' + restoredDate.getMinutes() : restoredDate.getMinutes();

    outTime = h + ':' + m;

    if (this.type === 'date') {
      this.output = outDate;
    }

    if (this.type === 'time') {
      this.output = outTime;
    }

  }
}
