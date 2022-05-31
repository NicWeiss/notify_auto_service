import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class CategorySerializer extends RESTSerializer.extend(EmbeddedRecordsMixin) {
  attrs = {
    userId: 'user_id',
    isHidden: 'is_hidden',
  };
};
