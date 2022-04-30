import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class SessionSerializer extends RESTSerializer.extend(EmbeddedRecordsMixin) {
  attrs = {
    userId: 'user_id',
    expireAt: 'expire_at',
  };
};
