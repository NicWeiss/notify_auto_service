import Model, { attr } from '@ember-data/model';

export default class AcceptorModel extends Model {
  @attr('string') name
  @attr('string') systemId
  @attr('string') account
  @attr('string') status
  @attr('string') type
  @attr('boolean') is_system
}
