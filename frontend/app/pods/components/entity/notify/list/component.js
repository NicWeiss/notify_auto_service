import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

import { PERIODIC, WEEK } from 'frontend/constants';

export default class NotificationsComponent extends Component {
  @service notify;

  @tracked itemForModal = null;
  @tracked isShowDeleteModal = false;
  @tracked isShowEditModal = false;
  @tracked isShowShowModal = false;
  @tracked categoryId = false;

  PERIODIC = PERIODIC
  WEEK = WEEK

  constructor(owner, args) {
    super(owner, args);
    this.categoryId = this.args.categoryId || false;
  }

  @action
  onShow(notify) {
    this.itemForModal = notify;
    this.isShowShowModal = true;
  }


  @action
  onAdd() {
    this.onClose();
    this.itemForModal = null;
    this.isShowEditModal = true;
  }

  @action
  onEdit(notify) {
    this.onClose();
    this.itemForModal = notify;
    this.isShowEditModal = true;
  }

  @action
  onComplete(isNotifyAlreadyExist) {
    this.onClose();
    let isListEmpty = this.checkListNotEmpty();

    if (isNotifyAlreadyExist && !isListEmpty) {
      return;
    }

    this.args.reloadModel();
  }

  @action
  onChangeStatus(notify) {
    notify.isDisabled = notify.isDisabled == 0 ? 1 : 0;
    notify.save();
  }

  @action
  onDeleteNotify(notify) {
    this.itemForModal = notify;
    this.isShowDeleteModal = true;
  }

  @action
  onCompleteDeleting() {
    if (this.checkListNotEmpty()) {
      this.args.reloadModel();
    }
  }

  @action
  onClose() {
    this.isShowDeleteModal = false;
    this.isShowEditModal = false;
    this.isShowShowModal = false;
  }

  checkListNotEmpty() {
    let isListEmpty = true;
    this.args.model.forEach((item) => {
      if (item.categoryId == this.args.categoryId && !item.isDeleted) {
        isListEmpty = false;
      }
    })

    return isListEmpty;
  }
}
