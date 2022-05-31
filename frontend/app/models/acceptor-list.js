import Model, { attr, hasMany } from '@ember-data/model';

export default class AcceptorListModel extends Model {
  @hasMany acceptor
}
