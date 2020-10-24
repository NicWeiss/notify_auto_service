import Component from '@glimmer/component';
import { action } from '@ember/object';
import { tracked } from '@glimmer/tracking';

import { PERIODIC } from 'frontend/constants';

export default class ShowComponent extends Component {
  PERIODIC = PERIODIC

  @tracked acceptorList = null;

  constructor(owner, args) {
    super(owner, args);
    this.notify = this.args.model;
    this.setAcceptorList();
  }

  async setAcceptorList() {
    if (!this.args.model) return
    let list = await this.args.model.get('acceptorsList');
    this.acceptorList = list.map(item => {return item.name}).join(", ")
  }

  @action
  onClose() {
    this.args.onClose();
  }

}
