import Model, { attr } from '@ember-data/model'; 

export default class AcceptorAddModel extends Model {
  @attr name
  @attr system
  @attr account
  @attr status
  @attr type
}
