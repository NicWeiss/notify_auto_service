import RESTSerializer from '@ember-data/serializer/rest';
import { EmbeddedRecordsMixin } from '@ember-data/serializer/rest';

export default class PostSerializer extends RESTSerializer.extend(EmbeddedRecordsMixin){
  attrs = {
    acceptorsList: { embedded: 'always' }
  };
};