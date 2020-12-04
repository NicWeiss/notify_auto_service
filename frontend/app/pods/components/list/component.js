import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { PERIODIC, WEEK } from 'frontend/constants';

export default class ListComponent extends Component {
  @service notify;

  @tracked isShowDeleteModal = false;
  @tracked itemForDelete = null;

  PERIODIC = PERIODIC
  WEEK = WEEK

  constructor(owner, args)  {
    super(owner, args)
  }

  @action
  onChangeStatus(record) {
    record.status = record.status == 0 ? 1 : 0;
    record.save();
  }

  @action
  onDeleteNotify(record) {
    this.itemForDelete = record;
    this.isShowDeleteModal = true;
  }

  @action
  onClose() {
    this.isShowDeleteModal = false;
    this.args.onRefresh();
  }

  @action
  row(){
    console.log('row');
  }
  @action
  col(id){
    console.log('col - ', id);
  }
}
