import RESTAdapter from '@ember-data/adapter/rest';

export default RESTAdapter.extend({
   // don't pluralize, just return type as-is
   pathForType: function(type) {
    return type;
  },
  namespace: 'api'
  //host: 'https://private-c2f4d-nicweiss.apiary-mock.com/'
});

