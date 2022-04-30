import Model, { attr } from '@ember-data/model';

export default class SessionModel extends Model {
  @attr userId
  @attr session
  @attr expireAt
  @attr location
  @attr client
}
