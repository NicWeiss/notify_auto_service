import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';


export default class FromTimestamp extends Component {
  @tracked output = null;
  @tracked type = null;
  @tracked item = null;
  tagName = '';

  init() {
    super.init(...arguments);
  }

  didReceiveAttrs() {
    let outTime;
    let outDate;

    this.item;
    this.type;

    if (!this.item) {
      return;
    }


    const time = this.item.time;
    const date = this.item.date || '01.01.1970'
    const restoredDate = new Date(date + ' ' + time + ' GMT-0');
    if (this.item.date) {
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
