import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';


export default class FromTimestamp extends Component {
  @tracked output = null;
  @tracked timestamp = null;
  tagName = '';

  init() {
    super.init(...arguments);
  }

  didReceiveAttrs() {
    let date = new Date(this.timestamp * 1000);
    this.output = date.toLocaleString()
  }
}
