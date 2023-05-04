import Component from '@ember/component';
import { tracked } from '@glimmer/tracking';


export default class FancyType extends Component {
  @tracked type = null;
  @tracked systems = [];
  @tracked systemId = null;
  tagName = '';

  init() {
    super.init(...arguments);
  }

  didReceiveAttrs() {
    this.type = "unknown";

    this.systems.forEach(system => {
      if (system.id == this.systemId) {
        this.type = system.type;
      }
    });
  }
}
