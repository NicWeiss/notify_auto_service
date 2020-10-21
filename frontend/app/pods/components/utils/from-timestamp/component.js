import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';


export default class FromTimestamp extends Component {
  @tracked output = null;
  @tracked type = null;
  @tracked timestamp = null;

  constructor(owner, args) {
    super(owner, args);

    this.type = this.args.type;
    this.timestamp = this.args.timestamp;

    var date = new Date();
    date.setTime(this.timestamp);
    
    if (!this.timestamp) {
      return;
    }

    if (this.type === 'date') {
      let day = date.getDate() < 10 ? `0${date.getDate()}` : date.getDate();
      let month = (date.getMonth()+1) < 10 ? `0${date.getMonth()+1}` : (date.getMonth()+1);
      this.output = `${day}.${month}.${date.getFullYear()}`;
    }

    if (this.type === 'time') {
      let hours = date.getHours() < 10 ? `0${date.getHours()}` : date.getHours();
      let minutes = date.getMinutes() < 10 ? `0${date.getMinutes()}` : date.getMinutes();
      this.output = `${hours}:${minutes}`;
    }

  }
}