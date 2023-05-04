import DS from 'ember-data';
import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';


export default class FancyType extends Component {
  @tracked pass = false;
  @tracked object = {};
  @tracked field = '';
  @tracked value = '';
  tagName = '';

  init() {
    super.init(...arguments);
  }

  didReceiveAttrs() {
    let value = null;

    if (this.object instanceof DS.Model) {
      value = this.object.get(this.field)
    } else {
      value = this.object[this.field]
    }

    if ( String(value) !== String(this.value)){
      this.pass = true;
    }
  }
}
