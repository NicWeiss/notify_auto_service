import Model, { attr } from '@ember-data/model';

export default class AcceptorModel extends Model {
  @attr('string') name
  @attr('number') systemId
  @attr('string') account
  @attr('boolean') isDisabled
  @attr('boolean') isSystem
}
