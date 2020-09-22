import Service from '@ember/service';
import { computed } from '@ember/object';

export default Service.extend({

  toApi: async function (method, data = null) {
    return new Promise((resolves, rejects) => {
      const url = 'http://notifier.lo/api/' + method

      const request = new XMLHttpRequest()
      request.open('POST', url, false)

      request.onload = () => (request.status == 200) ?
        resolves(JSON.parse(request.response)) :
        rejects(request.status)
      request.send(JSON.stringify(data));
    })
  }

})