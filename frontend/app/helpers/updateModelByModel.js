import { helper } from '@ember/component/helper';


export function updateModelByModel(target, source) {
  target.eachAttribute(function (key, meta) {
    if (source[key]) {
      target[key] = source[key];
    }
  });

  return target;
}

export default helper(updateModelByModel);
