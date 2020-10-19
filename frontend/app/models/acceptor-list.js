import Model, { attr, hasMany } from '@ember-data/model'; 

export default class AcceptorAddModel extends Model {
  @hasMany acceptor
}
