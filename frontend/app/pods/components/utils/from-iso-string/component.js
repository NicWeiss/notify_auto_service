import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';


export default class FromIsoString extends Component {
  @tracked output = null;
  @tracked isoString = null;
  tagName = '';

  init() {
    super.init(...arguments);
  }

  didReceiveAttrs() {
    let date = new Date(this.isoString);
    this.output = date.toLocaleString()
  }
}
