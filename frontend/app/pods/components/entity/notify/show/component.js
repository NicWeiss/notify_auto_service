import Component from '@glimmer/component';
import { action } from '@ember/object';
import { tracked } from '@glimmer/tracking';

import { INTERVAL_SLUG, PERIODIC, WEEK } from 'frontend/constants';

export default class NotificationsShowComponent extends Component {
  INTERVAL_SLUG = INTERVAL_SLUG
  PERIODIC = PERIODIC
  WEEK = WEEK

  @tracked notify = null;
  @tracked acceptorList = null;

  constructor(owner, args) {
    super(owner, args);
    this.notify = this.args.model;

    this.setAcceptorList();
  }

  async setAcceptorList() {
    if (!this.notify) return
    let list = await this.notify.get('acceptors');
    this.acceptorList = list.map(item => { return item.name }).join(", ")
  }

  @action
  onEdit() {
    this.args.onEdit(this.args.model);
  }

  @action
  onClose() {
    this.args.onClose();
  }
}
